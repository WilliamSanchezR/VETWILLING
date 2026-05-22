<?php

/**
 * Controlador API de reportes para el administrador — RFS 14 subtareas 6, 7, 8
 * Rutas:
 *   GET  /admin/reportes/data    → payload JSON completo
 *   GET  /admin/reportes/pdf     → descarga PDF
 *   GET  /admin/reportes/excel   → descarga Excel
 */

session_start();

// Subtarea 6: validar permisos — solo rol 1 (administrador)
if (!isset($_SESSION['user']['id_rol']) || (int)$_SESSION['user']['id_rol'] !== 1) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

require_once BASE_PATH . '/app/models/ReportesAdmin.php';
require_once BASE_PATH . '/app/helpers/pdf_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $GLOBALS['_route_action'] ?? ($_GET['action'] ?? 'data');

if ($method !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

if ($action === 'pdf') {
    generarReporteAdminPdf();
    exit;
}

if ($action === 'excel') {
    generarReporteAdminExcel();
    exit;
}

// Default: data
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status'  => 'success',
    'payload' => construirPayloadAdmin()
]);
exit;

// ─────────────────────────────────────────────────────────────────────────────

function resolverRango(): array
{
    $periodo = $_GET['periodo'] ?? 'mes';
    $hoy     = new DateTime('today');

    switch ($periodo) {
        case 'hoy':
            $inicio   = clone $hoy;
            $fin      = clone $hoy;
            $etiqueta = 'Hoy';
            break;
        case 'semana':
            $inicio   = clone $hoy;
            $inicio->modify('monday this week');
            $fin      = clone $inicio;
            $fin->modify('+6 days');
            $etiqueta = 'Esta Semana';
            break;
        case 'ano':
            $inicio   = new DateTime($hoy->format('Y-01-01'));
            $fin      = new DateTime($hoy->format('Y-12-31'));
            $etiqueta = 'Este Año';
            break;
        case 'personalizado':
            $fi = $_GET['fecha_inicio'] ?? null;
            $ff = $_GET['fecha_fin']    ?? null;
            if (!empty($fi) && !empty($ff)) {
                $inicio   = new DateTime($fi);
                $fin      = new DateTime($ff);
                $etiqueta = 'Personalizado';
            } else {
                $inicio   = new DateTime($hoy->format('Y-m-01'));
                $fin      = clone $hoy;
                $etiqueta = 'Este Mes';
            }
            break;
        default: // mes
            $inicio   = new DateTime($hoy->format('Y-m-01'));
            $fin      = clone $hoy;
            $etiqueta = 'Este Mes';
            break;
    }

    return [$inicio->format('Y-m-d'), $fin->format('Y-m-d'), $etiqueta, $periodo];
}

function construirPayloadAdmin(): array
{
    [$inicio, $fin, $etiqueta, $periodo] = resolverRango();
    $idVeterinaria = !empty($_GET['id_veterinaria']) ? (int)$_GET['id_veterinaria'] : null;

    $model = new ReportesAdmin();

    return [
        'meta' => [
            'periodo'        => $periodo,
            'etiqueta'       => $etiqueta,
            'fecha_inicio'   => $inicio,
            'fecha_fin'      => $fin,
            'id_veterinaria' => $idVeterinaria,
        ],
        'veterinarias'      => $model->obtenerListaVeterinarias(),
        'resumen'           => $model->obtenerResumenGlobal($inicio, $fin, $idVeterinaria),
        'estados_citas'     => $model->obtenerEstadosCitas($inicio, $fin, $idVeterinaria),
        'desempeno'         => $model->obtenerDesempenioPersonal($inicio, $fin, $idVeterinaria),
        'inventario'        => $model->obtenerResumenInventario($idVeterinaria),
        'productos_vencer'  => $model->obtenerProductosProximosVencer($idVeterinaria, 60),
        'top_veterinarias'  => $model->obtenerTopVeterinarias($inicio, $fin),
        'ingresos_mensuales'=> $model->obtenerIngresosMensuales($idVeterinaria),
    ];
}

// ─────────────────────────────────────────────────────────────────────────────

function generarReporteAdminPdf(): void
{
    $payload  = construirPayloadAdmin();
    $resumen  = $payload['resumen'];
    $estados  = $payload['estados_citas'];
    $desempeno= $payload['desempeno'];
    $inventario = $payload['inventario'];
    $productos= $payload['productos_vencer'];
    $topVets  = $payload['top_veterinarias'];
    $meta     = $payload['meta'];

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_admin_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_admin_' . date('Y-m-d') . '.pdf', false);
}

function generarReporteAdminExcel(): void
{
    $payload   = construirPayloadAdmin();
    $resumen   = $payload['resumen'];
    $estados   = $payload['estados_citas'];
    $desempeno = $payload['desempeno'];
    $inventario= $payload['inventario'];
    $productos = $payload['productos_vencer'];
    $topVets   = $payload['top_veterinarias'];
    $meta      = $payload['meta'];

    $filename = 'reporte_admin_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $periodo = htmlspecialchars($meta['etiqueta'] ?? '');
    $fi      = htmlspecialchars($meta['fecha_inicio'] ?? '');
    $ff      = htmlspecialchars($meta['fecha_fin'] ?? '');

    echo '<!DOCTYPE html><html><head><meta charset="utf-8">
    <style>
        body{font-family:Calibri,Arial,sans-serif;}
        .titulo{font-size:18px;font-weight:bold;color:#0a932c;}
        .subtitulo{font-size:13px;color:#6b7280;margin-bottom:8px;}
        .seccion{font-size:14px;font-weight:bold;color:#0a932c;background:#f0fdf4;padding:6px;border-left:4px solid #0a932c;margin-top:14px;}
        table{border-collapse:collapse;width:100%;margin-bottom:10px;}
        .resumen td{border:1px solid #d1d5db;padding:8px 12px;text-align:center;}
        .resumen .lbl{font-size:10px;color:#6b7280;text-transform:uppercase;}
        .resumen .val{font-size:16px;font-weight:bold;color:#1f2937;}
        .bg-green{background:#ecfdf3;} .bg-red{background:#fef2f2;}
        .bg-yellow{background:#fffbeb;} .bg-blue{background:#f0f4ff;}
        .bg-gray{background:#f9fafb;}
        .grid th{background:#0a932c;color:#fff;font-size:11px;text-transform:uppercase;padding:8px 10px;border:1px solid #088a28;}
        .grid td{border:1px solid #e5e7eb;padding:6px 10px;font-size:11px;}
        .grid tr:nth-child(even) td{background:#f9fafb;}
        .badge{padding:2px 8px;border-radius:3px;font-size:10px;font-weight:bold;}
        .badge-ok{background:#dcfce7;color:#166534;}
        .badge-warn{background:#fef3c7;color:#92400e;}
        .badge-danger{background:#fee2e2;color:#991b1b;}
    </style></head><body>';

    echo '<table><tr><td class="titulo">Reporte Administrativo — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Período: ' . $periodo . ' (' . $fi . ' a ' . $ff . ')</td></tr></table><br/>';

    // Resumen general
    echo '<table class="seccion"><tr><td>Resumen General</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td class="bg-green"><div class="lbl">Ingresos Totales</div><div class="val">$' . number_format((float)($resumen['ingresos_totales'] ?? 0), 0, ',', '.') . '</div></td>';
    echo '<td class="bg-blue"><div class="lbl">Citas Totales</div><div class="val">' . (int)($resumen['total_citas'] ?? 0) . '</div></td>';
    echo '<td class="bg-green"><div class="lbl">Citas Atendidas</div><div class="val">' . (int)($resumen['citas_atendidas'] ?? 0) . '</div></td>';
    echo '<td class="bg-red"><div class="lbl">Citas Canceladas</div><div class="val">' . (int)($resumen['citas_canceladas'] ?? 0) . '</div></td>';
    echo '<td class="bg-gray"><div class="lbl">Cumplimiento</div><div class="val">' . number_format((float)($resumen['cumplimiento'] ?? 0), 1) . '%</div></td>';
    echo '</tr></table>';

    // Inventario
    echo '<table class="seccion"><tr><td>Resumen de Inventario</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td class="bg-blue"><div class="lbl">Total Productos</div><div class="val">' . (int)($inventario['total_productos'] ?? 0) . '</div></td>';
    echo '<td class="bg-green"><div class="lbl">Vigentes</div><div class="val">' . (int)($inventario['vigentes'] ?? 0) . '</div></td>';
    echo '<td class="bg-yellow"><div class="lbl">Por Vencer (30 días)</div><div class="val">' . (int)($inventario['por_vencer'] ?? 0) . '</div></td>';
    echo '<td class="bg-red"><div class="lbl">Vencidos</div><div class="val">' . (int)($inventario['vencidos'] ?? 0) . '</div></td>';
    echo '</tr></table>';

    // Productos próximos a vencer
    if (!empty($productos)) {
        echo '<table class="seccion"><tr><td>Insumos Próximos a Vencer (60 días)</td></tr></table>';
        echo '<table class="grid"><thead><tr><th>Producto</th><th>Veterinaria</th><th>Lote</th><th>Cantidad</th><th>Vence</th><th>Días Restantes</th></tr></thead><tbody>';
        foreach ($productos as $p) {
            $dias = (int)($p['dias_restantes'] ?? 0);
            $badge = $dias < 0 ? 'badge-danger' : ($dias <= 15 ? 'badge-warn' : 'badge-ok');
            $label = $dias < 0 ? 'Vencido' : ($dias <= 15 ? 'Crítico' : 'Próximo');
            echo '<tr><td>' . htmlspecialchars($p['nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($p['veterinaria'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($p['numero_lote'] ?? '-') . '</td>';
            echo '<td style="text-align:center;">' . (int)($p['cantidad'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars($p['fecha_vencimiento'] ?? '') . '</td>';
            echo '<td style="text-align:center;"><span class="badge ' . $badge . '">' . $label . ' (' . $dias . 'd)</span></td></tr>';
        }
        echo '</tbody></table>';
    }

    // Desempeño del personal
    if (!empty($desempeno)) {
        echo '<table class="seccion"><tr><td>Desempeño del Personal</td></tr></table>';
        echo '<table class="grid"><thead><tr><th>Profesional</th><th>Veterinaria</th><th>Total Citas</th><th>Atendidas</th><th>Canceladas</th><th>Cumplimiento %</th><th>Tiempo Prom. (min)</th></tr></thead><tbody>';
        foreach ($desempeno as $d) {
            $tc = (float)($d['tasa_cumplimiento'] ?? 0);
            $badge = $tc >= 80 ? 'badge-ok' : ($tc >= 50 ? 'badge-warn' : 'badge-danger');
            echo '<tr>';
            echo '<td>' . htmlspecialchars($d['nombre_profesional'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($d['veterinaria'] ?? '') . '</td>';
            echo '<td style="text-align:center;">' . (int)($d['total_citas'] ?? 0) . '</td>';
            echo '<td style="text-align:center;">' . (int)($d['atendidas'] ?? 0) . '</td>';
            echo '<td style="text-align:center;">' . (int)($d['canceladas'] ?? 0) . '</td>';
            echo '<td style="text-align:center;"><span class="badge ' . $badge . '">' . number_format($tc, 1) . '%</span></td>';
            echo '<td style="text-align:center;">' . ($d['promedio_minutos'] !== null ? (int)$d['promedio_minutos'] : '-') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    // Top veterinarias
    if (!empty($topVets)) {
        echo '<table class="seccion"><tr><td>Ranking de Veterinarias</td></tr></table>';
        echo '<table class="grid"><thead><tr><th>#</th><th>Veterinaria</th><th>Citas Atendidas</th><th>Citas Canceladas</th><th>Ingresos</th></tr></thead><tbody>';
        foreach ($topVets as $i => $vet) {
            echo '<tr>';
            echo '<td style="text-align:center;">' . ($i + 1) . '</td>';
            echo '<td>' . htmlspecialchars($vet['veterinaria'] ?? '') . '</td>';
            echo '<td style="text-align:center;">' . (int)($vet['atendidas'] ?? 0) . '</td>';
            echo '<td style="text-align:center;">' . (int)($vet['canceladas'] ?? 0) . '</td>';
            echo '<td>$' . number_format((float)($vet['ingresos'] ?? 0), 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    echo '</body></html>';
}
