<?php

session_start();

require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/models/Venta.php';
require_once BASE_PATH . '/app/helpers/pdf_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'data';

if ($method !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$idVeterinaria = (int) ($_SESSION['user']['id_veterinaria'] ?? 0);

if ($idVeterinaria <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Veterinaria no válida']);
    exit;
}

if ($action === 'pdf') {
    generarHistorialVentasPdf($idVeterinaria);
    exit;
}

if ($action === 'excel') {
    generarHistorialVentasExcel($idVeterinaria);
    exit;
}

// Acción por defecto: devolver JSON con el payload de ventas
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status'  => 'success',
    'payload' => construirPayloadVentas($idVeterinaria)
]);
exit;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers de rango de período (mismo patrón que reportesInventarioRepresentante)
// ─────────────────────────────────────────────────────────────────────────────

function resolverRangoPeriodoVentas(string $periodo, ?string $fi = null, ?string $ff = null): array
{
    $hoy = new DateTime('today');

    switch ($periodo) {
        case 'hoy':
            return [$hoy->format('Y-m-d'), $hoy->format('Y-m-d'), 'Hoy'];
        case 'semana':
            $inicio = (clone $hoy)->modify('monday this week');
            return [$inicio->format('Y-m-d'), (clone $inicio)->modify('+6 days')->format('Y-m-d'), 'Esta Semana'];
        case 'ano':
            return [$hoy->format('Y-01-01'), $hoy->format('Y-12-31'), 'Este Año'];
        case 'personalizado':
            if ($fi && $ff) {
                return [$fi, $ff, 'Personalizado'];
            }
            // fallthrough a 'mes' si faltan fechas
        case 'mes':
        default:
            return [$hoy->format('Y-m-01'), $hoy->format('Y-m-d'), 'Este Mes'];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Construcción del payload principal (datos + resumen)
// ─────────────────────────────────────────────────────────────────────────────

function construirPayloadVentas(int $idVeterinaria): array
{
    $periodo   = $_GET['periodo']     ?? 'mes';
    $idCliente = !empty($_GET['id_cliente']) ? (int) $_GET['id_cliente'] : null;
    $producto  = trim($_GET['producto']  ?? '');
    $estado    = trim($_GET['estado']    ?? 'todos');

    [$inicio, $fin, $etiqueta] = resolverRangoPeriodoVentas(
        $periodo,
        $_GET['fecha_inicio'] ?? null,
        $_GET['fecha_fin']    ?? null
    );

    $model  = new Venta();
    $ventas = $model->listarVentas($idVeterinaria, $inicio, $fin, $idCliente, $producto ?: null, $estado);
    $resumen = $model->obtenerResumenVentas($idVeterinaria, $inicio, $fin);
    $clientes = $model->listarClientesConVentas($idVeterinaria);

    return [
        'meta' => [
            'periodo'         => $periodo,
            'etiqueta_periodo'=> $etiqueta,
            'fecha_inicio'    => $inicio,
            'fecha_fin'       => $fin,
            'id_cliente'      => $idCliente,
            'producto'        => $producto,
            'estado'          => $estado,
        ],
        'resumen'   => $resumen,
        'ventas'    => $ventas,
        'clientes'  => $clientes,
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// Exportar PDF — renderiza la vista HTML con dompdf
// ─────────────────────────────────────────────────────────────────────────────

function generarHistorialVentasPdf(int $idVeterinaria): void
{
    $payload = construirPayloadVentas($idVeterinaria);

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_ventas_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'historial_ventas_' . date('Y-m-d') . '.pdf', false);
}

// ─────────────────────────────────────────────────────────────────────────────
// Exportar Excel — tabla HTML descargable como .xls (mismo patrón inventario)
// ─────────────────────────────────────────────────────────────────────────────

function generarHistorialVentasExcel(int $idVeterinaria): void
{
    $payload  = construirPayloadVentas($idVeterinaria);
    $meta     = $payload['meta']    ?? [];
    $resumen  = $payload['resumen'] ?? [];
    $ventas   = $payload['ventas']  ?? [];

    $filename = 'historial_ventas_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $periodo = htmlspecialchars($meta['etiqueta_periodo'] ?? '');
    $fi      = htmlspecialchars($meta['fecha_inicio']     ?? '');
    $ff      = htmlspecialchars($meta['fecha_fin']        ?? '');
    $estado  = htmlspecialchars($meta['estado']           ?? 'todos');
    $prod    = htmlspecialchars($meta['producto']         ?? '');

    echo '<!DOCTYPE html><html><head><meta charset="utf-8">
    <style>
        body { font-family: Calibri, Arial, sans-serif; }
        .titulo  { font-size: 18px; font-weight: bold; color: #0a932c; }
        .subtitulo { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .seccion { font-size: 14px; font-weight: bold; color: #0a932c; background: #f0fdf4;
                   padding: 6px; border-left: 4px solid #0a932c; margin-top: 14px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
        .resumen td { border: 1px solid #d1d5db; padding: 8px 12px; text-align: center; }
        .resumen .lbl { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .resumen .val { font-size: 16px; font-weight: bold; color: #1f2937; }
        .grid th { background: #0a932c; color: #fff; font-size: 11px; text-transform: uppercase;
                   padding: 8px 10px; border: 1px solid #088a28; }
        .grid td { border: 1px solid #e5e7eb; padding: 6px 10px; font-size: 11px; }
        .grid tr:nth-child(even) td { background: #f9fafb; }
    </style></head><body>';

    echo '<table><tr><td class="titulo">Historial de Ventas — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Período: ' . $periodo . ' (' . $fi . ' a ' . $ff . ')</td></tr>';
    echo '<tr><td class="subtitulo">Filtros: Estado=' . $estado . ', Producto=' . ($prod ?: 'Todos') . '</td></tr></table><br/>';

    echo '<table class="seccion"><tr><td>Resumen</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td><div class="lbl">Total Ventas</div><div class="val">'   . (int) ($resumen['total_ventas']      ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Monto Total</div><div class="val">$'   . number_format((float) ($resumen['monto_total']       ?? 0), 2) . '</div></td>';
    echo '<td><div class="lbl">Descuentos</div><div class="val">$'    . number_format((float) ($resumen['total_descuentos']  ?? 0), 2) . '</div></td>';
    echo '<td><div class="lbl">Impuestos</div><div class="val">$'     . number_format((float) ($resumen['total_impuestos']   ?? 0), 2) . '</div></td>';
    echo '<td><div class="lbl">Prom. Venta</div><div class="val">$'   . number_format((float) ($resumen['promedio_venta']    ?? 0), 2) . '</div></td>';
    echo '</tr></table>';

    echo '<table class="seccion"><tr><td>Detalle de Ventas</td></tr></table>';
    echo '<table class="grid"><thead><tr>
        <th>#Venta</th><th>Fecha</th><th>Cliente</th><th>Usuario</th>
        <th>Subtotal</th><th>Descuento</th><th>Impuesto</th><th>Total</th><th>Estado</th>
    </tr></thead><tbody>';

    if (empty($ventas)) {
        echo '<tr><td colspan="9">Sin ventas para el período seleccionado</td></tr>';
    } else {
        foreach ($ventas as $v) {
            echo '<tr>';
            echo '<td>' . (int) $v['id_venta'] . '</td>';
            echo '<td>' . htmlspecialchars($v['fecha_venta'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($v['cliente']    ?? '—') . '</td>';
            echo '<td>' . htmlspecialchars($v['usuario']    ?? '') . '</td>';
            echo '<td>$' . number_format((float) ($v['subtotal']  ?? 0), 2) . '</td>';
            echo '<td>$' . number_format((float) ($v['descuento'] ?? 0), 2) . '</td>';
            echo '<td>$' . number_format((float) ($v['impuesto']  ?? 0), 2) . '</td>';
            echo '<td>$' . number_format((float) ($v['total']     ?? 0), 2) . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($v['estado'] ?? 'completada')) . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table></body></html>';
}
