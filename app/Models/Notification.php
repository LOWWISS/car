<?php
/** Notification model (in-app bell). */
final class Notification extends BaseModel
{
    protected string $table = 'notifications';

    public function unreadForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 20'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function forUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    public function markRead(int $id, int $userId): void
    {
        // IDOR-safe: scope by user_id so a user can't mark another user's notif.
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public function send(int $userId, string $message, string $type = 'info', ?string $link = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $message, $type, $link]);
    }
}
