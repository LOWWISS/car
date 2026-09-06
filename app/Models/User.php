<?php
/**
 * User model. Handles auth lookups, registration, tokens, and lockout state.
 *
 * SECURITY: passwords are only ever stored as bcrypt hashes (password_hash).
 * Sensitive fields (phone) are encrypted at rest via Encryption::encrypt().
 */
final class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function create(array $data): int
    {
        // password_hash with bcrypt cost 12 (Argon2id available if PHP compiled with it)
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $phone = !empty($data['phone']) ? Encryption::encrypt($data['phone']) : null;
        $verifyToken = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, phone, email_verified, verify_token)
             VALUES (?, ?, ?, ?, ?, 0, ?)'
        );
        $stmt->execute([
            $data['name'], $data['email'], $hash,
            $data['role'] ?? 'buyer', $phone, $verifyToken,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function verifyEmail(string $token): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET email_verified = 1, verify_token = NULL WHERE verify_token = ?'
        );
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function setResetToken(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        $stmt = $this->db->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
        $stmt->execute([$token, $expires, $user['id']]);
        $user['reset_token'] = $token;
        return $user;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user) return false;
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $upd = $this->db->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $upd->execute([$hash, $user['id']]);
        return true;
    }

    public function recordFailedLogin(int $userId): void
    {
        $max = (int) Config::get('LOGIN_MAX_ATTEMPTS', 5);
        $lockMins = (int) Config::get('LOGIN_LOCKOUT_MINUTES', 15);
        $stmt = $this->db->prepare(
            'UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $stmt = $this->db->prepare('SELECT failed_attempts FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $count = (int) $stmt->fetchColumn();
        if ($count >= $max) {
            $lock = $this->db->prepare('UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?');
            $lock->execute([$lockMins, $userId]);
        }
    }

    public function resetFailedLogin(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function isLocked(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT locked_until FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $until = $stmt->fetchColumn();
        return $until && strtotime($until) > time();
    }

    public function getLockedUntil(int $userId): ?string
    {
        $stmt = $this->db->prepare('SELECT locked_until FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $until = $stmt->fetchColumn();
        return $until ?: null;
    }

    public function getFailedAttempts(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT failed_attempts FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getPhone(int $userId): ?string
    {
        $stmt = $this->db->prepare('SELECT phone FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $raw = $stmt->fetchColumn();
        if (!$raw) return null;
        return Encryption::decrypt($raw);
    }

    public function allWithCounts(): array
    {
        $stmt = $this->db->query(
            'SELECT u.id, u.name, u.email, u.role, u.email_verified, u.is_active, u.created_at,
                    (SELECT COUNT(*) FROM cars WHERE seller_id = u.id) AS listings,
                    (SELECT COUNT(*) FROM bids WHERE bidder_id = u.id) AS bids
             FROM users u ORDER BY u.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        return $stmt->execute([$role, $id]);
    }

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        return $stmt->execute([$active ? 1 : 0, $id]);
    }

    /** All active admin users (used to notify admins of new listing submissions). */
    public function admins(): array
    {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role = 'admin' AND is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
