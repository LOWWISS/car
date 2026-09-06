<?php
/**
 * Configuration loader.
 * Parses .env (no external dependency) and exposes config via Config::get().
 */
final class Config
{
    private static array $items = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException("Environment file not found: $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Strip surrounding quotes
            if (preg_match('/^"(.*)"$/', $value, $m)) {
                $value = $m[1];
            }
            self::$items[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::$items;
    }
}
