<?php

require_once BASE_PATH . '/app/helpers/session_administrador.php';
require_once BASE_PATH . '/app/models/CanalEnvio.php';

header('Content-Type: application/json; charset=utf-8');

$model  = new CanalEnvio();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($accion) {

        // ── GET ──────────────────────────────────────────────

        case 'canales':
            echo json_encode(['ok' => true, 'data' => $model->listarCanales()]);
            break;

        case 'plantillas':
            $idCanal = (int)($_GET['id_canal'] ?? 0);
            if (!$idCanal) throw new InvalidArgumentException('id_canal requerido');
            echo json_encode(['ok' => true, 'data' => $model->listarPlantillas($idCanal)]);
            break;

        case 'fallos':
            $horas  = (int)($_GET['horas']  ?? 24);
            $limite = (int)($_GET['limite'] ?? 100);
            echo json_encode([
                'ok'      => true,
                'data'    => $model->obtenerFallosRecientes($horas, $limite),
                'resumen' => $model->obtenerResumenMonitoreo($horas),
            ]);
            break;

        // ── POST ─────────────────────────────────────────────

        case 'actualizar_config':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body    = json_decode(file_get_contents('php://input'), true) ?? [];
            $idCanal = (int)($body['id_canal'] ?? 0);
            if (!$idCanal) throw new InvalidArgumentException('id_canal requerido');
            unset($body['id_canal'], $body['accion']);
            $ok = $model->actualizarConfig($idCanal, $body);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Configuración guardada' : 'Sin cambios']);
            break;

        case 'toggle_canal':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body      = json_decode(file_get_contents('php://input'), true) ?? [];
            $idCanal   = (int)($body['id_canal']   ?? 0);
            $habilitado = isset($body['habilitado']) ? (bool)$body['habilitado'] : null;
            if (!$idCanal || $habilitado === null) throw new InvalidArgumentException('id_canal y habilitado requeridos');
            $ok = $model->toggleHabilitado($idCanal, $habilitado);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Estado actualizado' : 'Error']);
            break;

        case 'guardar_plantilla':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($body['id_canal']) || empty($body['tipo_notificacion'])) {
                throw new InvalidArgumentException('id_canal y tipo_notificacion requeridos');
            }
            $ok = $model->guardarPlantilla($body);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Plantilla guardada' : 'Error']);
            break;

        case 'reintentar':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body  = json_decode(file_get_contents('php://input'), true) ?? [];
            $canal = $body['canal'] ?? 'email';
            $n     = $model->reintentarFallidos($canal);
            echo json_encode(['ok' => true, 'reintentados' => $n,
                              'mensaje' => "$n notificaciones marcadas para reintento"]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[mediosEnvioController] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor']);
}
