<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Servicio.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        registrarServicio();
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
    $costo = $_POST['costo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_veterinaria = $_POST['id_veterinaria'] ?? '';

    // Validamos que los campos no esten vacios
    if (empty($nombre) || empty($costo) || empty($id_veterinaria)) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    // Creamos una instancia del modelo Servicio
    $servicioModel = new Servicio();

    // Preparamos los datos para registrar el servicio
    $data = [
        'nombre' => $nombre,
        'costo' => $costo,
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
