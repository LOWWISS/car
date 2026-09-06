<?php
/**
 * Global helper functions, loaded explicitly by the front controller so they
 * are available everywhere (controllers, models, views) regardless of
 * autoloader timing.
 */

/**
 * HTML-escape helper. Always use e() when outputting user data in Views.
 * SECURITY (XSS Prevention): wraps htmlspecialchars with ENT_QUOTES + UTF-8.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Convenience: get a config value. */
function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

/** Absolute asset URL helper. */
function asset(string $path): string
{
    return rtrim(Config::get('APP_URL', '/car'), '/') . '/assets/' . ltrim($path, '/');
}
