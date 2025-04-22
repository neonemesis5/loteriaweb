<?php

use Core\BaseController;

require_once __DIR__ . '/../models/personamodel.php';
require_once __DIR__ . '/../models/cartonmodel.php';
require_once __DIR__ . '/../models/entidadesmodel.php';
require_once __DIR__ . '/../core/basecontroller.php';

class CompraController extends BaseController
{
    protected $personaModel;
    protected $cartonModel;
    protected $entidades;

    public function __construct()
    {
        parent::__construct();
        $this->personaModel = new PersonaModel();
        $this->cartonModel = new CartonModel();
        $this->entidades = new EntidadesModel();
    }

    public function registrarCompra($data)
    {
        try {

            // Validar datos recibidos
            $requiredFields = ['nombre', 'apellido', 'numIdentificacion', 'pais', 'email', 'telefono', 'numeros', 'sorteo_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            if (empty($data['is_adult']) || $data['is_adult'] !== '1') {
                throw new Exception("Debes ser mayor de edad para participar");
            }

            // Crear persona 
            $personaData = [
                'location_id' => (int)$data['pais'],
                'name' => $data['nombre'],
                'lastname' => $data['apellido'],
                'telefonos' => $data['telefono'],
                'email' => $data['email'],
                'nrocedula' => $data['numIdentificacion'],
                'login' => $data['email'],
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                'validado' => '1',
                'status' => 'A'
            ];

            $res = $this->personaModel->getByCedula($personaData['nrocedula']);
            // if(!empty($res))
            //     file_put_contents('error.log', json_encode($res), FILE_APPEND);
            if (empty($this->personaModel->getByCedula($personaData['nrocedula']))) {
                $personaId = $this->personaModel->createPersona($personaData);
            } else {
                $personaId = $res[0]['id'];
            }
            if (!$personaId) {
                throw new Exception("Error al registrar el cliente");
            }
            // Registrar números comprados 
            $numeros = $data['numeros'];
            $sorteoId = (int)$data['sorteo_id'];
            $precio = (float)$data['precio'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $fecha = date('Y-m-d H:i:s');
            $_duplicados = [];
            foreach ($numeros as $numero) {
                $cartonData = [
                    'persona_id' => $personaId,
                    'sorteo_id' => $sorteoId,
                    'fechacompra' => $fecha,
                    'numero' => (int)$numero,
                    'ip' => $ip,
                    'precio' => $precio,
                    'status' => 'V' // Vendido
                ];
                $_restemp=$this->cartonModel->getById($numero);
                if(empty($_restemp))
                    $this->cartonModel->insert($cartonData);
                else
                    $numeros = array_diff($numeros, [$numero]);
            }
           
            // Enviar email de confirmación 
            $this->enviarEmailConfirmacion($personaData, $numeros, $sorteoId, $precio);
            $precioTotal = number_format($precio * count($numeros), 2);
            $whatsappUrl = $this->generarWhatsAppUrl($personaData, $numeros, $sorteoId, $precioTotal);
        
        return [
            'success' => true,
            'message' => 'Registro exitoso',
            'whatsapp_url' => $whatsappUrl
        ];
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function enviarEmailConfirmacion($personaData, $numeros, $sorteoId, $precioTotal)
    {
        $to = $personaData['email'];
        $subject = "Confirmación de compra - Los Audaces";
        $logMessage = "";

        // Registrar datos de entrada
        $logMessage .= "Datos de entrada:\n";
        $logMessage .= "Destinatario: $to\n";
        $logMessage .= "Sorteo ID: $sorteoId\n";
        $logMessage .= "Números: " . implode(', ', $numeros) . "\n";
        $logMessage .= "Precio total: $precioTotal\n";

        $numerosStr = implode(', ', $numeros);
        $total = number_format($precioTotal * count($numeros), 2);
        $entidades = $this->entidades->getEntidades();

        // Generar la tabla HTML de métodos de pago
        $metodosPagoHTML = '<h3>Métodos de Pago Disponibles:</h3>';
        $metodosPagoHTML .= '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; margin: 20px 0;">';
        $metodosPagoHTML .= '
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="text-align: left;">Entidad</th>
                    <th style="text-align: left;">Titular</th>
                    <th style="text-align: left;">Tipo de Cuenta</th>
                    <th style="text-align: left;">Número/Email</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($entidades as $entidad) {
            $logoPath = 'https://losaudaces.com/resources/entidades/' . $entidad['logo'];

            $metodosPagoHTML .= '
                <tr>
                    <td style="vertical-align: top;">
                        <img src="' . $logoPath . '" alt="' . $entidad['nombreentidad'] . '" style="max-height: 30px; margin-right: 10px;">
                        ' . $entidad['nombreentidad'] . '
                    </td>
                    <td style="vertical-align: top;">' . $entidad['nombretitular'] . '<br>'
                . (!empty($entidad['cedulatitular']) ? 'CI: ' . $entidad['cedulatitular'] : '') . '
                    </td>
                    <td style="vertical-align: top;">' . $entidad['tipocta'] . '</td>
                    <td style="vertical-align: top;">'
                . (!empty($entidad['numcta']) ? $entidad['numcta'] : $entidad['emailcta']) . '
                    </td>
                </tr>';
        }

        $metodosPagoHTML .= '</tbody></table>';

        $message = "
        <html>
        <head>
            <title>Confirmación de compra</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                h2 { color: #2c3e50; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background-color: #f2f2f2; text-align: left; padding: 10px; }
                td { padding: 10px; border-bottom: 1px solid #ddd; vertical-align: top; }
                .highlight { background-color: #fffde7; padding: 15px; border-left: 4px solid #ffd600; }
                .warning { color: #d32f2f; font-weight: bold; }
                .whatsapp-link { color: #25D366; text-decoration: none; font-weight: bold; }
            </style>
        </head>
        <body>
            <h2>¡Gracias por tu compra, {$personaData['name']}!</h2>
            <p>Has seleccionado los siguientes números para el sorteo #$sorteoId:</p>
            <p><strong>Números:</strong> $numerosStr</p>
            <p><strong>Total a pagar:</strong> $$total</p>
            
            <div class='highlight'>
                <p class='warning'>¡Importante!</p>
                <p>Debes realizar el pago en un tiempo máximo de 60 minutos, de lo contrario los números serán liberados.</p>
                <p>Por favor envía el comprobante de pago a nuestro 
                <a href='https://api.whatsapp.com/send/?phone=573204563721&text=Hola,%20aquí%20está%20mi%20comprobante%20de%20pago%20para%20los%20números%20{$numerosStr}' 
                   class='whatsapp-link'>
                   WhatsApp
                </a> 
                o haz clic en este número: 
                <a href='https://api.whatsapp.com/send/?phone=573204563721&text=Hola,%20aquí%20está%20mi%20comprobante%20de%20pago%20para%20los%20números%20{$numerosStr}' 
                   class='whatsapp-link'>
                   +57 320 4563721
                </a>
            </p>
            </div>
            $metodosPagoHTML
            
            <p>Te deseamos mucha suerte en el sorteo.</p>
            <p>Atentamente,</p>
            <p>El equipo de Los Audaces</p>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Los Audaces <no-reply@losaudaces.com>\r\n";

        // Registrar el email completo
        $logMessage .= "\nContenido del email:\n";
        $logMessage .= "Asunto: $subject\n";
        $logMessage .= "Cabeceras: " . str_replace("\r\n", " | ", $headers) . "\n";
        $logMessage .= "Mensaje: " . strip_tags($message) . "\n";

        // Intentar enviar el email
        $mailSent = mail($to, $subject, $message, $headers);

        $_subject = '✅ Nueva Compra Registrada - Cliente: ' . $personaData['name'] . ' - Números: ' . $numerosStr;

        $_message = "
        <html>
        <head>
            <title>Notificación de Nueva Compra</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                }
                .header {
                    background-color: #2c3e50;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    border: 1px solid #ddd;
                    padding: 20px;
                    border-top: none;
                    border-radius: 0 0 5px 5px;
                }
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                .data-table th {
                    background-color: #f2f2f2;
                    text-align: left;
                    padding: 10px;
                }
                .data-table td {
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
                .highlight {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-left: 4px solid #2c3e50;
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2 style='margin:0;'>Nueva Compra Registrada</h2>
            </div>
            
            <div class='content'>
                <table class='data-table'>
                    <tr>
                        <th colspan='2' style='text-align:center;'>Información del Cliente</th>
                    </tr>
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>{$personaData['name']} {$personaData['lastname']}</td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>{$personaData['telefonos']}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{$personaData['email']}</td>
                    </tr>
                </table>
        
                <table class='data-table'>
                    <tr>
                        <th colspan='2' style='text-align:center;'>Detalles de la Compra</th>
                    </tr>
                    <tr>
                        <td><strong>Sorteo #:</strong></td>
                        <td>$sorteoId</td>
                    </tr>
                    <tr>
                        <td><strong>Números:</strong></td>
                        <td>$numerosStr</td>
                    </tr>
                    <tr>
                        <td><strong>Total a pagar:</strong></td>
                        <td>$$total</td>
                    </tr>
                </table>
        
                <div class='highlight'>
                    <p><strong>⚠️ Acción requerida:</strong></p>
                    <p>Verificar el pago del cliente y confirmar la reserva de los números.</p>
                    <p>Contactar al cliente si no se recibe el comprobante en los próximos 60 minutos.</p>
                </div>
        
                <p style='text-align:center; color:#666; margin-top:20px;'>
                    Este email fue generado automáticamente por el sistema de Los Audaces
                </p>
            </div>
        </body>
        </html>
        ";

        mail('los.audaces1625@gmail.com', $_subject, $_message, $headers);

        // Registrar resultado
        $logMessage .= "\nResultado del envío: " . ($mailSent ? "ÉXITO" : "FALLO") . "\n";
        $logMessage .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $logMessage .= "----------------------------------------\n";

        // Escribir en el log
        // $this->logEmail($logMessage, $mailSent);

        return $mailSent;
    }
    private function generarWhatsAppUrl($personaData, $numeros, $sorteoId, $precioTotal)
    {
        $numerosStr = implode(', ', $numeros);
        $total = number_format($precioTotal * count($numeros), 2);

        $texto = rawurlencode(
            "Hola, mi nombre es *{$personaData['name']} {$personaData['lastname']}*.\n" .
            "Teléfono: {$personaData['telefonos']}\n" .
            "Correo: {$personaData['email']}\n\n" .
            "He realizado una compra en el sorteo #$sorteoId.\n" .
            "Números seleccionados: *$numerosStr*\n" .
            "Total pagado: *$$total*\n\n" .
            "Adjunto el comprobante de pago.".
            "El metodo de Pago Elegido es: ".
            "Cuentas RUT 25.732.506-7 Banco Copec Pay Tipo de cuenta - Cuenta de Vista Nº de Cuenta 12573250601 \nZelle fmedinac24@gmail.com FA MEDINA C. CONSTRUCTION LLC \nChristian Gerardo Medina Colmenares Cedula 16.612.896 Nº de Cuenta 0134-0435-6643-5102-8330"
        );

        return "https://api.whatsapp.com/send/?phone=573204563721&text=$texto&type=phone_number";
    }

    private function logEmail($message, $success)
    {
        $logFile = __DIR__ . '/../email_logs/' . date('Y-m-d') . '.log';

        // Crear directorio si no existe
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        // Agregar estado al mensaje
        $status = $success ? "ENVIADO" : "FALLIDO";
        $logEntry = "[$status] " . date('Y-m-d H:i:s') . " - " . $message . "\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // También registrar en el error_log general si falló
        if (!$success) {
            error_log("Fallo en envío de email a: " . $message);
        }
    }
}
