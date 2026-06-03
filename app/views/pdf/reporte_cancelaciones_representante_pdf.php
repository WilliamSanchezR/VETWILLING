<?php
$meta = $payload['meta'] ?? [];
$motivos = $payload['motivos_cancelacion'] ?? [];
$resumen = $payload['resumen_estados'] ?? [];
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
    <h1>Resumen de Cancelaciones</h1>
    <div class="subtitulo">
        Periodo: <?= htmlspecialchars($meta['etiqueta_periodo'] ?? '') ?>
        (<?= htmlspecialchars($meta['fecha_inicio'] ?? '') ?> a <?= htmlspecialchars($meta['fecha_fin'] ?? '') ?>)
    </div>

    <div class="seccion">Resumen de Estados</div>
    <table class="resumen">
        <tr>
            <td>Total Citas<br><strong><?= (int)($resumen['total'] ?? 0) ?></strong></td>
            <td>Atendidas<br><strong><?= (int)($resumen['atendidas'] ?? 0) ?></strong></td>
            <td>Canceladas<br><strong><?= (int)($resumen['canceladas'] ?? 0) ?></strong></td>
            <td>Pendientes<br><strong><?= (int)($resumen['pendientes'] ?? 0) ?></strong></td>
        </tr>
    </table>

    <div class="seccion">Motivos de Cancelacion</div>
    <table>
        <thead>
            <tr>
                <th>Motivo</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($motivos)): ?>
                <tr>
                    <td colspan="2" style="text-align:center;">Sin cancelaciones registradas</td>
                </tr>
            <?php else: ?>
                <?php foreach ($motivos as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['motivo'] ?? '') ?></td>
                        <td><?= (int)($row['total'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
