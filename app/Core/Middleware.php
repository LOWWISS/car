<?php
/**
 * Middleware: request gatekeepers (RBAC, CSRF, auth).
 *
 * SECURITY (Authorization / Access Control + CSRF):
 *  - requireAuth(): blocks guests from protected pages.
 *  - requireRole(): RBAC — only allow specified roles.
 *  - requireCsrf(): validates CSRF token on state-changing requests.
 *  - requireOwnerOrAdmin(): IDOR prevention — verifies resource ownership.
 */
final class Middleware
{
    public static function requireAuth(): void
    {
        if (Auth::guest()) {
            Session::flash('error', 'Please log in to continue.');
            Response::redirect('/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!in_array(Auth::role(), $roles, true)) {
            Logger::audit('authz_denied', ['required' => $roles, 'role' => Auth::role()], Auth::id());
            Response::forbidden('You do not have permission to access this page.');
        }
    }

    public static function requireCsrf(): void
    {
        if (!Csrf::verify()) {
            Logger::audit('csrf_failed', ['uri' => $_SERVER['REQUEST_URI'] ?? ''], Auth::id());
            http_response_code(419);
            Response::redirect('/?error=csrf');
        }
    }

    /** IDOR prevention: caller passes a boolean ownership result. */
    public static function requireOwnerOrAdmin(bool $isOwner): void
    {
        if ($isOwner || Auth::is('admin')) return;
        Response::forbidden('You do not own this resource.');
    }

    /** Block state-changing requests when rate limit exceeded. */
    public static function requireRateLimit(string $action, int $max, int $window): void
    {
        if (RateLimiter::tooMany($action, $max, $window, Auth::id())) {
            http_response_code(429);
            Response::json(['success' => false, 'message' => 'Too many requests. Please slow down.'], 429);
        }
    }
}
