<?php

use Core\BaseController;

require_once __DIR__ . '/../models/cartonmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';

class CartonController extends BaseController
{
    protected $model;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->model = new CartonModel();
    }

    /**
     * Obtener todos los cartones
     */
    public function getAll(): ?array
    {
        try {
            return $this->model->getAll();
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Obtener un cartón por ID
     */
    public function getById(int $id): ?array
    {
        try {
            return $this->model->getById($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Obtener cartones vendidos por sorteo
     */
    public function getCartonSellBySorteo(int $sorteoId): ?array
    {
        try {
            return $this->model->getCartonSellBySorteo($sorteoId);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Insertar nuevo cartón
     */
    public function insert(array $data): ?int
    {
        try {
            return $this->model->insert($data);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Actualizar un cartón por ID
     */
    public function update(int $id, array $data): bool
    {
        try {
            return $this->model->updateCarton($id, $data);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Eliminar un cartón por ID
     */
    public function delete(int $id): bool
    {
        try {
            return $this->model->deleteCarton($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Obtener cartones por persona
     */
    public function getByPersona(int $personaId): ?array
    {
        try {
            return $this->model->getByPersona($personaId);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Obtener cartones por sorteo
     */
    public function getBySorteo(int $sorteoId): ?array
    {
        try {
            return $this->model->getBySorteo($sorteoId);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }

    /**
     * Verificar si un número ya fue vendido en un sorteo
     */
    public function isNumeroVendido(int $sorteoId, int $numero): bool
    {
        try {
            return $this->model->isNumeroVendido($sorteoId, $numero);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Manejo centralizado de errores
     */
    protected function handleError(Exception $e): void
    {
        error_log($e->getMessage());
    }
}