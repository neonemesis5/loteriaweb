<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class CartonModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'carton';
    }

    // Obtener todos los cartones
    public function getAll(): array {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY id ASC"
        );
    }

    // Obtener un cartón por ID
    public function getById($id): ?array {
        $result = $this->query(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
        return $result ? $result[0] : null;
    }

    // Obtener cartones vendidos por sorteo
    public function getCartonSellBySorteo($sorteoId): array {
        return $this->query(
            "SELECT id, numero FROM {$this->table} WHERE sorteo_id = :sorteo_id AND status = :st ORDER BY numero ASC",
            ['sorteo_id' => $sorteoId, 'st' => 'V']
        );
    }

    // Insertar un nuevo cartón (usa create del BaseModel)
    public function insert(array $data): int {
        return $this->create($data);
    }

    // Actualizar un cartón (usa update del BaseModel)
    public function updateCarton($id, array $data): bool {
        return $this->update($id, $data);
    }

    // Eliminar un cartón por ID (usa delete del BaseModel)
    public function deleteCarton($id): bool {
        return $this->delete($id);
    }

    // Buscar cartones por persona
    public function getByPersona($personaId): array {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE persona_id = :persona_id",
            ['persona_id' => $personaId]
        );
    }

    // Buscar cartones por sorteo
    public function getBySorteo($sorteoId): array {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE sorteo_id = :sorteo_id",
            ['sorteo_id' => $sorteoId]
        );
    }

    // Verificar si un número ya fue vendido para un sorteo específico
    public function isNumeroVendido($sorteoId, $numero): bool {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM {$this->table} 
             WHERE sorteo_id = :sorteo_id AND numero = :numero AND status = 'V'",
            ['sorteo_id' => $sorteoId, 'numero' => $numero]
        );
        return isset($result[0]['total']) && $result[0]['total'] > 0;
    }
}
