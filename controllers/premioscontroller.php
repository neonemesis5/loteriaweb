<?php 
use Core\BaseController;
require_once __DIR__ . '/../models/premiosmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';
class PremiosController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new premiosModel();
    }

    public function getPremiosSorteo($id): ?array {
        try {
            return $this->model->getPremiosSorteo($id);
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