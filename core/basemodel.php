<?php
namespace Core;

use PDO;
use PDOStatement;
use PDOException;

abstract class BaseModel {
    protected Database $db;
    protected string $table;
    protected string $queryLogPath = __DIR__ . 'query.log';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureLogDirectoryExists();
    }

    protected function ensureLogDirectoryExists(): void
    {
        $logDir = dirname($this->queryLogPath);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    protected function logQuery(string $sql, array $params, float $startTime, bool $success, ?string $error = null): void
    {
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // en milisegundos
        
        $logEntry = sprintf(
            "[%s] Query: %s | Params: %s | Time: %sms | Status: %s | Error: %s\n",
            date('Y-m-d H:i:s'),
            $sql,
            json_encode($params),
            $executionTime,
            $success ? 'SUCCESS' : 'FAILED',
            $error ?? 'None'
        );

        file_put_contents($this->queryLogPath, $logEntry, FILE_APPEND);
    }

    public function all(): array {
        $sql = "SELECT * FROM {$this->table}";
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            // $this->logQuery($sql, [], $start, true);
            return $result;
        } catch (PDOException $e) {
            $this->logQuery($sql, [], $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch() ?: null;
            // $this->logQuery($sql, $params, $start, true);
            return $result;
        } catch (PDOException $e) {
            $this->logQuery($sql, $params, $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $lastId = $this->db->lastInsertId();
            // $this->logQuery($sql, $data, $start, true);
            return $lastId;
        } catch (PDOException $e) {
            $this->logQuery($sql, $data, $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data): bool {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "$key = :$key";
        }
        $setClause = implode(', ', $set);
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $params = array_merge($data, [':id' => $id]);
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            // $this->logQuery($sql, $params, $start, $result);
            return $result;
        } catch (PDOException $e) {
            $this->logQuery($sql, $params, $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            // $this->logQuery($sql, $params, $start, $result);
            return $result;
        } catch (PDOException $e) {
            $this->logQuery($sql, $params, $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function query(string $sql, array $params = []): array {
        $start = microtime(true);
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue(is_int($key) ? $key + 1 : ":$key", $value, $paramType);
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll();
            // $this->logQuery($sql, $params, $start, true);
            return $result;
        } catch (PDOException $e) {
            $this->logQuery($sql, $params, $start, false, $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction(): bool {
        $start = microtime(true);
        $result = $this->db->beginTransaction();
        // $this->logQuery('BEGIN TRANSACTION', [], $start, $result);
        return $result;
    }

    public function commit(): bool {
        $start = microtime(true);
        $result = $this->db->commit();
        // $this->logQuery('COMMIT', [], $start, $result);
        return $result;
    }

    public function rollBack(): bool {
        $start = microtime(true);
        $result = $this->db->rollBack();
        // $this->logQuery('ROLLBACK', [], $start, $result);
        return $result;
    }
}