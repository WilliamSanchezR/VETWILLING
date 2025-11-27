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
        mostrarSweetAlert('error', 'Error de autenticacion', $resultado['error']);
        exit();
    }

    // Si pasa esta linea, el usuario es valido

    session_start();
    $_SESSION['user'] = [
        'id_usuario' => $resultado['id_usuario'],
        'id_rol' => $resultado['id_rol'],
        'email' => $resultado['email'],
        'id_veterinaria' => $resultado['id_veterinaria'],
        'password_hash' => $resultado['password_hash'],
        'estado' => $resultado['estado'],
        'perfil' => $resultado['perfil']
    ];

    $rol = $resultado['id_rol'];

    switch ($rol) {
        case 1: // Administrador
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido administrador " . $resultado['perfil']['nombres'],
                "/vetwilling/administrador/dashBoard"
            );
            break;

        case 2: // Veterinario
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido veterinario " . $resultado['perfil']['nombres'],
                "/vetwilling/veterinaria/dashboard"
            );
            break;

        case 3: // Propietario
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido " . $resultado['perfil']['nombres'],
                "/vetwilling/cliente/dashBoard"
            );
            break;

        default:
            mostrarSweetAlert("error", "Rol no reconocido", "Redirigiendo al login...", "/vetwilling/login");
            break;
    }
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
