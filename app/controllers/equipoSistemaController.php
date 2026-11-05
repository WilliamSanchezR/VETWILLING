<?php

// Guarda la tarjeta del equipo del administrador que inició sesión.
// El usuario sale de la sesión: nadie puede editar la tarjeta de otra persona.

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/EquipoSistema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido');
}

acceso_exigir_csrf();

$idUsuario = acceso_id_usuario();
if ($idUsuario === null || acceso_rol() !== 1) {
    http_response_code(403);
    require __DIR__ . '/../views/auth/error403.php';
    exit();
}

function equipo_error(string $mensaje): void
{
    mostrarSweetAlert('error', 'Revisa los datos', $mensaje);
    exit();
}

function equipo_url(string $url, string $dominio): ?string
{
    if ($url === '') {
        return null;
    }
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $valida = filter_var($url, FILTER_VALIDATE_URL)
        && parse_url($url, PHP_URL_SCHEME) === 'https'
        && ($host === $dominio || str_ends_with($host, '.' . $dominio));
    if (!$valida) {
        equipo_error("El enlace debe empezar por https:// y ser de $dominio.");
    }
    return $url;
}

$nombre = trim($_POST['nombre'] ?? '');
$rol    = trim($_POST['rol_equipo'] ?? '');
$correo = trim($_POST['correo'] ?? '');

if ($nombre === '' || $rol === '') {
    equipo_error('El nombre y el rol son obligatorios.');
}
if (mb_strlen($nombre) > 120 || mb_strlen($rol) > 120) {
    equipo_error('El nombre y el rol admiten máximo 120 caracteres.');
}
if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    equipo_error('El correo no es válido.');
}

$habilidades = array_filter(array_map('trim', explode(',', (string) ($_POST['habilidades'] ?? ''))), 'strlen');
$habilidades = mb_substr(implode(', ', array_slice($habilidades, 0, 8)), 0, 500);

$modelo = new EquipoSistema();
$actual = $modelo->obtenerPorUsuario($idUsuario);
$foto   = $actual['foto'] ?? null;

if (!empty($_FILES['foto']['name'])) {
    $archivo = $_FILES['foto'];
    if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 2 * 1024 * 1024) {
        equipo_error('La foto no se pudo subir o supera 2 MB.');
    }
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo  = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tipos[$tipo])) {
        equipo_error('La foto debe ser JPG, PNG o WEBP.');
    }
    $nombreArchivo = 'equipo_' . bin2hex(random_bytes(8)) . '.' . $tipos[$tipo];
    if (!guardar_archivo_subido($archivo['tmp_name'], BASE_PATH . '/public/uploads/equipo/' . $nombreArchivo)) {
        equipo_error('No se pudo guardar la foto.');
    }
    $foto = 'public/uploads/equipo/' . $nombreArchivo;
}

$ok = $modelo->guardar($idUsuario, [
    'nombre'      => $nombre,
    'rol_equipo'  => $rol,
    'habilidades' => $habilidades,
    'foto'        => $foto,
    'github'      => equipo_url(trim($_POST['github'] ?? ''), 'github.com'),
    'linkedin'    => equipo_url(trim($_POST['linkedin'] ?? ''), 'linkedin.com'),
    'correo'      => $correo !== '' ? $correo : null,
]);

if ($ok) {
    mostrarSweetAlert('success', 'Tarjeta actualizada', 'Tus datos del equipo se guardaron.', BASE_URL . '/directorio');
} else {
    mostrarSweetAlert('error', 'Error', 'No se pudo guardar la tarjeta.');
}
exit();
