<?php

require __DIR__ . '/../helpers/pdf_helpers.php';
require __DIR__ . '/veterinarioController.php';
// require __DIR__ . '/app/controllers/pacienteControllers.php';

// ESTA FUNCION SE ENCARGA DE VALIDAR EL TIPO DE REPORTE Y EJECUTAR LA FUNCION CORRESPONDIENTE 
function reportesPdfControllers(){

    //CAPTURAMOS EL TIPO DE REPORTE ENVIADO DESDE LA VISTA 
    $tipo = $_GET['tipo'];
    // SEGUN EL TIPO DE REPORTE EJECUTAMOS X FUNCION

    switch ($tipo) {
        case 'veterinarios':
          reporteVeterinariosPDF();
            break;

        // case 'paciente':
        //   reportePacientesPDF();
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


// function reportePacientesPDF()
// {

    // cargar la vista y obtenerla como HTML

    // ob_start();
    // asignamos los datos de la funcion en el controlador enlazado a una varible que podamos manipular en la vista del pdf
    // $pacientes = mostrarPacientes();

    // archivo que tiene la interfaz diseñada en HTML

//     require BASE_PATH . '/app/views/pdf/veterinarios_pdf.php';
//     $html = ob_get_clean();

//     generarPDF($html, 'reporte_veterinarios.pdf', false);
// }



