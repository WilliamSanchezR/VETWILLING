<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardReportes.css">


</head>

<body data-reportes-api-url="<?= BASE_URL ?>/veterinaria/reportes/data" data-reportes-pdf-url="<?= BASE_URL ?>/veterinaria/reportes/pdf" data-reportes-excel-url="<?= BASE_URL ?>/veterinaria/reportes/excel" data-reportes-enviar-url="<?= BASE_URL ?>/veterinaria/reportes/enviar-ficha">

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Aqui va el include -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'
    ?>


    <!-- PANEL DERECHO -->
    <!-- aqui va el inclunde notifi -->
    <?php
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- ENCABEZADO DE PÁGINA -->
            <div class="encabezado-pagina mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="titulo-pagina">
                            <i class="bi bi-bar-chart-line-fill me-2"></i>
                            Reportes y Análisis
                        </h1>
                        <p class="subtitulo-pagina mb-0">Visualiza el rendimiento de tu práctica veterinaria</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="boton-exportar boton-pdf" onclick="exportarPDF()">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            <span class="d-none d-md-inline">Exportar PDF</span>
                        </button>
                        <button class="boton-exportar boton-excel" onclick="exportarExcel()">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span class="d-none d-md-inline">Exportar Excel</span>
                        </button>
                        <button class="boton-exportar" style="background:#2563eb;color:#fff;" onclick="abrirModalEnviarFicha()">
                            <i class="bi bi-envelope-fill"></i>
                            <span class="d-none d-md-inline">Enviar ficha</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="barra-filtros-reporte">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="grupo-filtros">
                        <label class="etiqueta-filtro">
                            <i class="bi bi-funnel-fill"></i> Periodo
                        </label>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <button class="boton-periodo active" data-periodo="hoy">
                                <i class="bi bi-calendar-day"></i> Hoy
                            </button>
                            <button class="boton-periodo" data-periodo="semana">
                                <i class="bi bi-calendar-week"></i> Semana
                            </button>
                            <button class="boton-periodo" data-periodo="mes">
                                <i class="bi bi-calendar-month"></i> Mes
                            </button>
                            <button class="boton-periodo" data-periodo="ano">
                                <i class="bi bi-calendar-range"></i> Año
                            </button>
                            <button class="boton-periodo" data-periodo="personalizado">
                                <i class="bi bi-calendar2-range"></i> Personalizado
                            </button>
                        </div>
                        <!-- Rango personalizado (RFS 32 subtask 3) -->
                        <div class="d-flex gap-2 flex-wrap align-items-center mt-2" id="rangoPersonalizado" style="display:none!important;">
                            <input type="date" id="fechaInicioReporte" class="form-control form-control-sm" style="width:auto;" title="Fecha inicio">
                            <span class="text-muted small">al</span>
                            <input type="date" id="fechaFinReporte" class="form-control form-control-sm" style="width:auto;" title="Fecha fin">
                            <button class="btn btn-sm btn-success" onclick="cargarDashboardReportes()">
                                <i class="bi bi-search"></i> Aplicar
                            </button>
                        </div>
                    </div>
                    <div class="grupo-filtros">
                        <label class="etiqueta-filtro">
                            <i class="bi bi-funnel-fill"></i> Estado de Cita
                        </label>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <select id="filtroEstadoCita" class="form-select form-select-sm" style="width: auto; min-width: 160px;">
                                <option value="">Todos los estados</option>
                                <option value="ATENDIDA">Atendidas</option>
                                <option value="CANCELADA">Canceladas</option>
                                <option value="PENDIENTE">Pendientes</option>
                            </select>
                        </div>
                    </div>
                    <div class="periodo-seleccionado">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span id="textoperiodoSeleccionado">Hoy</span>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE RESUMEN MEJORADAS -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-ingresos">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Ingresos Totales</p>
                                <h3 class="valor-metrica" id="reporteIngresosTotales">$0</h3>
                                <span class="badge-periodo" id="reportePeriodoEtiqueta1">
                                    <i class="bi bi-clock"></i> Cargando...
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-citas">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Citas Atendidas</p>
                                <h3 class="valor-metrica" id="reporteCitasAtendidas">0</h3>
                                <span class="badge-periodo" id="reportePeriodoEtiqueta2">
                                    <i class="bi bi-clock"></i> Cargando...
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-activity"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-canceladas">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-calendar-x"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Citas Canceladas</p>
                                <h3 class="valor-metrica" id="reporteCitasCanceladas">0</h3>
                                <span class="badge-periodo" id="reportePeriodoEtiqueta4">
                                    <i class="bi bi-clock"></i> Cargando...
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-pendientes">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Citas Pendientes</p>
                                <h3 class="valor-metrica" id="reporteCitasPendientes">0</h3>
                                <span class="badge-periodo" id="reportePeriodoEtiqueta5">
                                    <i class="bi bi-clock"></i> Cargando...
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-pacientes">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Nuevos Pacientes</p>
                                <h3 class="valor-metrica" id="reporteNuevosPacientes">0</h3>
                                <span class="badge-periodo" id="reportePeriodoEtiqueta3">
                                    <i class="bi bi-clock"></i> Cargando...
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="tarjeta-metrica tarjeta-cumplimiento">
                        <div class="contenido-tarjeta">
                            <div class="icono-metrica">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="info-metrica">
                                <p class="etiqueta-metrica">Cumplimiento</p>
                                <h3 class="valor-metrica" id="reporteCumplimiento">0%</h3>
                                <span class="badge-periodo" id="reporteTotalCitas">
                                    <i class="bi bi-list-check"></i> 0 citas
                                </span>
                            </div>
                        </div>
                        <div class="decoracion-tarjeta">
                            <i class="bi bi-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DE GRÁFICOS PRINCIPALES -->
            <div class="row g-3 mb-4">
                <!-- Gráfico de Ingresos Mensuales -->
                <div class="col-12 col-lg-8">
                    <div class="tarjeta-grafico-principal">
                        <div class="encabezado-grafico-principal">
                            <div class="info-grafico">
                                <div class="icono-titulo-grafico">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div>
                                    <h5 class="titulo-grafico">Evolución de Ingresos</h5>
                                    <p class="subtitulo-grafico">Comparativa últimos 6 meses</p>
                                </div>
                            </div>
                            <div class="controles-grafico">
                                <select class="select-grafico-year" id="selectorAnioReporte">
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                </select>
                            </div>
                        </div>
                        <div class="contenedor-canvas-principal">
                            <canvas id="graficoIngresos"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Servicios (Doughnut) -->
                <div class="col-12 col-lg-4">
                    <div class="tarjeta-grafico-lateral">
                        <div class="encabezado-grafico-lateral">
                            <div class="icono-titulo-grafico">
                                <i class="bi bi-pie-chart-fill"></i>
                            </div>
                            <div>
                                <h5 class="titulo-grafico">Distribución de Servicios</h5>
                                <p class="subtitulo-grafico">Top servicios solicitados</p>
                            </div>
                        </div>
                        <div class="contenedor-canvas-lateral">
                            <canvas id="graficoServicios"></canvas>
                        </div>
                        <div class="leyenda-grafico-servicios" id="leyendaServiciosReporte">
                            <!-- Leyenda dinámica -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS DETALLADAS -->
            <div class="row g-3 mb-4">
                <!-- Top Tratamientos -->
                <div class="col-12 col-lg-6">
                    <div class="tarjeta-lista-estadisticas">
                        <div class="encabezado-lista">
                            <div class="icono-titulo-lista">
                                <i class="bi bi-clipboard2-pulse-fill"></i>
                            </div>
                            <div>
                                <h5 class="titulo-lista">Tratamientos Más Realizados</h5>
                                <p class="subtitulo-lista">Top 5 procedimientos</p>
                            </div>
                        </div>
                        <div class="contenedor-lista-items" id="listaTratamientosReporte">
                            <!-- Lista dinámica -->
                        </div>
                    </div>
                </div>

                <!-- Distribución por Especie -->
                <div class="col-12 col-lg-6">
                    <div class="tarjeta-lista-estadisticas">
                        <div class="encabezado-lista">
                            <div class="icono-titulo-lista">
                                <i class="bi bi-diagram-3-fill"></i>
                            </div>
                            <div>
                                <h5 class="titulo-lista">Pacientes por Especie</h5>
                                <p class="subtitulo-lista">Clasificación de atenciones</p>
                            </div>
                        </div>
                        <div class="contenedor-lista-items" id="listaEspeciesReporte">
                            <!-- Lista dinámica -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA FINANCIERA MENSUAL -->
            <div class="tarjeta-tabla-financiera-mejorada">
                <div class="encabezado-tabla-principal">
                    <div class="info-tabla">
                        <div class="icono-titulo-tabla">
                            <i class="bi bi-table"></i>
                        </div>
                        <div>
                            <h5 class="titulo-tabla">Resumen Financiero Mensual</h5>
                            <p class="subtitulo-tabla">Desglose detallado por concepto</p>
                        </div>
                    </div>
                    <button class="boton-detalle-tabla">
                        <span>Ver Detalle Completo</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </div>
                <div class="contenedor-tabla-scroll">
                    <table class="tabla-financiera-moderna">
                        <thead>
                            <tr>
                                <th class="columna-concepto">Concepto</th>
                                <th class="columna-mes" id="reporteMes1">Enero</th>
                                <th class="columna-mes" id="reporteMes2">Febrero</th>
                                <th class="columna-mes" id="reporteMes3">Marzo</th>
                                <th class="columna-mes" id="reporteMes4">Abril</th>
                                <th class="columna-mes" id="reporteMes5">Mayo</th>
                                <th class="columna-total">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tablaFinancieraBody">
                            <!-- Datos dinámicos -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12 col-lg-6">
                    <div class="tarjeta-lista-estadisticas">
                        <div class="encabezado-lista">
                            <div class="icono-titulo-lista">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div>
                                <h5 class="titulo-lista">Pacientes Asignados Activos</h5>
                                <p class="subtitulo-lista">Fuente: paciente_profesional_asignacion</p>
                            </div>
                        </div>
                        <div class="contenedor-lista-items" id="listaAsignacionesActivasReporte">
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="tarjeta-lista-estadisticas">
                        <div class="encabezado-lista">
                            <div class="icono-titulo-lista">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h5 class="titulo-lista">Historial de Asignaciones</h5>
                                <p class="subtitulo-lista">Movimientos del periodo seleccionado</p>
                            </div>
                        </div>
                        <div class="contenedor-lista-items" id="listaHistorialAsignacionesReporte">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DETALLE DE CITAS (RFS 39) -->
            <div class="tarjeta-tabla-financiera-mejorada mt-3">
                <div class="encabezado-tabla-principal">
                    <div class="info-tabla">
                        <div class="icono-titulo-tabla">
                            <i class="bi bi-list-columns-reverse"></i>
                        </div>
                        <div>
                            <h5 class="titulo-tabla">Detalle de Citas</h5>
                            <p class="subtitulo-tabla">Listado de citas atendidas, canceladas y pendientes</p>
                        </div>
                    </div>
                </div>
                <div class="contenedor-tabla-scroll">
                    <table class="tabla-detalle-citas">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Propietario</th>
                                <th>Servicio</th>
                                <th>Subservicio</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalleCitasBody">
                            <!-- Datos dinámicos -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Enviar Ficha Clínica (RFS 32 subtask 7) -->
    <div class="modal fade" id="modalEnviarFicha" tabindex="-1" aria-labelledby="modalEnviarFichaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnviarFichaLabel">
                        <i class="bi bi-envelope-fill me-2 text-primary"></i>Enviar Ficha Clínica por Correo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paciente seleccionado</label>
                        <input type="text" class="form-control" id="enviarFichaNombrePaciente" readonly>
                        <input type="hidden" id="enviarFichaIdPaciente">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensaje adicional <span class="text-muted">(opcional)</span></label>
                        <textarea class="form-control" id="enviarFichaMensaje" rows="3" placeholder="Ej: Por favor revisar la evolución del tratamiento..."></textarea>
                    </div>
                    <div id="enviarFichaAlerta" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarEnviarFicha">
                        <i class="bi bi-send-fill me-1"></i> Enviar al propietario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Propio -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/reportesConfig.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardReportes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>


</body>

</html>