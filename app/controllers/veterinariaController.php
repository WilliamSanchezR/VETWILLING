<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/veterinaria.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion']?? '';
        if ($accion === 'actualizar') {
            // actualizarUsuario();
        } else {
            listarVeterinariasRegistradas();
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
function listarVeterinariasRegistradas()
{
    $veterinariaModel = new Veterinaria();
    $veterinarias = $veterinariaModel->listarVeterinariasRegistradas();

    return $veterinarias;

  
}


   






