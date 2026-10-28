<?php

// Controlador de recuperación de contraseña.
// index.php define $accion: 'solicitar' | 'formulario' | 'guardar'

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';
require_once __DIR__ . '/../services/PasswordResetService.php';

$servicio = new PasswordResetService();
$accion   = $accion ?? 'solicitar';

switch ($accion) {

    // POST /generar-clave -> envía el enlace al correo
    case 'solicitar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido($_POST['csrf_token'] ?? null)) {
            mostrarSweetAlert('error', 'Solicitud inválida', 'Recarga la página e intenta nuevamente', BASE_URL . '/recoverpw');
            break;
        }

        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            mostrarSweetAlert('error', 'Campo vacío', 'Por favor ingresa tu correo electrónico');
            break;
        }

        if (!$servicio->solicitar($email, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')) {
            mostrarSweetAlert('warning', 'Demasiados intentos', 'Has hecho varias solicitudes seguidas. Espera un momento e intenta de nuevo', BASE_URL . '/login');
            break;
        }

        // Mismo mensaje exista o no la cuenta, para no revelar qué correos están registrados
        mostrarSweetAlert(
            'success',
            'Revisa tu correo',
            'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña. Revisa también la carpeta de spam',
            BASE_URL . '/login'
        );
        break;

    // GET /restablecer?token=... -> formulario de nueva contraseña
    case 'formulario':
        $token = $_GET['token'] ?? '';
        if (!$servicio->validarToken($token)) {
            mostrarSweetAlert('error', 'Enlace inválido', 'El enlace no es válido o ya expiró. Solicita uno nuevo', BASE_URL . '/recoverpw');
            break;
        }
        require BASE_PATH . '/app/views/auth/restablecer-pass.php';
        break;

    // POST /restablecer/guardar -> guarda la nueva contraseña
    case 'guardar':
        $token = $_POST['token'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido($_POST['csrf_token'] ?? null)) {
            mostrarSweetAlert('error', 'Solicitud inválida', 'Recarga la página e intenta nuevamente', BASE_URL . '/recoverpw');
            break;
        }

        $error = $servicio->restablecer($token, $_POST['password'] ?? '', $_POST['password_confirm'] ?? '');
        if ($error !== null) {
            mostrarSweetAlert('error', 'No se pudo restablecer', $error);
            break;
        }

        mostrarSweetAlert('success', 'Contraseña actualizada', 'Ya puedes iniciar sesión con tu nueva contraseña', BASE_URL . '/login');
        break;
}
exit();
