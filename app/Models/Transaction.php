<?php
/**
 * Transaction model.
 *
 * Tracks the post-auction lifecycle of a sale:
 *   payment_pending -> payment_confirmed -> ready_for_handover -> completed
 *   (any state) -> cancelled
 *
 * One transaction per car (UNIQUE car_id). The car's `status` column remains
 * the source of truth for listing state (sold/closed); this table tracks the
 * deal/payment/handover lifecycle that happens AFTER a winner is determined.
 *
 * SECURITY: all queries are prepared statements. Read methods are scoped by
 * user_id so a buyer/seller only sees their own transactions (IDOR prevention
 * is enforced in the controller via ownership checks before any state change).
 */
final class Transaction extends BaseModel
{
    protected string $table = 'transactions';

    /** Find a transaction by its primary key, with related car/bidder/seller names. */
    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.title AS car_title, c.make AS car_make, c.model AS car_model,
                    c.year AS car_year, c.location AS car_location,
                    c.auction_end AS car_auction_end,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    seller.name AS seller_name, seller.email AS seller_email,
                    winner.name AS winner_name, winner.email AS winner_email
             FROM transactions t
             INNER JOIN cars c   ON c.id = t.car_id
             INNER JOIN users seller ON seller.id = t.seller_id
             INNER JOIN users winner ON winner.id = t.winner_id
             WHERE t.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Transactions where the given user is the winner (buyer's purchases). */
    public function purchasesByBuyer(int $winnerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.title AS car_title, c.auction_end AS car_auction_end,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    seller.name AS seller_name
             FROM transactions t
             INNER JOIN cars c ON c.id = t.car_id
             INNER JOIN users seller ON seller.id = t.seller_id
             WHERE t.winner_id = ?
             ORDER BY (t.status = "completed") ASC, t.created_at DESC'
        );
        $stmt->execute([$winnerId]);
        return $stmt->fetchAll();
    }

    /** Transactions where the given user is the seller (seller's sales). */
    public function salesBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, c.title AS car_title, c.auction_end AS car_auction_end,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    winner.name AS winner_name
             FROM transactions t
             INNER JOIN cars c ON c.id = t.car_id
             INNER JOIN users winner ON winner.id = t.winner_id
             WHERE t.seller_id = ?
             ORDER BY (t.status = "completed") ASC, t.created_at DESC'
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    /** All transactions (admin view). */
    public function allWithDetails(): array
    {
        $stmt = $this->db->query(
            'SELECT t.*, c.title AS car_title,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    seller.name AS seller_name, winner.name AS winner_name
             FROM transactions t
             INNER JOIN cars c ON c.id = t.car_id
             INNER JOIN users seller ON seller.id = t.seller_id
             INNER JOIN users winner ON winner.id = t.winner_id
             ORDER BY (t.status = "completed") ASC, t.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /** Create a transaction for a won auction / buy-now. Idempotent per car. */
    public function createForWin(int $carId, int $sellerId, int $winnerId, ?int $winningBidId, float $finalAmount): int
    {
        // UNIQUE(car_id) guarantees one transaction per car. Use INSERT IGNORE
        // so re-running auction-close logic never duplicates a transaction.
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO transactions (car_id, seller_id, winner_id, winning_bid_id, final_amount, status)
             VALUES (?, ?, ?, ?, ?, "payment_pending")'
        );
        $stmt->execute([$carId, $sellerId, $winnerId, $winningBidId, $finalAmount]);
        // lastInsertId is "0" when IGNORE skipped the insert; fetch the real id.
        $id = (int) $this->db->lastInsertId();
        if ($id > 0) return $id;
        $row = $this->db->prepare('SELECT id FROM transactions WHERE car_id = ? LIMIT 1');
        $row->execute([$carId]);
        return (int) $row->fetchColumn();
    }

    /** Advance the transaction status. Validates allowed forward transitions. */
    public function transition(int $id, string $newStatus): bool
    {
        $allowed = [
            'payment_pending'     => ['payment_confirmed', 'cancelled'],
            'payment_confirmed'   => ['ready_for_handover', 'cancelled'],
            'ready_for_handover'  => ['completed', 'cancelled'],
            'completed'           => [],
            'cancelled'           => [],
        ];
        $current = $this->find($id);
        if (!$current) return false;
        $from = $current['status'];
        if (!isset($allowed[$from]) || !in_array($newStatus, $allowed[$from], true)) {
            return false;
        }

        $tsCol = match ($newStatus) {
            'payment_confirmed'  => 'paid_at',
            'ready_for_handover' => null, // no separate timestamp; tracked via status
            'completed'          => 'completed_at',
            'cancelled'          => 'cancelled_at',
            default              => null,
        };

        if ($newStatus === 'completed') {
            $stmt = $this->db->prepare(
                'UPDATE transactions SET status = "completed", completed_at = NOW(), handed_over_at = COALESCE(handed_over_at, NOW()) WHERE id = ?'
            );
            $stmt->execute([$id]);
            return true;
        } elseif ($tsCol) {
            $stmt = $this->db->prepare(
                "UPDATE transactions SET status = ?, {$tsCol} = NOW() WHERE id = ?"
            );
            $stmt->execute([$newStatus, $id]);
            return true;
        }
        // ready_for_handover has no dedicated timestamp column
        $stmt = $this->db->prepare('UPDATE transactions SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $id]);
        return true;
    }

    /** Count purchases (as buyer) by status. */
    public function countByBuyer(int $winnerId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM transactions WHERE winner_id = ? AND status = ?');
        $stmt->execute([$winnerId, $status]);
        return (int) $stmt->fetchColumn();
    }

    /** Count sales (as seller) by status. */
    public function countBySeller(int $sellerId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM transactions WHERE seller_id = ? AND status = ?');
        $stmt->execute([$sellerId, $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Monthly spending breakdown for a buyer (winner), excluding cancelled.
     * Returns the last 6 months (including months with zero spend) as
     * ['label' => 'Jan', 'amount' => 12345.67, 'count' => 2].
     */
    public function monthlySpendingByBuyer(int $winnerId): array
    {
        // Fetch raw monthly totals for the last 6 months
        $stmt = $this->db->prepare(
            'SELECT DATE_FORMAT(created_at, "%Y-%m") AS ym,
                    COALESCE(SUM(final_amount), 0) AS total,
                    COUNT(*) AS cnt
             FROM transactions
             WHERE winner_id = ? AND status != "cancelled"
               AND created_at >= DATE_SUB(DATE_FORMAT(NOW(), "%Y-%m-01"), INTERVAL 5 MONTH)
             GROUP BY ym ORDER BY ym ASC'
        );
        $stmt->execute([$winnerId]);
        $rows = $stmt->fetchAll();

        // Build a complete 6-month series (fill gaps with zero)
        $map = [];
        foreach ($rows as $r) {
            $map[$r['ym']] = [(float) $r['total'], (int) $r['cnt']];
        }
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime("first day of this month -$i months");
            $ym = date('Y-m', $ts);
            $label = date('M', $ts);
            [$total, $count] = $map[$ym] ?? [0.0, 0];
            $result[] = ['label' => $label, 'amount' => $total, 'count' => $count];
        }
        return $result;
    }

    /** Total spent by a buyer (sum of non-cancelled final_amount). */
    public function totalSpentByBuyer(int $winnerId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(final_amount), 0) FROM transactions WHERE winner_id = ? AND status != "cancelled"'
        );
        $stmt->execute([$winnerId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Spending broken down by transaction status for a buyer (non-cancelled).
     * Returns ['completed' => float, 'pending' => float, 'in_progress' => float].
     * Used by the dashboard spending chart donut/summary.
     */
    public function spendingBreakdownByBuyer(int $winnerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, COALESCE(SUM(final_amount), 0) AS total
             FROM transactions WHERE winner_id = ? AND status != "cancelled"
             GROUP BY status'
        );
        $stmt->execute([$winnerId]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['status']] = (float) $r['total'];
        }
        return [
            'completed'   => $map['completed'] ?? 0.0,
            'pending'     => ($map['payment_pending'] ?? 0.0),
            'in_progress' => ($map['payment_confirmed'] ?? 0.0) + ($map['ready_for_handover'] ?? 0.0),
        ];
    }
}
