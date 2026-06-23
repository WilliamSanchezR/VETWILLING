<?php
/**
 * Vista PDF — Historial médico (Dompdf)
 * Variables esperadas: $paciente, $citas, $historial_clinico, $vacunas,
 *                       $tratamientos, $fechaGeneracion, $rutaFoto
 */
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 80px 25px 50px 25px; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        color: #373a38;
        font-size: 10px;
    }

    /* ── Encabezado fijo ── */
    header {
        position: fixed;
        top: -60px;
        left: 0;
        right: 0;
        height: 60px;
        background-color: #0d5e8a;
        color: #fff;
        padding: 14px 24px;
    }
    header .titulo { font-size: 16px; font-weight: bold; margin: 0; }
    header .subtitulo { font-size: 9px; color: #dceaf2; margin: 4px 0 0 0; }
    header .nombre-mascota {
        position: absolute; top: 32px; right: 24px;
        font-size: 11px; font-weight: bold; color: #fff;
    }

    /* ── Pie fijo ──
       IMPORTANTE: NUNCA usar float dentro de un elemento position:fixed
       en Dompdf — rompe por completo el cálculo de paginación y genera
       páginas en blanco. Usamos una tabla en su lugar. */
    footer {
        position: fixed;
        bottom: -50px;
        left: 0;
        right: 0;
        height: 40px;
        border-top: 0.5px solid #dce2e6;
        padding-top: 8px;
        font-size: 8px;
        color: #787a78;
    }
    footer table { width: 100%; }
    footer .confidencial { text-align: left; }

    /* ── Tarjeta de mascota ── */
    .tarjeta-mascota {
        background-color: #f4f7f9;
        border: 1px solid #dce2e6;
        padding: 12px;
        margin-bottom: 15px;
    }
    .info-mascota { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .info-mascota td { padding: 5px; vertical-align: top; }
    .tarjeta-mascota .nombre-mascota { font-size: 15px; font-weight: bold; color: #073c54; margin-bottom: 5px; }
    .tarjeta-mascota .subinfo { font-size: 9px; color: #787a78; margin-bottom: 8px; }
    .tarjeta-mascota .dato-label { font-size: 8px; color: #787a78; }
    .tarjeta-mascota .dato-valor { font-size: 10px; font-weight: bold; color: #373a38; }

    /* ── Secciones ── */
    .seccion-titulo { font-size: 12.5px; font-weight: bold; padding: 4px 0 6px 0; margin-top: 6px; }
    .seccion-titulo .badge-num {
        display: inline-block; width: 16px; height: 16px;
        border-radius: 8px; color: #fff; font-size: 9px;
        text-align: center; line-height: 16px; margin-right: 6px;
    }

    table.reporte { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    table.reporte thead th { color: #ffffff; font-size: 9px; text-align: left; padding: 7px 6px; }
    table.reporte tbody td { font-size: 8.7px; padding: 6px 6px; border-bottom: 0.5px solid #e6e9eb; }
    table.reporte tbody tr:nth-child(even) { background-color: #f7f9fa; }

    .citas thead th       { background-color: #108c78; }
    .historial thead th   { background-color: #634ea8; }
    .vacunas thead th      { background-color: #388e3c; }
    .tratamientos thead th { background-color: #c46e12; }

    .citas .seccion-titulo       { color: #108c78; }
    .historial .seccion-titulo   { color: #634ea8; }
    .vacunas .seccion-titulo      { color: #388e3c; }
    .tratamientos .seccion-titulo { color: #c46e12; }

    .citas .badge-num       { background-color: #108c78; }
    .historial .badge-num   { background-color: #634ea8; }
    .vacunas .badge-num      { background-color: #388e3c; }
    .tratamientos .badge-num { background-color: #c46e12; }

    .vacio { color: #9a9c99; font-size: 9px; font-style: italic; padding: 6px 0; }
</style>
</head>
<body>

    <header>
        <p class="titulo">Historial médico</p>
        <p class="subtitulo">VetWilling &middot; Sistema de gestión veterinaria</p>
        <span class="nombre-mascota"><?= e($paciente['nombre'] ?? '') ?></span>
    </header>

    <footer>
        <table>
            <tr>
                <td class="confidencial">Documento confidencial &middot; Uso exclusivo del propietario registrado</td>
            </tr>
        </table>
    </footer>

    <!-- ── Tarjeta de la mascota ── -->
    <div class="tarjeta-mascota">
        <div class="nombre-mascota"><?= e($paciente['nombre'] ?? '—') ?></div>
        <div class="subinfo">
            <?= e($paciente['especie'] ?? '—') ?> &middot;
            <?= e($paciente['raza'] ?? '—') ?> &middot;
            <?= e($paciente['sexo'] ?? '—') ?>
        </div>
        <table class="info-mascota">
            <tr>
                <td width="33%">
                    <div class="dato-label">Edad</div>
                    <div class="dato-valor"><?= e(trim(($paciente['edad_numero'] ?? '') . ' ' . ($paciente['edad_unidad'] ?? ''))) ?></div>
                </td>
                <td width="33%">
                    <div class="dato-label">Peso</div>
                    <div class="dato-valor"><?= !empty($paciente['peso']) ? e($paciente['peso']) . ' kg' : '—' ?></div>
                </td>
                <td width="34%">
                    <div class="dato-label">Estado de salud</div>
                    <div class="dato-valor"><?= e($paciente['estado_salud'] ?? '—') ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── 1. Citas ── -->
    <div class="citas">
        <p class="seccion-titulo"><span class="badge-num">1</span>Citas</p>
        <?php if (!empty($citas)): ?>
        <table class="reporte citas">
            <thead><tr><th>Fecha</th><th>Servicio</th><th>Tipo</th><th>Estado</th><th>Observaciones</th></tr></thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime($c['fecha_hora']))) ?></td>
                    <td><?= e($c['servicio_nombre'] ?? $c['tipo'] ?? '—') ?></td>
                    <td><?= e($c['tipo'] ?? '—') ?></td>
                    <td><?= e($c['estado'] ?? '—') ?></td>
                    <td><?= e($c['observaciones'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="vacio">No hay citas registradas.</p>
        <?php endif; ?>
    </div>

    <!-- ── 2. Historial clínico ── -->
    <div class="historial">
        <p class="seccion-titulo"><span class="badge-num">2</span>Historial clínico</p>
        <?php if (!empty($historial_clinico)): ?>
        <table class="reporte historial">
            <thead><tr><th>Fecha</th><th>Motivo</th><th>Diagnóstico</th><th>Tratamientos</th><th>Profesional</th></tr></thead>
            <tbody>
                <?php foreach ($historial_clinico as $h): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime($h['fecha_atencion']))) ?></td>
                    <td><?= e($h['motivo_consulta'] ?? '—') ?></td>
                    <td><?= e($h['diagnostico'] ?? '—') ?></td>
                    <td><?= e($h['tratamientos_aplicados'] ?? '—') ?></td>
                    <td><?= e($h['profesional_nombre'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="vacio">No hay historial clínico registrado.</p>
        <?php endif; ?>
    </div>

    <!-- ── 3. Vacunas ── -->
    <div class="vacunas">
        <p class="seccion-titulo"><span class="badge-num">3</span>Vacunas</p>
        <?php if (!empty($vacunas)): ?>
        <table class="reporte vacunas">
            <thead><tr><th>Vacuna</th><th>Dosis</th><th>Fecha</th><th>Profesional</th><th>Observaciones</th></tr></thead>
            <tbody>
                <?php foreach ($vacunas as $v): ?>
                <tr>
                    <td><?= e($v['tipo_vacuna'] ?? '—') ?></td>
                    <td><?= e($v['dosis'] ?? '—') ?></td>
                    <td><?= e(date('d/m/Y', strtotime($v['fecha_aplicacion']))) ?></td>
                    <td><?= e($v['profesional_nombre'] ?? '—') ?></td>
                    <td><?= e($v['observaciones'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="vacio">No hay vacunas registradas.</p>
        <?php endif; ?>
    </div>

    <!-- ── 4. Tratamientos ── -->
    <div class="tratamientos">
        <p class="seccion-titulo"><span class="badge-num">4</span>Tratamientos</p>
        <?php if (!empty($tratamientos)): ?>
        <table class="reporte tratamientos">
            <thead><tr><th>Medicamento</th><th>Dosis</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Profesional</th></tr></thead>
            <tbody>
                <?php foreach ($tratamientos as $t): ?>
                <tr>
                    <td><?= e($t['medicamento'] ?? '—') ?></td>
                    <td><?= e($t['dosis'] ?? '—') ?></td>
                    <td><?= e(date('d/m/Y', strtotime($t['fecha_inicio']))) ?></td>
                    <td><?= !empty($t['fecha_fin']) ? e(date('d/m/Y', strtotime($t['fecha_fin']))) : '—' ?></td>
                    <td><?= e($t['estado'] ?? '—') ?></td>
                    <td><?= e($t['profesional_nombre'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="vacio">No hay tratamientos registrados.</p>
        <?php endif; ?>
    </div>

</body>
</html>