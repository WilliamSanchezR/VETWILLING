<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte resumen veterinario</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        h1 {
            margin: 0 0 6px 0;
            font-size: 20px;
            color: #0a932c;
        }

        h2 {
            margin: 18px 0 8px 0;
            font-size: 15px;
            color: #0a932c;
        }

        .meta {
            margin-bottom: 12px;
            color: #4b5563;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }

        .grid th {
            background: #f3f4f6;
        }

        .chip {
            display: inline-block;
            padding: 4px 8px;
            background: #ecfdf3;
            border: 1px solid #86efac;
            border-radius: 4px;
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <h1>Reporte Resumen del Veterinario</h1>
    <div class="meta">
        Periodo: <strong><?= htmlspecialchars($payload['meta']['etiqueta_periodo']) ?></strong>
        (<?= htmlspecialchars($payload['meta']['fecha_inicio']) ?> a <?= htmlspecialchars($payload['meta']['fecha_fin']) ?>)
    </div>

    <div>
        <span class="chip">Ingresos: $<?= number_format((float)$payload['resumen']['ingresos_totales'], 0, ',', '.') ?></span>
        <span class="chip">Citas atendidas: <?= (int)$payload['resumen']['citas_atendidas'] ?></span>
        <span class="chip">Nuevos pacientes: <?= (int)$payload['resumen']['nuevos_pacientes'] ?></span>
        <span class="chip">Cumplimiento: <?= number_format((float)$payload['resumen']['cumplimiento'], 1) ?>%</span>
    </div>

    <h2>Servicios más solicitados</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Citas</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['servicios'])): ?>
                <?php foreach ($payload['servicios'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td><?= (int)$item['total'] ?></td>
                        <td><?= number_format((float)$item['porcentaje'], 1) ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos para el periodo seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Tratamientos más realizados</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Tratamiento</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['tratamientos'])): ?>
                <?php foreach ($payload['tratamientos'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td><?= (int)$item['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Sin datos para el periodo seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Pacientes por especie</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Especie</th>
                <th>Total</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['especies'])): ?>
                <?php foreach ($payload['especies'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['especie']) ?></td>
                        <td><?= (int)$item['total'] ?></td>
                        <td><?= number_format((float)$item['porcentaje'], 1) ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Sin datos para el periodo seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Pacientes asignados activos</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Especie / Raza</th>
                <th>Propietario</th>
                <th>Fecha inicio asignación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['asignaciones_activas'])): ?>
                <?php foreach ($payload['asignaciones_activas'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['paciente_nombre']) ?></td>
                        <td><?= htmlspecialchars(($item['especie'] ?? 'N/A') . ' / ' . ($item['raza'] ?? 'N/A')) ?></td>
                        <td><?= htmlspecialchars($item['propietario_nombre'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['fecha_inicio'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Sin pacientes asignados activos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Historial de asignaciones (periodo)</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Estado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['historial_asignaciones'])): ?>
                <?php foreach ($payload['historial_asignaciones'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['paciente_nombre']) ?></td>
                        <td><?= htmlspecialchars($item['estado']) ?></td>
                        <td><?= htmlspecialchars($item['fecha_inicio'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['fecha_fin'] ?? 'Activo') ?></td>
                        <td><?= htmlspecialchars($item['motivo_cambio'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Sin historial de asignaciones en el periodo.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>