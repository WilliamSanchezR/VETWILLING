<?php
$meta = $payload['meta'] ?? [];
$totales = $payload['totales'] ?? [];
$servicios = $payload['servicios'] ?? [];
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
    </style>
</head>

<body>
    <h1>Reporte de Servicios</h1>
    <div class="subtitulo">
        Periodo: <?= htmlspecialchars($meta['etiqueta_periodo'] ?? '') ?>
        (<?= htmlspecialchars($meta['fecha_inicio'] ?? '') ?> a <?= htmlspecialchars($meta['fecha_fin'] ?? '') ?>)
    </div>

    <div class="seccion">Resumen</div>
    <table class="resumen">
        <tr>
            <td>Total Citas<br><strong><?= (int)($totales['total_citas'] ?? 0) ?></strong></td>
            <td>Atendidas<br><strong><?= (int)($totales['atendidas'] ?? 0) ?></strong></td>
            <td>Canceladas<br><strong><?= (int)($totales['canceladas'] ?? 0) ?></strong></td>
            <td>Ingresos<br><strong>$<?= number_format((float)($totales['ingresos'] ?? 0), 0, ',', '.') ?></strong></td>
        </tr>
    </table>

    <div class="seccion">Detalle por Servicio</div>
    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Total Citas</th>
                <th>Atendidas</th>
                <th>Canceladas</th>
                <th>Ingresos</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($servicios)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Sin datos para el periodo seleccionado</td>
                </tr>
            <?php else: ?>
                <?php foreach ($servicios as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['servicio'] ?? '') ?></td>
                        <td><?= (int)($row['total_citas'] ?? 0) ?></td>
                        <td><?= (int)($row['atendidas'] ?? 0) ?></td>
                        <td><?= (int)($row['canceladas'] ?? 0) ?></td>
                        <td>$<?= number_format((float)($row['ingresos'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
