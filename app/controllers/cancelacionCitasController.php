<?php

/**
 * Controlador para la Cancelación de Citas (RFS 36)
 * Gestiona todo el flujo de cancelación de citas programadas
 */

session_start();

require_once __DIR__ . '/../models/Calendario.php';
require_once __DIR__ . '/../helpers/alert_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'];

        if (strpos($content_type, 'application/json') !== false) {
            // Petición AJAX JSON
            if (strpos($request_uri, '/cancelarCita') !== false) {
                validarYCancelarCitaAjax();
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Ruta no reconocida']);
                exit();
            }
        } else {
            // Petición de formulario tradicional
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'validar_estado') {
                validarEstadoCita();
            } elseif ($accion === 'registrar_motivo') {
                registrarMotivoCancelacion();
            } elseif ($accion === 'cancelar') {
                cancelarCita();
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no especificada']);
                exit();
            }
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'validar') {
            validarEstadoCitaGet();
        } elseif ($accion === 'detalles_cancelacion') {
            obtenerDetallesCancelacionGet();
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Acción no especificada']);
            exit();
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        exit();
}

/**
 * SUBTAREA 1: Valida el estado de una cita antes de cancelarla
 * Verifica que la cita exista y esté en estado Pendiente
 */
function validarEstadoCita()
{
    try {
        $id_agendamiento = $_POST['id_agendamiento'] ?? null;

        if (!$id_agendamiento) {
            alertaModal('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        $calendario = new Calendario();
        $resultado = $calendario->validarEstadoCita($id_agendamiento);

        if ($resultado['valido']) {
            alertaModal('success', 'Validación Exitosa', $resultado['mensaje']);
        } else {
            alertaModal('error', 'Validación Fallida', $resultado['mensaje']);
        }
    } catch (Exception $e) {
        error_log("Error en validarEstadoCita -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al validar el estado de la cita');
    }
}

/**
 * SUBTAREA 1: Valida el estado de una cita (GET)
 * Útil para validaciones previas desde el frontend
 */
function validarEstadoCitaGet()
{
    try {
        $id_agendamiento = $_GET['id_agendamiento'] ?? null;

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de cita no proporcionado'
            ]);
            exit();
        }

        $calendario = new Calendario();
        $resultado = $calendario->validarEstadoCita($id_agendamiento);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => $resultado['valido'] ? 'success' : 'error',
            'valido' => $resultado['valido'],
            'mensaje' => $resultado['mensaje'],
            'estado_actual' => $resultado['estado_actual'],
            'cita' => $resultado['cita']
        ]);
    } catch (Exception $e) {
        error_log("Error en validarEstadoCitaGet -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema al validar la cita'
        ]);
    }
}

/**
 * Función para validar y cancelar cita via AJAX
 * Integra subtareas 1 y 2
 */
function validarYCancelarCitaAjax()
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_agendamiento = $data['id_agendamiento'] ?? null;
        $motivo_cancelacion = $data['motivo_cancelacion'] ?? null;

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de cita no proporcionado'
            ]);
            exit();
        }

        // SUBTAREA 1: Validar estado de la cita
        $calendario = new Calendario();
        $validacion = $calendario->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $validacion['mensaje']
            ]);
            exit();
        }

        // SUBTAREA 2: Registrar motivo de cancelación
        if (!$motivo_cancelacion) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Motivo de cancelación es requerido'
            ]);
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $registroMotivo = $calendario->registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario);

        if (!$registroMotivo['exito']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $registroMotivo['mensaje']
            ]);
            exit();
        }

        // TODO: Subtareas 3 y 4 se completarán más adelante
        echo json_encode([
            'status' => 'success',
            'message' => 'Subtareas 1 y 2 completadas. Motivo registrado. Pendientes: Actualizar estado y enviar notificaciones.',
            'id_agendamiento' => $id_agendamiento
        ]);
    } catch (Exception $e) {
        error_log("Error en validarYCancelarCitaAjax -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema'
        ]);
    }
}

/**
 * SUBTAREA 2: Registra el motivo de cancelación de una cita
 */
function registrarMotivoCancelacion()
{
    try {
        $id_agendamiento = $_POST['id_agendamiento'] ?? null;
        $motivo_cancelacion = $_POST['motivo_cancelacion'] ?? null;

        if (!$id_agendamiento) {
            alertaModal('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        if (!$motivo_cancelacion) {
            alertaModal('error', 'Error', 'Motivo de cancelación es requerido');
            return;
        }

        // SUBTAREA 1: Validar estado de la cita
        $calendario = new Calendario();
        $validacion = $calendario->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            alertaModal('error', 'Error', $validacion['mensaje']);
            return;
        }

        // SUBTAREA 2: Registrar motivo de cancelación
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $resultado = $calendario->registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario);

        if ($resultado['exito']) {
            alertaModal('success', 'Éxito', $resultado['mensaje']);
        } else {
            alertaModal('error', 'Error', $resultado['mensaje']);
        }
    } catch (Exception $e) {
        error_log("Error en registrarMotivoCancelacion -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al registrar el motivo');
    }
}

/**
 * Obtiene los detalles de cancelación de una cita (GET)
 */
function obtenerDetallesCancelacionGet()
{
    try {
        $id_agendamiento = $_GET['id_agendamiento'] ?? null;

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de cita no proporcionado'
            ]);
            exit();
        }

        $calendario = new Calendario();
        $detalles = $calendario->obtenerDetallesCancelacion($id_agendamiento);

        if (!$detalles) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontraron detalles de cancelación'
            ]);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'detalles' => $detalles
        ]);
    } catch (Exception $e) {
        error_log("Error en obtenerDetallesCancelacionGet -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema'
        ]);
    }
}

/**
 * PLACEHOLDER: Función para cancelar cita (POST tradicional)
 * Se completará en las siguientes subtareas
 */
function cancelarCita()
{
    try {
        $id_agendamiento = $_POST['id_agendamiento'] ?? null;

        if (!$id_agendamiento) {
            alertaModal('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        // SUBTAREA 1: Validar estado de la cita
        $calendario = new Calendario();
        $validacion = $calendario->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            alertaModal('error', 'Error', $validacion['mensaje']);
            return;
        }

        // TODO: Subtareas 3 y 4 se completarán más adelante
        alertaModal('info', 'Información', 'Validación completada. Próximas subtareas pendientes.');
    } catch (Exception $e) {
        error_log("Error en cancelarCita -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al procesar la cancelación');
    }
}
