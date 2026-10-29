<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
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

    // Capturamos en variables los valores enviados a traves de los names de formulario y el metho POST

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validamos el token CSRF del formulario (protege contra envíos desde otros sitios).
    // Se comprueba antes de cualquier acción, también la selección de veterinaria.
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        volverAlLogin('Tu sesión expiró. Vuelve a intentarlo.', 'warning', $email);
    }

    if ($accion === 'seleccionarVeterinaria') {
        // Solo un usuario que inició sesión con varias veterinarias puede elegir, y únicamente
        // entre las suyas. Nunca crea $_SESSION['user'] (antes, cualquiera podía fabricarlo aquí).
        if (!acceso_seleccionar_veterinaria($_POST['id_veterinaria'] ?? null)) {
            http_response_code(403);
            mostrarSweetAlert(
                "error",
                "Selección no válida",
                "No puedes ingresar a esa veterinaria.",
                BASE_URL . "/login"
            );
            exit();
        }
        mostrarSweetAlert(
            "success",
            "Veterinaria Seleccionada",
            "Ha seleccionado la veterinaria correctamente.",
            BASE_URL . "/veterinaria/dashboard"
        );
        exit();
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

    // Sesión del usuario con la marca de login, nuevo id de sesión (evita la fijación de sesión)
    // y nuevo token CSRF. Con varias veterinarias queda pendiente de seleccionar una.
    acceso_iniciar_sesion_usuario($resultado);
    unset($_SESSION['login_flash']);

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

    // Usuario asociado a varias veterinarias: debe elegir una antes de entrar
    if (acceso_seleccion_pendiente()) {
        header('Location: ' . BASE_URL . ACCESO_RUTA_SELECCION);
        exit();
    }

    // Redirigimos directo al panel de cada rol
    $panel = acceso_panel_rol((int) $resultado['id_rol']);

    if ($panel === null) {
        $_SESSION = [];
        volverAlLogin('Tu usuario no tiene un rol válido. Comunícate con el administrador.');
    }

    header('Location: ' . BASE_URL . $panel);
    exit();
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
