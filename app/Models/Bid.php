<?php
/**
 * Bid model.
 *
 * SECURITY: all queries are prepared. The minimum-increment + outbid logic is
 * enforced in the controller (business rules) before calling place(); the model
 * is a thin data-access layer.
 */
final class Bid extends BaseModel
{
    protected string $table = 'bids';

    public function historyForCar(int $carId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.name AS bidder_name
             FROM bids b INNER JOIN users u ON u.id = b.bidder_id
             WHERE b.car_id = ? ORDER BY b.bid_amount DESC, b.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(1, $carId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function highestBid(int $carId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.name AS bidder_name
             FROM bids b INNER JOIN users u ON u.id = b.bidder_id
             WHERE b.car_id = ? ORDER BY b.bid_amount DESC, b.created_at ASC LIMIT 1'
        );
        $stmt->execute([$carId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function place(int $carId, int $bidderId, float $amount): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bids (car_id, bidder_id, bid_amount) VALUES (?, ?, ?)'
        );
        $stmt->execute([$carId, $bidderId, $amount]);
        return (int) $this->db->lastInsertId();
    }

    public function bidsByBidder(int $bidderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.title AS car_title, c.status AS car_status,
                    c.auction_end, (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image
             FROM bids b INNER JOIN cars c ON c.id = b.car_id
             WHERE b.bidder_id = ?
             GROUP BY b.car_id, b.id
             ORDER BY b.created_at DESC'
        );
        $stmt->execute([$bidderId]);
        return $stmt->fetchAll();
    }

    public function wonByBidder(int $bidderId): array
    {
        // A bidder "wins" a closed/sold car where they hold the highest bid and
        // the reserve was met. Includes the related transaction status (if any)
        // so the Won Auctions view can link to the payment/handover flow.
        $stmt = $this->db->prepare(
            "SELECT c.*, h.bid_amount, h.created_at AS won_at,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    tx.id AS transaction_id, tx.status AS transaction_status
             FROM cars c
             INNER JOIN (
                SELECT car_id, MAX(bid_amount) AS bid_amount
                FROM bids WHERE bidder_id = ? GROUP BY car_id
             ) hb ON hb.car_id = c.id
             INNER JOIN bids h ON h.car_id = c.id AND h.bid_amount = hb.bid_amount AND h.bidder_id = ?
             LEFT JOIN transactions tx ON tx.car_id = c.id
             WHERE c.status IN ('closed','sold')
               AND (c.reserve_price IS NULL OR hb.bid_amount >= c.reserve_price)
             GROUP BY c.id ORDER BY c.auction_end DESC"
        );
        $stmt->execute([$bidderId, $bidderId]);
        return $stmt->fetchAll();
    }

    public function countByBidder(int $bidderId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(DISTINCT car_id) FROM bids WHERE bidder_id = ?');
        $stmt->execute([$bidderId]);
        return (int) $stmt->fetchColumn();
    }

    public function countWonByBidder(int $bidderId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT c.id)
             FROM cars c
             INNER JOIN (
                SELECT car_id, MAX(bid_amount) AS bid_amount
                FROM bids WHERE bidder_id = ? GROUP BY car_id
             ) hb ON hb.car_id = c.id
             INNER JOIN bids h ON h.car_id = c.id AND h.bid_amount = hb.bid_amount AND h.bidder_id = ?
             WHERE c.status IN ('closed','sold')
               AND (c.reserve_price IS NULL OR hb.bid_amount >= c.reserve_price)"
        );
        $stmt->execute([$bidderId, $bidderId]);
        return (int) $stmt->fetchColumn();
    }
}
