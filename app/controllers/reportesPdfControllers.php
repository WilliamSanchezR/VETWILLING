<?php

require_once BASE_PATH . '/app/helpers/pdf_helpers.php';
require_once BASE_PATH . '/app/controllers/veterinarioController.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
require_once BASE_PATH . '/app/controllers/mascotasController.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {


    case 'GET':
        // Esta variable captura la accion de mostrar
        $accion = $_GET['action'] ?? '';

        if ($accion === 'reporteVeterinariasPDF') {
            reporteVeterinariasPDF();
        } else if ($accion === 'reporteVeterinario') {
            reporteVeterinario();
        } else if ($accion === 'reporteMascotas') {
            reporteMascotas();
        }


        break;


    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

function reporteVeterinario()
{

    //cargar la vista y obtenerla como HTML 
    ob_start();
    //aignamos los datos de la funcion en el controlador enlazado
    // a una variableque podamos manipular en la vista del pdf
    $veterinarios = mostrarVeterinarios();

    //archivo que tiene la interfaz diseniada en html 
    require BASE_PATH . '/app/views/pdf/veterinarios_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_veterinarios.pdf', false);
}




// function reportePropietariosPDF()
// {
//     // cargar la vista y obtenerla como HTML

//     ob_start();
//     // asignamos los datos de la funcion en el controlador enlazado a una varible que podamos manipular en la vista del pdf
//     $veterinarios = mostrarPropietarios();

//     // archivo que tiene la interfaz diseñada en HTML

//     require BASE_PATH . '/app/views/pdf/propietarios_pdf.php';
//     $html = ob_get_clean();

//     generarPDF($html, 'reporte_veterinarios.pdf', false);
// }

// funcion para reporte de veterinarias
function reporteVeterinariasPDF()
{
    // cargar la vista y obtenerla como HTML

    ob_start();
    // asignamos los datos de la funcion en el controlador enlazado a una varible que podamos manipular en la vista del pdf
    $veterinarias = listarVeterinariasRegistradas();

    // archivo que tiene la interfaz diseñada en HTML

    require BASE_PATH . '/app/views/pdf/veterinarias_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_veterinarias.pdf', false);
}

function reporteMascotas()
{

    //cargar la vista y obtenerla como HTML 
    ob_start();
    //aignamos los datos de la funcion en el controlador enlazado
    // a una variableque podamos manipular en la vista del pdf
    $mascotas = listarMascotas();

    //archivo que tiene la interfaz diseniada en html 
    require BASE_PATH . '/app/views/pdf/mascotas_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_mascotas.pdf', false);
}
