<?php
require_once __DIR__ . '/../controllers/croncontroller.php'; 
// use App\constrollers\croncon;

// Solo permitir ejecución CLI
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos');
}

$cronController = new CronController();
echo $cronController->cleanReservations();