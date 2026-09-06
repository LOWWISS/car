<?php
/**
 * Database: singleton PDO connection.
 *
 * SECURITY (SQL Injection Prevention):
 *  - Default PDO error mode = ERRMODE_EXCEPTION for safe, centralized handling.
 *  - All queries throughout the app use prepared statements with bound parameters
 *    (see BaseModel). No string concatenation of user input into SQL.
 *  - PDO::ATTR_EMULATE_PREPARES = false to force real server-side prepared statements.
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = Config::get('DB_HOST', '127.0.0.1');
            $port = Config::get('DB_PORT', '3306');
            $name = Config::get('DB_NAME', 'car_auction');
            $user = Config::get('DB_USER', 'root');
            $pass = Config::get('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
                PDO::ATTR_PERSISTENT         => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Never expose DSN/credentials to end users.
                Logger::error('Database connection failed: ' . $e->getMessage());
                Response::serverError('Database unavailable. Please try again later.');
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
