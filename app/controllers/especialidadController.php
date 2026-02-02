<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/especialidad.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarEspecialidad();
        } else {
            registrarEspecialidad();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        if ($accion === 'eliminar') {
            eliminarEspecialidad($_GET['id_especialidad'], $_GET['id_veterinaria']);
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


// FUNCION PARA REGISTRAR UNA NUEVA ESPECIALIDAD
function registrarEspecialidad()
{
    // Capturamos los datos enviados por el formulario
    $nombre = $_POST['nombre'] ?? '';
    $id_veterinaria = $_POST['id_veterinaria'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($nombre) || empty($id_veterinaria)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Preparamos los datos para registrar la especialidad
    $data = [
        'nombre' => $nombre,
        'id_veterinaria' => $id_veterinaria 
    ];

    // Llamamos a la función registrar del modelo
    $resultado = $especialidadModel->registrarEspecialidad($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Especialidad registrada', 'La especialidad ha sido registrada exitosamente.', '/vetwilling/representante/listar-especialidades');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'Hubo un problema al registrar la especialidad.');
    }
}

// FUNCION PARA LISTAR LAS ESPECIALIDADES REGISTRADAS
function listarEspecialidadesRegistradas($id_veterinaria)
{
    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Llamamos a la función listar del modelo
    return $especialidadModel->listarEspecialidades($id_veterinaria);
}

function actualizarEspecialidad() {
    // Capturamos los datos enviados por el formulario
    $id_especialidad = $_POST['id_especialidad'] ?? '';
    $nombre_especialidad = $_POST['nombre_especialidad'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($id_especialidad) || empty($nombre_especialidad)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Preparamos los datos para actualizar la especialidad
    $data = [
        'id_especialidad' => $id_especialidad,
        'nombre' => $nombre_especialidad
    ];

    // Llamamos a la función actualizar del modelo
    $resultado = $especialidadModel->actualizarEspecialidad($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Especialidad actualizada', 'La especialidad ha sido actualizada exitosamente.', '/vetwilling/representante/listar-especialidades');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'Hubo un problema al actualizar la especialidad.');
    }
}

// FUNCION PARA ELIMINAR UNA ESPECIALIDAD
function eliminarEspecialidad($id_especialidad,$id_veterinaria)
{
    // Validamos que el ID no esté vacío
    if (empty($id_especialidad)) {
        mostrarSweetAlert('error', 'ID vacío', 'El ID de la especialidad no puede estar vacío.');
        exit();
    }

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Llamamos a la función eliminar del modelo
    $resultado = $especialidadModel->eliminarEspecialidad($id_especialidad,$id_veterinaria);

    if ($resultado) {
        mostrarSweetAlert('success', 'Especialidad eliminada', 'La especialidad ha sido eliminada exitosamente.');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'Hubo un problema al eliminar la especialidad.');
    }
}
