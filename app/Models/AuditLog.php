<?php
/** AuditLog model — persists structured audit records. */
final class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    public function record(?int $userId, string $action, array $details = [], string $ip = ''): void
    {
        unset($details['password'], $details['password_hash'], $details['_csrf_token']);
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            json_encode($details, JSON_UNESCAPED_SLASHES),
            $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'),
        ]);
    }

    public function recent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
