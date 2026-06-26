<?php
$meta    = $payload['meta']    ?? [];
$resumen = $payload['resumen'] ?? [];
$ventas  = $payload['ventas']  ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body     { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1       { font-size: 18px; margin: 0 0 6px; color: #0a932c; }
        .subtitulo { font-size: 11px; color: #6b7280; margin-bottom: 12px; }
        .seccion { font-size: 12px; font-weight: bold; color: #0a932c; background: #f0fdf4;
                   padding: 6px; border-left: 4px solid #0a932c; margin-top: 14px; }
        table    { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th       { background: #0a932c; color: #fff; text-transform: uppercase; font-size: 10px;
                   padding: 6px; border: 1px solid #088a28; }
        td       { border: 1px solid #e5e7eb; padding: 6px; font-size: 10px; }
        .resumen td { text-align: center; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge-ok  { color: #166534; font-weight: bold; }
        .badge-ko  { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Historial de Ventas — VetWilling</h1>
    <div class="subtitulo">
        Período: <?= htmlspecialchars($meta['etiqueta_periodo'] ?? '') ?>
        (<?= htmlspecialchars($meta['fecha_inicio'] ?? '') ?> a <?= htmlspecialchars($meta['fecha_fin'] ?? '') ?>)
        <br>
        Estado: <?= htmlspecialchars($meta['estado'] ?? 'todos') ?>
        &nbsp;|&nbsp;
        Producto: <?= htmlspecialchars($meta['producto'] ?: 'Todos') ?>
    </div>

    <div class="seccion">Resumen</div>
    <table class="resumen">
        <tr>
            <td>Total Ventas<br><strong><?= (int) ($resumen['total_ventas'] ?? 0) ?></strong></td>
            <td>Monto Total<br><strong>$<?= number_format((float) ($resumen['monto_total'] ?? 0), 2) ?></strong></td>
            <td>Descuentos<br><strong>$<?= number_format((float) ($resumen['total_descuentos'] ?? 0), 2) ?></strong></td>
            <td>Impuestos<br><strong>$<?= number_format((float) ($resumen['total_impuestos'] ?? 0), 2) ?></strong></td>
            <td>Prom. por Venta<br><strong>$<?= number_format((float) ($resumen['promedio_venta'] ?? 0), 2) ?></strong></td>
        </tr>
    </table>

    <div class="seccion">Detalle de Ventas</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Subtotal</th>
                <th>Descuento</th>
                <th>Impuesto</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ventas)): ?>
                <tr><td colspan="9" style="text-align:center;">Sin ventas para el período seleccionado</td></tr>
            <?php else: ?>
                <?php foreach ($ventas as $v): ?>
                <tr>
                    <td>#<?= (int) $v['id_venta'] ?></td>
                    <td><?= htmlspecialchars($v['fecha_venta'] ?? '') ?></td>
                    <td><?= htmlspecialchars($v['cliente'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['usuario'] ?? '') ?></td>
                    <td>$<?= number_format((float) ($v['subtotal']  ?? 0), 2) ?></td>
                    <td>$<?= number_format((float) ($v['descuento'] ?? 0), 2) ?></td>
                    <td>$<?= number_format((float) ($v['impuesto']  ?? 0), 2) ?></td>
                    <td><strong>$<?= number_format((float) ($v['total'] ?? 0), 2) ?></strong></td>
                    <td class="<?= ($v['estado'] ?? '') === 'anulada' ? 'badge-ko' : 'badge-ok' ?>">
                        <?= htmlspecialchars(ucfirst($v['estado'] ?? 'completada')) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
