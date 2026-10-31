<?php

// API JSON: sin sesión de administrador responde 403 en JSON (en vez de redirigir con HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ((int) ($_SESSION['user']['id_rol'] ?? 0) !== 1) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'mensaje' => 'Acceso solo para administradores']);
    exit;
}
require_once BASE_PATH . '/app/helpers/session_administrador.php';
require_once BASE_PATH . '/app/models/CanalEnvio.php';
require_once BASE_PATH . '/app/helpers/mailer_helper.php';
require_once BASE_PATH . '/app/helpers/csrf_helper.php';

header('Content-Type: application/json; charset=utf-8');

$model  = new CanalEnvio();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Toda acción que modifica algo exige JSON y el token CSRF de la página (cabecera X-CSRF-Token).
// Evita que otra web dispare cambios o envíos aprovechando la sesión del administrador.
if ($method === 'POST') {
    $esJson = str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
    if (!$esJson || !csrf_valido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'Solicitud no válida o sesión expirada. Recarga la página.']);
        exit;
    }
}

try {
    switch ($accion) {

        // ── GET ──────────────────────────────────────────────

        case 'canales':
            echo json_encode([
                'ok'         => true,
                'data'       => $model->listarCanales(),
                'transporte' => mailer_transporte_actual(),
            ]);
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
            $errores = CanalEnvio::validarConfig($body);
            if ($errores) throw new InvalidArgumentException(implode(' ', $errores));
            try {
                $ok = $model->actualizarConfig($idCanal, $body);
            } catch (RuntimeException $e) {
                // Falta APP_ENCRYPTION_KEY: no se guarda nada (ni siquiera en texto plano)
                http_response_code(422);
                echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
                break;
            }
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Configuración guardada' : 'Sin cambios']);
            break;

        case 'toggle_canal':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body      = json_decode(file_get_contents('php://input'), true) ?? [];
            $idCanal   = (int)($body['id_canal']   ?? 0);
            $habilitado = isset($body['habilitado']) ? (bool)$body['habilitado'] : null;
            if (!$idCanal || $habilitado === null) throw new InvalidArgumentException('id_canal y habilitado requeridos');
            // El envío push todavía no está implementado: no se deja activar para no aparentar que funciona
            $canal = $model->obtenerPorId($idCanal);
            if ($habilitado && ($canal['codigo_canal'] ?? '') === 'push') {
                throw new InvalidArgumentException('Las notificaciones push aún no están disponibles en VetWilling.');
            }
            $ok = $model->toggleHabilitado($idCanal, $habilitado);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Estado actualizado' : 'Error']);
            break;

        case 'guardar_plantilla':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($body['id_canal']) || empty($body['tipo_notificacion'])) {
                throw new InvalidArgumentException('id_canal y tipo_notificacion requeridos');
            }
            $errores = $model->validarPlantilla($body);
            if ($errores) throw new InvalidArgumentException(implode(' ', $errores));
            $ok = $model->guardarPlantilla($body);
            echo json_encode(['ok' => $ok, 'mensaje' => $ok ? 'Plantilla guardada' : 'Error']);
            break;

        case 'reintentar':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            // Reenvía de verdad los correos fallidos con contenido guardado (solo existe el canal de correo)
            $resultado = reintentarCorreosFallidos(false);
            if (!$resultado['ok']) http_response_code(409);
            echo json_encode($resultado);
            break;

        case 'probar_correo':
            if ($method !== 'POST') throw new RuntimeException('Método no permitido');
            // Un correo real, solo al administrador que lo pide (el destino no se puede elegir),
            // y como máximo uno por minuto para evitar envíos repetidos.
            $ultimo = (int) ($_SESSION['medios_envio_ultima_prueba'] ?? 0);
            if (time() - $ultimo < 60) {
                http_response_code(429);
                echo json_encode(['ok' => false, 'mensaje' => 'Espera ' . (60 - (time() - $ultimo)) . ' s antes de enviar otra prueba.']);
                break;
            }
            $_SESSION['medios_envio_ultima_prueba'] = time();

            $resultado = mailer_enviar_prueba((string) ($_SESSION['user']['email'] ?? ''));
            if (!$resultado['ok']) http_response_code(502);
            echo json_encode($resultado);
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
