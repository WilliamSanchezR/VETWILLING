<?php

//Importamos las dependencias
require_once __DIR__ . '/../models/rol.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {


    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        // Se valida si la accion es eliminar

        listarRolAdmin();


        break;


    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

//FUNCIONES CRUD

function listarRolAdmin()
{
    $resultado = new Rol();
    $roles = $resultado->listarRolAdmin();

    return $roles;
}

// Funcion para listar los roles disponibles para el representante
function listarRolRepresentante()
{
    $resultado = new Rol();
    $roles = $resultado->listarRolRepresentante();

    return $roles;
}
