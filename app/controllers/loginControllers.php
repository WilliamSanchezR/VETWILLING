<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capturamos en variables los valores enviados a traves de los names de formulario y el metho POST 

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // validacion de campos vacios

    if (empty($email) || empty($password)) {
        mostrarSweetAlert('error', 'Campos vacio', 'Por favor complete todos los campos');
        exit();
    }

    // POO - instanciamos la clase del modelo, para acceder a un METHOd (funcion) en especifico.

    $login = new Login();
    $resultado =  $login->autenticar($email, $password);

    // Verificar si el modelo devolvio un error

    if (isset($resultado['error'])) {
        mostrarSweetAlert('error', 'error de atunticacion', $resultado['error']);
        exit();
    }

    // Si pasa esta linea, el usuario es valido

    session_start();
    $_SESSION['user'] = [
        'id_usuario' => $resultado['id_usuario'],
        'id_rol' => $resultado['id_rol'],
        'email' => $resultado['email'],
        'estado' => $resultado['estado'],
        'perfil' => $resultado['perfil'] // Guardamos el perfil completo
    ];

    $user = $resultado['id_rol'];

    switch ($user['id_rol']) {

        case '1': //Administrador

            $redirectUrl = '/inicio-sesion-administrador';
            mostrarSweetAlert("success", "Inicio de Sesión Exitoso", "Bienvenido administrador" . $user['nombres']);

            break;

        case '2': //Veterinario

            $redirectUrl = '/veterinario/dashboard';
            mostrarSweetAlert("success", "Inicio de Sesión Exitoso", "Bienvenido veterinario" . $user['nombres']);

            break;

        case '3': //Propietario

            $redirectUrl = '/inicio-sesion-propietario';
            mostrarSweetAlert("success", "Inicio de Sesión Exitoso", "Bienvenido" . $user['nombres']);

            break;

        default:

            $mensaje = 'Rol no reconocido. Redirigiendo al login...';
            $redirectUrl = '/login';

            break;
    }
    exit();
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
