<?php

namespace PlacementPro\Config;

use PDO;
use PDOException;
use RuntimeException;

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Load env variables (assume they are set via getenv or $_ENV)
            // In a real app, you'd use vlucas/phpdotenv to load the .env file.
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '5432';
            $db   = getenv('DB_NAME') ?: 'placement_pro';
            $user = getenv('DB_USER') ?: 'postgres';
            $pass = getenv('DB_PASS') ?: '';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";

            try {
                // Ensure no HTML errors are dumped to the output
                ini_set('display_errors', 0);
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Do not expose database errors in production
                error_log("Database Connection Error: " . $e->getMessage());
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Database connection failed. Ensure PostgreSQL is installed and pdo_pgsql extension is enabled in PHP.']);
                exit;
            }
        }

        return self::$instance;
    }
}
