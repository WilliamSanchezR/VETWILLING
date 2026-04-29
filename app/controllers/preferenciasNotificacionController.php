<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  CONTROLADOR DE PREFERENCIAS DE NOTIFICACIÓN - RFS 37
 *  Archivo: preferenciasNotificacionController.php
 * ═══════════════════════════════════════════════════════════════════
 */

session_start();

require_once __DIR__ . '/../models/usuario.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/json') !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
            $accion = $data['accion'] ?? '';
            
            switch ($accion) {
                case 'actualizar':
                    actualizarPreferencia();
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
                    break;
            }
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

            case 'obtener':
                obtenerPreferencia();
                break;
            case 'historial':
                obtenerHistorialNotificaciones();
                break;
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no especificada']);
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

        header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la preferencia']);
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
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $limite = $_GET['limite'] ?? 20;
        $limite = min((int)$limite, 100); // Máximo 100 registros

        $modeloUsuario = new Usuario();
        $historial = $modeloUsuario->obtenerHistorialNotificaciones($id_usuario, $limite);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($historial),
            'historial' => $historial
        ]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerHistorialNotificaciones: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}
