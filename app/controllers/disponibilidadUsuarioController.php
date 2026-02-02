<?php

session_start();

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/DisponibilidadUsuario.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        // Esta variable captura la accion de listar
        $accion = $_GET['action'] ?? '';
        if ($accion === 'horarios') {
            obtenerDisponibilidadesJson();
        } else if ($accion === 'eliminar') {
            eliminarAgenda($_GET['id']);
        }
        break;
    case 'POST':
        $accion = $_POST['action'] ?? '';
        if ($accion === 'editar') {
            editarDisponibilidadAgenda();
        } else {
            agregarDisponibilidadAgenda();
        }
        break;

    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

// =========================================
//  FUNCIONES CRUD
// =========================================

// Función para listar los usuarios/profesionales de una veterinaria
function listaUsuarios($id_veterinaria)
{
    // Creamos una instancia del modelo DisponibilidadUsuario
    $disponibilidadUsuarioModel = new DisponibilidadUsuario();

    // Llamamos al método listaProfesionales del modelo
    $profesionales = $disponibilidadUsuarioModel->listaProfesionales($id_veterinaria);

    return $profesionales;
}

// Función para agregar una nueva disponibilidad a la agenda
function agregarDisponibilidadAgenda()
{
    // Creamos una instancia del modelo DisponibilidadUsuario
    $disponibilidadUsuarioModel = new DisponibilidadUsuario();

    $data = [
        'id_usuario' => $_POST['id_usuario'],
        'id_especialidad' => $_POST['id_especialidad'],
        'id_veterinaria' => $_POST['id_veterinaria'],
        'dia_semana' => $_POST['dia_semana'],
        'hora_inicio' => $_POST['hora_inicio'],
        'hora_fin' => $_POST['hora_fin'],
        'hora_inicio_2' => $_POST['hora_inicio_seccond'],
        'hora_fin_2' => $_POST['hora_fin_seccond'],
        'duracion_minutos' => $_POST['duracion_minutos']
    ];

    if (empty($data['id_usuario']) || empty($data['id_veterinaria']) || empty($data['dia_semana']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || empty($data['duracion_minutos']) || (empty($data['hora_inicio_2']) && !empty($data['hora_fin_2'])) || (!empty($data['hora_inicio_2']) && empty($data['hora_fin_2']))) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Llamamos al método agregarDisponibilidadAgenda del modelo
    $resultado = $disponibilidadUsuarioModel->agregarDisponibilidadAgenda($data);

    $redirect = $_POST['redirect'] ?? null;

    if ($resultado) {
        mostrarSweetAlert('success', 'Disponibilidad registrada', 'La disponibilidad ha sido registrada exitosamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'Hubo un problema al registrar la disponibilidad.', $redirect);
    }
}


function obtenerDisponibilidadesPorUsuario($id_usuario, $id_veterinaria)
{
    // Creamos una instancia del modelo DisponibilidadUsuario
    $disponibilidadUsuarioModel = new DisponibilidadUsuario();

    // Llamamos al método obtenerDisponibilidadesPorUsuario del modelo
    $disponibilidades = $disponibilidadUsuarioModel->obtenerDisponibilidadesPorUsuario($id_usuario, $id_veterinaria);

    return $disponibilidades;
}

// Función para eliminar una disponibilidad de la agenda
function eliminarAgenda($id_disponibilidad)
{
    // Creamos una instancia del modelo DisponibilidadUsuario
    $disponibilidadUsuarioModel = new DisponibilidadUsuario();

    // Llamamos al método eliminarDisponibilidad del modelo
    $resultado = $disponibilidadUsuarioModel->eliminarAgenda($id_disponibilidad);

    $redirect = $_GET['redirect'] ?? null;

    if ($resultado) {
        mostrarSweetAlert('success', 'Disponibilidad eliminada', 'La disponibilidad ha sido eliminada exitosamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'Hubo un problema al eliminar la disponibilidad.', $redirect);
    }
}

// Función para editar una disponibilidad de la agenda
function editarDisponibilidadAgenda()
{
    // Creamos una instancia del modelo DisponibilidadUsuario
    $disponibilidadUsuarioModel = new DisponibilidadUsuario();

    $data = [
        'id_disponibilidad' => $_POST['id_disponibilidad'],
        'id_usuario' => $_POST['id_usuario'],
        'id_especialidad' => $_POST['id_especialidad'],
        'id_veterinaria' => $_POST['id_veterinaria'],
        'dia_semana' => $_POST['dia_semana'],
        'hora_inicio' => $_POST['hora_inicio'],
        'hora_fin' => $_POST['hora_fin'],
        'duracion_minutos' => $_POST['duracion_minutos']
    ];

    if (empty($data['id_disponibilidad']) || empty($data['id_usuario']) || empty($data['id_veterinaria']) || empty($data['dia_semana']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || empty($data['duracion_minutos'])) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Llamamos al método editarDisponibilidadAgenda del modelo
    $resultado = $disponibilidadUsuarioModel->editarDisponibilidadAgenda($data);

    $redirect = $_POST['redirect'] ?? null;

    if ($resultado) {
        mostrarSweetAlert('success', 'Disponibilidad actualizada', 'La disponibilidad ha sido actualizada exitosamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'Hubo un problema al actualizar la disponibilidad.', $redirect);
    }
}

// Función para obtener disponibilidades en formato JSON (FullCalendar)
function obtenerDisponibilidadesJson()
{
    $id_usuario = $_GET['id_usuario'] ?? ($_SESSION['user']['id_usuario'] ?? null);
    $id_veterinaria = $_GET['id_veterinaria'] ?? ($_SESSION['user']['id_veterinaria'] ?? null);

    if (empty($id_veterinaria) && !empty($id_usuario)) {
        $disponibilidadUsuarioModel = new DisponibilidadUsuario();
        $id_veterinaria = $disponibilidadUsuarioModel->obtenerVeterinariaPorUsuario($id_usuario);
    }

    if (empty($id_usuario) || empty($id_veterinaria)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros de usuario o veterinaria']);
        exit();
    }

    $disponibilidadUsuarioModel = new DisponibilidadUsuario();
    $disponibilidades = $disponibilidadUsuarioModel->obtenerDisponibilidadesPorUsuario($id_usuario, $id_veterinaria);

    $businessHours = [];
    foreach ($disponibilidades as $disponibilidad) {
        $dia = (int)$disponibilidad['dia_semana'];
        // FullCalendar usa 0=Domingo ... 6=Sábado
        $diaFullCalendar = ($dia === 7) ? 0 : $dia;

        $businessHours[] = [
            'daysOfWeek' => [$diaFullCalendar],
            'startTime' => substr($disponibilidad['hora_inicio'], 0, 5),
            'endTime' => substr($disponibilidad['hora_fin'], 0, 5)
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($businessHours);
    exit();
}
