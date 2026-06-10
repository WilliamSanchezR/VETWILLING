<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Servicios — VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon"        href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet"  href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet"  href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet"  href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet"  href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
    <link rel="stylesheet"  href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleReportesAdmin.css">
    <style>
        /* Fallback local para asegurar que el contenido no ocupe todo el ancho
           cuando falten reglas específicas en otros CSS cargados */
        .contenido-principal {
            margin-left: var(--sidebar-width, 260px);
            min-height: calc(100vh - var(--navbar-height, 70px));
            padding-top: calc(var(--navbar-height, 70px) + 20px);
            transition: margin-left 0.22s ease;
        }
        #sidebar.collapsed ~ .contenido-principal,
        .sidebar.collapsed ~ .contenido-principal {
            margin-left: var(--sidebar-collapsed-width, 80px);
        }
    </style>
</head>

<body   
    data-api-url="<?= BASE_URL ?>/representante/reportes-servicios/data"
    data-pdf-url="<?= BASE_URL ?>/representante/reportes-servicios/pdf"
    data-excel-url="<?= BASE_URL ?>/representante/reportes-servicios/excel"
>

    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php'; ?>

    <style>
        /* Forzar margen del contenido después de que se carguen estilos del sidebar */
        .contenido-principal {
            margin-left: var(--sidebar-width, 260px) !important;
            padding-top: calc(var(--navbar-height, 70px) + 20px) !important;
            min-height: calc(100vh - var(--navbar-height, 70px)) !important;
            box-sizing: border-box !important;
        }
        #sidebar.collapsed ~ .contenido-principal,
        .sidebar.collapsed ~ .contenido-principal {
            margin-left: var(--sidebar-collapsed-width, 80px) !important;
        }
    </style>

    <!-- ═══════════════════════════════ CONTENIDO PRINCIPAL ═══════════════════ -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php'; ?>

        <div class="rep-wrapper">

            <!-- ── Encabezado ─────────────────────────────────────────────── -->
            <header class="rep-header">
                <div class="rep-header__meta">
                    <h1 class="rep-header__titulo">
                        <i class="bi bi-graph-up-arrow" style="color: var(--clr-primary-600); margin-right: .35rem; font-size: 1.25rem; vertical-align: -2px;"></i>
                        Reporte de Servicios
                    </h1>
                    <p class="rep-header__subtitulo">
                        Resumen consolidado de citas, atención e ingresos por veterinaria
                    </p>
                    <span class="rep-header__periodo" id="labelPeriodo">
                        <i class="bi bi-calendar3" style="font-size: .65rem;"></i>
                        Cargando período...
                    </span>
                </div>

                <div class="rep-header__acciones">
                    <select class="rep-sel-vet" id="selVeterinaria" title="Filtrar por veterinaria">
                        <option value="">Todas las veterinarias</option>
                    </select>
                    <button class="btn-exportar btn-exportar--pdf" onclick="exportarPDF()" title="Exportar a PDF">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        PDF
                    </button>
                    <button class="btn-exportar btn-exportar--excel" onclick="exportarExcel()" title="Exportar a Excel">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Excel
                    </button>
                </div>
            </header>

            <!-- ── Barra de filtros por período ───────────────────────────── -->
            <div class="rep-filtros">
                <span class="rep-filtros__label">Período</span>

                <div class="rep-filtros__periodos">
                    <button class="btn-periodo active" data-periodo="mes">
                        <i class="bi bi-calendar-month"></i> Mes
                    </button>
                    <button class="btn-periodo" data-periodo="semana">
                        <i class="bi bi-calendar-week"></i> Semana
                    </button>
                    <button class="btn-periodo" data-periodo="hoy">
                        <i class="bi bi-calendar-day"></i> Hoy
                    </button>
                    <button class="btn-periodo" data-periodo="ano">
                        <i class="bi bi-calendar-range"></i> Año
                    </button>
                    <button class="btn-periodo" data-periodo="personalizado">
                        <i class="bi bi-calendar2-range"></i> Personalizado
                    </button>
                </div>

                <!-- Rango personalizado (oculto por defecto) -->
                <div class="rep-filtros__rango" id="rangoPersonalizado" style="display: none;">
                    <input type="date" id="fechaInicio" title="Fecha de inicio">
                    <span class="rep-filtros__sep">al</span>
                    <input type="date" id="fechaFin" title="Fecha de fin">
                    <button class="btn-exportar btn-exportar--excel" id="btnAplicarRango" style="padding: .35rem .75rem; font-size: .75rem;">
                        <i class="bi bi-search"></i> Aplicar
                    </button>
                </div>
            </div>

            <!-- ── KPI Cards ───────────────────────────────────────────────── -->
            <div class="rep-kpis">

                <div class="kpi-card kpi-card--verde">
                    <div class="kpi-card__cabecera">
                        <span class="kpi-card__etiqueta">Total citas</span>
                        <span class="kpi-card__icono">
                            <i class="bi bi-calendar-check"></i>
                        </span>
                    </div>
                    <div class="kpi-card__valor" id="statTotal">—</div>
                    <div class="kpi-card__pie">
                        <i class="bi bi-calendar2-check"></i>
                        Período actual
                    </div>
                </div>

                <div class="kpi-card kpi-card--verde">
                    <div class="kpi-card__cabecera">
                        <span class="kpi-card__etiqueta">Atendidas</span>
                        <span class="kpi-card__icono">
                            <i class="bi bi-check-circle"></i>
                        </span>
                    </div>
                    <div class="kpi-card__valor" id="statAtendidas">—</div>
                    <div class="kpi-card__pie">
                        <i class="bi bi-heart-pulse"></i>
                        Servicios finalizados
                    </div>
                </div>

                <div class="kpi-card kpi-card--rojo">
                    <div class="kpi-card__cabecera">
                        <span class="kpi-card__etiqueta">Canceladas</span>
                        <span class="kpi-card__icono">
                            <i class="bi bi-x-circle"></i>
                        </span>
                    </div>
                    <div class="kpi-card__valor" id="statCanceladas">—</div>
                    <div class="kpi-card__pie">
                        <i class="bi bi-exclamation-triangle"></i>
                        Requieren atención
                    </div>
                </div>

                <div class="kpi-card kpi-card--amarillo">
                    <div class="kpi-card__cabecera">
                        <span class="kpi-card__etiqueta">Ingresos</span>
                        <span class="kpi-card__icono">
                            <i class="bi bi-currency-dollar"></i>
                        </span>
                    </div>
                    <div class="kpi-card__valor num-money" id="statIngresos">—</div>
                    <div class="kpi-card__pie">
                        <i class="bi bi-cash-coin"></i>
                        Total facturado
                    </div>
                </div>

            </div>

            <!-- ── Detalle por servicio ────────────────────────────────────── -->
            <section class="rep-seccion" aria-label="Detalle por servicio">
                <div class="rep-seccion__cabecera">
                    <h2 class="rep-seccion__titulo">
                        <i class="bi bi-table"></i>
                        Detalle por Servicio
                    </h2>
                    <div class="rep-seccion__acciones">
                        <span class="text-muted" style="font-size: var(--text-xs);">
                            Actualizado al cargarse la página
                        </span>
                    </div>
                </div>

                <div class="rep-seccion__cuerpo rep-seccion__cuerpo--tabla">
                    <div class="rep-tabla-scroll">
                        <table class="rep-tabla" id="tablaServicios">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th class="col-center">Total citas</th>
                                    <th class="col-center">Atendidas</th>
                                    <th class="col-center">Canceladas</th>
                                    <th class="col-right">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyServicios">
                                <tr class="tabla-vacia">
                                    <td colspan="5">
                                        <i class="bi bi-hourglass-split"></i>
                                        Cargando datos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div><!-- /.rep-wrapper -->

    </main>

    <!-- ── Spinner de carga ────────────────────────────────────────────────── -->
    <div class="rep-spinner" id="spinnerOverlay" style="display: none;" role="status" aria-label="Cargando">
        <div class="rep-spinner__anillo"></div>
    </div>


    <!-- ═══════════════════════════════ SCRIPTS ═══════════════════════════════ -->
    <script>
    /* ── Constantes ──────────────────────────────────────────────────────── */

    const API_URL   = document.body.dataset.apiUrl;
    const PDF_URL   = document.body.dataset.pdfUrl;
    const EXCEL_URL = document.body.dataset.excelUrl;

    let periodoActual = 'mes';


    /* ── DOM ─────────────────────────────────────────────────────────────── */

    const elLabelPeriodo   = document.getElementById('labelPeriodo');
    const elRango          = document.getElementById('rangoPersonalizado');
    const elSelVet         = document.getElementById('selVeterinaria');
    const elTbody          = document.getElementById('tbodyServicios');
    const elStatTotal      = document.getElementById('statTotal');
    const elStatAtendidas  = document.getElementById('statAtendidas');
    const elStatCanceladas = document.getElementById('statCanceladas');
    const elStatIngresos   = document.getElementById('statIngresos');
    const elSpinner        = document.getElementById('spinnerOverlay');


    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /**
     * Formatea un número como moneda colombiana (sin decimales).
     * @param {number|string} n
     * @returns {string} ej. "$1.234.567"
     */
    function formatCOP(n) {
        return '$' + Number(n).toLocaleString('es-CO', { maximumFractionDigits: 0 });
    }

    /**
     * Escapa HTML para evitar XSS al insertar texto dinámico.
     * @param {*} str
     * @returns {string}
     */
    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    /**
     * Construye los query params del período actual.
     * @returns {string}
     */
    function construirParams() {
        const params = new URLSearchParams({ periodo: periodoActual });

        if (elSelVet.value) {
            params.set('id_veterinaria', elSelVet.value);
        }

        if (periodoActual === 'personalizado') {
            const fi = document.getElementById('fechaInicio').value;
            const ff = document.getElementById('fechaFin').value;
            if (fi && ff) {
                params.set('fecha_inicio', fi);
                params.set('fecha_fin',    ff);
            }
        }

        return params.toString();
    }

    /** Muestra u oculta el spinner de carga. */
    function mostrarSpinner(visible) {
        elSpinner.style.display = visible ? 'flex' : 'none';
    }


    /* ── Carga de datos ──────────────────────────────────────────────────── */

    async function cargarReporte() {
        mostrarSpinner(true);

        try {
            const res  = await fetch(`${API_URL}?${construirParams()}`);
            const json = await res.json();

            if (!res.ok || json.status !== 'success') {
                throw new Error(json.message ?? 'Error al obtener los datos');
            }

            renderReporte(json.payload ?? {});

        } catch (err) {
            elTbody.innerHTML = `
                <tr class="tabla-vacia">
                    <td colspan="5">
                        <i class="bi bi-wifi-off"></i>
                        ${esc(err.message)}
                    </td>
                </tr>`;
        } finally {
            mostrarSpinner(false);
        }
    }


    /* ── Render ──────────────────────────────────────────────────────────── */

    function renderReporte(payload) {
        const servicios = payload.servicios ?? [];
        const totales   = payload.totales   ?? {};
        const meta      = payload.meta      ?? {};

        /* Etiqueta de período */
        const fi = meta.fecha_inicio ?? '';
        const ff = meta.fecha_fin    ?? '';
        elLabelPeriodo.innerHTML = `
            <i class="bi bi-calendar3" style="font-size: .65rem;"></i>
            ${esc(meta.etiqueta_periodo ?? '')}
            ${fi && ff ? `&nbsp;·&nbsp; ${esc(fi)} al ${esc(ff)}` : ''}
        `;

        /* Poblar selector de veterinarias (solo la primera vez) */
        if (elSelVet.options.length <= 1 && Array.isArray(payload.veterinarias)) {
            payload.veterinarias.forEach(v => {
                const opt = new Option(v.nombre, v.id_veterinaria);
                elSelVet.add(opt);
            });
        }

        /* KPIs */
        elStatTotal.textContent      = (totales.total_citas  ?? 0).toLocaleString('es-CO');
        elStatAtendidas.textContent  = (totales.atendidas    ?? 0).toLocaleString('es-CO');
        elStatCanceladas.textContent = (totales.canceladas   ?? 0).toLocaleString('es-CO');
        elStatIngresos.textContent   = formatCOP(totales.ingresos ?? 0);

        /* Tabla de servicios */
        if (!servicios.length) {
            elTbody.innerHTML = `
                <tr class="tabla-vacia">
                    <td colspan="5">
                        <i class="bi bi-inbox"></i>
                        Sin datos para el período seleccionado
                    </td>
                </tr>`;
            return;
        }

        elTbody.innerHTML = servicios.map(row => {
            const total     = Number(row.total_citas ?? 0);
            const atendidas = Number(row.atendidas   ?? 0);
            const canceladas= Number(row.canceladas  ?? 0);
            const ingresos  = Number(row.ingresos    ?? 0);

            /* Calcular tasa de cancelación para badge */
            const tasaCancel = total > 0 ? Math.round((canceladas / total) * 100) : 0;
            let badgeClass = 'badge--ok';
            if      (tasaCancel >= 30) badgeClass = 'badge--danger';
            else if (tasaCancel >= 15) badgeClass = 'badge--warn';

            return `
                <tr>
                    <td>
                        <span class="rep-tabla__nombre">${esc(row.servicio)}</span>
                    </td>
                    <td class="col-center text-strong">${total.toLocaleString('es-CO')}</td>
                    <td class="col-center">
                        <span class="badge badge--ok">${atendidas.toLocaleString('es-CO')}</span>
                    </td>
                    <td class="col-center">
                        <span class="badge ${badgeClass}">${canceladas.toLocaleString('es-CO')}</span>
                    </td>
                    <td class="col-right num-money text-strong">${formatCOP(ingresos)}</td>
                </tr>
            `;
        }).join('');
    }


    /* ── Exportar ────────────────────────────────────────────────────────── */

    function exportarPDF() {
        window.open(`${PDF_URL}?${construirParams()}`, '_blank');
    }

    function exportarExcel() {
        window.location.href = `${EXCEL_URL}?${construirParams()}`;
    }


    /* ── Eventos ─────────────────────────────────────────────────────────── */

    /* Botones de período */
    document.querySelectorAll('.btn-periodo').forEach(btn => {
        btn.addEventListener('click', () => {
            /* Actualizar estado activo */
            document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            periodoActual = btn.dataset.periodo;

            /* Mostrar / ocultar rango personalizado */
            elRango.style.display = periodoActual === 'personalizado' ? 'flex' : 'none';

            if (periodoActual !== 'personalizado') {
                cargarReporte();
            }
        });
    });

    /* Aplicar rango personalizado */
    document.getElementById('btnAplicarRango').addEventListener('click', () => {
        const fi = document.getElementById('fechaInicio').value;
        const ff = document.getElementById('fechaFin').value;

        if (!fi || !ff) {
            alert('Por favor selecciona fecha de inicio y fecha de fin.');
            return;
        }

        if (fi > ff) {
            alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
            return;
        }

        cargarReporte();
    });

    /* Cambio de veterinaria */
    elSelVet.addEventListener('change', () => cargarReporte());


    /* ── Init ────────────────────────────────────────────────────────────── */

    cargarReporte();
    </script>
<script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
</body>
</html>
