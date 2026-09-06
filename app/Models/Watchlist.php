<?php
/** Watchlist (favorites) model. */
final class Watchlist extends BaseModel
{
    protected string $table = 'watchlist';

    public function toggle(int $userId, int $carId): bool
    {
        // Returns true if added, false if removed.
        $stmt = $this->db->prepare(
            'SELECT id FROM watchlist WHERE user_id = ? AND car_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $carId]);
        if ($stmt->fetch()) {
            $del = $this->db->prepare('DELETE FROM watchlist WHERE user_id = ? AND car_id = ?');
            $del->execute([$userId, $carId]);
            return false;
        }
        $ins = $this->db->prepare('INSERT INTO watchlist (user_id, car_id) VALUES (?, ?)');
        $ins->execute([$userId, $carId]);
        return true;
    }

    public function isWatching(int $userId, int $carId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM watchlist WHERE user_id = ? AND car_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $carId]);
        return (bool) $stmt->fetchColumn();
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, w.created_at AS watched_at,
                    (SELECT image_path FROM car_images WHERE car_id = c.id AND is_primary = 1 LIMIT 1) AS primary_image,
                    (SELECT MAX(bid_amount) FROM bids WHERE car_id = c.id) AS current_bid,
                    (SELECT COUNT(*) FROM bids WHERE car_id = c.id) AS bid_count
             FROM watchlist w INNER JOIN cars c ON c.id = w.car_id
             WHERE w.user_id = ? ORDER BY w.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM watchlist WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
