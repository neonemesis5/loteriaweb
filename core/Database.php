<?php
namespace Core;

use PDO;
use PDOException;
use PDOStatement;

require_once __DIR__ . '/envloader.php';
EnvLoader::load(__DIR__ . '/../.env');
class Database {
    private static $instance = null;
    private $connection;
    private function __construct() {
        try {
            $host     = $_ENV['DB_HOST'] ?? 'localhost';
            $dbName   = $_ENV['DB_NAME'] ?? '';
            $username = $_ENV['DB_USER'] ?? '';
            $password = $_ENV['DB_PASS'] ?? '';
            $this->connection = new PDO(
                "mysql:host={$host};dbname={$dbName};charset=utf8",
                $username,
                $password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function prepare(string $sql): PDOStatement {
        return $this->connection->prepare($sql);
    }

    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool {
        return $this->connection->commit();
    }

    public function rollBack(): bool {
        return $this->connection->rollBack();
    }

    /**
     * Escapa caracteres especiales para búsquedas LIKE
     */
    public function escapeLikeString(string $str): string {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
    }
}