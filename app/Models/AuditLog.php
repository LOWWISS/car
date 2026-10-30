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

    /** Filtered log search: q (details/user/action), action, date range. */
    public function search(array $f, int $limit = 300): array
    {
        $where = '1=1';
        $params = [];
        if (!empty($f['q'])) {
            $where .= ' AND (a.details LIKE ? OR u.name LIKE ? OR a.action LIKE ? OR a.ip_address LIKE ?)';
            $l = '%' . $f['q'] . '%';
            array_push($params, $l, $l, $l, $l);
        }
        if (!empty($f['action'])) { $where .= ' AND a.action = ?'; $params[] = $f['action']; }
        if (!empty($f['from']))   { $where .= ' AND a.created_at >= ?'; $params[] = $f['from'] . ' 00:00:00'; }
        if (!empty($f['to']))     { $where .= ' AND a.created_at <= ?'; $params[] = $f['to'] . ' 23:59:59'; }

        $stmt = $this->db->prepare(
            "SELECT a.*, u.name AS user_name FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE $where ORDER BY a.created_at DESC LIMIT $limit"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** All distinct action names for the filter dropdown. */
    public function actions(): array
    {
        return $this->db->query('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC')->fetchAll(PDO::FETCH_COLUMN);
    }
}
