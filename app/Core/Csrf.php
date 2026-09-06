<?php
/**
 * Csrf: per-session CSRF token generation & validation.
 *
 * SECURITY (CSRF Protection):
 *  - A random token is generated once per session and stored server-side.
 *  - Every state-changing POST/PUT/DELETE request must include the token
 *    (hidden field or X-CSRF-Token header); it is validated here.
 *  - Using a per-session token (rather than per-form) is safe as long as we
 *    use SameSite cookies + token comparison with hash_equals (timing-safe).
 */
final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function verify(): bool
    {
        $token = $_POST['_csrf_token']
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if ($token === null || !Session::has('_csrf_token')) {
            return false;
        }
        return hash_equals(Session::get('_csrf_token'), (string) $token);
    }

    /** Hidden input to embed in forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }
}
