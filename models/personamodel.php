<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class PersonaModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'persona';
    }

    /**
     * Obtener todas las personas (ordenadas por ID descendente)
     */
    public function getAll(): array {
        return $this->query("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    /**
     * Obtener una persona por su ID
     */
    public function getById(int $id): ?array {
        return $this->find($id);
    }

    /**
     * Crear una nueva persona
     */
    public function createPersona(array $data): int {
        return $this->create($data);
    }

    /**
     * Actualizar una persona existente
     */
    public function updatePersona(int $id, array $data): bool {
        return $this->update($id, $data);
    }

    /**
     * Eliminar una persona por ID
     */
    public function deletePersona(int $id): bool {
        return $this->delete($id);
    }
    /** obtener persona por cedula */
    public function getByCedula($cedula): array {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE nrocedula = :cedulaPer",
            ['cedulaPer' => $cedula]
        );
    }
}
