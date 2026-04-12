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

if ($action === 'excel') {
    generarReporteExcel($idUsuario);
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

    // RFS 39: Filtros adicionales por veterinaria, propietario y estado de cita
    $filtros = [];
    if (!empty($_GET['id_veterinaria'])) {
        $filtros['id_veterinaria'] = (int)$_GET['id_veterinaria'];
    }
    if (!empty($_GET['id_propietario'])) {
        $filtros['id_propietario'] = (int)$_GET['id_propietario'];
    }
    if (!empty($_GET['estado_cita'])) {
        $estadoPermitido = ['ATENDIDA', 'CANCELADA', 'PENDIENTE'];
        $estadoSolicitado = strtoupper(trim($_GET['estado_cita']));
        if (in_array($estadoSolicitado, $estadoPermitido, true)) {
            $filtros['estado_cita'] = $estadoSolicitado;
        }
    }

    $reportesModel = new Reportes();

    return [
        'meta' => [
            'periodo' => $periodo,
            'etiqueta_periodo' => $etiquetaPeriodo,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'anio' => $anio,
            'filtros' => $filtros
        ],
        'resumen' => $reportesModel->obtenerResumenGeneral($idUsuario, $inicio, $fin, $filtros),
        'resumen_estados' => $reportesModel->obtenerResumenEstadosCitas($idUsuario, $inicio, $fin, $filtros),
        'ingresos_mensuales' => $reportesModel->obtenerIngresosUltimosSeisMeses($idUsuario, $filtros),
        'servicios' => $reportesModel->obtenerServiciosMasSolicitados($idUsuario, $inicio, $fin, 4, $filtros),
        'tratamientos' => $reportesModel->obtenerTopTratamientos($idUsuario, $inicio, $fin, 5, $filtros),
        'especies' => $reportesModel->obtenerPacientesPorEspecie($idUsuario, $inicio, $fin, $filtros),
        'financiero' => $reportesModel->obtenerResumenFinancieroMensual($idUsuario, $anio, $filtros),
        'asignaciones_activas' => $reportesModel->obtenerPacientesAsignadosActivos($idUsuario),
        'historial_asignaciones' => $reportesModel->obtenerHistorialAsignacionesPeriodo($idUsuario, $inicio, $fin),
        'detalle_citas' => $reportesModel->obtenerDetalleCitas($idUsuario, $inicio, $fin, $filtros)
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

function generarReporteExcel($idUsuario)
{
    $payload = construirPayloadReportes($idUsuario);

    $filename = 'reporte_citas_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $resumen = $payload['resumen'] ?? [];
    $estados = $payload['resumen_estados'] ?? [];
    $detalle = $payload['detalle_citas'] ?? [];
    $servicios = $payload['servicios'] ?? [];
    $periodo = htmlspecialchars($payload['meta']['etiqueta_periodo'] ?? '');
    $fechaInicio = htmlspecialchars($payload['meta']['fecha_inicio'] ?? '');
    $fechaFin = htmlspecialchars($payload['meta']['fecha_fin'] ?? '');
    $filtroEstado = $payload['meta']['filtros']['estado_cita'] ?? '';

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
        .bg-green { background: #ecfdf3; }
        .bg-red { background: #fef2f2; }
        .bg-yellow { background: #fffbeb; }
        .bg-blue { background: #f0f4ff; }
        .bg-gray { background: #f9fafb; }
        .grid th { background: #0a932c; color: #ffffff; font-size: 11px; text-transform: uppercase; padding: 8px 10px; border: 1px solid #088a28; }
        .grid td { border: 1px solid #e5e7eb; padding: 6px 10px; font-size: 11px; }
        .grid tr:nth-child(even) td { background: #f9fafb; }
        .badge { padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-atendida { background: #dcfce7; color: #166534; }
        .badge-cancelada { background: #fee2e2; color: #991b1b; }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .filtro-info { font-size: 11px; color: #9ca3af; font-style: italic; }
    </style>
    </head><body>';

    // Encabezado
    echo '<table><tr><td class="titulo">Reporte de Citas — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Periodo: ' . $periodo . ' (' . $fechaInicio . ' a ' . $fechaFin . ')</td></tr>';
    if (!empty($filtroEstado)) {
        echo '<tr><td class="filtro-info">Filtro estado: ' . htmlspecialchars($filtroEstado) . '</td></tr>';
    }
    echo '</table><br/>';

    // Resumen de estados
    echo '<table class="seccion"><tr><td>Resumen de Estados</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td class="bg-green"><div class="lbl">Atendidas</div><div class="val">' . (int)($estados['atendidas'] ?? 0) . '</div></td>';
    echo '<td class="bg-red"><div class="lbl">Canceladas</div><div class="val">' . (int)($estados['canceladas'] ?? 0) . '</div></td>';
    echo '<td class="bg-yellow"><div class="lbl">Pendientes</div><div class="val">' . (int)($estados['pendientes'] ?? 0) . '</div></td>';
    echo '<td class="bg-blue"><div class="lbl">Total</div><div class="val">' . (int)($estados['total'] ?? 0) . '</div></td>';
    echo '</tr></table>';

    // Resumen general
    echo '<table class="seccion"><tr><td>Resumen General</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td class="bg-green"><div class="lbl">Ingresos</div><div class="val">$' . number_format((float)($resumen['ingresos_totales'] ?? 0), 0, ',', '.') . '</div></td>';
    echo '<td class="bg-gray"><div class="lbl">Citas Atendidas</div><div class="val">' . (int)($resumen['citas_atendidas'] ?? 0) . '</div></td>';
    echo '<td class="bg-gray"><div class="lbl">Nuevos Pacientes</div><div class="val">' . (int)($resumen['nuevos_pacientes'] ?? 0) . '</div></td>';
    echo '<td class="bg-gray"><div class="lbl">Cumplimiento</div><div class="val">' . number_format((float)($resumen['cumplimiento'] ?? 0), 1) . '%</div></td>';
    echo '</tr></table>';

    // Servicios más solicitados
    if (!empty($servicios)) {
        echo '<table class="seccion"><tr><td>Servicios más Solicitados</td></tr></table>';
        echo '<table class="grid"><thead><tr><th>Servicio</th><th>Citas</th><th>Porcentaje</th></tr></thead><tbody>';
        foreach ($servicios as $s) {
            echo '<tr><td>' . htmlspecialchars($s['nombre']) . '</td><td style="text-align:center;">' . (int)$s['total'] . '</td><td style="text-align:center;">' . number_format((float)$s['porcentaje'], 1) . '%</td></tr>';
        }
        echo '</tbody></table>';
    }

    // Detalle de citas
    echo '<table class="seccion"><tr><td>Detalle de Citas</td></tr></table>';
    echo '<table class="grid"><thead><tr><th>Fecha</th><th>Paciente</th><th>Propietario</th><th>Servicio</th><th>Subservicio</th><th>Estado</th><th>Observaciones</th></tr></thead><tbody>';
    if (!empty($detalle)) {
        foreach ($detalle as $cita) {
            $est = strtoupper($cita['estado'] ?? 'PENDIENTE');
            $badgeClass = 'badge-pendiente';
            if ($est === 'ATENDIDA') $badgeClass = 'badge-atendida';
            elseif ($est === 'CANCELADA') $badgeClass = 'badge-cancelada';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($cita['fecha'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($cita['paciente'] ?? 'Sin paciente') . '</td>';
            echo '<td>' . htmlspecialchars($cita['propietario'] ?? 'Sin propietario') . '</td>';
            echo '<td>' . htmlspecialchars($cita['servicio'] ?? 'Sin servicio') . '</td>';
            echo '<td>' . htmlspecialchars($cita['subservicio'] ?? '-') . '</td>';
            echo '<td><span class="badge ' . $badgeClass . '">' . $est . '</span></td>';
            echo '<td>' . htmlspecialchars($cita['observaciones'] ?? '-') . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" style="text-align:center; color:#9ca3af;">Sin citas para el periodo seleccionado.</td></tr>';
    }
    echo '</tbody></table>';

    // Pie
    echo '<br/><table><tr><td style="font-size:9px; color:#9ca3af;">Generado el ' . date('d/m/Y H:i') . ' — VetWilling</td></tr></table>';
    echo '</body></html>';
    exit;
}
