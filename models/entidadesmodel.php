<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class EntidadesModel extends BaseModel {  // Nombre de clase con mayúscula
    public function __construct() {
        parent::__construct();  // Primero llamar al padre
        $this->table = 'entidades';
    }
    public function getAll(): array {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY id ASC"  // Cambiado a 'posicion' para consistencia
        );
    }
    public function getEntidades(): array {
        return $this->query(
            "SELECT nombreentidad ,  logo, emailcta ,cedulatitular ,nombretitular , tipocta,numcta  FROM {$this->table} WHERE status = :st",
            ['st' => 'A']  // Parámetro preparado
        );
    }
   
}