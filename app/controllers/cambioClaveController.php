<?php

// Cambio obligatorio de la clave temporal que asignó el administrador (Soporte de cuentas).
// index.php define $accion: 'formulario' (GET /cuenta/cambiar-clave) | 'guardar' (POST /cuenta/guardar-clave)

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/SoporteCuenta.php';
require_once __DIR__ . '/../services/PasswordResetService.php';

$usuario = acceso_usuario();
if ($usuario === null) {
    acceso_redirigir('/login');
}

// Destino después de cambiarla (o si no tenía nada que cambiar)
$destino = !empty($usuario['seleccion_pendiente'])
    ? ACCESO_RUTA_SELECCION
    : (acceso_panel_rol((int) $usuario['id_rol']) ?? '/login');

if (empty($usuario['debe_cambiar_clave'])) {
    acceso_redirigir($destino);
}

if (($accion ?? 'formulario') === 'formulario') {
    require BASE_PATH . '/app/views/auth/cambiar-clave-temporal.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido($_POST['csrf_token'] ?? null)) {
    mostrarSweetAlert('error', 'Solicitud inválida', 'Recarga la página e intenta nuevamente', BASE_URL . ACCESO_RUTA_CAMBIO_CLAVE);
    exit();
}

$clave        = (string) ($_POST['password'] ?? '');
$confirmacion = (string) ($_POST['password_confirm'] ?? '');
$idUsuario    = (int) $usuario['id_usuario'];
$modelo       = new SoporteCuenta();

$error = null;
if (strlen($clave) < PasswordResetService::LONGITUD_MINIMA) {
    $error = 'La contraseña debe tener al menos ' . PasswordResetService::LONGITUD_MINIMA . ' caracteres.';
} elseif ($clave !== $confirmacion) {
    $error = 'Las contraseñas no coinciden.';
} elseif (password_verify($clave, (string) $modelo->hashActual($idUsuario))) {
    $error = 'La nueva contraseña debe ser distinta de la temporal.';
}
if ($error !== null) {
    mostrarSweetAlert('error', 'No se pudo cambiar', $error);
    exit();
}

$version = $modelo->completarCambioObligatorio($idUsuario, password_hash($clave, PASSWORD_DEFAULT));
acceso_actualizar_sesion_actual($idUsuario, $version);
$_SESSION['user']['debe_cambiar_clave'] = false;

mostrarSweetAlert('success', 'Contraseña actualizada', 'Ya puedes usar el sistema con tu nueva contraseña.', BASE_URL . $destino);
exit();
