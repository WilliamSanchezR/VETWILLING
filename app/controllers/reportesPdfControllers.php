<?php

require_once BASE_PATH . '/app/helpers/pdf_helpers.php';
require_once BASE_PATH . '/app/controllers/veterinarioController.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
require_once BASE_PATH . '/app/controllers/mascotasController.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
        } else if ($accion === 'historialClinico') {
            historialClinicoPdf();
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


function historialClinicoPdf()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Verificar autenticación
    if (!isset($_SESSION['user']['id_usuario'])) {
        http_response_code(401);
        exit('Debes iniciar sesión para ver este documento.');
    }

    require_once BASE_PATH . '/app/models/CitasCliente.php';
    $modeloCitas = new CitasCliente();

    // 2. Resolver id_propietario (mismo patrón que citasClienteController.php)
    $id_propietario = $_SESSION['user']['id_propietario'] ?? null;
    if (!$id_propietario) {
        $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario($_SESSION['user']['id_usuario']);
    }

    if (!$id_propietario) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    // 3. Validar paciente
    $id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
    if ($id_paciente <= 0) {
        http_response_code(400);
        exit('Paciente inválido.');
    }

    // 4. Verificar que el paciente pertenezca a este propietario
    $paciente = $modeloCitas->obtenerInfoPacienteConPropiedad($id_propietario, $id_paciente);
    if (!$paciente) {
        http_response_code(403);
        exit('No tienes acceso a este historial.');
    }

    /**
     * IMPORTANTE: aquí debe ir la misma validación de acceso temporal al
     * historial clínico que usas en historialMascota.php (AccesoHistorial::estadoAcceso).
     * Si el acceso no está aprobado, no se debe generar el PDF.
     *
     * require_once BASE_PATH . '/app/models/AccesoHistorial.php';
     * $accesoModel = new AccesoHistorial();
     * $accesoInfo  = $accesoModel->estadoAcceso($id_paciente, $id_propietario);
     * if (($accesoInfo['estado'] ?? '') !== 'aprobado') {
     *     http_response_code(403);
     *     exit('No tienes acceso aprobado al historial clínico de este paciente.');
     * }
     */

    // 5. Obtener los datos (mismas fuentes que usa la vista vía AJAX)
    $vacunas           = $modeloCitas->obtenerVacunasPorPaciente($id_propietario, $id_paciente);
    $historial_clinico = $modeloCitas->obtenerHistorialClinicoPorPaciente($id_propietario, $id_paciente);
    $tratamientos      = $modeloCitas->obtenerTratamientosPorPaciente($id_propietario, $id_paciente);
    $citas             = $modeloCitas->obtenerHistorialPorPaciente($id_propietario, $id_paciente, 50);

    $fechaGeneracion = date('d/m/Y H:i');

    // 6. Resolver ruta de la foto en disco (Dompdf puede leer archivos locales directo)
    $nombreImg = $paciente['img_mascota'] ?? 'default.png';
    $rutaFoto  = BASE_PATH . '/public/uploads/mascotas/' . $nombreImg;
    if (!is_file($rutaFoto)) {
        $rutaFoto = BASE_PATH . '/public/assets/webSite/img/default-pet.png';
    }

    // 7. Cargar la vista y obtenerla como HTML
    ob_start();
    require BASE_PATH . '/app/views/pdf/historial_clinico_pdf.php';
    $html = ob_get_clean();

    $nombreArchivo = 'historial_' . preg_replace('/\s+/', '_', $paciente['nombre'] ?? 'mascota') . '.pdf';

    // 8. Generar el PDF directamente con Dompdf (no usamos generarPDF() aquí
    //    porque necesitamos el objeto $dompdf después del render para dibujar
    //    "Página X / Y" con $canvas->page_text() — Dompdf no soporta de forma
    //    confiable counter(pages) en CSS normal, siempre marca el total como 0).
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Dibujar el número de página real (con el total correcto) en cada página
    $canvas = $dompdf->getCanvas();
    $canvas->page_text(
        $canvas->get_width() - 100,
        $canvas->get_height() - 42,
        "Página {PAGE_NUM} / {PAGE_COUNT}",
        null,
        8,
        [0.47, 0.48, 0.47]
    );

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
    echo $dompdf->output();
    exit();
}