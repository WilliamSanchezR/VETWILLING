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
            // actualizarVeterinaria();
        } else {
            registrarVeterinaria();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        break;

    //Estas lineas se usarian si se trabajara con apis resful
    // case 'PUT':
    //     actualizarUsuario();
    //     break;

    // case 'DELETE':
    //     eliminarUsuario();
    //     break;

    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

//FUNCIONES CRUD
// FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
function registrarVeterinaria()
{
    $nit = $_POST['nit'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (
        empty($nit) || empty($nombre) || empty($direccion) || empty($ciudad) || empty($telefono) ||
        empty($email)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }



    $objVeterinaria = new Veterinaria();

    $data = [
        'nit' => $nit,
        'nombre' => $nombre,
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'telefono' => $telefono,
        'email' => $email,
    ];

    $resultado = $objVeterinaria->registrar($data);

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


function listarVeterinariasRegistradas()
{
    $veterinariaModel = new Veterinaria();
    $veterinarias = $veterinariaModel->listarVeterinariasRegistradas();

    return $veterinarias;
}


