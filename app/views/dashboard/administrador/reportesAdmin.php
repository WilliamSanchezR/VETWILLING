<?php
// RFS 14 — Subtarea 6: solo administradores (rol 1)
require_once BASE_PATH . '/app/helpers/session_administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Administrativos — VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleReportesAdmin.css">
</head>

<body
    data-api-url="<?= BASE_URL ?>/admin/reportes/data"
    data-pdf-url="<?= BASE_URL ?>/admin/reportes/pdf"
    data-excel-url="<?= BASE_URL ?>/admin/reportes/excel"
>

    <!-- Spinner de carga -->
    <div class="spinner-overlay" id="spinnerOverlay" style="display:none;">
        <div class="spinner-ring"></div>
    </div>

    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'; ?>

        <div class="area-contenido" style="padding:1.25rem;">

            <!-- ── Encabezado ────────────────────────────────────────── -->
            <div class="encabezado-pagina mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h1 class="titulo-pagina">
                            <i class="bi bi-bar-chart-line-fill me-2 text-success"></i>Reportes Administrativos
                        </h1>
                        <p class="subtitulo-pagina mb-0">Análisis global de la plataforma VetWilling</p>
                        <small class="text-muted" id="labelPeriodo"></small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn-exportar btn-pdf" onclick="exportarPDF()">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            <span>PDF</span>
                        </button>
                        <button class="btn-exportar btn-excel" onclick="exportarExcel()">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span>Excel</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Filtros ───────────────────────────────────────────── -->
            <div class="barra-filtros mb-3">
                <div class="d-flex flex-wrap gap-3 align-items-end">

                    <!-- Período -->
                    <div>
                        <label class="form-label fw-semibold small mb-1">
                            <i class="bi bi-calendar3 me-1 text-success"></i>Período
                        </label>
                        <div class="d-flex flex-wrap gap-1">
                            <button class="btn-periodo active" data-periodo="mes">Mes</button>
                            <button class="btn-periodo" data-periodo="semana">Semana</button>
                            <button class="btn-periodo" data-periodo="hoy">Hoy</button>
                            <button class="btn-periodo" data-periodo="ano">Año</button>
                            <button class="btn-periodo" data-periodo="personalizado">Personalizado</button>
                        </div>
                        <div id="rangoPersonalizado" class="d-flex align-items-center gap-2 mt-2" style="display:none!important;">
                            <input type="date" id="fechaInicio" class="form-control form-control-sm" style="width:auto;">
                            <span class="text-muted small">al</span>
                            <input type="date" id="fechaFin" class="form-control form-control-sm" style="width:auto;">
                            <button class="btn btn-sm btn-success" id="btnAplicarRango">
                                <i class="bi bi-search"></i> Aplicar
                            </button>
                        </div>
                    </div>

                    <!-- Veterinaria -->
                    <div>
                        <label class="form-label fw-semibold small mb-1" for="selVeterinaria">
                            <i class="bi bi-hospital me-1 text-success"></i>Veterinaria
                        </label>
                        <select class="form-select form-select-sm" id="selVeterinaria" style="min-width:180px;">
                            <option value="">Todas</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- ── Tarjetas KPI ──────────────────────────────────────── -->
            <div class="tarjetas-resumen mb-3">
                <div class="tarjeta-stat">
                    <span class="stat-label">Ingresos Totales</span>
                    <span class="stat-value" id="statIngresos">—</span>
                    <i class="bi bi-cash-coin stat-icon"></i>
                </div>
                <div class="tarjeta-stat azul">
                    <span class="stat-label">Total Citas</span>
                    <span class="stat-value" id="statTotalCitas">—</span>
                    <i class="bi bi-calendar2-check stat-icon"></i>
                </div>
                <div class="tarjeta-stat">
                    <span class="stat-label">Citas Atendidas</span>
                    <span class="stat-value" id="statAtendidas">—</span>
                    <i class="bi bi-check-circle stat-icon"></i>
                </div>
                <div class="tarjeta-stat rojo">
                    <span class="stat-label">Citas Canceladas</span>
                    <span class="stat-value" id="statCanceladas">—</span>
                    <i class="bi bi-x-circle stat-icon"></i>
                </div>
                <div class="tarjeta-stat amarillo">
                    <span class="stat-label">Cumplimiento</span>
                    <span class="stat-value" id="statCumplimiento">—</span>
                    <i class="bi bi-graph-up stat-icon"></i>
                </div>
                <div class="tarjeta-stat">
                    <span class="stat-label">Pacientes Atendidos</span>
                    <span class="stat-value" id="statPacientes">—</span>
                    <i class="bi bi-heart-pulse stat-icon"></i>
                </div>
            </div>

            <!-- ── Fila: Ingresos mensuales + Estados de citas ───────── -->
            <div class="row g-3 mb-3">

                <div class="col-lg-8">
                    <div class="seccion-card h-100">
                        <div class="card-header">
                            <i class="bi bi-bar-chart-fill"></i> Ingresos Últimos 6 Meses
                        </div>
                        <div class="card-body">
                            <canvas id="chartIngresos" height="90"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="seccion-card h-100">
                        <div class="card-header">
                            <i class="bi bi-pie-chart-fill"></i> Estado de Citas
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-success fw-semibold">Atendidas</span>
                                    <span id="lblAtendidas">—</span>
                                </div>
                                <div class="barra-progreso">
                                    <div class="barra-progreso-fill" id="barraAtendidas" style="width:0%;background:#0a932c;"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-danger fw-semibold">Canceladas</span>
                                    <span id="lblCanceladas">—</span>
                                </div>
                                <div class="barra-progreso">
                                    <div class="barra-progreso-fill" id="barraCanceladas" style="width:0%;background:#ef4444;"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-warning fw-semibold">Pendientes</span>
                                    <span id="lblPendientes">—</span>
                                </div>
                                <div class="barra-progreso">
                                    <div class="barra-progreso-fill" id="barraPendientes" style="width:0%;background:#f59e0b;"></div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <small class="text-muted">Total: <strong id="lblTotalCitas">—</strong></small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Ranking de veterinarias ─── subtarea 2 ───────────── -->
            <div class="seccion-card mb-3">
                <div class="card-header">
                    <i class="bi bi-trophy-fill"></i> Ranking de Veterinarias
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="tabla-reportes">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:40px">#</th>
                                    <th>Veterinaria</th>
                                    <th class="text-center">Atendidas</th>
                                    <th class="text-center">Canceladas</th>
                                    <th>Ingresos</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTopVets">
                                <tr><td colspan="5" class="estado-vacio"><i class="bi bi-hourglass-split"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── Desempeño del personal ─── subtarea 5 ─────────────── -->
            <div class="seccion-card mb-3">
                <div class="card-header">
                    <i class="bi bi-people-fill"></i> Desempeño del Personal
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="tabla-reportes">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Profesional</th>
                                    <th>Veterinaria</th>
                                    <th class="text-center">Total Citas</th>
                                    <th class="text-center">Atendidas</th>
                                    <th class="text-center">Canceladas</th>
                                    <th class="text-center">Cumplimiento</th>
                                    <th class="text-center">T. Promedio</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDesempeno">
                                <tr><td colspan="8" class="estado-vacio"><i class="bi bi-hourglass-split"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── Inventario ─── subtarea 3 ─────────────────────────── -->
            <div class="row g-3 mb-3">
                <div class="col-md-3 col-6">
                    <div class="tarjeta-stat azul">
                        <span class="stat-label">Total Productos</span>
                        <span class="stat-value" id="invTotal">—</span>
                        <i class="bi bi-box-seam stat-icon"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="tarjeta-stat">
                        <span class="stat-label">Vigentes</span>
                        <span class="stat-value" id="invVigentes">—</span>
                        <i class="bi bi-check2-circle stat-icon"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="tarjeta-stat amarillo">
                        <span class="stat-label">Por Vencer (30d)</span>
                        <span class="stat-value" id="invVencer">—</span>
                        <i class="bi bi-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="tarjeta-stat rojo">
                        <span class="stat-label">Vencidos</span>
                        <span class="stat-value" id="invVencidos">—</span>
                        <i class="bi bi-x-octagon stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="seccion-card mb-4">
                <div class="card-header">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Insumos Próximos a Vencer (60 días)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="tabla-reportes">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Veterinaria</th>
                                    <th>Lote</th>
                                    <th class="text-center">Cantidad</th>
                                    <th>Fecha Vencimiento</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyInventario">
                                <tr><td colspan="6" class="estado-vacio"><i class="bi bi-hourglass-split"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /area-contenido -->
    </div><!-- /contenido-principal -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/reportesAdmin.js"></script>

</body>
</html>
