<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['action'] ?? '';


    if ($accion === 'seleccionarVeterinaria') {
        // Capturamos el id_veterinaria seleccionado
        $id_veterinaria = $_POST['id_veterinaria'] ?? '';

        session_start();
        $_SESSION['user']['id_veterinaria'] = $id_veterinaria;
        mostrarSweetAlert(
            "success",
            "Veterinaria Seleccionada",
            "Ha seleccionado la veterinaria correctamente.",      
            BASE_URL . "/veterinaria/dashboard"
            // Se utiliza BASE_URL para construir una ruta dinámica del sistema.
            // BASE_URL contiene automáticamente el dominio y la carpeta base del proyecto.
            // Esto permite que la ruta funcione correctamente tanto en entorno local
            // (ej: http://localhost/vetwilling) como en producción
            // (ej: https://vetwilling.com) sin tener que modificar el código.


            //"/veterinaria/dashboard"
        );
        exit();;
    }

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

    // Verificar si el modelo devolvio un error y mostrar la alerta correspondiente segun el tipo de error
    if (isset($resultado['error'])) {
        $tipoAlerta = $resultado['tipo'] ?? 'error';
        $tituloAlerta = $resultado['titulo'] ?? 'Error de autenticacion';
        mostrarSweetAlert($tipoAlerta, $tituloAlerta, $resultado['error']);
        exit();
    }

    // Si pasa esta linea, el usuario es valido

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user'] = [
        'id_usuario' => $resultado['id_usuario'],
        'id_rol' => $resultado['id_rol'],
        'email' => $resultado['email'],
        'password_hash' => $resultado['password_hash'],
        'id_veterinaria' => $resultado['id_veterinaria'],
        'nombres' => $resultado['nombres'],
        'apellidos' => $resultado['apellidos'],
        'estado' => $resultado['estado'],
    ];

    // Cargar preferencias del usuario al iniciar sesión
    if ((int)$resultado['id_rol'] === 3) {
        if (!class_exists('PreferenciasManager')) {
            require_once __DIR__ . '/../services/PreferenciasManager.php';
        }
        $_pm_login = new PreferenciasManager((int)$resultado['id_usuario']);
        $_prefs_login = $_pm_login->obtener();
        $_SESSION['lang'] = $_prefs_login['idioma'];
        $_SESSION['user']['tema'] = $_prefs_login['tema'];
    }

    $rol = $resultado['id_rol'];

    // Validamos si el usuario retorna mas de un id_veterinaria (caso veterinarios asociados a varias veterinarias)
    if (strpos($resultado['id_veterinaria'], ',') !== false) {
        // mostramos una alerta para que seleccione la veterinaria a la que desea ingresar
        mostrarSweetAlert(
            'info',
            'Seleccione Veterinaria',
            'Usted esta asociado a varias veterinarias, por favor seleccione a cual desea ingresar.',
            BASE_URL . '/login/seleccionarVeterinaria'
        );
        exit();
    }

    switch ($rol) {
        case 1: // Administrador
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido administrador " . $resultado['nombres'],
                BASE_URL . "/admin/dashBoard"
            );
            break;

        case 2: // Veterinario
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido veterinario " . $resultado['nombres'],
                BASE_URL . "/veterinaria/dashboard"
            );
            break;

        case 3: // Propietario
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido " . $resultado['nombres'],
                BASE_URL . "/cliente/dashboard"
            );
            break;

        case 4: // Representante
            mostrarSweetAlert(
                "success",
                "Inicio de Sesión Exitoso",
                "Bienvenido Representante " . $resultado['nombres'],
                BASE_URL . "/representante/dashboard"
            );
            break;

        default:
            mostrarSweetAlert("error", "Rol no reconocido", "Redirigiendo al login...", BASE_URL . "/login");
            break;
    }
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
