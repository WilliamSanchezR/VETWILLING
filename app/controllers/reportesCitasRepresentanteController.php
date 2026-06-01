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
    generarReporteCancelacionesPdf($idVeterinaria);
    exit;
}

if ($action === 'excel') {
    generarReporteCitasExcel($idVeterinaria);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'payload' => construirPayloadCitas($idVeterinaria)
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

function construirPayloadCitas($idVeterinaria)
{
    $periodo = $_GET['periodo'] ?? 'mes';
    [$inicio, $fin, $etiqueta] = resolverRangoPeriodo($periodo, $_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);

    $reportes = new Reportes();

    return [
        'meta' => [
            'periodo' => $periodo,
            'etiqueta_periodo' => $etiqueta,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin
        ],
        'resumen_estados' => $reportes->obtenerResumenEstadosCitasPorVeterinaria($idVeterinaria, $inicio, $fin),
        'detalle_citas' => $reportes->obtenerDetalleCitasPorVeterinaria($idVeterinaria, $inicio, $fin),
        'motivos_cancelacion' => $reportes->obtenerMotivosCancelacionPorVeterinaria($idVeterinaria, $inicio, $fin)
    ];
}

function generarReporteCancelacionesPdf($idVeterinaria)
{
    $payload = construirPayloadCitas($idVeterinaria);

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_cancelaciones_representante_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_cancelaciones_' . date('Y-m-d') . '.pdf', false);
}

function generarReporteCitasExcel($idVeterinaria)
{
    $payload = construirPayloadCitas($idVeterinaria);

    $filename = 'reporte_citas_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $meta = $payload['meta'] ?? [];
    $resumen = $payload['resumen_estados'] ?? [];
    $detalle = $payload['detalle_citas'] ?? [];
    $motivos = $payload['motivos_cancelacion'] ?? [];

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
    </style>
    </head><body>';

    echo '<table><tr><td class="titulo">Reporte de Citas — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Periodo: ' . $periodo . ' (' . $fi . ' a ' . $ff . ')</td></tr></table><br/>';

    echo '<table class="seccion"><tr><td>Resumen de Estados</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td><div class="lbl">Atendidas</div><div class="val">' . (int)($resumen['atendidas'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Canceladas</div><div class="val">' . (int)($resumen['canceladas'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Pendientes</div><div class="val">' . (int)($resumen['pendientes'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Total</div><div class="val">' . (int)($resumen['total'] ?? 0) . '</div></td>';
    echo '</tr></table>';

    echo '<table class="seccion"><tr><td>Detalle de Citas</td></tr></table>';
    echo '<table class="grid"><thead><tr>';
    echo '<th>Fecha</th><th>Paciente</th><th>Propietario</th><th>Servicio</th><th>Subservicio</th><th>Estado</th><th>Motivo Cancelacion</th>';
    echo '</tr></thead><tbody>';

    if (empty($detalle)) {
        echo '<tr><td colspan="7">Sin datos para el periodo seleccionado</td></tr>';
    } else {
        foreach ($detalle as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['fecha'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['paciente'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['propietario'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['servicio'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['subservicio'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['estado'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['motivo_cancelacion'] ?? '') . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';

    echo '<table class="seccion"><tr><td>Motivos de Cancelacion</td></tr></table>';
    echo '<table class="grid"><thead><tr><th>Motivo</th><th>Total</th></tr></thead><tbody>';

    if (empty($motivos)) {
        echo '<tr><td colspan="2">Sin cancelaciones registradas</td></tr>';
    } else {
        foreach ($motivos as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['motivo'] ?? '') . '</td>';
            echo '<td>' . (int)($row['total'] ?? 0) . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';
    echo '</body></html>';
}
