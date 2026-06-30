<?php

require_once __DIR__ . '/../../vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generarPDF($html, $filename = "documento.pdf", $download = false, $orientation = 'portrait')
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // permite imágenes externas



    if(ob_get_length()){
        ob_clean();
    }

    $dompdf = new Dompdf($options);

    // Cargar el HTML recibido
    $dompdf->loadHtml($html);

    // Tamaño y orientación
    $dompdf->setPaper('A4', $orientation);

    // Renderizar
    $dompdf->render();

    // Descargar o mostrar
    $dompdf->stream($filename, [
        "Attachment" => $download ? 1 : 0
    ]);
}