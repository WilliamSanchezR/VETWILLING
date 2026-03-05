<?php

require_once __DIR__ . '/../helpers/alert_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guardar datos antes de destruir (para que el helper pueda usarlos)
$userData = $_SESSION['user'] ?? null;

// Vaciar sesión
$_SESSION = [];

// Eliminar cookie para evitar volver con botón atrás
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

// Restaurar datos temporalmente para el mensaje
if ($userData) {
    $_SESSION['user'] = $userData;
}

// Mostrar pantalla personalizada
mostrarCierreSesion('/vetwilling/login');

// Ahora sí destruir completamente
session_destroy();

exit();