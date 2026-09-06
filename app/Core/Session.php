<?php
/**
 * Session: secure session management.
 *
 * SECURITY (Authentication & Session Security):
 *  - Cookies are HttpOnly (not accessible via JS), SameSite=Lax (CSRF mitigation),
 *    and Secure when served over HTTPS.
 *  - session_regenerate_id(true) on login to prevent session fixation.
 *  - Inactivity timeout: last-activity timestamp; auto-logout when exceeded.
 *  - Strict mode enabled to reject uninitialized session IDs.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['SERVER_PORT'] ?? '') == 443);

        session_set_cookie_params([
            'lifetime' => (int) Config::get('SESSION_LIFETIME', 1800),
            'path'     => '/',
            'httponly' => true,
            'secure'   => $https,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        session_name('CARAUCTION_SESS');
        session_start();

        // Inactivity timeout
        $timeout = (int) Config::get('SESSION_LIFETIME', 1800);
        $now = time();
        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > $timeout) {
            self::destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = $now;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /** Regenerate ID to prevent session fixation (call on login/privilege change). */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'],
                $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
