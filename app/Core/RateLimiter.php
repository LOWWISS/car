<?php
/**
 * RateLimiter: fixed-window counter keyed by (action, ip, user_id).
 *
 * SECURITY (Rate Limiting + Brute-force Protection):
 *  - Throttles login, registration, bid submission and AJAX endpoints.
 *  - Stored in the `rate_limits` table. Each (action, ip, user_id) tuple has a
 *    window_start timestamp; counts reset when the window expires.
 *  - Account lockout for logins is handled by Auth using LOGIN_MAX_ATTEMPTS.
 */
final class RateLimiter
{
    public static function hit(string $action, int $max, int $windowSeconds, ?int $userId = null): int
    {
        $db = Database::getInstance();
        $ip = self::ip();
        $now = time();

        // Find existing window row
        $stmt = $db->prepare(
            'SELECT id, attempt_count, window_start FROM rate_limits
             WHERE action = ? AND ip_address = ? AND user_id <=> ? LIMIT 1'
        );
        $stmt->execute([$action, $ip, $userId]);
        $row = $stmt->fetch();

        if (!$row || ($now - (int)$row['window_start']) >= $windowSeconds) {
            // Start/refresh window
            if ($row) {
                $upd = $db->prepare('UPDATE rate_limits SET attempt_count = 1, window_start = ? WHERE id = ?');
                $upd->execute([$now, $row['id']]);
            } else {
                $ins = $db->prepare(
                    'INSERT INTO rate_limits (action, ip_address, user_id, attempt_count, window_start)
                     VALUES (?, ?, ?, 1, ?)'
                );
                $ins->execute([$action, $ip, $userId, $now]);
            }
            return 1;
        }

        $newCount = (int)$row['attempt_count'] + 1;
        $upd = $db->prepare('UPDATE rate_limits SET attempt_count = ? WHERE id = ?');
        $upd->execute([$newCount, $row['id']]);
        return $newCount;
    }

    public static function tooMany(string $action, int $max, int $windowSeconds, ?int $userId = null): bool
    {
        $db = Database::getInstance();
        $ip = self::ip();
        $now = time();
        $stmt = $db->prepare(
            'SELECT attempt_count, window_start FROM rate_limits
             WHERE action = ? AND ip_address = ? AND user_id <=> ? LIMIT 1'
        );
        $stmt->execute([$action, $ip, $userId]);
        $row = $stmt->fetch();
        if (!$row) return false;
        if (($now - (int)$row['window_start']) >= $windowSeconds) return false;
        return (int)$row['attempt_count'] >= $max;
    }

    public static function remaining(string $action, int $max, int $windowSeconds, ?int $userId = null): int
    {
        $db = Database::getInstance();
        $ip = self::ip();
        $now = time();
        $stmt = $db->prepare(
            'SELECT attempt_count, window_start FROM rate_limits
             WHERE action = ? AND ip_address = ? AND user_id <=> ? LIMIT 1'
        );
        $stmt->execute([$action, $ip, $userId]);
        $row = $stmt->fetch();
        if (!$row || ($now - (int)$row['window_start']) >= $windowSeconds) return $max;
        return max(0, $max - (int)$row['attempt_count']);
    }

    public static function clear(string $action, ?int $userId = null): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM rate_limits WHERE action = ? AND ip_address = ? AND user_id <=> ?');
        $stmt->execute([$action, self::ip(), $userId]);
    }

    private static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
