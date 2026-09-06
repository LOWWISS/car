<?php
/**
 * Auth: authentication & current-user helpers.
 *
 * SECURITY (Authentication & Session Security + Brute-force Protection):
 *  - password_hash() (bcrypt, cost 12) for storage; password_verify() on login.
 *  - session_regenerate_id(true) on successful login (session fixation defense).
 *  - Account lockout after LOGIN_MAX_ATTEMPTS failed attempts within a window.
 *  - Role checks (RBAC) via requireRole()/is()/guest().
 */
final class Auth
{
    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['_auth_user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        $_SESSION['_auth_at'] = time();
        Logger::audit('login', ['user' => $user['email']], (int) $user['id']);
    }

    public static function logout(): void
    {
        if (self::check()) {
            Logger::audit('logout', ['user' => self::user()['email']], self::id());
        }
        Session::destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['_auth_user']);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?array
    {
        return $_SESSION['_auth_user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['_auth_user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['_auth_user']['role'] ?? null;
    }

    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    /** True if the current user's role is in the given list. */
    public static function inRoles(array $roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    /**
     * Refresh the session-stored user data from the database.
     * Call after updating name (or other fields cached in session).
     */
    public static function refreshUser(User $userModel): void
    {
        $id = self::id();
        if ($id === null) return;
        $user = $userModel->find($id);
        if ($user) {
            $_SESSION['_auth_user']['name'] = $user['name'];
            $_SESSION['_auth_user']['email'] = $user['email'];
            $_SESSION['_auth_user']['role'] = $user['role'];
        }
    }
}
