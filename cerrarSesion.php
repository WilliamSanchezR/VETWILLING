<?php
/**
 * Script para cerrar sesión de usuario
 * Destruye la sesión actual y redirige al inicio
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración
require_once __DIR__ . '/config/config.php';

// Guardar información antes de destruir la sesión (opcional, para logs)
$user_id = $_SESSION['user']['id'] ?? null;
$user_name = $_SESSION['user']['nombre'] ?? 'Usuario desconocido';

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(), 
        '', 
        time() - 3600, 
        '/',
        '',
        isset($_SERVER['HTTPS']), // secure
        true // httponly
    );
}

// Destruir la sesión
session_destroy();

// Opcional: Registrar el cierre de sesión en logs
// error_log("Usuario {$user_name} (ID: {$user_id}) cerró sesión el " . date('Y-m-d H:i:s'));

// Prevenir caché de esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// Redirigir al inicio
// Redirigir a la raíz de Aventura Go
header("Location: http://localhost/registro_gastos/");
exit();
?>