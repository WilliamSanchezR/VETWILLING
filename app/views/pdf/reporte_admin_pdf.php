<?php
// Vista PDF para reporte administrativo (RFS 14)
// Variables disponibles: $payload, $resumen, $estados, $desempeno, $inventario, $productos, $topVets, $meta
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Administrativo</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        .kpi-row { display: flex; gap: 16px; margin-bottom: 12px; }
        .kpi { background: #f5f5f5; border-radius: 6px; padding: 10px 18px; min-width: 120px; text-align: center; }
        .kpi .label { font-size: 11px; color: #666; }
        .kpi .value { font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; }
        th { background: #e9ecef; font-weight: bold; }
        .section { margin-bottom: 22px; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 11px; }
        .ok { background: #d1fae5; color: #065f46; }
        .warn { background: #fef3c7; color: #92400e; }
        .danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Reporte Administrativo</h1>
    <div style="font-size:13px; margin-bottom:10px;">
        <strong>Período:</strong> <?= htmlspecialchars($meta['etiqueta'] ?? '') ?>
        <?php if (!empty($meta['fecha_inicio']) && !empty($meta['fecha_fin'])): ?>
            (<?= htmlspecialchars($meta['fecha_inicio']) ?> a <?= htmlspecialchars($meta['fecha_fin']) ?>)
        <?php endif; ?>
    </div>

    <!-- KPIs principales -->
    <div class="kpi-row">
        <div class="kpi"><div class="label">Ingresos</div><div class="value">$<?= number_format($resumen['ingresos'] ?? 0, 0, ',', '.') ?></div></div>
        <div class="kpi"><div class="label">Total Citas</div><div class="value"><?= $resumen['total_citas'] ?? 0 ?></div></div>
        <div class="kpi"><div class="label">Citas Atendidas</div><div class="value"><?= $estados['atendidas'] ?? 0 ?></div></div>
        <div class="kpi"><div class="label">Canceladas</div><div class="value"><?= $estados['canceladas'] ?? 0 ?></div></div>
        <div class="kpi"><div class="label">Cumplimiento</div><div class="value"><?= number_format($resumen['tasa_cumplimiento'] ?? 0, 1) ?>%</div></div>
        <div class="kpi"><div class="label">Pacientes</div><div class="value"><?= $resumen['total_pacientes'] ?? 0 ?></div></div>
    </div>

    <!-- Ranking de veterinarias -->
    <div class="section">
        <h2>Ranking de Veterinarias por Ingresos</h2>
        <table>
            <thead><tr><th>#</th><th>Veterinaria</th><th>Ingresos</th></tr></thead>
            <tbody>
            <?php foreach ($topVets as $i => $vet): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($vet['nombre_veterinaria'] ?? '') ?></td>
                    <td>$<?= number_format($vet['ingresos'] ?? 0, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Desempeño del personal -->
    <div class="section">
        <h2>Desempeño del Personal</h2>
        <table>
            <thead><tr><th>Profesional</th><th>Veterinaria</th><th>Citas Atendidas</th><th>Cumplimiento</th><th>Prom. Minutos/Cita</th></tr></thead>
            <tbody>
            <?php foreach ($desempeno as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre_profesional'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['nombre_veterinaria'] ?? '') ?></td>
                    <td><?= $p['citas_atendidas'] ?? 0 ?></td>
                    <td><?= number_format($p['tasa_cumplimiento'] ?? 0, 1) ?>%</td>
                    <td><?= number_format($p['promedio_minutos'] ?? 0, 1) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Inventario -->
    <div class="section">
        <h2>Inventario Global</h2>
        <div class="kpi-row">
            <div class="kpi"><div class="label">Total Productos</div><div class="value"><?= $inventario['total_productos'] ?? 0 ?></div></div>
            <div class="kpi"><div class="label">Vigentes</div><div class="value"><?= $inventario['vigentes'] ?? 0 ?></div></div>
            <div class="kpi"><div class="label">Por Vencer (30d)</div><div class="value"><?= $inventario['por_vencer'] ?? 0 ?></div></div>
            <div class="kpi"><div class="label">Vencidos</div><div class="value"><?= $inventario['vencidos'] ?? 0 ?></div></div>
        </div>
        <h3 style="margin-top:12px;">Productos próximos a vencer (60 días)</h3>
        <table>
            <thead><tr><th>Producto</th><th>Veterinaria</th><th>Categoría</th><th>Lote</th><th>Cantidad</th><th>Vence</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($productos as $p):
                $dias = (int)($p['dias_restantes'] ?? 0);
                if ($dias < 0)        { $badge = 'danger'; $label = 'Vencido'; }
                else if ($dias <= 15) { $badge = 'warn';   $label = 'Crítico'; }
                else                  { $badge = 'ok';     $label = 'Próximo'; }
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['nombre_veterinaria'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['categoria'] ?? '') ?></td>
                    <td><?= htmlspecialchars($p['numero_lote'] ?? '') ?></td>
                    <td><?= $p['cantidad'] ?? 0 ?></td>
                    <td><?= htmlspecialchars($p['fecha_vencimiento'] ?? '') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $label ?> (<?= $dias ?>d)</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="font-size:10px; color:#888; text-align:right; margin-top:18px;">
        Generado por VetWilling - <?= date('Y-m-d H:i') ?>
    </div>
</body>
</html>
