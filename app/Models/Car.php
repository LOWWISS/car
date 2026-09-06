<?php
/**
 * Car (listing) model.
 *
 * SECURITY: catalog filtering uses prepared statements for values; sort columns
 * are whitelisted in the controller via Validator::whitelist() before being
 * interpolated (identifiers cannot be bound as parameters).
 */
final class Car extends BaseModel
{
    protected string $table = 'cars';

    /** Catalog search with filters, sorting, and pagination. */
    public function search(array $f, int $perPage, int $offset): array
    {
        $where = [];
        $args = [];
        // Only show approved listings in the public catalog
        $where[] = "c.status IN ('active','sold','closed')";

        if (!empty($f['q'])) {
            $where[] = "(c.title LIKE ? OR c.make LIKE ? OR c.model LIKE ?)";
            $like = '%' . $f['q'] . '%';
            $args[] = $like; $args[] = $like; $args[] = $like;
        }
        if (!empty($f['make'])) {
            $where[] = "c.make = ?"; $args[] = $f['make'];
        }
        if (!empty($f['body_type'])) {
            $where[] = "c.body_type = ?"; $args[] = $f['body_type'];
        }
        if (!empty($f['year'])) {
            $where[] = "c.year = ?"; $args[] = (int)$f['year'];
        }
        if (!empty($f['location'])) {
            $where[] = "c.location LIKE ?"; $args[] = '%' . $f['location'] . '%';
        }
        if (isset($f['min_price']) && $f['min_price'] !== '') {
            $where[] = "c.starting_price >= ?"; $args[] = (float)$f['min_price'];
        }
        if (isset($f['max_price']) && $f['max_price'] !== '') {
            $where[] = "c.starting_price <= ?"; $args[] = (float)$f['max_price'];
        }
        if (!empty($f['status'])) {
            $where[] = "c.status = ?"; $args[] = $f['status'];
        }

        // Sort: column whitelisted by caller
        $sortCol = $f['sort'] ?? 'newest';
        $orderMap = [
            'newest'   => 'c.created_at DESC',
            'oldest'   => 'c.created_at ASC',
            'price_low'=> 'c.starting_price ASC',
            'price_high'=> 'c.starting_price DESC',
            'ending'   => 'c.auction_end ASC',
            'year_desc'=> 'c.year DESC',
        ];
        $order = $orderMap[$sortCol] ?? $orderMap['newest'];

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.*, u.name AS seller_name,
                       (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                       (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                       (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
                FROM cars c
                INNER JOIN users u ON u.id = c.seller_id
                WHERE {$whereSql}
                ORDER BY {$order}
                LIMIT ? OFFSET ?";

        $args[] = $perPage;
        $args[] = $offset;
        $stmt = $this->db->prepare($sql);
        // bind limit/offset as INT
        $last = count($args);
        $stmt->bindValue($last - 1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($last, $offset, PDO::PARAM_INT);
        // re-bind prior params positionally
        $idx = 1;
        for ($i = 0; $i < count($args) - 2; $i++) {
            $stmt->bindValue($idx++, $args[$i]);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return ['rows' => $rows, 'total' => $total];
    }

    /** Single car with seller + bid aggregate. */
    public function getWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.name AS seller_name, u.email AS seller_email, u.id AS seller_id,
                    (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                    (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
             FROM cars c INNER JOIN users u ON u.id = c.seller_id
             WHERE c.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $d, int $sellerId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cars (seller_id, title, make, model, year, mileage, body_type, `condition`, location, description, starting_price, reserve_price, buy_now_price, auction_start, auction_end, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $sellerId, $d['title'], $d['make'], $d['model'], (int)$d['year'],
            (int)$d['mileage'], $d['body_type'], $d['condition'], $d['location'],
            $d['description'], $d['starting_price'], $d['reserve_price'] ?: null,
            $d['buy_now_price'] ?: null, $d['auction_start'], $d['auction_end'],
            'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE cars SET title=?, make=?, model=?, year=?, mileage=?, body_type=?, `condition`=?, location=?, description=?, starting_price=?, reserve_price=?, buy_now_price=?, auction_start=?, auction_end=? WHERE id=?'
        );
        return $stmt->execute([
            $d['title'], $d['make'], $d['model'], (int)$d['year'], (int)$d['mileage'],
            $d['body_type'], $d['condition'], $d['location'], $d['description'],
            $d['starting_price'], $d['reserve_price'] ?: null, $d['buy_now_price'] ?: null,
            $d['auction_start'], $d['auction_end'], $id,
        ]);
    }

    public function setStatus(int $id, string $status, ?int $approverId = null): bool
    {
        if ($approverId !== null) {
            $stmt = $this->db->prepare('UPDATE cars SET status=?, approved_by=? WHERE id=?');
            return $stmt->execute([$status, $approverId, $id]);
        }
        $stmt = $this->db->prepare('UPDATE cars SET status=? WHERE id=?');
        return $stmt->execute([$status, $id]);
    }

    public function listingsBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                    (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
             FROM cars c WHERE c.seller_id = ? ORDER BY c.created_at DESC'
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function endingSoon(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                    (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
             FROM cars c WHERE c.status = 'active' AND c.auction_end > NOW()
             ORDER BY c.auction_end ASC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function featured(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                    (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
             FROM cars c WHERE c.status = 'active' ORDER BY c.created_at DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function pendingApprovals(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, u.name AS seller_name FROM cars c INNER JOIN users u ON u.id = c.seller_id
             WHERE c.status = 'pending' ORDER BY c.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function distinctMakes(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT make FROM cars ORDER BY make ASC");
        return array_column($stmt->fetchAll(), 'make');
    }

    /** Close auctions whose end time has passed. Returns closed car ids. */
    public function closeExpired(): array
    {
        $stmt = $this->db->query(
            "SELECT id FROM cars WHERE status = 'active' AND auction_end <= NOW()"
        );
        $ids = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $upd = $this->db->prepare("UPDATE cars SET status = 'closed' WHERE id IN ($in)");
            $upd->execute($ids);
        }
        return $ids;
    }

    /**
     * Expired active auctions with their highest bid (if any) and reserve.
     * Used by AuctionCloser to determine winners. The DB (auction_end) is the
     * source of truth for timing — not the frontend countdown.
     */
    public function expiredActiveAuctions(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.seller_id, c.title, c.reserve_price, c.starting_price,
                    c.auction_end,
                    hb.bid_amount AS highest_bid, hb.id AS highest_bid_id, hb.bidder_id AS highest_bidder_id,
                    bidder.name AS highest_bidder_name, bidder.email AS highest_bidder_email,
                    seller.name AS seller_name, seller.email AS seller_email
             FROM cars c
             LEFT JOIN (SELECT car_id, MAX(bid_amount) AS bid_amount FROM bids GROUP BY car_id) hm
                    ON hm.car_id = c.id
             LEFT JOIN bids hb ON hb.car_id = c.id AND hb.bid_amount = hm.bid_amount
             LEFT JOIN users bidder ON bidder.id = hb.bidder_id
             LEFT JOIN users seller ON seller.id = c.seller_id
             WHERE c.status = 'active' AND c.auction_end <= NOW()"
        );
        return $stmt->fetchAll();
    }
}
