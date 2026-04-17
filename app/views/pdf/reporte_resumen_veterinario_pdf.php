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

        .resumen-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .resumen-table td {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            text-align: center;
            font-size: 11px;
        }

        .resumen-table .label {
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .resumen-table .valor {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }

        .bg-green {
            background: #ecfdf3;
        }

        .bg-red {
            background: #fef2f2;
        }

        .bg-yellow {
            background: #fffbeb;
        }

        .bg-blue {
            background: #f0f4ff;
        }

        .bg-gray {
            background: #f9fafb;
        }

        .badge-estado {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-atendida {
            background: #ecfdf3;
            color: #059669;
        }

        .badge-cancelada {
            background: #fef2f2;
            color: #dc2626;
        }

        .badge-pendiente {
            background: #fffbeb;
            color: #d97706;
        }
    </style>
</head>

<body>
    <h1>Reporte Resumen del Veterinario</h1>
    <div class="meta">
        Periodo: <strong><?= htmlspecialchars($payload['meta']['etiqueta_periodo']) ?></strong>
        (<?= htmlspecialchars($payload['meta']['fecha_inicio']) ?> a <?= htmlspecialchars($payload['meta']['fecha_fin']) ?>)
        <?php if (!empty($payload['meta']['filtros']['estado_cita'])): ?>
            &nbsp;| Filtro estado: <strong><?= htmlspecialchars($payload['meta']['filtros']['estado_cita']) ?></strong>
        <?php endif; ?>
    </div>

    <table class="resumen-table">
        <tr>
            <td class="bg-green">
                <div class="label">Ingresos</div>
                <div class="valor">$<?= number_format((float)$payload['resumen']['ingresos_totales'], 0, ',', '.') ?></div>
            </td>
            <td class="bg-green">
                <div class="label">Citas atendidas</div>
                <div class="valor"><?= (int)$payload['resumen']['citas_atendidas'] ?></div>
            </td>
            <td class="bg-red">
                <div class="label">Citas canceladas</div>
                <div class="valor"><?= (int)($payload['resumen_estados']['canceladas'] ?? 0) ?></div>
            </td>
            <td class="bg-yellow">
                <div class="label">Citas pendientes</div>
                <div class="valor"><?= (int)($payload['resumen_estados']['pendientes'] ?? 0) ?></div>
            </td>
        </tr>
        <tr>
            <td class="bg-blue">
                <div class="label">Total citas</div>
                <div class="valor"><?= (int)($payload['resumen_estados']['total'] ?? 0) ?></div>
            </td>
            <td class="bg-gray">
                <div class="label">Nuevos pacientes</div>
                <div class="valor"><?= (int)$payload['resumen']['nuevos_pacientes'] ?></div>
            </td>
            <td class="bg-gray">
                <div class="label">Cumplimiento</div>
                <div class="valor"><?= number_format((float)$payload['resumen']['cumplimiento'], 1) ?>%</div>
            </td>
            <td></td>
        </tr>
    </table>

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

    <h2>Detalle de citas</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Propietario</th>
                <th>Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['detalle_citas'])): ?>
                <?php foreach ($payload['detalle_citas'] as $cita): ?>
                    <?php
                    $estado = strtoupper($cita['estado'] ?? 'PENDIENTE');
                    $claseEstado = 'badge-pendiente';
                    if ($estado === 'ATENDIDA') $claseEstado = 'badge-atendida';
                    elseif ($estado === 'CANCELADA') $claseEstado = 'badge-cancelada';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cita['fecha'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($cita['paciente'] ?? 'Sin paciente') ?></td>
                        <td><?= htmlspecialchars($cita['propietario'] ?? 'Sin propietario') ?></td>
                        <td><?= htmlspecialchars($cita['servicio'] ?? 'Sin servicio') ?></td>
                        <td><span class="badge-estado <?= $claseEstado ?>"><?= $estado ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Sin citas para el periodo seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>