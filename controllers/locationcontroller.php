<?php 
use Core\BaseController;
require_once __DIR__ . '/../models/locationmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';
class LocationController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new LocationModel();
    }

    public function getAllCountries(): array {
        try {
            return $this->model->getCountries();
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }
    public function getStatesByCountries($id): ?array {
        try {
            return $this->model->getStatesByCountries($id);
        } catch (Exception $e) {
            $this->handleError($e);
            return null;
        }
    }
        /**
     * Maneja errores de forma centralizada
     */
    protected function handleError(Exception $e): void {
        // Aquí puedes registrar el error, enviar notificaciones, etc.
        error_log($e->getMessage());
    }
}