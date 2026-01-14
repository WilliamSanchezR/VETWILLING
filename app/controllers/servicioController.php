<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Servicio.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $action = $_POST['action'] ?? '';
        if ($action === 'actualizar') {
            actualizarServicio();
        } else {
            registrarServicio();
        }
        break;
    case 'GET':
        // Aquí podrías manejar solicitudes GET si es necesario
        $action = $_GET['action'] ?? '';
        if ($action === 'eliminar') {
            $id_servicio = $_GET['id'] ?? '';
            eliminarServicio($id_servicio);
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
// FUNCION PARA REGISTRAR UN NUEVO SERVICIO
function registrarServicio()
{
    // Capturamos los datos enviados por el formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_veterinaria = $_POST['id_veterinaria'] ?? '';


    // Validamos que los campos no esten vacios
    if (empty($nombre) ||  empty($id_veterinaria)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Preparamos los datos para registrar el servicio
    $data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'id_veterinaria' => $id_veterinaria
    ];

    // Llamamos al método para registrar el servicio
    $resultado = $servicioModel->crearServicio($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Servicio Registrado', 'El servicio ha sido registrado exitosamente.', BASE_URL . '/representante/listar-servicios');
    } else {
        mostrarSweetAlert('error', 'Error al Registrar', 'Hubo un problema al registrar el servicio. Intente nuevamente.');
    }
    exit();
}

function listaServiciosPorVeterinaria($id_veterinaria)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener los servicios
    return $servicioModel->obtenerServiciosPorVeterinaria($id_veterinaria);
}

function obtenerServicioPorId($id_servicio)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener el servicio por ID
    return $servicioModel->obtenerServicioPorId($id_servicio);
}

function actualizarServicio()
{
    // Capturamos los datos enviados por el formulario
    $data = [
        'id_servicio' => $_POST['id_servicio'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? ''
    ];

    // Validamos que los campos no esten vacios
    if (empty($data['id_servicio']) || empty($data['nombre'])  || empty($data['estado'])) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para actualizar el servicio

    $resultado = $servicioModel->actualizarServicio($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Servicio Actualizado', 'El servicio ha sido actualizado exitosamente.', BASE_URL . '/representante/listar-servicios');
    } else {
        mostrarSweetAlert('error', 'Error al Actualizar', 'Hubo un problema al actualizar el servicio. Intente nuevamente.');
    }
    exit();
}

function eliminarServicio($id_servicio)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para eliminar el servicio
    $resultado = $servicioModel->eliminarServicio($id_servicio);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Servicio Eliminado',
            'El servicio ha sido eliminado correctamente',
            '/vetwilling/representante/listar-servicios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el servicio');
    }

    exit();
}
