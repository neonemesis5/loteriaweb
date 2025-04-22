<?php
require_once '../controllers/autenticadorcontroller.php';

header('Content-Type: text/plain');

$auth = new AutenticadorController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if ($auth->login($login, $password)) {
        echo "Bienvenido, " . htmlspecialchars($auth->getUsuario()['name']);
    } else {
        echo "Credenciales incorrectas";
    }
} else {
    echo "Datos incompletos";
}
