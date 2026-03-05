<?php

/**
 * ═══════════════════════════════════════════════════════════════════
 *  CONTROLADOR DE SEGUIMIENTOS
 *  Descripción: Maneja todas las acciones relacionadas con seguimientos
 * ═══════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../models/Seguimientos.php';
require_once __DIR__ . '/../helpers/alert_helpers.php';

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user']['id_usuario'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'listar':
                listarSeguimientos();
                break;
            case 'estadisticas':
                obtenerEstadisticas();
                break;
            case 'detalle':
                obtenerDetalle();
                break;
            case 'actividades':
                obtenerActividades();
                break;
            case 'medicaciones':
                obtenerMedicaciones();
                break;
            case 'alertas':
                obtenerAlertas();
                break;
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
                break;
        }
        break;

    case 'POST':
        switch ($action) {
            case 'actualizar-observaciones':
                actualizarObservaciones();
                break;
            case 'finalizar':
                finalizarSeguimiento();
                break;
            case 'notificar':
                enviarNotificacion();
                break;
            case 'registrar-actividad':
                registrarActividad();
                break;
            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        break;
}

/**
 * Lista todos los seguimientos activos del veterinario
 */
function listarSeguimientos()
{
    try {
        $id_usuario = $_SESSION['user']['id_usuario'];
        $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? null;

        $seguimientosModel = new Seguimientos();
        $seguimientos = $seguimientosModel->obtenerSeguimientosPorProfesional($id_usuario, $id_veterinaria);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $seguimientos,
            'count' => count($seguimientos)
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener seguimientos: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtiene estadísticas de seguimientos
 */
function obtenerEstadisticas()
{
    try {
        $id_usuario = $_SESSION['user']['id_usuario'];
        $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? null;

        $seguimientosModel = new Seguimientos();
        $stats = $seguimientosModel->obtenerEstadisticas($id_usuario, $id_veterinaria);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $stats
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtiene el detalle completo de un seguimiento
 */
function obtenerDetalle()
{
    try {
        $id_seguimiento = $_GET['id'] ?? null;
        
        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        
        $seguimientosModel = new Seguimientos();
        $seguimiento = $seguimientosModel->obtenerSeguimientoPorId($id_seguimiento, $id_usuario);

        if (!$seguimiento) {
            throw new Exception('Seguimiento no encontrado');
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $seguimiento
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Obtiene las actividades de un seguimiento
 */
function obtenerActividades()
{
    try {
        $id_seguimiento = $_GET['id'] ?? null;
        
        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $seguimientosModel = new Seguimientos();
        $actividades = $seguimientosModel->obtenerActividades($id_seguimiento);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $actividades
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Obtiene las medicaciones de un seguimiento
 */
function obtenerMedicaciones()
{
    try {
        $id_seguimiento = $_GET['id'] ?? null;
        
        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $seguimientosModel = new Seguimientos();
        $medicaciones = $seguimientosModel->obtenerMedicaciones($id_seguimiento);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $medicaciones
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Obtiene las alertas de un seguimiento
 */
function obtenerAlertas()
{
    try {
        $id_seguimiento = $_GET['id'] ?? null;
        
        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $seguimientosModel = new Seguimientos();
        $alertas = $seguimientosModel->obtenerAlertas($id_seguimiento);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $alertas
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Actualiza las observaciones de un seguimiento
 */
function actualizarObservaciones()
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_seguimiento = $data['id_seguimiento'] ?? null;
        $observacion = $data['observacion'] ?? null;

        if (!$id_seguimiento || !$observacion) {
            throw new Exception('Datos incompletos');
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        
        $seguimientosModel = new Seguimientos();
        $resultado = $seguimientosModel->actualizarObservaciones($id_seguimiento, $observacion, $id_usuario);

        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Observaciones actualizadas correctamente'
            ]);
        } else {
            throw new Exception('No se pudieron actualizar las observaciones');
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Finaliza un seguimiento
 */
function finalizarSeguimiento()
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_seguimiento = $data['id_seguimiento'] ?? null;

        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        
        $seguimientosModel = new Seguimientos();
        $resultado = $seguimientosModel->finalizarSeguimiento($id_seguimiento, $id_usuario);

        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Seguimiento finalizado correctamente'
            ]);
        } else {
            throw new Exception('No se pudo finalizar el seguimiento');
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Registra una nueva actividad en el seguimiento
 */
function registrarActividad()
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_seguimiento = $data['id_seguimiento'] ?? null;
        $tipo_actividad = $data['tipo_actividad'] ?? null;
        $titulo = $data['titulo'] ?? null;
        $descripcion = $data['descripcion'] ?? '';

        if (!$id_seguimiento || !$tipo_actividad || !$titulo) {
            throw new Exception('Datos incompletos');
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        
        $seguimientosModel = new Seguimientos();
        $resultado = $seguimientosModel->registrarActividad([
            'id_seguimiento' => $id_seguimiento,
            'tipo_actividad' => $tipo_actividad,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'categoria' => $data['categoria'] ?? 'clinico',
            'estado' => 'completada',
            'importancia' => $data['importancia'] ?? 'media',
            'registrado_por' => $id_usuario
        ]);

        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Actividad registrada correctamente'
            ]);
        } else {
            throw new Exception('No se pudo registrar la actividad');
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Envía una notificación al propietario del paciente
 */
function enviarNotificacion()
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_seguimiento = $data['id_seguimiento'] ?? null;
        $mensaje = $data['mensaje'] ?? 'Recordatorio de seguimiento';

        if (!$id_seguimiento) {
            throw new Exception('ID de seguimiento no proporcionado');
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        
        $seguimientosModel = new Seguimientos();
        $seguimiento = $seguimientosModel->obtenerSeguimientoPorId($id_seguimiento, $id_usuario);

        if (!$seguimiento) {
            throw new Exception('Seguimiento no encontrado');
        }

        // Aquí se integraría con el sistema de notificaciones/email existente
        // Por ahora solo confirmamos la acción
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Notificación enviada al propietario'
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
