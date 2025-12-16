<?php

date_default_timezone_set('America/Bogota');

require_once BASE_PATH . '/app/helpers/pdf_helpers.php';
require_once BASE_PATH . '/app/controllers/veterinarioController.php';
require_once BASE_PATH . '/app/controllers/mascotasController.php';

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
    reporteVeterinario();

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
    reporteMascotas();