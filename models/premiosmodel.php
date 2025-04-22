<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class PremiosModel extends BaseModel {  // Nombre de clase con mayúscula
    public function __construct() {
        parent::__construct();  // Primero llamar al padre
        $this->table = 'premios';
    }

    /**
     * Obtiene los premios de un sorteo activo
     * 
     * @param int $idSorteo ID del sorteo
     * @return array Premios del sorteo
     */
    public function getPremiosSorteo($idSorteo): array {
        return $this->query(
            "SELECT `name`, `descripcion`, `posicion`, `valor`, `foto` FROM {$this->table} 
            WHERE sorteo_id = :sorteoId AND `status` = :st 
            ORDER BY posicion ASC",
            ['sorteoId' => $idSorteo,'st' => 'A']  // Parámetro preparado
        );
    }

    public function getAll(): array {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY posicion ASC"  // Cambiado a 'posicion' para consistencia
        );
    }
}