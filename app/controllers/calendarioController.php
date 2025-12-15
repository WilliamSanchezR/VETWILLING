<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Eventos.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'crear') {
            crearAgendamiento();
        } else if ($accion === 'actualizar') {
            actualizarAgendamiento();
        } else if ($accion === 'eliminar') {
            eliminarAgendamiento($_POST['id'] ?? null);
        } else {
            // Verificar si es una petición AJAX JSON
            $json_data = file_get_contents("php://input");
            if (!empty($json_data) && is_array(json_decode($json_data, true))) {
                $request_uri = $_SERVER['REQUEST_URI'];
                if (strpos($request_uri, '/calendario/storeEvent') !== false) {
                    crearAgendamientoAjax();
                } elseif (strpos($request_uri, '/calendario/updateEvent') !== false) {
                    actualizarAgendamientoAjax();
                } else {
                    crearAgendamientoAjax();
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos válidos']);
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
        // Obtener datos enviados en JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            exit();
        }

        $tipo = $data['tipo'] ?? '';
        $fecha_hora = $data['fecha_hora'] ?? '';
        $fecha_hora_fin = $data['fecha_hora_fin'] ?? null;
        $estado = $data['estado'] ?? 'Pendiente';
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        $id_paciente = $data['id_paciente'] ?? null;
        $id_servicio = $data['id_servicio'] ?? null;
        $id_especialidad = $data['id_especialidad'] ?? null;

        // Validamos que los campos requeridos no estén vacíos
        if (empty($tipo) || empty($fecha_hora)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Campos requeridos vacíos: tipo y fecha_hora son obligatorios']);
            exit();
        }

        // Creamos el objeto del modelo
        $objEventos = new Eventos();

        // Preparamos los datos para el registro
        $dataToInsert = [
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
    $id_usuario = $_SESSION['id_usuario'] ?? null;
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
        mostrarSweetAlert('error', 'Error', 'ID no proporcionado');
        exit();
    }

    // Creamos el objeto del modelo
    $objEventos = new Eventos();

    // Eliminamos el agendamiento
    $resultado = $objEventos->eliminar($id);

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Agendamiento eliminado',
            'El agendamiento ha sido eliminado correctamente',
            '/vetwilling/admin/calendario'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el agendamiento');
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
            'backgroundColor' => getColorByEstado($agendamiento['estado']),
            'allDay' => false
        ];
    }

    // Devolvemos la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($calendar_events);
    exit();
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
