<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha clínica completa por mascota</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            margin: 0 0 6px 0;
            font-size: 18px;
            color: #0a932c;
        }

        h2 {
            margin: 16px 0 8px 0;
            font-size: 14px;
            color: #0a932c;
        }

        .meta {
            margin-bottom: 10px;
            color: #4b5563;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        .grid th {
            background: #f3f4f6;
        }

        .texto-vacio {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>

<body>
    <?php
    $ficha = $payload['ficha'] ?? [];
    $paciente = $ficha['paciente'] ?? [];
    $historialClinico = $ficha['historial_clinico'] ?? [];
    $vacunas = $ficha['vacunas'] ?? [];
    $tratamientos = $ficha['tratamientos'] ?? [];
    $consultas = $ficha['consultas'] ?? [];
    $notas = $ficha['notas'] ?? [];
    ?>

    <h1>Ficha clínica completa por mascota</h1>
    <div class="meta">
        Generado el: <strong><?= htmlspecialchars($payload['meta']['fecha_generacion'] ?? '') ?></strong>
    </div>

    <h2>Datos del paciente</h2>
    <table class="grid">
        <tbody>
            <tr>
                <th>Nombre</th>
                <td><?= htmlspecialchars($paciente['nombre'] ?? 'N/A') ?></td>
                <th>Especie</th>
                <td><?= htmlspecialchars($paciente['especie'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Raza</th>
                <td><?= htmlspecialchars($paciente['raza'] ?? 'N/A') ?></td>
                <th>Sexo</th>
                <td><?= htmlspecialchars($paciente['sexo'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Edad</th>
                <td><?= htmlspecialchars(($paciente['edad_numero'] ?? 'N/A') . ' ' . ($paciente['edad_unidad'] ?? '')) ?></td>
                <th>Propietario</th>
                <td><?= htmlspecialchars($paciente['propietario_nombre'] ?? 'N/A') ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Historial clínico</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Diagnóstico</th>
                <th>Tratamiento</th>
                <th>Medicación</th>
                <th>Observaciones</th>
                <th>Versión</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($historialClinico)): ?>
                <?php foreach ($historialClinico as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars(!empty($item['fecha_atencion']) ? substr($item['fecha_atencion'], 0, 16) : 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['motivo_consulta'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['diagnostico'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['tratamientos_aplicados'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['medicacion_recetada'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['observaciones_adicionales'] ?? '') ?></td>
                        <td><?= htmlspecialchars(!empty($item['version_registro']) ? ('v' . $item['version_registro']) : 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="texto-vacio">Sin historial clínico registrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Vacunas</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Dosis</th>
                <th>Fecha aplicación</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vacunas)): ?>
                <?php foreach ($vacunas as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['tipo_vacuna'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['dosis'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['fecha_aplicacion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="texto-vacio">Sin vacunas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Tratamientos</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Medicamento</th>
                <th>Dosis</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tratamientos)): ?>
                <?php foreach ($tratamientos as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['medicamento'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['dosis'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['fecha_inicio'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['fecha_fin'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['estado'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="texto-vacio">Sin tratamientos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Consultas</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Fecha consulta</th>
                <th>Motivo</th>
                <th>Diagnóstico</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($consultas)): ?>
                <?php foreach ($consultas as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['fecha_consulta'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['motivo'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['diagnostico'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="texto-vacio">Sin consultas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Notas clínicas</h2>
    <table class="grid">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Nota</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($notas)): ?>
                <?php foreach ($notas as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['created_at'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['nota'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="texto-vacio">Sin notas clínicas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
