<?php
/**
 * sesionesClienteController.php
 * Maneja las peticiones AJAX de la sección "Sesiones Activas" en confi.php
 *
 * Rutas que lo invocan (definidas en index.php):
 *   POST /cliente/api/sesiones/cerrar        → cierra una sesión por token
 *   POST /cliente/api/sesiones/cerrar-todas  → cierra todas menos la actual
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/services/SessionManager.php';

// Solo aceptar POST y responder JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'code' => 'method_not_allowed']);
    exit;
}

// Leer el cuerpo JSON enviado por fetch()
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Detectar la acción por la URL completa (igual que hace index.php)
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$uri = rtrim(str_replace($baseFolder, '', $uri), '/');

$sm = new SessionManager($_SESSION['user']['id_usuario']);

/* ================================================================
   POST /cliente/api/sesiones/cerrar
   Body: { "token": "abc123..." }
================================================================ */
if ($uri === '/cliente/api/sesiones/cerrar') {
    $token = trim($body['token'] ?? '');

    if ($token === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'code' => 'missing_token']);
        exit;
    }

    $resultado = $sm->cerrar($token);

    // cerrar() devuelve: 'ok' | 'not_found' | 'is_current' | 'write_error'
    $httpCodes = [
        'ok'          => 200,
        'not_found'   => 404,
        'is_current'  => 403,
        'write_error' => 500,
    ];

    http_response_code($httpCodes[$resultado] ?? 500);
    echo json_encode([
        'status' => $resultado === 'ok' ? 'ok' : 'error',
        'code'   => $resultado,
    ]);
    exit;
}

/* ================================================================
   POST /cliente/api/sesiones/cerrar-todas
   Body: {} (sin parámetros)
================================================================ */
if ($uri === '/cliente/api/sesiones/cerrar-todas') {
    $cerradas = $sm->cerrarTodas();

    echo json_encode([
        'status'   => 'ok',
        'cerradas' => $cerradas,
    ]);
    exit;
}

// Si la URI no coincide con ninguna acción conocida
http_response_code(400);
echo json_encode(['status' => 'error', 'code' => 'unknown_action']);