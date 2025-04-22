<?php
namespace Core;

use RuntimeException;
use InvalidArgumentException;

abstract class Base_Controller {
    protected static array $instances = [];
    protected array $config = [];
    protected array $services = [];
    protected array $validationRules = [];
    protected bool $csrfProtection = true;

    public function __construct(array $config = []) {
        $this->config = $config;
        $this->initialize();
        $this->generateCsrfToken();
    }

    /*--------------------------------------------------------------
    # SEGURIDAD CENTRALIZADA
    --------------------------------------------------------------*/
    
    /**
     * Middleware de seguridad para todas las acciones
     */
    protected function secureRequest(array $data): array {
        $this->validateRequestMethod();
        $this->validateCsrfToken($data['csrf_token'] ?? '');
        return $this->sanitizeInput($data);
    }

    /**
     * Genera token CSRF si no existe
     */
    protected function generateCsrfToken(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Valida el método HTTP contra los permitidos
     */
    protected function validateRequestMethod(string $expected = 'POST'): void {
        $currentMethod = $_SERVER['REQUEST_METHOD'];
        if (strtoupper($expected) !== $currentMethod) {
            throw new RuntimeException("Método $currentMethod no permitido", 405);
        }
    }

    /**
     * Valida token CSRF
     */
    protected function validateCsrfToken(string $token): void {
        if ($this->csrfProtection && !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new RuntimeException("Token CSRF inválido", 403);
        }
    }

    /**
     * Sanitiza todos los inputs
     */
    protected function sanitizeInput(array $data): array {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitizeInput($value);
            } else {
                $clean[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $clean;
    }

    /**
     * Valida datos contra reglas definidas
     */
    protected function validateData(array $data, array $rules = null): array {
        $rules = $rules ?? $this->validationRules;
        $clean = [];
        $errors = [];

        foreach ($rules as $field => $type) {
            if (!isset($data[$field])) {
                $errors[$field] = 'Campo requerido';
                continue;
            }

            switch ($type) {
                case 'int':
                    $clean[$field] = filter_var($data[$field], FILTER_VALIDATE_INT);
                    if ($clean[$field] === false) $errors[$field] = 'Debe ser entero';
                    break;
                case 'email':
                    $clean[$field] = filter_var($data[$field], FILTER_SANITIZE_EMAIL);
                    if (!filter_var($clean[$field], FILTER_VALIDATE_EMAIL)) $errors[$field] = 'Email inválido';
                    break;
                case 'string':
                    $clean[$field] = filter_var($data[$field], FILTER_SANITIZE_STRING);
                    break;
                default:
                    $clean[$field] = $data[$field];
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException("Errores de validación: " . json_encode($errors));
        }

        return $clean;
    }

    /*--------------------------------------------------------------
    # MÉTODOS HEREDADOS (compatibles con versión anterior)
    --------------------------------------------------------------*/
    
    protected function initialize(): void {
        $this->loadConfig();
    }

    private function loadConfig(): void {
        if (file_exists(__DIR__.'/../../config.php')) {
            $this->config = array_merge($this->config, require __DIR__.'/../../config.php');
        }
        $this->loadEnv(__DIR__ . '/../../.env');
    }

    private function loadEnv(string $filePath): void {
        if (!file_exists($filePath)) return;

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                $this->config[$name] = $value;
            }
        }
    }

    public function register(string $name, callable $resolver): void {
        $this->services[$name] = $resolver;
    }

    public function get(string $name) {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        if (isset($this->services[$name])) {
            self::$instances[$name] = $this->services[$name]();
            return self::$instances[$name];
        }

        $modelClass = 'Models\\' . ucfirst($name);
        if (class_exists($modelClass)) {
            self::$instances[$name] = new $modelClass();
            return self::$instances[$name];
        }

        throw new RuntimeException("Servicio o modelo no encontrado: $name");
    }

    public function __get(string $name) {
        return $this->get($name);
    }

    public function __isset(string $name): bool {
        return isset($this->services[$name]) || class_exists('Models\\' . ucfirst($name));
    }

    public function config(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    public static function getInstance(array $config = []): self {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static($config);
        }
        return self::$instances[$class];
    }

    public function model(string $name): BaseModel {
        $model = $this->get($name);
        if (!$model instanceof BaseModel) {
            throw new RuntimeException("$name no es un modelo válido");
        }
        return $model;
    }

    /*--------------------------------------------------------------
    # MANEJO DE RESPUESTAS
    --------------------------------------------------------------*/
    
    protected function jsonResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function errorResponse(string $message, int $statusCode = 400): void {
        $this->jsonResponse([
            'success' => false,
            'error' => $message,
            'code' => $statusCode
        ], $statusCode);
    }
}