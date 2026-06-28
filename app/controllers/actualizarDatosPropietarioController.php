<?php

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/models/Perfil.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

$id_usuario = (int)($_SESSION['user']['id_usuario'] ?? 0);
if (!$id_usuario) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

$raw = file_get_contents('php://input');
$datos = json_decode($raw, true);
if (!$datos) {
    $datos = $_POST;
}

$nombres   = trim($datos['nombres']   ?? '');
$apellidos = trim($datos['apellidos'] ?? '');
$telefono  = trim($datos['telefono']  ?? '');
$direccion = trim($datos['direccion'] ?? '');

if (empty($nombres) || empty($apellidos)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Nombres y apellidos son obligatorios']);
    exit();
}

if (!empty($telefono) && !preg_match('/^\d{7,15}$/', $telefono)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Teléfono inválido (solo dígitos, 7-15)']);
    exit();
}

$perfil = new Perfil();
$ok = $perfil->actualizarDatosPropietario($id_usuario, compact('nombres', 'apellidos', 'telefono', 'direccion'));

if ($ok) {
    // Actualizar nombre en sesión para que el panel superior refleje el cambio
    $_SESSION['user']['nombres']   = $nombres;
    $_SESSION['user']['apellidos'] = $apellidos;
    echo json_encode(['status' => 'ok', 'message' => 'Datos actualizados correctamente']);
} else {
    // rowCount = 0 puede significar que no hubo cambios (los datos eran iguales)
    echo json_encode(['status' => 'ok', 'message' => 'Sin cambios']);
}
exit();
