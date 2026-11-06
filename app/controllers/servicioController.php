<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/Servicio.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $action = $_POST['action'] ?? '';
        if ($action === 'actualizar') {
            actualizarServicio();
        } elseif ($action === 'eliminar') {
            eliminarServicio();
        } elseif ($action === 'eliminar_horarios') {
            eliminarHorariosPorServicio();
        } else {
            registrarServicio();
        }
        break;
    case 'GET':
        // Aquí podrías manejar solicitudes GET si es necesario
        $action = $_GET['action'] ?? '';
        // Las eliminaciones solo se aceptan por POST con token CSRF
        if ($action === 'eliminar' || $action === 'eliminar_horarios') {
            acceso_denegar('405', 'eliminación por GET');
        } elseif ($action === 'obtener_horarios') {
            $id_servicio = $_GET['id'] ?? '';
            listaHorariosPorServicio($id_servicio);
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
    $horarios = $_POST['horarios'] ?? '';


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
        'id_veterinaria' => $id_veterinaria,
        'horarios' => $horarios
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

// FUNCION PARA LISTAR LOS SERVICIOS DE UNA VETERINARIA
function listaServiciosPorVeterinaria($id_veterinaria)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener los servicios
    return $servicioModel->obtenerServiciosPorVeterinaria($id_veterinaria);
}

// FUNCION PARA LISTAR LOS SERVICIOS DE UNA VETERINARIA
function listaServiciosPorVeterinariaActivos($id_veterinaria)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener los servicios
    return $servicioModel->obtenerServiciosPorVeterinariaActivos($id_veterinaria);
}

// FUNCION PARA LISTAR LOS SERVICIOS ACTIVOS DE UNA VETERINARIA
function listaServiciosActivosPorVeterinaria($id_veterinaria)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener los servicios activos
    return $servicioModel->obtenerServiciosActivosPorVeterinaria($id_veterinaria);
}

// FUNCION PARA OBTENER UN SERVICIO POR SU ID
function obtenerServicioPorId($id_servicio)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener el servicio por ID
    return $servicioModel->obtenerServicioPorId($id_servicio);
}

// FUNCION PARA ACTUALIZAR UN SERVICIO
function actualizarServicio()
{
    // Capturamos los datos enviados por el formulario
    $data = [
        'id_servicio' => $_POST['id_servicio'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'horarios' => $_POST['horarios'] ?? ''
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

// 
// Solo servicios de la clínica de la sesión, por POST con token CSRF (otra clínica → 404)
function eliminarServicio()
{
    acceso_exigir_accion_post([4]);
    $id_veterinaria = acceso_exigir_clinica();
    $id_servicio = acceso_entero_positivo($_POST['id'] ?? null);
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para eliminar el servicio
    $resultado = $id_servicio !== null && $servicioModel->eliminarServicio($id_servicio, $id_veterinaria);
    if (!$resultado) {
        acceso_denegar('404', 'servicio que no es de la clínica de la sesión');
    }

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Servicio Eliminado',
            'El servicio ha sido eliminado correctamente',
            BASE_URL . '/representante/listar-servicios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el servicio');
    }

    exit();
}

function listaHorariosPorServicio($id_servicio)
{
    try { // Creamos una instancia del modelo Servicio
        $servicioModel = new Servicio();

        // Llamamos al método para obtener los horarios del servicio
        $horarios = $servicioModel->obtenerHorariosPorServicio($id_servicio);

        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'horarios' => $horarios,
        ]);
    } catch (Exception $e) {
        // ┌─ RETORNAR ERROR    
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los horarios del servicio: ' . $e->getMessage()
        ]);
    }
}

// Solo horarios de servicios de la clínica de la sesión, por POST con token CSRF (otra → 404)
function eliminarHorariosPorServicio()
{
    acceso_exigir_accion_post([4]);
    $id_veterinaria = acceso_exigir_clinica();
    $id_horario = acceso_entero_positivo($_POST['id'] ?? null);
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para eliminar los horarios del servicio
    $resultado = $id_horario !== null && $servicioModel->eliminarHorariosPorServicio($id_horario, $id_veterinaria);
    if (!$resultado) {
        acceso_denegar('404', 'horario de un servicio que no es de la clínica de la sesión');
    }

    if ($resultado) {
        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Horarios eliminados correctamente'
        ]);
    } else {
        // ┌─ RETORNAR ERROR    
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudieron eliminar los horarios'
        ]);
    }
    exit();
}

function listarServiciosActivos($id_veterinaria)
{
    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Llamamos al método para obtener los servicios activos de la veterinaria
    return $servicioModel->obtenerServiciosActivos($id_veterinaria);
}
