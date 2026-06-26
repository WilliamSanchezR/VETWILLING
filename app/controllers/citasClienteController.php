<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  CONTROLADOR DE CITAS PARA CLIENTES/PROPIETARIOS - COMPLETO
 *  Archivo: citasClienteController.php
 * ═══════════════════════════════════════════════════════════════════
 */

require_once BASE_PATH . '/app/helpers/session_helper.php';
initSession();

require_once __DIR__ . '/../models/CitasCliente.php';
require_once __DIR__ . '/../models/Profesional.php';
require_once __DIR__ . '/../helpers/email_helper.php';
require_once __DIR__ . '/../helpers/notificacion_helper.php';
require_once BASE_PATH . '/app/helpers/notification/notificacion_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($content_type, 'application/json') !== false) {
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
            case 'historial_paciente':
                obtenerHistorialPaciente();
                break;
            case 'ficha_paciente':
                obtenerFichaCompletaPaciente();
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

function obtenerIdPropietario()
{
    if (isset($_SESSION['user']['id_propietario'])) {
        return (int)$_SESSION['user']['id_propietario'];
    }

    if (isset($_SESSION['user']['id_usuario'])) {
        $id_usuario = $_SESSION['user']['id_usuario'];
        $modeloCitas = new CitasCliente();
        $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario($id_usuario);
        
        if ($id_propietario) {
            $_SESSION['user']['id_propietario'] = $id_propietario;
            return $id_propietario;
        }
    }

    return null;
}

// Registro local de errores para depuración (archivo: app/logs/citas_error.log)
function logCitaError($mensaje)
{
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/citas_error.log';
    $ts = date('Y-m-d H:i:s');
    $entry = "[{$ts}] " . $mensaje . "\n";
    @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 1: CREAR CITA (RFS 33)
// ═══════════════════════════════════════════════════════════════════


function crearCitaCliente()
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
            echo json_encode(['status' => 'error', 'message' => 'Solo propietarios pueden agendar citas']);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            exit();
        }

        $camposRequeridos = ['id_paciente', 'id_servicio', 'id_subservicio', 'fecha_hora', 'fecha_hora_fin'];
        foreach ($camposRequeridos as $campo) {
            if (empty($data[$campo])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "El campo {$campo} es requerido"]);
                exit();
            }
        }

        $fechaInicio = strtotime($data['fecha_hora']);
        if ($fechaInicio !== false && $fechaInicio < time()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No puedes agendar una cita en el pasado.']);
            exit();
        }

        $modeloCitas = new CitasCliente();
        $id_usuario_veterinario = null;

        $id_usuario_profesional = null;
        if (!empty($data['id_usuario']) && is_numeric($data['id_usuario'])) {
            $id_usuario_profesional = (int)$data['id_usuario'];
        } elseif (!empty($data['id_veterinario']) && is_numeric($data['id_veterinario'])) {
            $id_usuario_profesional = (int)$data['id_veterinario'];
        }

        if ($id_usuario_profesional !== null) {
            $profesionalModel = new Profesional();
            $profesional = $profesionalModel->consultarPorId($id_usuario_profesional);
            if ($profesional) {
                $id_usuario_veterinario = $id_usuario_profesional;
            }
        }

        if (!$modeloCitas->verificarMascotaPropietario($data['id_paciente'], $id_propietario)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'La mascota no pertenece a este propietario']);
            exit();
        }

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
            // El campo id_usuario almacena el veterinario asignado para la cita
            'id_usuario' => $id_usuario_veterinario
        ];

        $id_cita = $modeloCitas->crearCita($datosInsert);

        if ($id_cita) {
            // ═══════════════════════════════════════════════════════════════════
            // RFS 37: ENVIAR EMAIL DE CONFIRMACION AL PROPIETARIO
            // ═══════════════════════════════════════════════════════════════════
            
            try {
                // Obtener detalles completos de la cita para el email
                $detallesCita = $modeloCitas->obtenerDetallesCita($id_cita);
                
                if ($detallesCita && !empty($detallesCita['email_propietario'])) {
                    // Preparar datos para enviar al email
                    $datosCitaEmail = [
                        'email_propietario' => $detallesCita['email_propietario'],
                        'nombre_propietario' => $detallesCita['nombre_propietario'],
                        'nombre_mascota' => $detallesCita['nombre_mascota'] ?? 'su mascota',
                        'tipo_servicio' => $detallesCita['servicio_nombre'] ?? $detallesCita['tipo'],
                        'fecha_hora' => $detallesCita['fecha_hora'],
                        'estado' => $detallesCita['estado'] ?? 'Pendiente'
                    ];
                    
                    // Enviar notificación de confirmación de cita
                    $emailEnviado = enviarNotificacionCitaCreada($datosCitaEmail);
                    
                    // Registrar intento de envío en auditoría
                    if ($emailEnviado) {
                        registrarNotificacionEnviada([
                            'id_agendamiento' => $id_cita,
                            'medio_notificacion' => 'email',
                            'destinatario' => $detallesCita['email_propietario'],
                            'estado_envio' => 'exitoso',
                            'mensaje_error' => null,
                            'intentos_envio' => 1
                        ]);
                        error_log("✅ Email de confirmación enviado exitosamente - Cita ID: {$id_cita}");
                    } else {
                        registrarNotificacionEnviada([
                            'id_agendamiento' => $id_cita,
                            'medio_notificacion' => 'email',
                            'destinatario' => $detallesCita['email_propietario'],
                            'estado_envio' => 'fallido',
                            'mensaje_error' => 'Error al enviar email de confirmación - Verificar configuración SMTP',
                            'intentos_envio' => 1
                        ]);
                        error_log("❌ Error al enviar email de confirmación - Cita ID: {$id_cita}");
                    }
                } else {
                    error_log("⚠️ No se pudo obtener datos de propietario para enviar email - Cita ID: {$id_cita}");
                }
            } catch (Exception $e) {
                error_log("⚠️ Excepción al enviar email de confirmación: " . $e->getMessage());
                // No detenemos el proceso si falla el email, la cita ya fue creada
            }

            // Notificación in-app al veterinario asignado
            if (!empty($id_usuario_veterinario) && !empty($detallesCita)) {
                $fechaCita     = date('d/m/Y H:i', strtotime($data['fecha_hora']));
                $nombreMascota = $detallesCita['nombre_mascota'] ?? 'un paciente';
                notificar(
                    'cita',
                    'Nueva cita agendada',
                    "Se agendó una cita para {$nombreMascota} el {$fechaCita}.",
                    (int) $id_usuario_veterinario,
                    (int) $data['id_paciente'],
                    '/veterinario/citas'
                );
            }

            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Cita creada exitosamente',
                'id' => $id_cita
            ]);
            exit();
        } else {
            // Log detallado para depuración local
            $payload = isset($data) ? print_r($data, true) : 'sin payload';
            logCitaError("crearCita devolvió false. Payload: {$payload}");
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al crear la cita. Consulte logs locales.']);
            exit();
        }

    } catch (Exception $e) {
        error_log("❌ Error en crearCitaCliente: " . $e->getMessage());
        // Además del error_log, escribimos en el log local para que el usuario pueda acceder desde el proyecto
        $payload = isset($data) ? print_r($data, true) : 'sin payload';
        logCitaError("Excepción en crearCitaCliente: " . $e->getMessage() . " | Payload: " . $payload);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema. Consulte logs locales.']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  FUNCIÓN 2: LISTAR CITAS DEL CLIENTE (RFS 34)
// ═══════════════════════════════════════════════════════════════════

function listarCitasCliente()
{
    try {
        logCitaError("Entrando a listarCitasCliente. Usuario: " . (isset($_SESSION['user']['id_usuario']) ? $_SESSION['user']['id_usuario'] : 'no_auth'));
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
        logCitaError("listarCitasCliente: id_propietario={$id_propietario} - citas_obtenidas=" . count($citas));

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

        // Subtarea 5: confirmar que el slot quedó liberado para nuevos agendamientos
        $slotLiberado = $modeloCitas->confirmarSlotLiberado($id_agendamiento);
        if (!$slotLiberado) {
            error_log("⚠️ Advertencia: el slot del agendamiento {$id_agendamiento} no pudo verificarse como liberado");
        }

        // Notificación in-app al veterinario sobre la cancelación
        try {
            $detalleCancelada = $modeloCitas->obtenerDetallesCita($id_agendamiento);
            if (!empty($detalleCancelada['id_usuario'])) {
                $fechaCita     = date('d/m/Y H:i', strtotime($detalleCancelada['fecha_hora']));
                $nombreMascota = $detalleCancelada['nombre_mascota'] ?? 'un paciente';
                notificar(
                    'cita',
                    'Cita cancelada por el propietario',
                    "Se canceló la cita de {$nombreMascota} del {$fechaCita}. Motivo: {$motivo_cancelacion}",
                    (int) $detalleCancelada['id_usuario'],
                    (int) $detalleCancelada['id_paciente'],
                    '/veterinario/citas'
                );
            }
        } catch (\Throwable $e) {
            error_log('citasClienteController: error al notificar cancelación: ' . $e->getMessage());
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
            // Notificación in-app al veterinario sobre el reagendamiento
            try {
                $detalleModif = $modeloCitas->obtenerDetallesCita($id_agendamiento);
                if (!empty($detalleModif['id_usuario'])) {
                    $fechaNueva    = date('d/m/Y H:i', strtotime($nueva_fecha_hora));
                    $nombreMascota = $detalleModif['nombre_mascota'] ?? 'un paciente';
                    notificar(
                        'cita',
                        'Cita reagendada',
                        "El propietario modificó la cita de {$nombreMascota}. Nueva fecha: {$fechaNueva}.",
                        (int) $detalleModif['id_usuario'],
                        (int) $detalleModif['id_paciente'],
                        '/veterinario/citas'
                    );
                }
            } catch (\Throwable $e) {
                error_log('citasClienteController: error al notificar reagendamiento: ' . $e->getMessage());
            }

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

function obtenerHistorialPaciente()
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

        $id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;

        if ($id_paciente <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de paciente inválido']);
            exit();
        }

        $limite = max(1, min($limite, 100));

        $modeloCitas = new CitasCliente();
        $historial = $modeloCitas->obtenerHistorialPorPaciente($id_propietario, $id_paciente, $limite);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($historial),
            'id_paciente' => $id_paciente,
            'historial' => $historial
        ]);
        exit();
    } catch (Exception $e) {
        error_log("❌ Error en obtenerHistorialPaciente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error del sistema']);
        exit();
    }
}

function obtenerFichaCompletaPaciente()
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

        $id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
        if ($id_paciente <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de paciente inválido']);
            exit();
        }

        $modeloCitas = new CitasCliente();

        // Subtareas 2/6: info completa con validación de propiedad
        $paciente = $modeloCitas->obtenerInfoPacienteConPropiedad($id_propietario, $id_paciente);
        if (!$paciente) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Paciente no encontrado o acceso denegado']);
            exit();
        }

        // Subtarea 3: vacunas
        $vacunas = $modeloCitas->obtenerVacunasPorPaciente($id_propietario, $id_paciente);

        // Subtarea 5: historial clínico y tratamientos
        $historial_clinico = $modeloCitas->obtenerHistorialClinicoPorPaciente($id_propietario, $id_paciente);
        $tratamientos      = $modeloCitas->obtenerTratamientosPorPaciente($id_propietario, $id_paciente);
        $citas             = $modeloCitas->obtenerHistorialPorPaciente($id_propietario, $id_paciente, 50);

        header('Content-Type: application/json');
        echo json_encode([
            'status'           => 'success',
            'paciente'         => $paciente,
            'vacunas'          => $vacunas,
            'historial_clinico'=> $historial_clinico,
            'tratamientos'     => $tratamientos,
            'citas'            => $citas,
        ]);
        exit();

    } catch (Exception $e) {
        error_log("❌ Error en obtenerFichaCompletaPaciente: " . $e->getMessage());
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