<?php
/**
 * Request: thin wrapper around $_GET/$_POST/$_SERVER for clean access.
 */
final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip the subfolder the app lives in so routing is clean.
        // Strategy: try APP_URL first (most reliable), then fall back to
        // SCRIPT_NAME dirname. The root .htaccess rewrites to public/index.php,
        // so SCRIPT_NAME may be /car/public/index.php while the real base is
        // /car — APP_URL from .env gives us the correct base.
        $base = null;
        $appUrl = Config::get('APP_URL', '');
        if ($appUrl !== '') {
            $appPath = parse_url($appUrl, PHP_URL_PATH) ?? '';
            $appPath = rtrim($appPath, '/');
            if ($appPath !== '' && $appPath !== '/') {
                $base = $appPath;
            }
        }
        if ($base === null) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            $base = str_replace('\\', '/', dirname($scriptName));
        }
        if ($base !== '/' && $base !== '' && ($uri === $base || str_starts_with($uri, $base . '/'))) {
            $uri = substr($uri, strlen($base));
        }
        return '/' . trim($uri, '/');
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
