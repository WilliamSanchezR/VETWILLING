<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/Especialidad.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarEspecialidad();
        } else if ($accion === 'eliminar') {
            eliminarEspecialidad();
        } else {
            registrarEspecialidad();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        if ($accion === 'eliminar') {
            acceso_denegar('405', 'eliminación por GET');
        } else if ($accion === 'lista') {
            listarEpsServicios($_GET['servicio']);
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
    $servicio = $_POST['servicio'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($nombre) || empty($id_veterinaria) || empty($servicio)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Preparamos los datos para registrar la especialidad
    $data = [
        'nombre' => $nombre,
        'servicio' => $servicio,
        'id_veterinaria' => $id_veterinaria
    ];

    // Llamamos a la función registrar del modelo
    $resultado = $especialidadModel->registrarEspecialidad($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Especialidad registrada', 'La especialidad ha sido registrada exitosamente.', BASE_URL . '/representante/listar-especialidades');
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

function actualizarEspecialidad()
{
    // Capturamos los datos enviados por el formulario
    $id_especialidad = $_POST['id_especialidad'] ?? '';
    $nombre_especialidad = $_POST['nombre_especialidad'] ?? '';
    $servicio_especialidad = $_POST['servicio_especialidad'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($id_especialidad) || empty($nombre_especialidad) || empty($servicio_especialidad)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Preparamos los datos para actualizar la especialidad
    $data = [
        'id_especialidad' => $id_especialidad,
        'nombre' => $nombre_especialidad,
        'servicio' => $servicio_especialidad
    ];

    // Llamamos a la función actualizar del modelo
    $resultado = $especialidadModel->actualizarEspecialidad($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Especialidad actualizada', 'La especialidad ha sido actualizada exitosamente.', BASE_URL . '/representante/listar-especialidades');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'Hubo un problema al actualizar la especialidad.');
    }
}

// FUNCION PARA ELIMINAR UNA ESPECIALIDAD
// Quita la especialidad de la clínica de la sesión: POST con token CSRF (otra clínica → 404)
function eliminarEspecialidad()
{
    acceso_exigir_accion_post([4]);
    $id_veterinaria = acceso_exigir_clinica();
    $id_especialidad = acceso_entero_positivo($_POST['id_especialidad'] ?? null);

    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();

    // Llamamos a la función eliminar del modelo
    $resultado = $id_especialidad !== null && $especialidadModel->eliminarEspecialidad($id_especialidad, $id_veterinaria);
    if (!$resultado) {
        acceso_denegar('404', 'especialidad que no es de la clínica de la sesión');
    }

    mostrarSweetAlert('success', 'Especialidad eliminada', 'La especialidad ha sido eliminada exitosamente.', BASE_URL . '/representante/listar-especialidades');
    exit();
}

function listarEpsServicios($servicio)
{
    // Creamos una instancia del modelo Especialidad
    $especialidadModel = new Especialidad();
    // Recibir el parámetro
    $id_servicio = $_GET['servicio'] ?? '';

    // Convertir string "1,2,3" a array [1,2,3]
    if (!empty($id_servicio)) {
        $id_servicio = explode(',', $id_servicio);
        $id_servicio = array_map('intval', $id_servicio); // Convertir a enteros
    }

    // Llamamos a la función obtenerEspecialidadesPorServicio del modelo
    $especilidades = $especialidadModel->obtenerEspecialidadesPorServicio($id_servicio);

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'especialidades' => $especilidades
    ]);
    exit();
}
