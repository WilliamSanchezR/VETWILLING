<?php

require __DIR__ . '/app/helpers/pdf_helpers.php';
require __DIR__ . '/app/controllers/veterinarioControllers.php';

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

reporteVeterinariosPDF();
