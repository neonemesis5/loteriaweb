<?php

use Core\BaseController;
require_once __DIR__ . '/../models/sorteomodel.php';
require_once __DIR__ . '/../core/basecontroller.php';
class SorteoController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new SorteoModel();
    }

    /**
     * Obtiene todos los sorteos
     */
    public function getAllSorteos(): array {
        try {
            return $this->model->getAll();
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }

    /**
     * Obtiene un sorteo por ID
     */
    public function getSorteo(int $id): ?array {
        try {
            return $this->model->find($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Crea un nuevo sorteo
     */
    public function createSorteo(array $data): array {
        $errors = $this->validateSorteoData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $id = $this->model->create($data);
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualiza un sorteo existente
     */
    public function updateSorteo(int $id, array $data): array {
        $errors = $this->validateSorteoData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $success = $this->model->update($id, $data);
            return ['success' => $success];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Elimina un sorteo
     */
    public function deleteSorteo(int $id): array {
        try {
            $success = $this->model->delete($id);
            return ['success' => $success];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene sorteos activos
     */
    public function getActiveSorteos(): array {
        try {
            return $this->model->getActive()[0];
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }

    /**
     * Valida los datos del sorteo
     */
    protected function validateSorteoData(array $data): array {
        $errors = [];
        $requiredFields = ['titulo', 'fecha_sorteo', 'nrosorteo'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "El campo $field es requerido";
            }
        }

        return $errors;
    }

    /**
     * Maneja errores de forma centralizada
     */
    protected function handleError(Exception $e): void {
        // Aquí puedes registrar el error, enviar notificaciones, etc.
        error_log($e->getMessage());
    }
}