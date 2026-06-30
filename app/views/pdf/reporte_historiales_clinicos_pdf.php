<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de historiales clínicos</title>
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

        .chip {
            display: inline-block;
            padding: 4px 8px;
            background: #ecfdf3;
            border: 1px solid #86efac;
            border-radius: 4px;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }

        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
            font-size: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .grid th {
            background: #f3f4f6;
            font-size: 10px;
            font-weight: bold;
        }

        /* Anchos explícitos por columna */
        .col-paciente    { width: 8%; }
        .col-especie     { width: 9%; }
        .col-fecha       { width: 9%; }
        .col-veterinario { width: 11%; }
        .col-motivo      { width: 10%; }
        .col-diagnostico { width: 10%; }
        .col-tratamiento { width: 10%; }
        .col-medicacion  { width: 9%; }
        .col-obs         { width: 20%; }
        .col-version     { width: 4%; text-align: center; }

        .texto-vacio {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h1>Reporte de Historiales Clínicos</h1>

    <div class="meta">
        Generado el: <strong><?= htmlspecialchars($payload['meta']['fecha_generacion']) ?></strong>
    </div>

    <div>
        <span class="chip">Paciente: <?= htmlspecialchars($payload['meta']['filtros']['paciente'] !== '' ? $payload['meta']['filtros']['paciente'] : 'Todos') ?></span>
        <span class="chip">Fecha: <?= htmlspecialchars($payload['meta']['filtros']['fecha'] !== '' ? $payload['meta']['filtros']['fecha'] : 'Todas') ?></span>
        <span class="chip">Veterinario: <?= htmlspecialchars($payload['meta']['filtros']['veterinario'] !== '' ? $payload['meta']['filtros']['veterinario'] : 'Todos') ?></span>
    </div>

    <h2>Resumen</h2>
    <table class="grid">
        <tbody>
            <tr>
                <th>Total historiales</th>
                <td><?= (int) count($payload['historiales']) ?></td>
            </tr>
            <tr>
                <th>Registros con diagnóstico</th>
                <td><?= (int) count(array_filter($payload['historiales'], function ($item) {
                        return !empty($item['diagnostico']);
                    })) ?></td>
            </tr>
            <tr>
                <th>Registros con medicación</th>
                <td><?= (int) count(array_filter($payload['historiales'], function ($item) {
                        return !empty($item['medicacion_recetada']);
                    })) ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Detalle de historiales clínicos</h2>
    <table class="grid">
        <thead>
            <tr>
                <th class="col-paciente">Paciente</th>
                <th class="col-especie">Especie / Raza</th>
                <th class="col-fecha">Fecha atención</th>
                <th class="col-veterinario">Veterinario</th>
                <th class="col-motivo">Motivo</th>
                <th class="col-diagnostico">Diagnóstico</th>
                <th class="col-tratamiento">Tratamiento</th>
                <th class="col-medicacion">Medicación</th>
                <th class="col-obs">Observaciones</th>
                <th class="col-version">Ver.</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payload['historiales'])): ?>
                <?php foreach ($payload['historiales'] as $item): ?>
                    <tr>
                        <td class="col-paciente"><?= htmlspecialchars($item['paciente_nombre'] ?? 'N/A') ?></td>
                        <td class="col-especie"><?= htmlspecialchars(($item['especie'] ?? 'N/A') . ' / ' . ($item['raza'] ?? 'N/A')) ?></td>
                        <td class="col-fecha"><?= htmlspecialchars(!empty($item['fecha_atencion']) ? substr($item['fecha_atencion'], 0, 16) : 'N/A') ?></td>
                        <td class="col-veterinario"><?= htmlspecialchars($item['veterinario_responsable'] ?? 'N/A') ?></td>
                        <td class="col-motivo"><?= htmlspecialchars($item['motivo_consulta'] ?? '') ?></td>
                        <td class="col-diagnostico"><?= htmlspecialchars($item['diagnostico'] ?? '') ?></td>
                        <td class="col-tratamiento"><?= htmlspecialchars($item['tratamientos_aplicados'] ?? '') ?></td>
                        <td class="col-medicacion"><?= htmlspecialchars($item['medicacion_recetada'] ?? '') ?></td>
                        <td class="col-obs"><?= htmlspecialchars($item['observaciones_adicionales'] ?? '') ?></td>
                        <td class="col-version"><?= htmlspecialchars(!empty($item['version_registro']) ? ('v' . $item['version_registro']) : 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="texto-vacio">No hay historiales clínicos para los filtros seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
