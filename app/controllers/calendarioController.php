<?php

session_start();

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/email_helper.php';
require_once __DIR__ . '/../models/Calendario.php';
require_once __DIR__ . '/../models/Eventos.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        // Verificar si es una petición AJAX JSON
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'];

        if (strpos($content_type, 'application/json') !== false) {
            // Es una petición JSON desde el frontend
            if (strpos($request_uri, '/calendario/storeEvent') !== false) {
                crearAgendamientoAjax();
            } elseif (strpos($request_uri, '/calendario/updateEvent') !== false) {
                actualizarAgendamientoAjax();
            } elseif (strpos($request_uri, '/cancelarCita') !== false) {
                rfs36_validarYCancelarCitaAjax();
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Ruta no reconocida']);
                exit();
            }
        } else {
            // Es una petición de formulario tradicional
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'crear') {
                crearAgendamiento();
            } else if ($accion === 'actualizar') {
                actualizarAgendamiento();
            } else if ($accion === 'eliminar') {
                eliminarAgendamiento($_POST['id'] ?? null);
            } elseif ($accion === 'validar_estado') {
                rfs36_validarEstadoCita();
            } elseif ($accion === 'registrar_motivo') {
                rfs36_registrarMotivoCancelacion();
            } elseif ($accion === 'cancelar_cita') {
                rfs36_cancelarCita();
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no especificada']);
                exit();
            }
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'];

        if ($accion === 'eliminar') {
            eliminarAgendamiento($_GET['id']);
        } else if ($accion === 'cargar') {
            cargarEventos();
        } else if (strpos($request_uri, '/calendario/getPropietarios') !== false) {
            obtenerPropietarios();
        } else if (strpos($request_uri, '/calendario/getMascotas') !== false) {
            obtenerMascotasPorPropietario();
        } else if (strpos($request_uri, '/calendario/getServicios') !== false) {
            obtenerServicios();
        } elseif ($accion === 'validar') {
            rfs36_validarEstadoCitaGet();
        } elseif ($accion === 'detalles_cancelacion') {
            rfs36_obtenerDetallesCancelacionGet();
        } else if (isset($_GET['id'])) {
            consultarAgendamientoId($_GET['id']);
        } else {
            listarAgendamientos();
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        break;
}


// =========================================
//  FUNCIONES CRUD
// =========================================

// FUNCION PARA CREAR AGENDAMIENTO VIA AJAX JSON
function crearAgendamientoAjax()
{
    try {
        // Verificar que el usuario este autenticado
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
            exit();
        }

        // Obtener datos enviados en JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            exit();
        }

        $tipo = $data['tipo'] ?? '';
        $observaciones = $data['observaciones'] ?? null;
        $fecha_hora = $data['fecha_hora'] ?? '';
        $fecha_hora_fin = $data['fecha_hora_fin'] ?? null;
        $estado = $data['estado'] ?? 'Pendiente';

        // Convertir el id_usuario a entero
        $id_usuario = isset($_SESSION['user']['id_usuario']) ? (int)$_SESSION['user']['id_usuario'] : null;

        $id_propietario = !empty($data['id_propietario']) ? (int)$data['id_propietario'] : null;
        $id_paciente = !empty($data['id_paciente']) ? (int)$data['id_paciente'] : null;
        $id_servicio = !empty($data['id_servicio']) ? (int)$data['id_servicio'] : null;
        $id_especialidad = !empty($data['id_especialidad']) ? (int)$data['id_especialidad'] : null;

        // Validamos que los campos requeridos no esten vacios
        if (empty($tipo) || empty($fecha_hora)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tipo y fecha_hora son obligatorios']);
            exit();
        }

        if (empty($id_usuario)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado - id_usuario no encontrado']);
            exit();
        }

        // Convertir formato ISO 8601 a formato MySQL
        if ($fecha_hora) {
            $fecha_hora = date('Y-m-d H:i:s', strtotime($fecha_hora));
        }
        if ($fecha_hora_fin) {
            $fecha_hora_fin = date('Y-m-d H:i:s', strtotime($fecha_hora_fin));
        }

        // VERIFICAR DISPONIBILIDAD DE HORARIO
        $objEventos = new Eventos();
        $disponible = $objEventos->verificarDisponibilidad($fecha_hora, $fecha_hora_fin);

        if (!$disponible) {
            // Obtener las citas que causan conflicto
            $citasConflicto = $objEventos->obtenerCitasConflicto($fecha_hora, $fecha_hora_fin);

            $mensajeConflicto = "Ya existe una cita en este horario: ";
            if (!empty($citasConflicto)) {
                $cita = $citasConflicto[0];
                $horaInicio = date('H:i', strtotime($cita['fecha_hora']));
                $horaFin = date('H:i', strtotime($cita['fecha_hora_fin']));
                $mensajeConflicto .= "{$cita['tipo']} de {$horaInicio} a {$horaFin}";
                if ($cita['mascota']) {
                    $mensajeConflicto .= " ({$cita['mascota']})";
                }
            }

            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => $mensajeConflicto,
                'conflictos' => $citasConflicto
            ]);
            exit();
        }

        // Preparamos los datos para el registro
        $dataToInsert = [
            'tipo' => $tipo,
            'observaciones' => $observaciones,
            'fecha_hora' => $fecha_hora,
            'fecha_hora_fin' => $fecha_hora_fin,
            'estado' => $estado,
            'id_usuario' => $id_usuario,
            'id_propietario' => $id_propietario,
            'id_paciente' => $id_paciente,
            'id_servicio' => $id_servicio,
            'id_especialidad' => $id_especialidad,
        ];

        // Registramos el agendamiento
        $id_generado = $objEventos->registrar($dataToInsert);

        if ($id_generado) {
            header('Content-Type: application/json');
            http_response_code(201);

            // ENVIAR NOTIFICACION POR EMAIL AL PROPIETARIO
            try {
                $detallesCita = $objEventos->obtenerDetallesCita($id_generado);
                if ($detallesCita && !empty($detallesCita['email_propietario'])) {
                    $datosCita = [
                        'email_propietario' => $detallesCita['email_propietario'],
                        'nombre_propietario' => $detallesCita['nombre_propietario'],
                        'nombre_mascota' => $detallesCita['nombre_mascota'],
                        'tipo_servicio' => $detallesCita['tipo'],
                        'fecha_hora' => $detallesCita['fecha_hora'],
                        'estado' => $detallesCita['estado']
                    ];

                    enviarNotificacionCitaCreada($datosCita);
                    error_log("Notificacion enviada correctamente al email: " . $detallesCita['email_propietario']);
                } else {
                    error_log("No se pudo enviar notificacion: propietario sin email registrado");
                }
            } catch (Exception $e) {
                error_log("Error al enviar notificacion: " . $e->getMessage());
                // No detenemos el proceso si falla el envio del email
            }

            echo json_encode(['status' => 'success', 'message' => 'Agendamiento creado con éxito', 'id' => $id_generado]);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el agendamiento en la base de datos']);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        exit();
    }
}

// FUNCION PARA ACTUALIZAR AGENDAMIENTO VIA AJAX
function actualizarAgendamientoAjax()
{
    try {
        // Obtener datos enviados en JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            exit();
        }

        $id_agendamiento = $data['id_agendamiento'] ?? '';
        $fecha_hora = $data['new_fecha_hora'] ?? $data['fecha_hora'] ?? '';
        $fecha_hora_fin = $data['new_fecha_hora_fin'] ?? $data['fecha_hora_fin'] ?? null;

        if (empty($id_agendamiento) || empty($fecha_hora)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de agendamiento o fecha faltante']);
            exit();
        }

        // Convertir formato ISO 8601 a formato MySQL
        if ($fecha_hora) {
            $fecha_hora = date('Y-m-d H:i:s', strtotime($fecha_hora));
        }
        if ($fecha_hora_fin) {
            $fecha_hora_fin = date('Y-m-d H:i:s', strtotime($fecha_hora_fin));
        }

        $objEventos = new Eventos();

        // VERIFICAR DISPONIBILIDAD DE HORARIO (excluyendo la cita actual)
        $disponible = $objEventos->verificarDisponibilidad($fecha_hora, $fecha_hora_fin, $id_agendamiento);

        if (!$disponible) {
            // Obtener las citas que causan conflicto
            $citasConflicto = $objEventos->obtenerCitasConflicto($fecha_hora, $fecha_hora_fin, $id_agendamiento);

            $mensajeConflicto = "Ya existe una cita en este horario: ";
            if (!empty($citasConflicto)) {
                $cita = $citasConflicto[0];
                $horaInicio = date('H:i', strtotime($cita['fecha_hora']));
                $horaFin = date('H:i', strtotime($cita['fecha_hora_fin']));
                $mensajeConflicto .= "{$cita['tipo']} de {$horaInicio} a {$horaFin}";
                if ($cita['mascota']) {
                    $mensajeConflicto .= " ({$cita['mascota']})";
                }
            }

            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => $mensajeConflicto,
                'conflictos' => $citasConflicto
            ]);
            exit();
        }

        $dataToUpdate = [
            'id_agendamiento' => $id_agendamiento,
            'fecha_hora' => $fecha_hora,
            'fecha_hora_fin' => $fecha_hora_fin,
        ];

        $resultado = $objEventos->updateAgendamientoDates($dataToUpdate);

        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Agendamiento actualizado con éxito']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el agendamiento']);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        exit();
    }
}

// FUNCION PARA CREAR UN NUEVO AGENDAMIENTO (Formulario tradicional)
function crearAgendamiento()
{
    // Capturamos los datos enviados por el formulario
    $tipo = $_POST['tipo'] ?? '';
    $fecha_hora = $_POST['fecha_hora'] ?? '';
    $fecha_hora_fin = $_POST['fecha_hora_fin'] ?? null;
    $estado = $_POST['estado'] ?? 'Pendiente';
    $id_usuario = $_SESSION['user']['id_usuario'] ?? null;
    $id_propietario = $_POST['id_propietario'] ?? null;
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_servicio = $_POST['id_servicio'] ?? null;
    $id_especialidad = $_POST['id_especialidad'] ?? null;

    // Validamos que los campos requeridos no estén vacíos
    if (empty($tipo) || empty($fecha_hora)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete los campos requeridos');
        exit();
    }

    // Creamos el objeto del modelo
    $objEventos = new Eventos();

    // Preparamos los datos para el registro
    $data = [
        'tipo' => $tipo,
        'observaciones' => null,
        'fecha_hora' => $fecha_hora,
        'fecha_hora_fin' => $fecha_hora_fin,
        'estado' => $estado,
        'id_usuario' => $id_usuario,
        'id_propietario' => $id_propietario,
        'id_paciente' => $id_paciente,
        'id_servicio' => $id_servicio,
        'id_especialidad' => $id_especialidad,
    ];

    // Registramos el agendamiento
    $resultado = $objEventos->registrar($data);

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Agendamiento creado',
            'El agendamiento ha sido creado correctamente',
            '/vetwilling/admin/calendario'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo crear el agendamiento');
    }

    exit();
}

// FUNCION PARA LISTAR LOS AGENDAMIENTOS REGISTRADOS
function listarAgendamientos()
{
    $resultado = new Eventos();
    return $resultado->listar();
}

// FUNCION PARA CONSULTAR UN AGENDAMIENTO POR ID
function consultarAgendamientoId($id)
{
    $objEventos = new Eventos();
    return $objEventos->consultarAgendamiento($id);
}

// FUNCION PARA ACTUALIZAR UN AGENDAMIENTO
function actualizarAgendamiento()
{
    $id_agendamiento = $_POST['id_agendamiento'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $fecha_hora = $_POST['fecha_hora'] ?? '';
    $fecha_hora_fin = $_POST['fecha_hora_fin'] ?? null;
    $estado = $_POST['estado'] ?? '';
    $id_paciente = $_POST['id_paciente'] ?? null;
    $id_servicio = $_POST['id_servicio'] ?? null;
    $id_especialidad = $_POST['id_especialidad'] ?? null;

    // Validamos que los campos requeridos no estén vacíos
    if (empty($id_agendamiento) || empty($tipo) || empty($fecha_hora)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete los campos requeridos');
        exit();
    }

    // Creamos el objeto del modelo
    $objEventos = new Eventos();

    // Preparamos los datos para la actualización
    $data = [
        'id_agendamiento' => $id_agendamiento,
        'tipo' => $tipo,
        'fecha_hora' => $fecha_hora,
        'fecha_hora_fin' => $fecha_hora_fin,
        'estado' => $estado,
        'id_paciente' => $id_paciente,
        'id_servicio' => $id_servicio,
        'id_especialidad' => $id_especialidad,
    ];

    // Actualizamos el agendamiento
    $resultado = $objEventos->actualizar($data);

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Agendamiento actualizado',
            'El agendamiento ha sido actualizado correctamente',
            '/vetwilling/admin/calendario'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar el agendamiento');
    }

    exit();
}

// FUNCION PARA ELIMINAR UN AGENDAMIENTO
function eliminarAgendamiento($id)
{
    // Validamos que el ID no esté vacío
    if (empty($id)) {
        // Verificar si es petición AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
            exit();
        }
        mostrarSweetAlert('error', 'Error', 'ID no proporcionado');
        exit();
    }

    // Creamos el objeto del modelo
    $objEventos = new Eventos();

    // Eliminamos el agendamiento
    $resultado = $objEventos->eliminar($id);

    // Verificar si es petición AJAX o desde navegador
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $isCalendarRequest = strpos($_SERVER['REQUEST_URI'], '/calendario/deleteEvent') !== false;

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        if ($isAjax || $isCalendarRequest) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Agendamiento eliminado correctamente']);
            exit();
        } else {
            mostrarSweetAlert(
                'success',
                'Agendamiento eliminado',
                'El agendamiento ha sido eliminado correctamente',
                '/vetwilling/admin/calendario'
            );
        }
    } else {
        if ($isAjax || $isCalendarRequest) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el agendamiento']);
            exit();
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el agendamiento');
        }
    }

    exit();
}

// FUNCION PARA CARGAR EVENTOS (Para FullCalendar con JSON)
function cargarEventos()
{
    $objEventos = new Eventos();
    $agendamientos = $objEventos->listar();

    $calendar_events = [];

    // Mapear los datos al formato de FullCalendar
    foreach ($agendamientos as $agendamiento) {
        $calendar_events[] = [
            'id' => $agendamiento['id_agendamiento'],
            'title' => $agendamiento['tipo'],
            'start' => $agendamiento['fecha_hora'],
            'end' => $agendamiento['fecha_hora_fin'] ?? null,
            'backgroundColor' => getColorByTipo($agendamiento['tipo']),
            'borderColor' => getColorByTipo($agendamiento['tipo']),
            'allDay' => false
        ];
    }

    // Devolvemos la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($calendar_events);
    exit();
}

// FUNCION AUXILIAR PARA ASIGNAR COLORES SEGÚN EL TIPO DE EVENTO
function getColorByTipo($tipo)
{
    $tipoLower = strtolower($tipo);

    if (strpos($tipoLower, 'consulta') !== false) {
        return '#007832'; // Verde SENA
    } else if (strpos($tipoLower, 'vacuna') !== false) {
        return '#17a2b8'; // Azul info
    } else if (strpos($tipoLower, 'cirug') !== false) {
        return '#dc3545'; // Rojo
    } else if (strpos($tipoLower, 'control') !== false) {
        return '#ffc107'; // Amarillo
    } else if (strpos($tipoLower, 'emergencia') !== false) {
        return '#fd7e14'; // Naranja
    } else if (strpos($tipoLower, 'desparasita') !== false) {
        return '#6f42c1'; // Púrpura
    } else if (strpos($tipoLower, 'peluque') !== false) {
        return '#e83e8c'; // Rosa
    } else if (strpos($tipoLower, 'baño') !== false || strpos($tipoLower, 'bano') !== false) {
        return '#20c997'; // Verde agua
    } else {
        return '#6c757d'; // Gris por defecto
    }
}

// FUNCION AUXILIAR PARA ASIGNAR COLORES SEGÚN EL ESTADO
function getColorByEstado($estado)
{
    switch ($estado) {
        case 'Confirmado':
            return '#28a745'; // Verde
        case 'Pendiente':
            return '#ffc107'; // Amarillo
        case 'Cancelado':
            return '#dc3545'; // Rojo
        default:
            return '#007bff'; // Azul por defecto
    }
}

// FUNCION PARA OBTENER LISTA DE PROPIETARIOS
function obtenerPropietarios()
{
    require_once __DIR__ . '/../models/Calendario.php';
    $objCalendario = new Calendario();
    $propietarios = $objCalendario->obtenerPropietarios();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $propietarios]);
    exit();
}

// FUNCION PARA OBTENER MASCOTAS POR PROPIETARIO
function obtenerMascotasPorPropietario()
{
    $id_propietario = $_GET['id_propietario'] ?? null;

    if (!$id_propietario) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'ID de propietario no proporcionado']);
        exit();
    }

    require_once __DIR__ . '/../models/Calendario.php';
    $objCalendario = new Calendario();
    $mascotas = $objCalendario->obtenerMascotasPorPropietario($id_propietario);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $mascotas]);
    exit();
}

// FUNCION PARA OBTENER SERVICIOS DESDE BASE DE DATOS
function obtenerServicios()
{
    try {
        $calendarioModel = new Calendario();
        $servicios = $calendarioModel->obtenerServicios();

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $servicios]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener servicios: ' . $e->getMessage()]);
    }
    exit();
}

// =========================================
//  RFS 36: CANCELACION DE CITAS
// =========================================

/**
 * SUBTAREA 1: Valida el estado de una cita antes de cancelarla
 * Verifica que la cita exista y esté en estado Pendiente
 */
function rfs36_validarEstadoCita()
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
        error_log("Error en rfs36_validarEstadoCita -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al validar el estado de la cita');
    }
}

/**
 * SUBTAREA 1: Valida el estado de una cita (GET)
 * Útil para validaciones previas desde el frontend
 */
function rfs36_validarEstadoCitaGet()
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
        error_log("Error en rfs36_validarEstadoCitaGet -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema al validar la cita'
        ]);
    }
}

/**
 * SUBTAREA 2: Registra el motivo de cancelación de una cita (POST)
 */
function rfs36_registrarMotivoCancelacion()
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
        error_log("Error en rfs36_registrarMotivoCancelacion -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al registrar el motivo');
    }
}

/**
 * RFS 36: Obtiene los detalles de cancelación de una cita (GET)
 */
function rfs36_obtenerDetallesCancelacionGet()
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
        error_log("Error en rfs36_obtenerDetallesCancelacionGet -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema'
        ]);
    }
}

/**
 * RFS 36: Función para validar y cancelar cita via AJAX
 * Integra subtareas 1, 2, 3 y 4 completas
 */
function rfs36_validarYCancelarCitaAjax()
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

        // SUBTAREA 3: Actualizar estado a Cancelada
        $actualizarEstado = $calendario->actualizarEstadoCancelada($id_agendamiento);

        if (!$actualizarEstado['exito']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $actualizarEstado['mensaje']
            ]);
            exit();
        }

        // SUBTAREA 4: Enviar notificación al propietario
        $datosNotificacion = $calendario->obtenerDatosParaNotificacionCancelacion($id_agendamiento);
        
        if ($datosNotificacion && !empty($datosNotificacion['email_propietario'])) {
            try {
                enviarNotificacionCitaCancelada($datosNotificacion);
                error_log("Notificación de cancelación enviada al email: " . $datosNotificacion['email_propietario']);
            } catch (Exception $e) {
                error_log("Error al enviar notificación de cancelación: " . $e->getMessage());
                // No detenemos el proceso si falla el envio del email
            }
        } else {
            error_log("No se pudo enviar notificación: propietario sin email registrado o datos incompletos");
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Cita cancelada correctamente. Notificación enviada al propietario.',
            'id_agendamiento' => $id_agendamiento
        ]);
    } catch (Exception $e) {
        error_log("Error en rfs36_validarYCancelarCitaAjax -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema'
        ]);
    }
}

/**
 * RFS 36: Cancelar cita (POST tradicional)
 * Integra subtareas 1, 2, 3 y 4 completas
 */
function rfs36_cancelarCita()
{
    try {
        $id_agendamiento = $_POST['id_agendamiento'] ?? null;
        $motivo_cancelacion = $_POST['motivo_cancelacion'] ?? null;

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

        // SUBTAREA 2: Registrar motivo de cancelación
        if (!$motivo_cancelacion) {
            alertaModal('error', 'Error', 'Motivo de cancelación es requerido');
            return;
        }

        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $registroMotivo = $calendario->registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario);

        if (!$registroMotivo['exito']) {
            alertaModal('error', 'Error', $registroMotivo['mensaje']);
            return;
        }

        // SUBTAREA 3: Actualizar estado a Cancelada
        $actualizarEstado = $calendario->actualizarEstadoCancelada($id_agendamiento);

        if (!$actualizarEstado['exito']) {
            alertaModal('error', 'Error', $actualizarEstado['mensaje']);
            return;
        }

        // SUBTAREA 4: Enviar notificación al propietario
        $datosNotificacion = $calendario->obtenerDatosParaNotificacionCancelacion($id_agendamiento);
        
        if ($datosNotificacion && !empty($datosNotificacion['email_propietario'])) {
            try {
                enviarNotificacionCitaCancelada($datosNotificacion);
                error_log("Notificación de cancelación enviada al email: " . $datosNotificacion['email_propietario']);
            } catch (Exception $e) {
                error_log("Error al enviar notificación de cancelación: " . $e->getMessage());
                // No detenemos el proceso si falla el envio del email
            }
        } else {
            error_log("No se pudo enviar notificación: propietario sin email registrado o datos incompletos");
        }

        alertaModal('success', 'Éxito', 'Cita cancelada correctamente. Notificación enviada al propietario.');
    } catch (Exception $e) {
        error_log("Error en rfs36_cancelarCita -> " . $e->getMessage());
        alertaModal('error', 'Error del Sistema', 'Error al procesar la cancelación');
    }
}
