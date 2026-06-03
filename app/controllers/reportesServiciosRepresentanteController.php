<?php

session_start();

require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/models/Reportes.php';
require_once BASE_PATH . '/app/helpers/pdf_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'data';

if ($method !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido']);
    exit;
}

$idVeterinaria = (int)($_SESSION['user']['id_veterinaria'] ?? 0);

if ($idVeterinaria <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Veterinaria no valida']);
    exit;
}

if ($action === 'pdf') {
    generarReporteServiciosPdf($idVeterinaria);
    exit;
}

if ($action === 'excel') {
    generarReporteServiciosExcel($idVeterinaria);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'payload' => construirPayloadServicios($idVeterinaria)
]);
exit;

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
            $etiqueta = 'Este Ano';
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

function construirPayloadServicios($idVeterinaria)
{
    $periodo = $_GET['periodo'] ?? 'mes';
    [$inicio, $fin, $etiqueta] = resolverRangoPeriodo($periodo, $_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);

    $reportes = new Reportes();
    $servicios = $reportes->obtenerReporteServiciosPorVeterinaria($idVeterinaria, $inicio, $fin);

    $totales = [
        'total_citas' => 0,
        'atendidas' => 0,
        'canceladas' => 0,
        'ingresos' => 0
    ];

    foreach ($servicios as $row) {
        $totales['total_citas'] += (int)($row['total_citas'] ?? 0);
        $totales['atendidas'] += (int)($row['atendidas'] ?? 0);
        $totales['canceladas'] += (int)($row['canceladas'] ?? 0);
        $totales['ingresos'] += (float)($row['ingresos'] ?? 0);
    }

    return [
        'meta' => [
            'periodo' => $periodo,
            'etiqueta_periodo' => $etiqueta,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin
        ],
        'totales' => $totales,
        'servicios' => $servicios
    ];
}

function generarReporteServiciosPdf($idVeterinaria)
{
    $payload = construirPayloadServicios($idVeterinaria);

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_servicios_representante_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_servicios_' . date('Y-m-d') . '.pdf', false);
}

function generarReporteServiciosExcel($idVeterinaria)
{
    $payload = construirPayloadServicios($idVeterinaria);

    $filename = 'reporte_servicios_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $meta = $payload['meta'] ?? [];
    $totales = $payload['totales'] ?? [];
    $servicios = $payload['servicios'] ?? [];

    $periodo = htmlspecialchars($meta['etiqueta_periodo'] ?? '');
    $fi = htmlspecialchars($meta['fecha_inicio'] ?? '');
    $ff = htmlspecialchars($meta['fecha_fin'] ?? '');

    echo '<!DOCTYPE html><html><head><meta charset="utf-8">
    <style>
        body { font-family: Calibri, Arial, sans-serif; }
        .titulo { font-size: 18px; font-weight: bold; color: #0a932c; }
        .subtitulo { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .seccion { font-size: 14px; font-weight: bold; color: #0a932c; background: #f0fdf4; padding: 6px; border-left: 4px solid #0a932c; margin-top: 14px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        .resumen td { border: 1px solid #d1d5db; padding: 8px 12px; text-align: center; }
        .resumen .lbl { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .resumen .val { font-size: 16px; font-weight: bold; color: #1f2937; }
        .grid th { background: #0a932c; color: #ffffff; font-size: 11px; text-transform: uppercase; padding: 8px 10px; border: 1px solid #088a28; }
        .grid td { border: 1px solid #e5e7eb; padding: 6px 10px; font-size: 11px; }
        .grid tr:nth-child(even) td { background: #f9fafb; }
        .bg-green { background: #ecfdf3; }
        .bg-red { background: #fef2f2; }
        .bg-yellow { background: #fffbeb; }
        .bg-blue { background: #f0f4ff; }
    </style>
    </head><body>';

    echo '<table><tr><td class="titulo">Reporte de Servicios — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Periodo: ' . $periodo . ' (' . $fi . ' a ' . $ff . ')</td></tr></table><br/>';

    echo '<table class="seccion"><tr><td>Resumen</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td class="bg-blue"><div class="lbl">Total Citas</div><div class="val">' . (int)($totales['total_citas'] ?? 0) . '</div></td>';
    echo '<td class="bg-green"><div class="lbl">Atendidas</div><div class="val">' . (int)($totales['atendidas'] ?? 0) . '</div></td>';
    echo '<td class="bg-red"><div class="lbl">Canceladas</div><div class="val">' . (int)($totales['canceladas'] ?? 0) . '</div></td>';
    echo '<td class="bg-yellow"><div class="lbl">Ingresos</div><div class="val">$' . number_format((float)($totales['ingresos'] ?? 0), 0, ',', '.') . '</div></td>';
    echo '</tr></table>';

    echo '<table class="seccion"><tr><td>Detalle por Servicio</td></tr></table>';
    echo '<table class="grid"><thead><tr>';
    echo '<th>Servicio</th><th>Total Citas</th><th>Atendidas</th><th>Canceladas</th><th>Ingresos</th>';
    echo '</tr></thead><tbody>';

    if (empty($servicios)) {
        echo '<tr><td colspan="5">Sin datos para el periodo seleccionado</td></tr>';
    } else {
        foreach ($servicios as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['servicio'] ?? '') . '</td>';
            echo '<td>' . (int)($row['total_citas'] ?? 0) . '</td>';
            echo '<td>' . (int)($row['atendidas'] ?? 0) . '</td>';
            echo '<td>' . (int)($row['canceladas'] ?? 0) . '</td>';
            echo '<td>$' . number_format((float)($row['ingresos'] ?? 0), 0, ',', '.') . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';
    echo '</body></html>';
}
