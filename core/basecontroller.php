<?php
namespace Core;

use RuntimeException;

abstract class BaseController {
    protected static array $instances = [];
    protected array $config = [];
    protected array $services = [];

    public function __construct(array $config = []) {
        $this->config = $config;
        $this->initialize();
    }

    /**
     * Inicialización básica del controlador
     */
    protected function initialize(): void {
        $this->loadConfig();
    }

    /**
     * Carga la configuración desde archivos y variables de entorno
     */
    private function loadConfig(): void {
        // Carga configuración desde el archivo en el directorio raíz si existe
        if (file_exists(__DIR__.'/../../config.php')) {
            $this->config = array_merge($this->config, require __DIR__.'/../../config.php');
        }
        
        // Carga variables de entorno
        $this->loadEnv(__DIR__ . '/../../.env');
    }

    /**
     * Carga variables de entorno desde el archivo .env
     */
    private function loadEnv(string $filePath): void {
        if (!file_exists($filePath)) return;

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignora comentarios
            if (strpos(trim($line), '#') === 0) continue;
            
            // Divide nombre y valor
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Solo asigna si no existe
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                $this->config[$name] = $value;
            }
        }
    }

    /**
     * Registra un servicio para creación bajo demanda
     */
    public function register(string $name, callable $resolver): void {
        $this->services[$name] = $resolver;
    }

    /**
     * Obtiene una instancia de servicio o modelo
     */
    public function get(string $name) {
        // Si ya existe una instancia, la retorna
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        // Si es un servicio registrado, lo crea y guarda
        if (isset($this->services[$name])) {
            self::$instances[$name] = $this->services[$name]();
            return self::$instances[$name];
        }

        // Busca automáticamente modelos en el directorio models/
        $modelClass = 'Models\\' . ucfirst($name);
        if (class_exists($modelClass)) {
            self::$instances[$name] = new $modelClass();
            return self::$instances[$name];
        }

        throw new RuntimeException("Servicio o modelo no encontrado: $name");
    }

    /**
     * Método mágico para acceder a servicios/modelos como propiedades
     */
    public function __get(string $name) {
        return $this->get($name);
    }

    /**
     * Método mágico para verificar existencia de servicios/modelos
     */
    public function __isset(string $name): bool {
        return isset($this->services[$name]) || class_exists('Models\\' . ucfirst($name));
    }

    /**
     * Obtiene un valor de configuración
     */
    public function config(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * Patrón Singleton: Obtiene la instancia única del controlador
     */
    public static function getInstance(array $config = []): self {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static($config);
        }
        return self::$instances[$class];
    }

    /**
     * Método específico para obtener modelos (opcional)
     */
    public function model(string $name): BaseModel {
        $model = $this->get($name);
        if (!$model instanceof BaseModel) {
            throw new RuntimeException("$name no es un modelo válido");
        }
        return $model;
    }
}