<?php

session_start();

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/email_helper.php';
require_once __DIR__ . '/../models/Calendario.php';
require_once __DIR__ . '/../models/Eventos.php';
require_once __DIR__ . '/../models/DisponibilidadUsuario.php';

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

        // ╔══════════════════════════════════════════════════════════════╗
        // ║                   RFS 34: CONSULTA DE CITAS                ║
        // ║  Nuevas rutas para consultar y filtrar citas               ║
        // ╚══════════════════════════════════════════════════════════════╝

        if ($accion === 'mis_citas') {
            // Obtiene las citas del usuario autenticado con filtros opcionales
            rfs34_validarSesionYObtenerCitas();
        } elseif ($accion === 'filtrar') {
            // Filtra citas por múltiples criterios (estado, fecha, propietario, etc.)
            rfs34_consultarYFiltrarCitas();
        } elseif ($accion === 'detalles_completo') {
            // Obtiene detalles completos de una cita específica
            rfs34_obtenerDetallesCitaCompleto();
        }
        // ─────────────────────────────────────────────────────────────

        else if ($accion === 'eliminar') {
            eliminarAgendamiento($_GET['id']);
        } else if ($accion === 'cargar') {
            cargarEventos();
        } else if (strpos($request_uri, '/calendario/loadEvents') !== false) {
            cargarEventos();
        } else if (strpos($request_uri, '/calendario/getPropietarios') !== false) {
            obtenerPropietarios();
        } else if (strpos($request_uri, '/calendario/getMascotas') !== false) {
            obtenerMascotasPorPropietario();
        } else if (strpos($request_uri, '/calendario/getServicios') !== false) {
            obtenerServicios();
        } else if (strpos($request_uri, '/calendario/getVeterinarios') !== false) {
            obtenerVeterinarios();
        } else if (strpos($request_uri, '/calendario/getSubservicios') !== false) {
            // Verificar si viene id_servicio como parámetro
            if (isset($_GET['id_servicio'])) {
                obtenerSubserviciosPorServicio();
            } else {
                obtenerSubservicios();
            }
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

// ╔══════════════════════════════════════════════════════════════════════════════╗
// ║                       RFS 33: REGISTRO DE CITAS                              ║
// ╚══════════════════════════════════════════════════════════════════════════════╝

/**
 * RFS 33: Función para crear agendamiento vía AJAX JSON
 * Permite registrar una nueva cita con validación de disponibilidad
 * y envío de notificaciones por email
 */
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

        // LOG TEMPORAL PARA DEPURACIÓN
        error_log("=== DATOS RECIBIDOS EN crearAgendamientoAjax ===");
        error_log("Data completa: " . print_r($data, true));
        error_log("id_servicio recibido: " . ($data['id_servicio'] ?? 'NO EXISTE'));
        error_log("id_subservicio recibido: " . ($data['id_subservicio'] ?? 'NO EXISTE'));
        error_log("==============================================");

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

        $id_paciente = !empty($data['id_paciente']) ? (int)$data['id_paciente'] : null;
        $id_servicio = !empty($data['id_servicio']) ? (int)$data['id_servicio'] : null;

        // Validación robusta para id_subservicio
        $id_subservicio = null;
        if (isset($data['id_subservicio']) && is_numeric($data['id_subservicio']) && $data['id_subservicio'] > 0) {
            $id_subservicio = (int)$data['id_subservicio'];
        }

        $id_especialidad = !empty($data['id_especialidad']) ? (int)$data['id_especialidad'] : 1;

        // Validamos que los campos requeridos no esten vacios
        if (empty($tipo) || empty($fecha_hora) || empty($id_subservicio)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tipo, fecha_hora y subservicio son obligatorios']);
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

        // VALIDAR QUE LA CITA ESTÉ DENTRO DE LA DISPONIBILIDAD DEL VETERINARIO
        $disponibilidadModel = new DisponibilidadUsuario();

        // Resolver id_veterinaria
        $id_veterinaria = resolverIdVeterinaria();

        if (!$id_veterinaria) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo determinar la veterinaria asociada'
            ]);
            exit();
        }

        // Validar que la cita esté dentro de la disponibilidad
        $validacionDisponibilidad = $disponibilidadModel->validarCitaDentroDisponibilidad(
            $id_usuario,
            $id_veterinaria,
            $fecha_hora,
            $fecha_hora_fin
        );

        if (!$validacionDisponibilidad['valido']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $validacionDisponibilidad['mensaje'],
                'tipo' => 'disponibilidad',
                'rangos_disponibles' => $validacionDisponibilidad['rangos_disponibles']
            ]);
            exit();
        }

        // VERIFICAR DISPONIBILIDAD DE HORARIO (conflicto con otras citas)
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
                'tipo' => 'conflicto',
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
            'id_paciente' => $id_paciente,
            'id_servicio' => $id_servicio,
            'id_subservicio' => $id_subservicio,
            'id_especialidad' => $id_especialidad,
        ];

        // LOG DATOS A INSERTAR
        error_log("=== DATOS A INSERTAR EN BD ===");
        error_log(print_r($dataToInsert, true));
        error_log("==============================");

        // Registramos el agendamiento
        $id_generado = $objEventos->registrar($dataToInsert);

        error_log("=== RESULTADO DE INSERCIÓN ===");
        error_log("ID Generado: " . ($id_generado ? $id_generado : 'FALSE'));
        error_log("==============================");

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

// ╔══════════════════════════════════════════════════════════════════════════════╗
// ║                    RFS 35: MODIFICACIÓN DE CITAS                             ║
// ╚══════════════════════════════════════════════════════════════════════════════╝

/**
 * RFS 35: Función para actualizar agendamiento vía AJAX
 * Permite modificar las fechas de una cita existente con validación
 * de disponibilidad de horario
 */
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

        // Obtener la cita actual para conocer el id_usuario (veterinario)
        $citaActual = $objEventos->consultarAgendamiento($id_agendamiento);

        if (!$citaActual) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Cita no encontrada']);
            exit();
        }

        $id_usuario = $citaActual['id_usuario'];

        // VALIDAR QUE LA CITA ESTÉ DENTRO DE LA DISPONIBILIDAD DEL VETERINARIO
        $disponibilidadModel = new DisponibilidadUsuario();

        // Resolver id_veterinaria
        $id_veterinaria = resolverIdVeterinaria();

        if (!$id_veterinaria) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo determinar la veterinaria asociada'
            ]);
            exit();
        }

        // Validar que la nueva fecha esté dentro de la disponibilidad
        $validacionDisponibilidad = $disponibilidadModel->validarCitaDentroDisponibilidad(
            $id_usuario,
            $id_veterinaria,
            $fecha_hora,
            $fecha_hora_fin
        );

        if (!$validacionDisponibilidad['valido']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $validacionDisponibilidad['mensaje'],
                'tipo' => 'disponibilidad',
                'rangos_disponibles' => $validacionDisponibilidad['rangos_disponibles']
            ]);
            exit();
        }

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
                'tipo' => 'conflicto',
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

/**
 * RFS 33: Función para crear un nuevo agendamiento (Formulario tradicional)
 * Permite registrar citas mediante formulario HTML tradicional
 */
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

/**
 * RFS 35: Función para actualizar un agendamiento (Formulario tradicional)
 * Permite modificar datos completos de una cita existente
 */
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
    // LOG DE INICIO
    error_log("=== CARGANDO EVENTOS (cargarEventos) ===");

    // Obtener el id_usuario del veterinario si se proporciona (filtro)
    $id_usuario = $_GET['id_usuario'] ?? null;

    $objEventos = new Eventos();

    // Si hay filtro por veterinario, usar listarPorVeterinario, sino listar todos
    if ($id_usuario) {
        $agendamientos = $objEventos->listarPorVeterinario($id_usuario);
    } else {
        $agendamientos = $objEventos->listar();
    }

    error_log("Eventos encontrados en BD: " . count($agendamientos));

    $calendar_events = [];

    // Mapear los datos al formato de FullCalendar
    foreach ($agendamientos as $agendamiento) {
        // Asegurar formato ISO 8601 para FullCalendar (YYYY-MM-DDTHH:mm:ss)
        $start = str_replace(' ', 'T', $agendamiento['fecha_hora']);
        $end = $agendamiento['fecha_hora_fin'] ? str_replace(' ', 'T', $agendamiento['fecha_hora_fin']) : null;

        $calendar_events[] = [
            'id' => $agendamiento['id_agendamiento'],
            'title' => $agendamiento['tipo'],
            'start' => $start,
            'end' => $end,
            'backgroundColor' => getColorByTipo($agendamiento['tipo']),
            'borderColor' => getColorByTipo($agendamiento['tipo']),
            'allDay' => false,
            'extendedProps' => [
                'id_usuario' => $agendamiento['id_usuario'],
                'id_servicio' => $agendamiento['id_servicio'],
                'id_subservicio' => $agendamiento['id_subservicio'] ?? null,
                'id_especialidad' => $agendamiento['id_especialidad'],
                'id_paciente' => $agendamiento['id_paciente'],
                'observaciones' => $agendamiento['observaciones']
            ]
        ];
    }

    // Devolvemos la respuesta en formato JSON
    // Limpiar cualquier buffer de salida previo para evitar JSON inválido
    if (ob_get_length()) ob_clean();

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // Por si acaso hay temas de CORS/Subdominios

    $json = json_encode($calendar_events);

    if ($json === false) {
        error_log("Error al codificar JSON: " . json_last_error_msg());
        echo json_encode(['error' => 'Error al generar JSON']);
    } else {
        echo $json;
    }

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

// Helper para obtener id_veterinaria desde sesión o relación profesional
function resolverIdVeterinaria()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? null;
    if (!empty($id_veterinaria)) {
        return $id_veterinaria;
    }

    $id_usuario = $_SESSION['user']['id_usuario'] ?? null;
    if (empty($id_usuario)) {
        return null;
    }

    $disponibilidadModel = new DisponibilidadUsuario();
    return $disponibilidadModel->obtenerVeterinariaPorUsuario($id_usuario);
}

// FUNCION PARA OBTENER SERVICIOS DESDE BASE DE DATOS FILTRADOS POR VETERINARIA
function obtenerServicios()
{
    try {
        // Verificar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Obtener el id_veterinaria de la sesión o relación profesional
        $id_veterinaria = resolverIdVeterinaria();

        error_log("=== DEBUG obtenerServicios ===");
        error_log("ID Veterinaria resuelta: " . $id_veterinaria);
        error_log("Datos de sesión: " . print_r($_SESSION['user'], true));

        if (!$id_veterinaria) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo obtener la veterinaria del usuario']);
            exit();
        }

        $calendarioModel = new Calendario();
        $servicios = $calendarioModel->obtenerServicios($id_veterinaria);

        error_log("Servicios encontrados: " . count($servicios));
        error_log("Servicios: " . print_r($servicios, true));

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $servicios]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener servicios: ' . $e->getMessage()]);
    }
    exit();
}

// FUNCION PARA OBTENER VETERINARIOS DESDE BASE DE DATOS
function obtenerVeterinarios()
{
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id_veterinaria = resolverIdVeterinaria();

        if (!$id_veterinaria) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo obtener la veterinaria del usuario']);
            exit();
        }

        require_once __DIR__ . '/../models/Veterinario.php';
        $veterinarioModel = new Veterinario();
        $veterinarios = $veterinarioModel->listar($id_veterinaria);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $veterinarios]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener veterinarios: ' . $e->getMessage()]);
    }
    exit();
}

// FUNCION PARA OBTENER SUBSERVICIOS FILTRADOS POR VETERINARIA
function obtenerSubservicios()
{
    try {
        // Verificar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Obtener el id_veterinaria de la sesión o relación profesional
        $id_veterinaria = resolverIdVeterinaria();

        if (!$id_veterinaria) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo obtener la veterinaria del usuario']);
            exit();
        }

        $calendarioModel = new Calendario();
        $subservicios = $calendarioModel->obtenerSubservicios($id_veterinaria);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $subservicios]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener subservicios: ' . $e->getMessage()]);
    }
    exit();
}

// FUNCION PARA OBTENER SUBSERVICIOS POR ID DE SERVICIO
function obtenerSubserviciosPorServicio()
{
    try {
        $id_servicio = $_GET['id_servicio'] ?? null;

        if (!$id_servicio) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de servicio no proporcionado']);
            exit();
        }

        $calendarioModel = new Calendario();
        $subservicios = $calendarioModel->obtenerSubserviciosPorServicio($id_servicio);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $subservicios]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener subservicios: ' . $e->getMessage()]);
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
            mostrarSweetAlert('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        $calendario = new Calendario();
        $resultado = $calendario->validarEstadoCita($id_agendamiento);

        if ($resultado['valido']) {
            mostrarSweetAlert('success', 'Validación Exitosa', $resultado['mensaje']);
        } else {
            mostrarSweetAlert('error', 'Validación Fallida', $resultado['mensaje']);
        }
    } catch (Exception $e) {
        error_log("Error en rfs36_validarEstadoCita -> " . $e->getMessage());
        mostrarSweetAlert('error', 'Error del Sistema', 'Error al validar el estado de la cita');
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
            mostrarSweetAlert('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        if (!$motivo_cancelacion) {
            mostrarSweetAlert('error', 'Error', 'Motivo de cancelación es requerido');
            return;
        }

        // SUBTAREA 1: Validar estado de la cita
        $calendario = new Calendario();
        $validacion = $calendario->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            mostrarSweetAlert('error', 'Error', $validacion['mensaje']);
            return;
        }

        // SUBTAREA 2: Registrar motivo de cancelación
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $resultado = $calendario->registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario);

        if ($resultado['exito']) {
            mostrarSweetAlert('success', 'Éxito', $resultado['mensaje']);
        } else {
            mostrarSweetAlert('error', 'Error', $resultado['mensaje']);
        }
    } catch (Exception $e) {
        error_log("Error en rfs36_registrarMotivoCancelacion -> " . $e->getMessage());
        mostrarSweetAlert('error', 'Error del Sistema', 'Error al registrar el motivo');
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
            mostrarSweetAlert('error', 'Error', 'ID de cita no proporcionado');
            return;
        }

        // SUBTAREA 1: Validar estado de la cita
        $calendario = new Calendario();
        $validacion = $calendario->validarEstadoCita($id_agendamiento);

        if (!$validacion['valido']) {
            mostrarSweetAlert('error', 'Error', $validacion['mensaje']);
            return;
        }

        // SUBTAREA 2: Registrar motivo de cancelación
        if (!$motivo_cancelacion) {
            mostrarSweetAlert('error', 'Error', 'Motivo de cancelación es requerido');
            return;
        }

        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $registroMotivo = $calendario->registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario);

        if (!$registroMotivo['exito']) {
            mostrarSweetAlert('error', 'Error', $registroMotivo['mensaje']);
            return;
        }

        // SUBTAREA 3: Actualizar estado a Cancelada
        $actualizarEstado = $calendario->actualizarEstadoCancelada($id_agendamiento);

        if (!$actualizarEstado['exito']) {
            mostrarSweetAlert('error', 'Error', $actualizarEstado['mensaje']);
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

        mostrarSweetAlert('success', 'Éxito', 'Cita cancelada correctamente. Notificación enviada al propietario.');
    } catch (Exception $e) {
        error_log("Error en rfs36_cancelarCita -> " . $e->getMessage());
        mostrarSweetAlert('error', 'Error del Sistema', 'Error al procesar la cancelación');
    }
}

// ╔══════════════════════════════════════════════════════════════════════════════╗
// ║                   RFS 34: CONSULTA DE CITAS PROGRAMADAS                    ║
// ║  Permite a los usuarios consultar sus citas con filtros por estado, fecha   ║
// ║  y otros criterios. Valida la sesión y el rol del usuario.                 ║
// ╚══════════════════════════════════════════════════════════════════════════════╝

/**
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ SUBTAREA 1: VALIDAR SESIÓN DEL USUARIO                                   │
 * │ Verifica que el usuario esté autenticado antes de consultar citas         │
 * └───────────────────────────────────────────────────────────────────────────┘
 */
function rfs34_validarSesionYObtenerCitas()
{
    try {
        // ┌─ VALIDAR SESIÓN
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ]);
            exit();
        }

        // ┌─ OBTENER DATOS DEL USUARIO ACTUAL
        $id_usuario = $_SESSION['user']['id_usuario'];
        $tipo_usuario = $_SESSION['user']['tipo_usuario'] ?? 'propietario'; // propietario, veterinario, admin

        // ┌─ OBTENER FILTROS DEL REQUEST
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

        // ┌─ LLAMAR AL MODELO
        $calendario = new Calendario();
        $citas = $calendario->obtenerCitasDelUsuario($id_usuario, $tipo_usuario, $filtros);

        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($citas),
            'citas' => $citas,
            'usuario' => [
                'id' => $id_usuario,
                'tipo' => $tipo_usuario
            ]
        ]);
        exit();
    } catch (Exception $e) {
        error_log("Error en rfs34_validarSesionYObtenerCitas -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error del sistema'
        ]);
        exit();
    }
}

/**
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ SUBTAREA 2 & 3: CONSULTAR Y FILTRAR CITAS                                │
 * │ Retorna lista de citas con filtros aplicados (fecha, estado, propietario) │
 * └───────────────────────────────────────────────────────────────────────────┘
 */
function rfs34_consultarYFiltrarCitas()
{
    try {
        // ┌─ OBTENER FILTROS DEL REQUEST
        $filtros = [];

        if (!empty($_GET['id_propietario'])) {
            $filtros['id_propietario'] = (int)$_GET['id_propietario'];
        }
        if (!empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }
        if (!empty($_GET['fecha_inicio'])) {
            $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        }
        if (!empty($_GET['fecha_fin'])) {
            $filtros['fecha_fin'] = $_GET['fecha_fin'];
        }
        if (!empty($_GET['tipo'])) {
            $filtros['tipo'] = $_GET['tipo'];
        }
        if (!empty($_GET['id_usuario'])) {
            $filtros['id_usuario'] = (int)$_GET['id_usuario'];
        }

        // ┌─ VALIDAR QUE AL MENOS UN FILTRO ESTÉ PRESENTE
        if (empty($filtros)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Debe proporcionar al menos un filtro'
            ]);
            exit();
        }

        // ┌─ LLAMAR AL MODELO PARA FILTRAR
        $eventos = new Eventos();
        $citas = $eventos->filtrarCitas($filtros);

        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cantidad' => count($citas),
            'filtros_aplicados' => $filtros,
            'citas' => $citas
        ]);
        exit();
    } catch (Exception $e) {
        error_log("Error en rfs34_consultarYFiltrarCitas -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al filtrar citas'
        ]);
        exit();
    }
}

/**
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ SUBTAREA 4: MOSTRAR DETALLES COMPLETOS DE LA CITA                         │
 * │ Retorna toda la información disponible de una cita específica              │
 * │ (propietario, mascota, servicio, veterinario, etc.)                       │
 * └───────────────────────────────────────────────────────────────────────────┘
 */
function rfs34_obtenerDetallesCitaCompleto()
{
    try {
        // ┌─ VALIDAR SESIÓN
        if (!isset($_SESSION['user']['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ]);
            exit();
        }

        // ┌─ OBTENER ID DE LA CITA
        $id_agendamiento = $_GET['id_agendamiento'] ?? null;

        if (!$id_agendamiento) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de cita no proporcionado'
            ]);
            exit();
        }

        // ┌─ OBTENER DETALLES COMPLETOS DE LA CITA
        $calendario = new Calendario();
        $detalles = $calendario->obtenerAgendamientoCompleto($id_agendamiento);

        if (!$detalles) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Cita no encontrada'
            ]);
            exit();
        }

        // ┌─ RETORNAR DETALLES COMPLETOS
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'cita' => $detalles
        ]);
        exit();
    } catch (Exception $e) {
        error_log("Error en rfs34_obtenerDetallesCitaCompleto -> " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener detalles de la cita'
        ]);
        exit();
    }
}
