<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and rethrow for production usage
            error_log('Database connection error: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
