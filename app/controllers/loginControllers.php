<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require __DIR__ . '/../models/Login.php';

/**
 * Devuelve al formulario de login mostrando el error en la misma página (patrón PRG).
 * Conserva el correo escrito para que el usuario no tenga que digitarlo de nuevo.
 */
function volverAlLogin(string $mensaje, string $tipo = 'error', string $email = ''): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['login_flash'] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje,
        'email' => $email,
    ];
    header('Location: ' . BASE_URL . '/login');
    exit();
}

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

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validamos el token CSRF del formulario (protege contra envíos desde otros sitios)
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        volverAlLogin('Tu sesión expiró. Vuelve a intentarlo.', 'warning', $email);
    }

    // validacion de campos vacios

    if ($email === '' || $password === '') {
        volverAlLogin('Por favor completa el correo y la contraseña.', 'error', $email);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        volverAlLogin('Ingresa un correo electrónico válido.', 'error', $email);
    }

    // POO - instanciamos la clase del modelo, para acceder a un METHOd (funcion) en especifico.

    $login = new Login();
    $resultado =  $login->autenticar($email, $password);

    // Verificar si el modelo devolvio un error y mostrarlo en el formulario
    if (isset($resultado['error'])) {
        // No revelamos si el correo existe: usuario inexistente y contraseña errada dan el mismo mensaje
        $credencialesInvalidas = in_array($resultado['error'], ['Usuario no encontrado', 'Contraseña incorrecta'], true);
        $mensaje = $credencialesInvalidas ? 'Correo o contraseña incorrectos.' : $resultado['error'];
        $tipo = ($resultado['tipo'] ?? 'error') === 'warning' ? 'warning' : 'error';
        volverAlLogin($mensaje, $tipo, $email);
    }

    // Si pasa esta linea, el usuario es valido

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Nuevo id de sesión al autenticarse, para evitar fijación de sesión
    session_regenerate_id(true);
    unset($_SESSION['login_flash']);

    // El hash de la contraseña no se guarda en sesión: ninguna vista lo necesita
    $_SESSION['user'] = [
        'id_usuario' => $resultado['id_usuario'],
        'id_rol' => $resultado['id_rol'],
        'email' => $resultado['email'],
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
    if (strpos((string) $resultado['id_veterinaria'], ',') !== false) {
        header('Location: ' . BASE_URL . '/login/seleccionarVeterinaria');
        exit();
    }

    // Redirigimos directo al panel de cada rol
    $paneles = [
        1 => '/admin/dashBoard',         // Administrador
        2 => '/veterinaria/dashboard',   // Veterinario
        3 => '/cliente/dashboard',       // Propietario
        4 => '/representante/dashboard', // Representante
    ];

    if (!isset($paneles[(int) $rol])) {
        $_SESSION = [];
        volverAlLogin('Tu usuario no tiene un rol válido. Comunícate con el administrador.');
    }

    header('Location: ' . BASE_URL . $paneles[(int) $rol]);
    exit();
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
