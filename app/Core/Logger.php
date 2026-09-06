<?php
/**
 * Logger: audit + error logging. Sensitive data (passwords, tokens) is never
 * written to logs.
 *
 * SECURITY (Logging & Auditing): writes auth attempts, bids, admin actions,
 * and errors to storage/logs/ for an audit trail.
 */
final class Logger
{
    private static string $auditFile = 'audit.log';
    private static string $errorFile = 'error.log';

    private static function dir(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function write(string $file, string $message): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        @file_put_contents(self::dir() . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function audit(string $action, ?array $details = null, ?int $userId = null): void
    {
        // Harden against null $details (a previous bug crashed here when a
        // caller passed null). Coerce to an array before any work.
        $details = $details ?? [];
        $uid = $userId ?? (Auth::id() ?? 'guest');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        // Strip any keys that look sensitive
        unset($details['password'], $details['password_hash'], $details['_csrf_token']);

        // 1) Flat-file audit trail (always written).
        $msg = "user={$uid} ip={$ip} action={$action} details=" . json_encode($details, JSON_UNESCAPED_SLASHES);
        self::write(self::$auditFile, $msg);

        // 2) Structured DB record (powers the admin Audit Logs page at
        //    /admin/logs, which reads from the audit_logs table). Wrapped so
        //    a DB issue never breaks the request being audited.
        $intUserId = $userId ?? Auth::id();
        try {
            (new AuditLog())->record($intUserId, $action, $details, $ip);
        } catch (Throwable $e) {
            self::error('AuditLog DB write failed: ' . $e->getMessage());
        }
    }

    public static function error(string $message): void
    {
        self::write(self::$errorFile, $message);
    }
}
