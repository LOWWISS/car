<?php
/**
 * ErrorHandler: centralized exception/error handling.
 *
 * SECURITY (Error Handling): in production, detailed errors/stack traces are
 * suppressed and a friendly page is shown; full details go to the error log.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'exception']);
        set_error_handler([self::class, 'error']);
        register_shutdown_function([self::class, 'shutdown']);
    }

    public static function exception(\Throwable $e): void
    {
        Logger::error('Exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        if (Config::get('APP_DEBUG') === 'true') {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<h1>Application Error (debug)</h1><pre>' . htmlspecialchars((string)$e) . '</pre>';
        } else {
            Response::serverError('Something went wrong. Please try again later.');
        }
    }

    public static function error(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) return false;
        Logger::error("Error [{$errno}]: {$errstr} @ {$errfile}:{$errline}");
        if (Config::get('APP_DEBUG') === 'true') {
            http_response_code(500);
            echo 'Error: ' . htmlspecialchars($errstr);
        } else {
            Response::serverError('Something went wrong. Please try again later.');
        }
        return true;
    }

    public static function shutdown(): void
    {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            Logger::error('Fatal: ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
            if (!headers_sent()) {
                http_response_code(500);
            }
        }
    }
}
