<?php

require_once __DIR__ . '/../helpers/alert_helper.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión si existe, asi al dar atras no abre la pagina en la que estaba anteriormente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir con mensaje
mostrarSweetAlert(
    'success',
    'Sesión cerrada',
    'Has cerrado sesión correctamente.',
    '/vetwilling/'
);

exit();