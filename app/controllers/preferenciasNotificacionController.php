<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  CONTROLADOR DE PREFERENCIAS DE NOTIFICACIÓN - RFS 37
 *  Archivo: preferenciasNotificacionController.php
 * ═══════════════════════════════════════════════════════════════════
 */

require_once BASE_PATH . '/app/helpers/session_helper.php';
initSession();

require_once __DIR__ . '/../models/usuario.php';
require_once __DIR__ . '/../models/Notificacion.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            $data = [];
        }
        $accion = $data['accion'] ?? '';
        
        switch ($accion) {
            case 'actualizar':
                actualizarPreferencia();
                break;
            case 'marcar_leida':
                marcarLeida();
                break;
            case 'marcar_todas':
                marcarTodasLeidas();
                break;
            default:
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida'], JSON_UNESCAPED_UNICODE);
                break;
        }
        break;
                    
    case 'GET':
        $accion = $_GET['accion'] ?? '';
        
        switch ($accion) {
            case 'obtener':
                obtenerNotificaciones();
                break;
            case 'marcar_leida':
                marcarLeida();
                break;
            case 'marcar_todas':
                marcarTodasLeidas();
                break;
            case 'historial':
                obtenerHistorialNotificaciones();
                break;
            case 'preferencia':
                obtenerPreferencia();
                break;
            default:
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Acción no especificada'], JSON_UNESCAPED_UNICODE);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        break;
}

// ═══════════════════════════════════════════════════════════════════
// FUNCIÓN 1: OBTENER PREFERENCIA DE NOTIFICACIÓN (RFS 37)
// ═══════════════════════════════════════════════════════════════════

function obtenerPreferencia()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloUsuario = new Usuario();
        
        $preferencia = $modeloUsuario->obtenerPreferenciaNotificacion($id_usuario);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'preferencia' => $preferencia,
            'opciones' => [
                ['valor' => 'email', 'etiqueta' => '📧 Notificaciones por Email'],
                ['valor' => 'ninguno', 'etiqueta' => '🔇 Sin Notificaciones']
            ]
        ]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerPreferencia: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
// FUNCIÓN 2: ACTUALIZAR PREFERENCIA DE NOTIFICACIÓN (RFS 37)
// ═══════════════════════════════════════════════════════════════════

function actualizarPreferencia()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['preferencia'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Preferencia no especificada']);
            exit();
        }

        $preferencia = $data['preferencia'];
        $preferenciaValida = ['email', 'ninguno'];

        if (!in_array($preferencia, $preferenciaValida)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Preferencia inválida. Valores permitidos: ' . implode(', ', $preferenciaValida)
            ]);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloUsuario = new Usuario();

        $resultado = $modeloUsuario->actualizarPreferenciaNotificacion($id_usuario, $preferencia);

        if ($resultado) {
            // Sincronizar con el archivo JSON para mantener consistencia
            try {
                if (!class_exists('PreferenciasManager')) {
                    require_once __DIR__ . '/../services/PreferenciasManager.php';
                }
                $pm = new PreferenciasManager($id_usuario);
                $pm->guardar(['notificaciones' => $preferencia]);
            } catch (\Throwable $e) {
                error_log('preferenciasNotificacion: no se pudo sincronizar JSON — ' . $e->getMessage());
            }

            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Preferencia actualizada exitosamente',
                'preferencia' => $preferencia
            ]);
            error_log("✅ Preferencia de notificación actualizada para usuario {$id_usuario}");
            exit();
        } else {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la preferencia'], JSON_UNESCAPED_UNICODE);
            exit();
        }

    } catch (Exception $e) {
        error_log("❌ Error en actualizarPreferencia: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
// FUNCIÓN 3: OBTENER HISTORIAL DE NOTIFICACIONES (RFS 37)
// ═══════════════════════════════════════════════════════════════════

function obtenerHistorialNotificaciones()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $limite = $_GET['limite'] ?? 20;
        $limite = min((int)$limite, 100); // Máximo 100 registros

        $modeloUsuario = new Usuario();
        $historial = $modeloUsuario->obtenerHistorialNotificaciones($id_usuario, $limite);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($historial),
            'historial' => $historial
        ], JSON_UNESCAPED_UNICODE);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerHistorialNotificaciones: " . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

function obtenerNotificaciones()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $limite = isset($_GET['limite']) ? max(1, min((int) $_GET['limite'], 100)) : 20;

        $modeloNotificacion = new Notificacion();
        $notificaciones = $modeloNotificacion->listarParaUsuario($id_usuario, $limite);
        $noLeidas = $modeloNotificacion->contarNoLeidas($id_usuario);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas,
            'limite' => $limite
        ], JSON_UNESCAPED_UNICODE);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerNotificaciones: " . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

function marcarLeida()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'ID de notificación inválido'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloNotificacion = new Notificacion();
        $resultado = $modeloNotificacion->marcarLeida($id, $id_usuario);

        header('Content-Type: application/json; charset=utf-8');
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Notificación marcada como leída'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No se encontró la notificación o ya fue marcada'], JSON_UNESCAPED_UNICODE);
        }
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en marcarLeida: " . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

function marcarTodasLeidas()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloNotificacion = new Notificacion();
        $resultado = $modeloNotificacion->marcarTodasLeidas($id_usuario);

        header('Content-Type: application/json; charset=utf-8');
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Todas las notificaciones marcadas como leídas'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'No había notificaciones pendientes'], JSON_UNESCAPED_UNICODE);
        }
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en marcarTodasLeidas: " . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}
