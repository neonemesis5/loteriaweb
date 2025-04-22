<?php

use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class SorteoModel extends BaseModel {

    public function __construct() {
        $this->table = 'sorteo';
        parent::__construct();
    }

    /**
     * Obtiene todos los sorteos ordenados por fecha de sorteo descendente
     */
    public function getAll(): array {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY fecha_sorteo DESC"
        );
    }

    /**
     * Obtiene sorteos activos ordenados por fecha de sorteo
     */
    public function getActive(): array {
        return $this->query(
            "SELECT id,titulo, FOTO,precio,qtynumeros, fecha_sorteo FROM {$this->table} 
             WHERE status = 'A' 
             ORDER BY fecha_sorteo ASC"
        );
    }

    /**
     * Obtiene sorteos próximos (fecha futura)
     */
    public function getUpcoming(): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE fecha_sorteo > NOW() 
             AND status = 'A'
             ORDER BY fecha_sorteo ASC"
        );
    }

    /**
     * Obtiene sorteos pasados
     */
    public function getPast(): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE fecha_sorteo <= NOW() 
             ORDER BY fecha_sorteo DESC"
        );
    }

    /**
     * Obtiene un sorteo por número de sorteo
     */
    public function getByNumber(string $numeroSorteo): ?array {
        $result = $this->query(
            "SELECT * FROM {$this->table} 
             WHERE nrosorteo = :nrosorteo 
             LIMIT 1",
            [':nrosorteo' => $numeroSorteo]
        );
        
        return $result[0] ?? null;
    }

    /**
     * Obtiene sorteos por rango de fechas
     */
    public function getByDateRange(string $startDate, string $endDate): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE fecha_sorteo BETWEEN :start_date AND :end_date 
             ORDER BY fecha_sorteo ASC",
            [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]
        );
    }

    /**
     * Actualiza el resultado de un sorteo
     */
    public function updateResult(int $id, string $resultado): bool {
        return $this->update($id, ['resultado' => $resultado]);
    }

    /**
     * Actualiza el estado de un sorteo
     */
    public function updateStatus(int $id, string $status): bool {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Incrementa la cantidad de números vendidos
     */
    public function incrementSold(int $id, int $quantity = 1): bool {
        return $this->query(
            "UPDATE {$this->table} 
             SET qtyvendidos = qtyvendidos + :quantity 
             WHERE id = :id",
            [
                ':quantity' => $quantity,
                ':id' => $id
            ]
        ) !== false;
    }

    /**
     * Obtiene el conteo de sorteos por estado
     */
    public function getCountByStatus(): array {
        return $this->query(
            "SELECT status, COUNT(*) as count 
             FROM {$this->table} 
             GROUP BY status"
        );
    }

    /**
     * Busca sorteos por título (búsqueda parcial)
     */
    public function searchByTitle(string $searchTerm): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE titulo LIKE :search 
             ORDER BY fecha_sorteo DESC",
            [':search' => "%".$this->db->escapeLikeString($searchTerm)."%"]
        );
    }

    /**
     * Obtiene los próximos 3 sorteos más relevantes
     */
    public function getFeaturedUpcoming(int $limit = 3): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE fecha_sorteo > NOW() 
             AND status = 'A'
             ORDER BY fecha_sorteo ASC 
             LIMIT :limit",
            [':limit' => $limit],
            [':limit' => PDO::PARAM_INT]
        );
    }

    /**
     * Verifica si un número de sorteo ya existe
     */
    public function numberExists(string $numeroSorteo, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE nrosorteo = :nrosorteo";
        
        $params = [':nrosorteo' => $numeroSorteo];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Obtiene estadísticas de sorteos
     */
    public function getStats(): array {
        return $this->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN fecha_sorteo > NOW() THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN fecha_sorteo <= NOW() THEN 1 ELSE 0 END) as past,
                SUM(qtynumeros) as total_numbers,
                SUM(qtyvendidos) as total_sold
             FROM {$this->table}"
        )[0] ?? [];
    }

    /**
     * Obtiene el último sorteo creado
     */
    public function getLatest(): ?array {
        $result = $this->query(
            "SELECT * FROM {$this->table} 
             ORDER BY id DESC 
             LIMIT 1"
        );
        
        return $result[0] ?? null;
    }

    /**
     * Obtiene sorteos con números agotados
     */
    public function getSoldOut(): array {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE qtynumeros = qtyvendidos 
             AND status = 'A'
             ORDER BY fecha_sorteo ASC"
        );
    }
}