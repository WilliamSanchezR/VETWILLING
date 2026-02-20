<?php

session_start();

require_once BASE_PATH . '/app/models/Reportes.php';
require_once BASE_PATH . '/app/helpers/pdf_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'data';

if (!isset($_SESSION['user']['id_usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

$idUsuario = (int)$_SESSION['user']['id_usuario'];

if ($method !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

if ($action === 'pdf') {
    generarReportePdf($idUsuario);
    exit;
}

if ($action === 'data') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'payload' => construirPayloadReportes($idUsuario)
    ]);
    exit;
}

http_response_code(400);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'error', 'message' => 'Acción no soportada']);
exit;

function construirPayloadReportes($idUsuario)
{
    $periodo = $_GET['periodo'] ?? 'mes';
    $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
    [$inicio, $fin, $etiquetaPeriodo] = resolverRangoPeriodo($periodo, $_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);

    $reportesModel = new Reportes();

    return [
        'meta' => [
            'periodo' => $periodo,
            'etiqueta_periodo' => $etiquetaPeriodo,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'anio' => $anio
        ],
        'resumen' => $reportesModel->obtenerResumenGeneral($idUsuario, $inicio, $fin),
        'ingresos_mensuales' => $reportesModel->obtenerIngresosUltimosSeisMeses($idUsuario),
        'servicios' => $reportesModel->obtenerServiciosMasSolicitados($idUsuario, $inicio, $fin),
        'tratamientos' => $reportesModel->obtenerTopTratamientos($idUsuario, $inicio, $fin),
        'especies' => $reportesModel->obtenerPacientesPorEspecie($idUsuario, $inicio, $fin),
        'financiero' => $reportesModel->obtenerResumenFinancieroMensual($idUsuario, $anio)
    ];
}

function resolverRangoPeriodo($periodo, $fechaInicioPersonalizada = null, $fechaFinPersonalizada = null)
{
    $hoy = new DateTime('today');

    switch ($periodo) {
        case 'hoy':
            $inicio = clone $hoy;
            $fin = clone $hoy;
            $etiqueta = 'Hoy';
            break;

        case 'semana':
            $inicio = clone $hoy;
            $inicio->modify('monday this week');
            $fin = clone $inicio;
            $fin->modify('+6 days');
            $etiqueta = 'Esta Semana';
            break;

        case 'ano':
            $inicio = new DateTime($hoy->format('Y-01-01'));
            $fin = new DateTime($hoy->format('Y-12-31'));
            $etiqueta = 'Este Año';
            break;

        case 'personalizado':
            if (!empty($fechaInicioPersonalizada) && !empty($fechaFinPersonalizada)) {
                $inicio = new DateTime($fechaInicioPersonalizada);
                $fin = new DateTime($fechaFinPersonalizada);
                $etiqueta = 'Personalizado';
            } else {
                $inicio = new DateTime($hoy->format('Y-m-01'));
                $fin = clone $hoy;
                $etiqueta = 'Este Mes';
            }
            break;

        case 'mes':
        default:
            $inicio = new DateTime($hoy->format('Y-m-01'));
            $fin = clone $hoy;
            $etiqueta = 'Este Mes';
            break;
    }

    return [
        $inicio->format('Y-m-d'),
        $fin->format('Y-m-d'),
        $etiqueta
    ];
}

function generarReportePdf($idUsuario)
{
    $payload = construirPayloadReportes($idUsuario);

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_resumen_veterinario_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_resumen_veterinario.pdf', false);
}
