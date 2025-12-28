<?php

session_start();

require_once __DIR__ . '/../helpers/alert_helpers.php';
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
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no especificada']);
                exit();
            }
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarAgendamiento($_GET['id']);
        } else if ($accion === 'cargar') {
            cargarEventos();
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
        // Verificar que el usuario esté autenticado
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

        $id_paciente = !empty($data['id_paciente']) ? (int)$data['id_paciente'] : null;
        $id_servicio = !empty($data['id_servicio']) ? (int)$data['id_servicio'] : null;
        $id_especialidad = !empty($data['id_especialidad']) ? (int)$data['id_especialidad'] : null;

        // Log para debugging
        error_log("DEBUG - Creando agendamiento: tipo=$tipo, fecha=$fecha_hora, id_usuario=$id_usuario");

        // Validamos que los campos requeridos no estén vacíos
        if (empty($tipo) || empty($fecha_hora)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tipo y fecha_hora son obligatorios']);
            exit();
        }

        if (empty($id_usuario)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado - id_usuario no encontrado en sesión']);
            exit();
        }

        // Convertir formato ISO 8601 a formato MySQL
        if ($fecha_hora) {
            $fecha_hora = date('Y-m-d H:i:s', strtotime($fecha_hora));
        }
        if ($fecha_hora_fin) {
            $fecha_hora_fin = date('Y-m-d H:i:s', strtotime($fecha_hora_fin));
        }

        // Creamos el objeto del modelo
        $objEventos = new Eventos();

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
            'id_especialidad' => $id_especialidad,
        ];

        // Registramos el agendamiento
        $id_generado = $objEventos->createAgendamiento($dataToInsert);

        if ($id_generado) {
            header('Content-Type: application/json');
            http_response_code(201);
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
        'fecha_hora' => $fecha_hora,
        'fecha_hora_fin' => $fecha_hora_fin,
        'estado' => $estado,
        'id_usuario' => $id_usuario,
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
