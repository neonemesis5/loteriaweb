<?php
use Core\BaseController;

require_once __DIR__ . '/../models/cartonmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';

class CronController extends BaseController {
    protected $model;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->model = new CartonModel();
    }
    
    /**
     * Limpia reservaciones expiradas (para ejecución CLI)
     */
    public function cleanReservations(): string {
        // Validar ejecución desde CLI
        if (php_sapi_name() !== 'cli') {
            header('HTTP/1.0 403 Forbidden');
            exit('Acceso denegado. Este script solo puede ejecutarse desde CLI.');
        }
        
        try {
            $deleted = $this->model->eliminarReservacionesExpiradas();
            return date('[Y-m-d H:i:s]') . " - Eliminadas $deleted reservaciones expiradas\n";
        } catch (\Exception $e) {
            error_log("Cron Error: " . $e->getMessage());
            return date('[Y-m-d H:i:s]') . " - Error: " . $e->getMessage() . "\n";
        }
    }
}