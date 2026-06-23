<?php
$meta = $payload['meta'] ?? [];
$resumen = $payload['resumen'] ?? [];
$detalle = $payload['detalle_inventario'] ?? [];
$metricas = $payload['metricas_consumo'] ?? [];
$masUsados = $payload['productos_mas_usados'] ?? [];
$alertas = $payload['items_con_alertas'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px; color: #0a932c; }
        .subtitulo { font-size: 11px; color: #6b7280; margin-bottom: 12px; }
        .seccion { font-size: 12px; font-weight: bold; color: #0a932c; background: #f0fdf4; padding: 6px; border-left: 4px solid #0a932c; margin-top: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #0a932c; color: #fff; text-transform: uppercase; font-size: 10px; padding: 6px; border: 1px solid #088a28; }
        td { border: 1px solid #e5e7eb; padding: 6px; font-size: 10px; }
        .resumen td { text-align: center; }
        .critico td { background: #fee2e2; }
        .alerta td { background: #fef3c7; }
    </style>
</head>

<body>
    <h1>Reporte de Inventario</h1>
    <div class="subtitulo">
        Periodo: <?= htmlspecialchars($meta['etiqueta_periodo'] ?? '') ?>
        (<?= htmlspecialchars($meta['fecha_inicio'] ?? '') ?> a <?= htmlspecialchars($meta['fecha_fin'] ?? '') ?>)
        <br>
        Filtros: Categoría=<?= htmlspecialchars($meta['categoria'] ?? 'Todas') ?>,
        Producto=<?= htmlspecialchars($meta['producto'] ?? 'Todos') ?>
    </div>

    <div class="seccion">Resumen General</div>
    <table class="resumen">
        <tr>
            <td>Total Lotes<br><strong><?= (int)($resumen['total_lotes'] ?? 0) ?></strong></td>
            <td>Stock Total<br><strong><?= (int)($resumen['stock_total'] ?? 0) ?></strong></td>
            <td>Lotes Críticos<br><strong><?= (int)($resumen['lotes_criticos'] ?? 0) ?></strong></td>
            <td>Total Salidas<br><strong><?= (int)($resumen['total_salidas'] ?? 0) ?></strong></td>
            <td>Consumo Prom./Día<br><strong><?= number_format((float)($resumen['consumo_promedio_diario'] ?? 0), 2) ?></strong></td>
            <td>Promedio/Salida<br><strong><?= number_format((float)($resumen['promedio_por_salida'] ?? 0), 2) ?></strong></td>
        </tr>
    </table>

    <div class="seccion">Detalle de Inventario</div>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th>Stock</th>
                <th>Stock Mín.</th>
                <th>Estado</th>
                <th>Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($detalle)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;">Sin datos para el periodo seleccionado</td>
                </tr>
            <?php else: ?>
                <?php foreach ($detalle as $row): ?>
                    <?php
                    $clase = '';
                    if (($row['alerta_stock'] ?? 0) == 1) {
                        $clase = 'critico';
                    } elseif (($row['dias_para_vencer'] ?? 999) < 30 && ($row['dias_para_vencer'] ?? -1) >= 0) {
                        $clase = 'alerta';
                    }
                    ?>
                    <tr class="<?= $clase ?>">
                        <td><?= htmlspecialchars($row['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['proveedor'] ?? '') ?></td>
                        <td><?= (int)($row['cantidad'] ?? 0) ?></td>
                        <td><?= (int)($row['stock_minimo'] ?? 0) ?></td>
                        <td><?= ($row['alerta_stock'] ?? 0) == 1 ? 'CRÍTICO' : 'NORMAL' ?></td>
                        <td><?= htmlspecialchars($row['fecha_vencimiento'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="seccion">Métricas de Consumo Promedio</div>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Total Salidas</th>
                <th>Total Entradas</th>
                <th>Promedio Salida</th>
                <th>Número Salidas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($metricas)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Sin datos de consumo para el periodo</td>
                </tr>
            <?php else: ?>
                <?php foreach ($metricas as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                        <td><?= (int)($row['total_salidas'] ?? 0) ?></td>
                        <td><?= (int)($row['total_entradas'] ?? 0) ?></td>
                        <td><?= number_format((float)($row['promedio_salida'] ?? 0), 2) ?></td>
                        <td><?= (int)($row['numero_salidas'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="seccion">Productos Más Usados</div>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Total Salidas</th>
                <th>Número Salidas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($masUsados)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">Sin datos de consumo para el periodo</td>
                </tr>
            <?php else: ?>
                <?php foreach ($masUsados as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                        <td><?= (int)($row['total_salidas'] ?? 0) ?></td>
                        <td><?= (int)($row['numero_salidas'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="seccion">Items con Alertas de Stock</div>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Stock Mín.</th>
                <th>Nivel Alerta</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alertas)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Sin alertas de stock para el periodo</td>
                </tr>
            <?php else: ?>
                <?php foreach ($alertas as $row): ?>
                    <?php
                    $clase = '';
                    if (($row['nivel_alerta'] ?? '') === 'CRITICO') {
                        $clase = 'critico';
                    } elseif (($row['nivel_alerta'] ?? '') === 'ALERTA') {
                        $clase = 'alerta';
                    }
                    ?>
                    <tr class="<?= $clase ?>">
                        <td><?= htmlspecialchars($row['nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                        <td><?= (int)($row['cantidad'] ?? 0) ?></td>
                        <td><?= (int)($row['stock_minimo'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($row['nivel_alerta'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
