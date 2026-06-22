<?php

session_start();

require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/models/Inventario.php';
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
    generarReporteInventarioPdf($idVeterinaria);
    exit;
}

if ($action === 'excel') {
    generarReporteInventarioExcel($idVeterinaria);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'payload' => construirPayloadInventario($idVeterinaria)
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

function construirPayloadInventario($idVeterinaria)
{
    $periodo = $_GET['periodo'] ?? 'mes';
    [$inicio, $fin, $etiqueta] = resolverRangoPeriodo($periodo, $_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);

    $categoria = $_GET['categoria'] ?? null;
    $producto = $_GET['producto'] ?? null;

    $inventario = new Inventario();

    return [
        'meta' => [
            'periodo' => $periodo,
            'etiqueta_periodo' => $etiqueta,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'categoria' => $categoria,
            'producto' => $producto
        ],
        'resumen' => $inventario->obtenerResumenReporteInventario($idVeterinaria, $inicio, $fin),
        'detalle_inventario' => $inventario->obtenerReporteInventario($idVeterinaria, $inicio, $fin, $categoria, $producto),
        'metricas_consumo' => $inventario->obtenerMetricasConsumo($idVeterinaria, $inicio, $fin),
        'productos_mas_usados' => $inventario->obtenerProductosMasUsados($idVeterinaria, $inicio, $fin, 10),
        'items_con_alertas' => $inventario->obtenerItemsConAlertas($idVeterinaria, $inicio, $fin, 10),
        'categorias_disponibles' => $inventario->obtenerCategoriasDisponibles($idVeterinaria)
    ];
}

function generarReporteInventarioPdf($idVeterinaria)
{
    $payload = construirPayloadInventario($idVeterinaria);

    ob_start();
    require BASE_PATH . '/app/views/pdf/reporte_inventario_representante_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_inventario_' . date('Y-m-d') . '.pdf', false);
}

function generarReporteInventarioExcel($idVeterinaria)
{
    $payload = construirPayloadInventario($idVeterinaria);

    $filename = 'reporte_inventario_' . date('Y-m-d') . '.xls';

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $meta = $payload['meta'] ?? [];
    $resumen = $payload['resumen'] ?? [];
    $detalle = $payload['detalle_inventario'] ?? [];
    $metricas = $payload['metricas_consumo'] ?? [];
    $masUsados = $payload['productos_mas_usados'] ?? [];
    $alertas = $payload['items_con_alertas'] ?? [];

    $periodo = htmlspecialchars($meta['etiqueta_periodo'] ?? '');
    $fi = htmlspecialchars($meta['fecha_inicio'] ?? '');
    $ff = htmlspecialchars($meta['fecha_fin'] ?? '');
    $cat = htmlspecialchars($meta['categoria'] ?? 'Todas');
    $prod = htmlspecialchars($meta['producto'] ?? 'Todos');

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
        .critico { background-color: #fee2e2 !important; }
        .alerta { background-color: #fef3c7 !important; }
    </style>
    </head><body>';

    echo '<table><tr><td class="titulo">Reporte de Inventario — VetWilling</td></tr>';
    echo '<tr><td class="subtitulo">Periodo: ' . $periodo . ' (' . $fi . ' a ' . $ff . ')</td></tr>';
    echo '<tr><td class="subtitulo">Filtros: Categoría=' . $cat . ', Producto=' . $prod . '</td></tr></table><br/>';

    echo '<table class="seccion"><tr><td>Resumen General</td></tr></table>';
    echo '<table class="resumen"><tr>';
    echo '<td><div class="lbl">Total Lotes</div><div class="val">' . (int)($resumen['total_lotes'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Stock Total</div><div class="val">' . (int)($resumen['stock_total'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Lotes Críticos</div><div class="val">' . (int)($resumen['lotes_criticos'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Vencidos</div><div class="val">' . (int)($resumen['vencidos'] ?? 0) . '</div></td>';
    echo '<td><div class="lbl">Por Vencer</div><div class="val">' . (int)($resumen['por_vencer'] ?? 0) . '</div></td>';
    echo '</tr></table>';

    echo '<table class="seccion"><tr><td>Detalle de Inventario</td></tr></table>';
    echo '<table class="grid"><thead><tr>';
    echo '<th>Producto</th><th>Categoría</th><th>Proveedor</th><th>Stock</th><th>Stock Mínimo</th><th>Estado</th><th>Vencimiento</th>';
    echo '</tr></thead><tbody>';

    if (empty($detalle)) {
        echo '<tr><td colspan="7">Sin datos para el periodo seleccionado</td></tr>';
    } else {
        foreach ($detalle as $row) {
            $alertaClass = '';
            if ($row['alerta_stock'] == 1) {
                $alertaClass = 'critico';
            } elseif ($row['dias_para_vencer'] < 30 && $row['dias_para_vencer'] >= 0) {
                $alertaClass = 'alerta';
            }

            echo '<tr class="' . $alertaClass . '">';
            echo '<td>' . htmlspecialchars($row['nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['categoria'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['proveedor'] ?? '') . '</td>';
            echo '<td>' . (int)($row['cantidad'] ?? 0) . '</td>';
            echo '<td>' . (int)($row['stock_minimo'] ?? 0) . '</td>';
            echo '<td>' . ($row['alerta_stock'] == 1 ? 'CRÍTICO' : 'NORMAL') . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_vencimiento'] ?? '') . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';

    echo '<table class="seccion"><tr><td>Productos Más Usados</td></tr></table>';
    echo '<table class="grid"><thead><tr><th>Producto</th><th>Categoría</th><th>Total Salidas</th><th>Número Salidas</th></tr></thead><tbody>';

    if (empty($masUsados)) {
        echo '<tr><td colspan="4">Sin datos de consumo para el periodo</td></tr>';
    } else {
        foreach ($masUsados as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['categoria'] ?? '') . '</td>';
            echo '<td>' . (int)($row['total_salidas'] ?? 0) . '</td>';
            echo '<td>' . (int)($row['numero_salidas'] ?? 0) . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';

    echo '<table class="seccion"><tr><td>Items con Alertas de Stock</td></tr></table>';
    echo '<table class="grid"><thead><tr><th>Producto</th><th>Categoría</th><th>Stock</th><th>Stock Mínimo</th><th>Nivel Alerta</th></tr></thead><tbody>';

    if (empty($alertas)) {
        echo '<tr><td colspan="5">Sin alertas de stock para el periodo</td></tr>';
    } else {
        foreach ($alertas as $row) {
            $alertaClass = '';
            if ($row['nivel_alerta'] == 'CRITICO') {
                $alertaClass = 'critico';
            } elseif ($row['nivel_alerta'] == 'ALERTA') {
                $alertaClass = 'alerta';
            }

            echo '<tr class="' . $alertaClass . '">';
            echo '<td>' . htmlspecialchars($row['nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['categoria'] ?? '') . '</td>';
            echo '<td>' . (int)($row['cantidad'] ?? 0) . '</td>';
            echo '<td>' . (int)($row['stock_minimo'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars($row['nivel_alerta'] ?? '') . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';
    echo '</body></html>';
}
