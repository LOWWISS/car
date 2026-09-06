<?php
/**
 * Response: HTTP response helpers.
 */
final class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $path, int $status = 302): void
    {
        // Build absolute URL from APP_URL if path is relative
        if (!str_starts_with($path, 'http')) {
            $base = rtrim(Config::get('APP_URL', ''), '/');
            $path = $base . '/' . ltrim($path, '/');
        }
        header("Location: {$path}", true, $status);
        exit;
    }

    public static function notFound(string $message = 'Page not found.'): void
    {
        http_response_code(404);
        // Render a friendly error view if available
        if (class_exists('View')) {
            View::render('errors/404', ['message' => $message], 'layouts/header', 'layouts/footer');
        } else {
            echo htmlspecialchars($message);
        }
        exit;
    }

    public static function serverError(string $message = 'Server error.'): void
    {
        http_response_code(500);
        if (class_exists('View')) {
            View::render('errors/500', ['message' => $message], 'layouts/header', 'layouts/footer');
        } else {
            echo htmlspecialchars($message);
        }
        exit;
    }

    public static function forbidden(string $message = 'Forbidden.'): void
    {
        http_response_code(403);
        if (class_exists('View')) {
            View::render('errors/403', ['message' => $message], 'layouts/header', 'layouts/footer');
        } else {
            echo htmlspecialchars($message);
        }
        exit;
    }
}
