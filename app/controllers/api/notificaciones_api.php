<?php

/**
 * API unificada de notificaciones — VetWilling
 * Ruta: /paciente/api/notificaciones
 *
 * Soporta dos formatos para compatibilidad con todos los JS del proyecto:
 *
 * Formato A (vista nueva)       → ?action=leer | descartar | leer_todas | contar
 * Formato B (notificaciones-paciente.js) → ?accion=listar | leida | todas | stream
 */

require_once BASE_PATH . '/app/helpers/session_helper.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

header('Content-Type: application/json');

// ── Verificar sesión ──────────────────────────────────────────────────────────
if (!isSessionActive()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => 'No autenticado']);
    exit;
}

$id_usuario = (int)getCurrentUserId();
$modelo     = new Notificacion();

// ── Leer acción — soporta ?action= y ?accion= ────────────────────────────────
$action = $_GET['action'] ?? $_GET['accion'] ?? $_POST['action'] ?? $_POST['accion'] ?? '';

// ── Leer body JSON si viene así (notificaciones-paciente.js lo envía así) ────
$bodyJson = [];
$rawBody  = file_get_contents('php://input');
if (!empty($rawBody)) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $bodyJson = $decoded;
    }
}

// Helper para obtener el ID de la notificación desde cualquier formato
function getNotifId(array $bodyJson): int
{
    // Formato JSON: { "id": 5 }
    if (isset($bodyJson['id'])) return (int)$bodyJson['id'];
    // Formato URLSearchParams: id=5
    if (isset($_POST['id']))    return (int)$_POST['id'];
    return 0;
}

// ── Enrutar ───────────────────────────────────────────────────────────────────
switch ($action) {

    // ── Listar notificaciones (notificaciones-paciente.js) ────────────────────
    case 'listar':
        $limite = (int)($_GET['limite'] ?? 100);
        $filas  = $modelo->listarParaUsuario($id_usuario, $limite);

        // Normalizar campos para que el JS los entienda
        $notificaciones = array_map(function ($n) {
            return [
                'id'             => $n['id_notificacion'],
                'tipo'           => $n['tipo'],
                'titulo'         => $n['titulo'],
                'mensaje'        => $n['mensaje'],
                'leido'          => (bool)$n['leida'],
                'leida'          => (bool)$n['leida'],
                'fecha'          => $n['fecha_creacion'],
                'fecha_creacion' => $n['fecha_creacion'],
                'url_accion'     => $n['url_accion'],
                'id_paciente'    => $n['id_paciente'],
            ];
        }, $filas);

        echo json_encode([
            'ok'             => true,
            'status'         => 'success',
            'notificaciones' => $notificaciones,
            'sin_leer'       => $modelo->contarNoLeidas($id_usuario),
        ]);
        break;

    // ── Marcar una como leída ─────────────────────────────────────────────────
    // Formato A: ?action=leer    POST id=5
    // Formato B: ?accion=leida   POST JSON { "id": 5 }
    case 'leer':
    case 'leida':
        $id_notificacion = getNotifId($bodyJson);

        if ($id_notificacion <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => 'ID inválido']);
            exit;
        }

        $resultado = $modelo->marcarLeida($id_notificacion, $id_usuario);
        echo json_encode([
            'ok'       => $resultado,
            'status'   => $resultado ? 'success' : 'error',
            'sin_leer' => $modelo->contarNoLeidas($id_usuario),
        ]);
        break;

    // ── Marcar todas como leídas ──────────────────────────────────────────────
    // Formato A: ?action=leer_todas
    // Formato B: ?accion=todas
    case 'leer_todas':
    case 'todas':
        $resultado = $modelo->marcarTodasLeidas($id_usuario);
        echo json_encode([
            'ok'       => $resultado,
            'status'   => $resultado ? 'success' : 'error',
            'sin_leer' => 0,
        ]);
        break;

    // ── Descartar / eliminar (formato A) ─────────────────────────────────────
    case 'descartar':
        $id_notificacion = getNotifId($bodyJson);

        if ($id_notificacion <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => 'ID inválido']);
            exit;
        }

        $resultado = $modelo->descartar($id_notificacion, $id_usuario);
        echo json_encode([
            'ok'       => $resultado,
            'status'   => $resultado ? 'success' : 'error',
            'sin_leer' => $modelo->contarNoLeidas($id_usuario),
        ]);
        break;

    // ── Contar no leídas (badge) ──────────────────────────────────────────────
    case 'contar':
        echo json_encode([
            'ok'       => true,
            'status'   => 'success',
            'sin_leer' => $modelo->contarNoLeidas($id_usuario),
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => 'Acción no reconocida: ' . htmlspecialchars($action)]);
        break;
}