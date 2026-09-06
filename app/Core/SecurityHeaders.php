<?php
/**
 * SecurityHeaders: sends defensive HTTP headers on every response.
 *
 * SECURITY (Security Headers + XSS Prevention via CSP):
 *  - X-Content-Type-Options: nosniff -> stops MIME sniffing.
 *  - X-Frame-Options: DENY -> clickjacking protection.
 *  - Strict-Transport-Security -> HSTS (only meaningful over HTTPS).
 *  - Referrer-Policy -> limit referrer leakage.
 *  - Content-Security-Policy -> restrict script/style/image sources; disallow
 *    inline scripts except for the Tailwind Play CDN config + nonce where needed.
 */
final class SecurityHeaders
{
    public static function send(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 1; mode=block'); // legacy browsers
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        if ($https) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // CSP: allow Tailwind Play CDN + Google Fonts + inline styles (Tailwind
        // injects styles) + 'unsafe-inline' for styles only. Scripts restricted
        // to self + Tailwind CDN. Inline scripts use a per-request nonce.
        $nonce = self::cspNonce();
        $csp = [
            "default-src 'self'",
            "script-src 'self' https://cdn.tailwindcss.com 'nonce-{$nonce}'",
            "style-src 'self' https://cdn.tailwindcss.com https://fonts.googleapis.com 'unsafe-inline'",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];
        header('Content-Security-Policy: ' . implode('; ', $csp));
    }

    /** Per-request nonce to allow our inline Tailwind config + app scripts. */
    public static function cspNonce(): string
    {
        static $nonce = null;
        if ($nonce === null) {
            $nonce = bin2hex(random_bytes(16));
        }
        return $nonce;
    }
}
