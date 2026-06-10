<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Citas - VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
</head>

<body data-api-url="<?= BASE_URL ?>/representante/reportes-citas/data"
    data-pdf-url="<?= BASE_URL ?>/representante/reportes-citas/pdf"
    data-excel-url="<?= BASE_URL ?>/representante/reportes-citas/excel">

    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php'; ?>

        <div class="area-contenido">
            <div class="header-admin">
                <div class="header-info">
                    <h1>Reporte de Citas</h1>
                    <p>Resumen de citas atendidas, canceladas y pendientes</p>
                    <small class="text-white-50" id="labelPeriodo"></small>
                </div>
                <div class="header-acciones">
                    <button class="btn-header" onclick="exportarPDF()">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        Exportar Cancelaciones (PDF)
                    </button>
                    <button class="btn-header" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Exportar Detalle (Excel)
                    </button>
                </div>
            </div>

            <div class="barra-acciones">
                <div class="acciones-superior">
                    <div class="acciones-izquierda">
                        <button class="btn-accion btn-primary btn-periodo" data-periodo="mes">
                            <i class="bi bi-calendar-month"></i> Mes
                        </button>
                        <button class="btn-accion btn-secondary btn-periodo" data-periodo="semana">
                            <i class="bi bi-calendar-week"></i> Semana
                        </button>
                        <button class="btn-accion btn-secondary btn-periodo" data-periodo="hoy">
                            <i class="bi bi-calendar-day"></i> Hoy
                        </button>
                        <button class="btn-accion btn-secondary btn-periodo" data-periodo="ano">
                            <i class="bi bi-calendar-range"></i> Ano
                        </button>
                        <button class="btn-accion btn-secondary btn-periodo" data-periodo="personalizado">
                            <i class="bi bi-calendar2-range"></i> Personalizado
                        </button>
                    </div>
                    <div class="acciones-derecha" id="rangoPersonalizado" style="display:none!important;">
                        <input type="date" id="fechaInicio" class="filtro-select" style="width:auto;">
                        <span class="text-muted small">al</span>
                        <input type="date" id="fechaFin" class="filtro-select" style="width:auto;">
                        <button class="btn-accion btn-primary" id="btnAplicarRango">
                            <i class="bi bi-search"></i>
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-graph-up"></i>
                            Total
                        </div>
                    </div>
                    <div class="stat-value" id="statTotal">—</div>
                    <div class="stat-label">Total Citas</div>
                    <div class="stat-footer">
                        <i class="bi bi-calendar2-check"></i> Periodo actual
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            Atendidas
                        </div>
                    </div>
                    <div class="stat-value" id="statAtendidas">—</div>
                    <div class="stat-label">Citas Atendidas</div>
                    <div class="stat-footer">
                        <i class="bi bi-heart-pulse"></i> Finalizadas
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="stat-trend down">
                            <i class="bi bi-arrow-down"></i>
                            Canceladas
                        </div>
                    </div>
                    <div class="stat-value" id="statCanceladas">—</div>
                    <div class="stat-label">Citas Canceladas</div>
                    <div class="stat-footer">
                        <i class="bi bi-exclamation-triangle"></i> Con motivo
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            Pendientes
                        </div>
                    </div>
                    <div class="stat-value" id="statPendientes">—</div>
                    <div class="stat-label">Citas Pendientes</div>
                    <div class="stat-footer">
                        <i class="bi bi-clock-history"></i> Por atender
                    </div>
                </div>
            </div>

            <div class="grafico-card">
                <div class="grafico-header">
                    <h3 class="grafico-titulo">Detalle de Citas</h3>
                </div>
                <div class="contenedor-tabla">
                    <div class="table-responsive">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Propietario</th>
                                    <th>Servicio</th>
                                    <th>Subservicio</th>
                                    <th class="text-center">Estado</th>
                                    <th>Motivo Cancelacion</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle">
                                <tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grafico-card">
                <div class="grafico-header">
                    <h3 class="grafico-titulo">Motivos de Cancelacion</h3>
                </div>
                <div class="contenedor-tabla">
                    <div class="table-responsive">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>Motivo</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMotivos">
                                <tr><td colspan="2" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const apiUrl = document.body.dataset.apiUrl;
        const pdfUrl = document.body.dataset.pdfUrl;
        const excelUrl = document.body.dataset.excelUrl;
        let periodoActual = 'mes';

        const rangoPersonalizado = document.getElementById('rangoPersonalizado');
        const labelPeriodo = document.getElementById('labelPeriodo');
        const tbodyDetalle = document.getElementById('tbodyDetalle');
        const tbodyMotivos = document.getElementById('tbodyMotivos');

        const statTotal = document.getElementById('statTotal');
        const statAtendidas = document.getElementById('statAtendidas');
        const statCanceladas = document.getElementById('statCanceladas');
        const statPendientes = document.getElementById('statPendientes');

        function construirParams() {
            const params = new URLSearchParams({ periodo: periodoActual });
            if (periodoActual === 'personalizado') {
                const fi = document.getElementById('fechaInicio').value;
                const ff = document.getElementById('fechaFin').value;
                if (fi && ff) {
                    params.append('fecha_inicio', fi);
                    params.append('fecha_fin', ff);
                }
            }
            return params.toString();
        }

        function badgeEstado(estado) {
            if (estado === 'ATENDIDA') return '<span class="badge-tabla badge-cliente">Atendida</span>';
            if (estado === 'CANCELADA') return '<span class="badge-tabla" style="background:#fee2e2;color:#991b1b;">Cancelada</span>';
            return '<span class="badge-tabla" style="background:#fff3cd;color:#92400e;">Pendiente</span>';
        }

        async function cargarReporte() {
            tbodyDetalle.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>';
            tbodyMotivos.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Cargando...</td></tr>';

            const response = await fetch(`${apiUrl}?${construirParams()}`);
            const data = await response.json();

            if (!data || data.status !== 'success') {
                tbodyDetalle.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar datos</td></tr>';
                tbodyMotivos.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error al cargar datos</td></tr>';
                return;
            }

            const payload = data.payload || {};
            const meta = payload.meta || {};
            const resumen = payload.resumen_estados || {};
            const detalle = payload.detalle_citas || [];
            const motivos = payload.motivos_cancelacion || [];

            labelPeriodo.textContent = `Periodo: ${meta.etiqueta_periodo || ''} (${meta.fecha_inicio || ''} a ${meta.fecha_fin || ''})`;

            statTotal.textContent = resumen.total ?? 0;
            statAtendidas.textContent = resumen.atendidas ?? 0;
            statCanceladas.textContent = resumen.canceladas ?? 0;
            statPendientes.textContent = resumen.pendientes ?? 0;

            if (!detalle.length) {
                tbodyDetalle.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin datos para el periodo seleccionado</td></tr>';
            } else {
                tbodyDetalle.innerHTML = detalle.map((row) => {
                    return `
                        <tr>
                            <td>${row.fecha || ''}</td>
                            <td>${row.paciente || ''}</td>
                            <td>${row.propietario || ''}</td>
                            <td>${row.servicio || ''}</td>
                            <td>${row.subservicio || ''}</td>
                            <td class="text-center">${badgeEstado(row.estado || '')}</td>
                            <td>${row.motivo_cancelacion || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }

            if (!motivos.length) {
                tbodyMotivos.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Sin cancelaciones registradas</td></tr>';
            } else {
                tbodyMotivos.innerHTML = motivos.map((row) => {
                    return `
                        <tr>
                            <td>${row.motivo || ''}</td>
                            <td class="text-center">${row.total || 0}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        function setPeriodo(periodo, button) {
            periodoActual = periodo;
            document.querySelectorAll('.btn-periodo').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
            });
            button.classList.remove('btn-secondary');
            button.classList.add('btn-primary');

            if (periodo === 'personalizado') {
                rangoPersonalizado.style.display = 'flex';
            } else {
                rangoPersonalizado.style.display = 'none';
                cargarReporte();
            }
        }

        document.querySelectorAll('.btn-periodo').forEach((btn) => {
            btn.addEventListener('click', () => setPeriodo(btn.dataset.periodo, btn));
        });

        document.getElementById('btnAplicarRango').addEventListener('click', () => {
            if (periodoActual === 'personalizado') {
                cargarReporte();
            }
        });

        function exportarPDF() {
            window.open(`${pdfUrl}?${construirParams()}`, '_blank');
        }

        function exportarExcel() {
            window.open(`${excelUrl}?${construirParams()}`, '_blank');
        }

        cargarReporte();
    </script>
</body>

</html>
