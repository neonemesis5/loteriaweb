<?php
use Core\BaseController;
require_once __DIR__ . '/../models/autenticadormodel.php';
require_once __DIR__ . '/../core/basecontroller.php';

class AutenticadorController extends BaseController {
    protected $model;

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->model = new AutenticadorModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Asegura que la sesión está iniciada
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(string $login, string $password): bool {
        try {
            $usuario = $this->model->findByLogin($login);
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Guardar datos del usuario en la sesión
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'name' => $usuario['name'],
                    'lastname' => $usuario['lastname'],
                    'email' => $usuario['email'],
                    'login' => $usuario['login']
                ];
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void {
        session_destroy();
    }

    /**
     * Verifica si hay sesión activa
     */
    public function isAuthenticated(): bool {
        return isset($_SESSION['usuario']);
    }

    /**
     * Obtener usuario logueado
     */
    public function getUsuario(): ?array {
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Manejo centralizado de errores
     */
    protected function handleError(Exception $e): void {
        error_log($e->getMessage());
    }
}
