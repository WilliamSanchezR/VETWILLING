<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/veterinaria.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarVeterinaria();
        } else {
            registrarVeterinaria();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
         if ($accion === 'eliminar') {
            eliminarVeterinaria($_GET['id']);
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

// FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
function registrarVeterinaria()
{
    // Capturamos los datos enviados por el formulario
    $nit = $_POST['nit'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validamos que los campos no esten vacios
    if (
        empty($nit) || empty($nombre) || empty($direccion) || empty($ciudad) || empty($telefono) ||
        empty($email)
    ) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }
    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();

    // Preparamos los datos para el registro
    $data = [
        'nit' => $nit,
        'nombre' => $nombre,
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'telefono' => $telefono,
        'email' => $email,
    ];
    // Llamamos a la funcion registrar del modelo Veterinaria
    $resultado = $objVeterinaria->registrar($data);

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria registrada',
            'La veterinaria ha sido creada correctamente',
            '/vetwilling/admin/registro-veterinaria'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar la veterinaria');
    }

    exit();
}

// FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS
function listarVeterinariasRegistradas()
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinarias = $veterinariaModel->listar();
    return $veterinarias;
}

// fUNCION PARA CONSULTAR UNA VETERINARIA POR ID
function consultarVeterinariasRegistradas($id)
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinaria = $veterinariaModel->consultarVeterinariasRegistradas($id);
    return $veterinaria;
}

// FUNCION PARA ACTUALIZAR LOS DATOS DE LA VETERINARIA
function actualizarVeterinaria()
{
    // Capturamos los datos enviados por el formulario
    $nit = $_POST['nit'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';


    // Validamos que los campos no esten vacios
    if (
        empty($nit) || empty($nombre) || empty($direccion) || empty($ciudad) || empty($telefono) ||
        empty($email) || empty($estado)
    ) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }
    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();
    // Preparamos los datos para la actualización
    $data = [
        'nit' => $nit,
        'nombre' => $nombre,
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'telefono' => $telefono,
        'email' => $email,
        'estado' => $estado,
        'id_veterinaria' => $_POST['id_veterinaria'] ?? ''
    ];
    // Llamamos a la funcion actualizar del modelo Veterinaria
    $resultado = $objVeterinaria->actualizar($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria actualizada',
            'Los datos han sido actualizados correctamente',
            '/vetwilling/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la veterinaria');
    }

    exit();
}

// FUNCION PARA ELIMINAR UNA VETERINARIA
function eliminarVeterinaria($id)
{
    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();
    // Llamamos a la funcion eliminar del modelo Veterinaria
    $resultado = $objVeterinaria->eliminar($id);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria eliminada',
            'La veterinaria ha sido eliminada correctamente',
            '/vetwilling/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la veterinaria');
    }

    exit();
}
