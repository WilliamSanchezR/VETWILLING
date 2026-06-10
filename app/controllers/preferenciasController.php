<?php
/**
 * preferenciasController.php
 *
 * API JSON para leer y guardar preferencias de usuario.
 *
 * Rutas en index.php:
 *   GET  /cliente/api/preferencias          → obtener todas
 *   POST /cliente/api/preferencias/guardar  → guardar cambios
 *   POST /cliente/api/preferencias/reset    → restablecer defaults
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/services/PreferenciasManager.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$uri    = rtrim(str_replace($baseFolder, '', strtok($_SERVER['REQUEST_URI'], '?')), '/');
$pm     = new PreferenciasManager($_SESSION['user']['id_usuario']);

/* ── GET /cliente/api/preferencias ── */
if ($method === 'GET' && $uri === '/cliente/api/preferencias') {
    echo json_encode([
        'status'      => 'ok',
        'preferencias'=> $pm->obtener(),
    ]);
    exit;
}

/* ── POST /cliente/api/preferencias/guardar ── */
if ($method === 'POST' && $uri === '/cliente/api/preferencias/guardar') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    try {
        $resultado = $pm->guardar($body);

        // AGREGAR ESTO: sincronizar sesión con el idioma guardado
        if (isset($body['idioma'])) {
            $_SESSION['lang'] = $body['idioma'];
        }

        echo json_encode([
            'status'      => 'ok',
            'preferencias'=> $resultado,
        ]);
    } catch (InvalidArgumentException $e) {
        // ... igual que antes
    }
    exit;
}
/* ── POST /cliente/api/preferencias/reset ── */
if ($method === 'POST' && $uri === '/cliente/api/preferencias/reset') {
    $resultado = $pm->restablecer();
    echo json_encode([
        'status'      => 'ok',
        'preferencias'=> $resultado,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'code' => 'method_not_allowed']);