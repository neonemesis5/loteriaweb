<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class LocationModel extends BaseModel {  // Nombre de clase con mayúscula
    public function __construct() {
        parent::__construct();  // Primero llamar al padre
        $this->table = 'location';
    }
    public function getAll(): array {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY id ASC"  // Cambiado a 'posicion' para consistencia
        );
    }
    public function getCountries(): array {
        return $this->query(
            "SELECT id,name FROM {$this->table} WHERE ancestor_id is null ORDER BY id ASC"  // Cambiado a 'posicion' para consistencia
        );
    }
    public function getStatesByCountries($countryId): array {
        return $this->query(
            "SELECT id,name FROM {$this->table} WHERE ancestor_id = :country_id ORDER BY id ASC",
            ['country_id' => $countryId]  // Parámetro preparado
        );
    }
}