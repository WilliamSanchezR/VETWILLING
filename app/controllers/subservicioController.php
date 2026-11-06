<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/Subservicio.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $action = $_POST['action'] ?? '';
        if ($action === 'actualizar') {
            actualizarSubservicio();
        } elseif ($action === 'eliminar') {
            eliminarSubservicio();
        } else {
            registrarSubservicio();
        }
        break;
    case 'GET':
        // Aquí podrías manejar solicitudes GET si es necesario
        $action = $_GET['action'] ?? '';
        if ($action === 'eliminar') {
            acceso_denegar('405', 'eliminación por GET');
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
// FUNCION PARA REGISTRAR UN NUEVO SUBSERVICIO
function registrarSubservicio()
{
    // Capturamos los datos enviados por el formulario
    $nombre = $_POST['nombre'] ?? '';
    $costo = $_POST['costo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_servicio = $_POST['servicio'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($nombre) || (empty($costo) && !is_numeric($costo)) || empty($id_servicio)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    // Creamos una instancia del modelo Subservicio
    $subservicioModel = new Subservicio();

    // Preparamos los datos para registrar el subservicio
    $data = [
        'nombre' => $nombre,
        'costo' => $costo,
        'descripcion' => $descripcion,
        'id_servicio' => $id_servicio
    ];

    // Llamamos al método para registrar el subservicio
    $resultado = $subservicioModel->crearSubservicio($data);
    if ($resultado) {
        mostrarSweetAlert('success', 'Subservicio Registrado', 'El subservicio ha sido registrado exitosamente.', BASE_URL . '/representante/listar-subservicios');
    } else {
        mostrarSweetAlert('error', 'Error al Registrar', 'Hubo un problema al registrar el servicio. Intente nuevamente.');
    }
    exit();
}

function listaSubserviciosPorVeterinaria($id_veterinaria)
{
    // Creamos una instancia del modelo Subservicio
    $subservicioModel = new Subservicio();

    // Llamamos al método para obtener los servicios
    return $subservicioModel->obtenerSubserviciosPorVeterinaria($id_veterinaria);
}

function obtenerSubservicioPorId($id_subservicio)
{
    // Creamos una instancia del modelo Subservicio
    $subservicioModel = new Subservicio();

    // Llamamos al método para obtener el servicio por ID
    return $subservicioModel->obtenerSubservicioPorId($id_subservicio);
}

function actualizarSubservicio()
{
    // Capturamos los datos enviados por el formulario
    $data = [
        'id_subservicio' => $_POST['id_subservicio'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'id_servicio' => $_POST['servicio'] ?? '',
        'costo' => $_POST['costo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? ''
    ];

    // Validamos que los campos no esten vacios
    if (empty($data['id_subservicio']) || empty($data['nombre']) || (empty($data['costo']) && !is_numeric($data['costo'])) || empty($data['estado'])) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    // Creamos una instancia del modelo Subservicio
    $subservicioModel = new Subservicio();

    // Llamamos al método para actualizar el servicio

    $resultado = $subservicioModel->actualizarSubservicio($data);
    if ($resultado) {
        mostrarSweetAlert('success', 'Subservicio Actualizado', 'El subservicio ha sido actualizado exitosamente.', BASE_URL . '/representante/listar-subservicios');
    } else {
        mostrarSweetAlert('error', 'Error al Actualizar', 'Hubo un problema al actualizar el subservicio. Intente nuevamente.');
    }
    exit();
}

// Solo subservicios de servicios de la clínica de la sesión, por POST con token CSRF (otra → 404)
function eliminarSubservicio()
{
    acceso_exigir_accion_post([4]);
    $id_veterinaria = acceso_exigir_clinica();
    $id_subservicio = acceso_entero_positivo($_POST['id'] ?? null);
    // Creamos una instancia del modelo Subservicio
    $subservicioModel = new Subservicio();

    // Llamamos al método para eliminar el servicio
    $resultado = $id_subservicio !== null && $subservicioModel->eliminarSubservicio($id_subservicio, $id_veterinaria);
    if (!$resultado) {
        acceso_denegar('404', 'subservicio que no es de la clínica de la sesión');
    }

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Subservicio Eliminado',
            'El subservicio ha sido eliminado correctamente',
            BASE_URL . '/representante/listar-subservicios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el subservicio');
    }

    exit();
}
