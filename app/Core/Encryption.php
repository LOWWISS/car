<?php
/**
 * Encryption: AES-256-CBC encryption for sensitive data at rest.
 *
 * SECURITY (Data Encryption):
 *  - Uses OpenSSL AES-256-CBC with a per-record random IV.
 *  - Key sourced from .env (ENCRYPTION_KEY, base64 of 32 bytes).
 *  - Ciphertext is stored as base64(IV . ciphertext) so it is DB-safe.
 *  - Authentication tag (HMAC) is appended to detect tampering (encrypt-then-MAC).
 */
final class Encryption
{
    private static string $cipher = 'aes-256-cbc';
    private static int $ivLen = 16;

    private static function key(): string
    {
        $b64 = Config::get('ENCRYPTION_KEY');
        $key = $b64 !== null ? base64_decode($b64, true) : false;
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('Invalid ENCRYPTION_KEY: must be base64 of 32 bytes.');
        }
        return $key;
    }

    public static function encrypt(string $plaintext): string
    {
        $iv = random_bytes(self::$ivLen);
        $key = self::key();
        $ct = openssl_encrypt($plaintext, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($ct === false) {
            throw new RuntimeException('Encryption failed.');
        }
        $mac = hash_hmac('sha256', $iv . $ct, $key, true);
        return base64_encode($iv . $mac . $ct);
    }

    public static function decrypt(string $payload): ?string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < self::$ivLen + 32) {
            return null;
        }
        $key = self::key();
        $iv  = substr($raw, 0, self::$ivLen);
        $mac = substr($raw, self::$ivLen, 32);
        $ct  = substr($raw, self::$ivLen + 32);

        $expected = hash_hmac('sha256', $iv . $ct, $key, true);
        if (!hash_equals($expected, $mac)) {
            // Tampered or wrong key
            return null;
        }
        $pt = openssl_decrypt($ct, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $pt === false ? null : $pt;
    }
}
