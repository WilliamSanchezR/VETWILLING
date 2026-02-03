<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  CONTROLADOR DE CITAS PARA CLIENTES/PROPIETARIOS
 *  Archivo: citasClienteController.php
 *  Descripción: Maneja todas las operaciones de citas desde el rol cliente
 * ═══════════════════════════════════════════════════════════════════
 */

session_start();

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/CitasCliente.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Verificar si es una petición AJAX JSON
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/json') !== false) {
            // Es una petición JSON desde el frontend
            $data = json_decode(file_get_contents("php://input"), true);
            $accion = $data['accion'] ?? '';
            
            switch ($accion) {
                case 'crear':
                    crearCitaCliente();
                    break;
                case 'cancelar':
                    cancelarCitaCliente();
                    break;
                case 'modificar':
                    modificarCitaCliente();
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
            case 'listar':
                listarCitasCliente();
                break;
            case 'detalle':
                obtenerDetalleCita();
                break;
            case 'servicios':
                obtenerServicios();
                break;
            case 'subservicios':
                obtenerSubservicios();
                break;
            case 'mascotas':
                obtenerMascotasCliente();
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
//  FUNCIÓN AUXILIAR: OBTENER ID_PROPIETARIO
// ═══════════════════════════════════════════════════════════════════

/**
 * Obtiene el id_propietario desde la sesión o desde la BD
 * 
 * @return int|null
 */
function obtenerIdPropietario()
{
    // Si ya está en sesión, usarlo
    if (isset($_SESSION['user']['id_propietario'])) {
        return (int)$_SESSION['user']['id_propietario'];
    }

    // Si no está en sesión pero tenemos id_usuario, buscarlo en BD
    if (isset($_SESSION['user']['id_usuario'])) {
        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloCitas = new CitasCliente();
        $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario($id_usuario);
        
        // Guardarlo en sesión para futuras peticiones
        if ($id_propietario) {
            $_SESSION['user']['id_propietario'] = $id_propietario;
            return $id_propietario;
        }
    }

    return null;
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 1: CREAR CITA (RFS 33)
// ═══════════════════════════════════════════════════════════════════

/**
 * Permite al cliente crear una nueva cita para su mascota
 */
function crearCitaCliente()
{
    try {
        // ┌─ VALIDAR SESIÓN
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        // ┌─ OBTENER ID DEL PROPIETARIO (desde sesión o BD)
        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Solo propietarios pueden agendar citas']);
            exit();
        }

        // ┌─ OBTENER DATOS DEL REQUEST
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            exit();
        }

        // ┌─ VALIDAR CAMPOS REQUERIDOS
        $camposRequeridos = ['id_paciente', 'id_servicio', 'id_subservicio', 'fecha_hora', 'fecha_hora_fin'];
        foreach ($camposRequeridos as $campo) {
            if (empty($data[$campo])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "El campo {$campo} es requerido"]);
                exit();
            }
        }

        // ┌─ VERIFICAR QUE LA MASCOTA PERTENEZCA AL PROPIETARIO
        $modeloCitas = new CitasCliente();
        
        if (!$modeloCitas->verificarMascotaPropietario($data['id_paciente'], $id_propietario)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'La mascota no pertenece a este propietario']);
            exit();
        }

        // ┌─ VERIFICAR DISPONIBILIDAD DE HORARIO
        $disponible = $modeloCitas->verificarDisponibilidad(
            $data['fecha_hora'], 
            $data['fecha_hora_fin']
        );

        if (!$disponible) {
            $conflictos = $modeloCitas->obtenerCitasConflicto(
                $data['fecha_hora'], 
                $data['fecha_hora_fin']
            );

            $mensajeConflicto = "Ya existe una cita en este horario";
            if (!empty($conflictos)) {
                $cita = $conflictos[0];
                $horaInicio = date('H:i', strtotime($cita['fecha_hora']));
                $horaFin = date('H:i', strtotime($cita['fecha_hora_fin']));
                $mensajeConflicto .= ": {$cita['tipo']} de {$horaInicio} a {$horaFin}";
            }

            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => $mensajeConflicto,
                'conflictos' => $conflictos
            ]);
            exit();
        }

        // ┌─ PREPARAR DATOS PARA INSERTAR
        $datosInsert = [
            'id_propietario' => $id_propietario,
            'id_paciente' => (int)$data['id_paciente'],
            'id_servicio' => (int)$data['id_servicio'],
            'id_subservicio' => (int)$data['id_subservicio'],
            'id_especialidad' => $data['id_especialidad'] ?? 1,
            'tipo' => $data['tipo'] ?? 'Consulta',
            'observaciones' => $data['observaciones'] ?? null,
            'fecha_hora' => $data['fecha_hora'],
            'fecha_hora_fin' => $data['fecha_hora_fin'],
            'estado' => 'Pendiente',
            'id_usuario' => null
        ];

        // ┌─ REGISTRAR CITA
        $id_cita = $modeloCitas->crearCita($datosInsert);

        if ($id_cita) {
            // ┌─ ENVIAR NOTIFICACIÓN POR EMAIL (opcional)
            try {
                $detallesCita = $modeloCitas->obtenerDetallesCita($id_cita);
                
                if ($detallesCita && !empty($detallesCita['email_propietario'])) {
                    // enviarNotificacionCitaCreada($detallesCita);
                    error_log("✅ Cita creada, notificación pendiente");
                }
            } catch (Exception $e) {
                error_log("⚠️ Error al enviar notificación: " . $e->getMessage());
            }

            // ┌─ RESPUESTA EXITOSA
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Cita creada exitosamente',
                'id' => $id_cita
            ]);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al crear la cita']);
            exit();
        }

    } catch (Exception $e) {
        error_log("❌ Error en crearCitaCliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema: ' . $e->getMessage()]);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 2: LISTAR CITAS DEL CLIENTE (RFS 34)
// ═══════════════════════════════════════════════════════════════════

function listarCitasCliente()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
            exit();
        }

        $filtros = [];
        
        if (!empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }
        if (!empty($_GET['fecha_inicio'])) {
            $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        }
        if (!empty($_GET['fecha_fin'])) {
            $filtros['fecha_fin'] = $_GET['fecha_fin'];
        }
        if (!empty($_GET['id_paciente'])) {
            $filtros['id_paciente'] = (int)$_GET['id_paciente'];
        }

        $modeloCitas = new CitasCliente();
        $citas = $modeloCitas->listarCitasPropietario($id_propietario, $filtros);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($citas),
            'citas' => $citas
        ]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en listarCitasCliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 3: CANCELAR CITA (RFS 36)
// ═══════════════════════════════════════════════════════════════════

function cancelarCitaCliente()
{
    try {
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id_agendamiento = $data['id_agendamiento'] ?? null;
        $motivo_cancelacion = $data['motivo_cancelacion'] ?? 'Sin motivo especificado';

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de cita no proporcionado']);
            exit();
        }

        $modeloCitas = new CitasCliente();

        if (!$modeloCitas->verificarCitaPropietario($id_agendamiento, $id_propietario)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Esta cita no te pertenece']);
            exit();
        }

        $validacion = $modeloCitas->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $validacion['mensaje']]);
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $resultadoMotivo = $modeloCitas->registrarMotivoCancelacion(
            $id_agendamiento, 
            $motivo_cancelacion, 
            $id_usuario
        );

        if (!$resultadoMotivo['exito']) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $resultadoMotivo['mensaje']]);
            exit();
        }

        $resultadoEstado = $modeloCitas->actualizarEstadoCancelada($id_agendamiento);

        if (!$resultadoEstado['exito']) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $resultadoEstado['mensaje']]);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Cita cancelada exitosamente'
        ]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en cancelarCitaCliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 4: MODIFICAR CITA (RFS 35)
// ═══════════════════════════════════════════════════════════════════

function modificarCitaCliente()
{
    try {
        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id_agendamiento = $data['id_agendamiento'] ?? null;
        $nueva_fecha_hora = $data['fecha_hora'] ?? null;
        $nueva_fecha_hora_fin = $data['fecha_hora_fin'] ?? null;

        if (!$id_agendamiento || !$nueva_fecha_hora || !$nueva_fecha_hora_fin) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit();
        }

        $modeloCitas = new CitasCliente();

        if (!$modeloCitas->verificarCitaPropietario($id_agendamiento, $id_propietario)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Esta cita no te pertenece']);
            exit();
        }

        $disponible = $modeloCitas->verificarDisponibilidad(
            $nueva_fecha_hora, 
            $nueva_fecha_hora_fin,
            $id_agendamiento
        );

        if (!$disponible) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'El horario no está disponible']);
            exit();
        }

        $resultado = $modeloCitas->modificarFechasCita(
            $id_agendamiento,
            $nueva_fecha_hora,
            $nueva_fecha_hora_fin
        );

        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Cita modificada exitosamente'
            ]);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al modificar la cita']);
            exit();
        }

    } catch (Exception $e) {
        error_log("❌ Error en modificarCitaCliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIONES AUXILIARES
// ═══════════════════════════════════════════════════════════════════

function obtenerDetalleCita()
{
    try {
        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
            exit();
        }

        $id_agendamiento = $_GET['id'] ?? null;

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
            exit();
        }

        $modeloCitas = new CitasCliente();
        $detalle = $modeloCitas->obtenerDetallesCita($id_agendamiento);

        if ($detalle) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'cita' => $detalle]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Cita no encontrada']);
        }
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerDetalleCita: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

function obtenerServicios()
{
    try {
        $modeloCitas = new CitasCliente();
        $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? null;
        
        $servicios = $modeloCitas->obtenerServicios($id_veterinaria);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $servicios]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerServicios: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

function obtenerSubservicios()
{
    try {
        $id_servicio = $_GET['id_servicio'] ?? null;

        if (!$id_servicio) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de servicio requerido']);
            exit();
        }

        $modeloCitas = new CitasCliente();
        $subservicios = $modeloCitas->obtenerSubserviciosPorServicio($id_servicio);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $subservicios]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerSubservicios: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

function obtenerMascotasCliente()
{
    try {
        $id_propietario = obtenerIdPropietario();

        if (!$id_propietario) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
            exit();
        }
        
        $modeloCitas = new CitasCliente();
        $mascotas = $modeloCitas->obtenerMascotasPropietario($id_propietario);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $mascotas]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerMascotasCliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}