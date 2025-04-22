<?php 
use Core\BaseController;
require_once __DIR__ . '/../models/entidadesmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';
class EntidadesController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new EntidadesModel();
    }

    public function getAllEntidades(): array {
        try {
            return $this->model->all();
        } catch (Exception $e) {
            $this->handleError($e);
            return [];
        }
    }
    public function getEntidades(): ?array {
        try {
            return $this->model->getEntidades();
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