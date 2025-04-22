<?php
use Core\BaseController;
require_once __DIR__ . '/../models/personamodel.php';
require_once __DIR__ . '/../core/basecontroller.php';

class PersonaController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new PersonaModel();
    }

    /**
     * Obtener todas las personas
     */
    public function getAll(): array {
        try {
            return $this->model->getAll();
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }

    /**
     * Obtener una persona por su ID
     */
    public function getById(int $id): ?array {
        try {
            return $this->model->getById($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Crear una nueva persona
     */
    public function create(array $data): int {
        try {
            return $this->model->createPersona($data);
        } catch (Exception $e) {
            $this->handleError($e);
            return 0;
        }
    }

    /**
     * Actualizar persona por ID
     */
    public function update(int $id, array $data): bool {
        try {
            return $this->model->updatePersona($id, $data);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Eliminar una persona
     */
    public function delete(int $id): bool {
        try {
            return $this->model->deletePersona($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }
    public function getByCedula(int $cedula): ?array {
        try {
            return $this->model->getByCedula($cedula);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Maneja errores de forma centralizada
     */
    protected function handleError(Exception $e): void {
        error_log($e->getMessage());
    }
}
