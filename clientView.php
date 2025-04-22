<?php
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;

    // Crear persona
/*
$controller->create([
    'location_id' => 1,
    'name' => 'Juan',
    'lastname' => 'Pérez',
    'telefonos' => '123456',
    'email' => 'juan@mail.com',
    'nrocedula' => 'ABC123',
    'login' => 'juanito',
    'password' => password_hash('1234', PASSWORD_DEFAULT),
    'instagram' => '@juan',
    'facebook' => 'fb.com/juan',
    'referidos' => '',
    'validado' => '1',
    'status' => 'A'
]);*/

/*
// Buscar persona por ID
$persona = $controller->getById(1);

// Actualizar persona
$controller->update(1, [...]);
*/

}