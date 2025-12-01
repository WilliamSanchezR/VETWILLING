<?php

require __DIR__ . '/../helpers/pdf_helpers.php';
require __DIR__ . '/veterinarioController.php';



// Esta funcion se encarga de validar el tipo de reporte y ejecutar la funcion corespondiente 
function reportesPdfControllers()
{

    // capturamos el tipo de reporte enivado desde la vista

    $tipo = $_GET['tipo'];

    // Segun el tipo de reporte ejecutamos X funcion

    switch ($tipo) {
        case 'veterinarios':
            reporteVeterinariosPDF();
            break;

        // case 'propietarios':
        //     reportePropietariosPDF();
        //     break;

        default:
            exit();
            break;
    }
}

function reporteVeterinariosPDF()
{
    // cargar la vista y obtenerla como HTML

    ob_start();
    // asignamos los datos de la funcion en el controlador enlazado a una varible que podamos manipular en la vista del pdf
    $veterinarios = mostrarVeterinarios();

    // archivo que tiene la interfaz diseñada en HTML

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
