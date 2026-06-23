<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario - VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
</head>

<body data-api-url="<?= BASE_URL ?>/representante/reportes-inventario/data"
    data-pdf-url="<?= BASE_URL ?>/representante/reportes-inventario/pdf"
    data-excel-url="<?= BASE_URL ?>/representante/reportes-inventario/excel">

    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php'; ?>

        <div class="area-contenido">
            <div class="header-admin">
                <div class="header-info">
                    <h1>Reporte de Inventario</h1>
                    <p>Análisis de stock, consumo y alertas de inventario</p>
                    <small class="text-white-50" id="labelPeriodo"></small>
                </div>
                <div class="header-acciones">
                    <button class="btn-header" onclick="exportarPDF()">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        Exportar PDF
                    </button>
                    <button class="btn-header" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Exportar Excel
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
                            <i class="bi bi-calendar-range"></i> Año
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

                <div class="acciones-inferior">
                    <div class="filtro-grupo">
                        <label class="filtro-label">Categoría:</label>
                        <select id="filtroCategoria" class="filtro-select">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label class="filtro-label">Producto:</label>
                        <input type="text" id="filtroProducto" class="filtro-select" placeholder="Buscar producto...">
                    </div>
                    <button class="btn-accion btn-primary" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                    <button class="btn-accion btn-secondary" id="btnLimpiarFiltros">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-graph-up"></i>
                            Total
                        </div>
                    </div>
                    <div class="stat-value" id="statTotalLotes">—</div>
                    <div class="stat-label">Total Lotes</div>
                    <div class="stat-footer">
                        <i class="bi bi-layers"></i> Productos registrados
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-stack"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            Stock
                        </div>
                    </div>
                    <div class="stat-value" id="statStockTotal">—</div>
                    <div class="stat-label">Stock Total</div>
                    <div class="stat-footer">
                        <i class="bi bi-box"></i> Unidades disponibles
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-trend down">
                            <i class="bi bi-arrow-down"></i>
                            Críticos
                        </div>
                    </div>
                    <div class="stat-value" id="statLotesCriticos">—</div>
                    <div class="stat-label">Lotes Críticos</div>
                    <div class="stat-footer">
                        <i class="bi bi-alert-circle"></i> Stock bajo mínimo
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>
                        <div class="stat-trend down">
                            <i class="bi bi-arrow-down"></i>
                            Salidas
                        </div>
                    </div>
                    <div class="stat-value" id="statTotalSalidas">—</div>
                    <div class="stat-label">Total Salidas</div>
                    <div class="stat-footer">
                        <i class="bi bi-cart-x"></i> Consumo del periodo
                    </div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-graph-up"></i>
                            Promedio
                        </div>
                    </div>
                    <div class="stat-value" id="statConsumoPromedio">—</div>
                    <div class="stat-label">Consumo Prom./Día</div>
                    <div class="stat-footer">
                        <i class="bi bi-calendar-day"></i> Unidades por día
                    </div>
                </div>
            </div>

            <div class="grafico-card">
                <div class="grafico-header">
                    <h3 class="grafico-titulo">Métricas de Consumo Promedio</h3>
                </div>
                <div class="contenedor-tabla">
                    <div class="table-responsive">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Total Salidas</th>
                                    <th>Total Entradas</th>
                                    <th>Promedio Salida</th>
                                    <th>Número Salidas</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMetricas">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grafico-card">
                <div class="grafico-header">
                    <h3 class="grafico-titulo">Detalle de Inventario</h3>
                </div>
                <div class="contenedor-tabla">
                    <div class="table-responsive">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Proveedor</th>
                                    <th>Stock</th>
                                    <th>Stock Mínimo</th>
                                    <th class="text-center">Estado</th>
                                    <th>Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="grafico-card">
                        <div class="grafico-header">
                            <h3 class="grafico-titulo">Productos Más Usados</h3>
                        </div>
                        <div class="contenedor-tabla">
                            <div class="table-responsive">
                                <table class="tabla-admin">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Total Salidas</th>
                                            <th>Número Salidas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyMasUsados">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Cargando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="grafico-card">
                        <div class="grafico-header">
                            <h3 class="grafico-titulo">Items con Alertas de Stock</h3>
                        </div>
                        <div class="contenedor-tabla">
                            <div class="table-responsive">
                                <table class="tabla-admin">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Stock</th>
                                            <th>Stock Mínimo</th>
                                            <th>Nivel Alerta</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAlertas">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Cargando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let periodoActual = 'mes';
        let categoriaActual = '';
        let productoActual = '';

        document.addEventListener('DOMContentLoaded', function() {
            cargarDatos();
            configurarEventos();
        });

        function configurarEventos() {
            document.querySelectorAll('.btn-periodo').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.btn-periodo').forEach(b => {
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-secondary');
                    });
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-primary');

                    periodoActual = this.dataset.periodo;
                    
                    if (periodoActual === 'personalizado') {
                        document.getElementById('rangoPersonalizado').style.setProperty('display', 'flex', 'important');
                    } else {
                        document.getElementById('rangoPersonalizado').style.setProperty('display', 'none', 'important');
                        cargarDatos();
                    }
                });
            });

            document.getElementById('btnAplicarRango').addEventListener('click', cargarDatos);
            document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
            document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
        }

        function limpiarFiltros() {
            categoriaActual = '';
            productoActual = '';
            document.getElementById('filtroCategoria').value = '';
            document.getElementById('filtroProducto').value = '';
            cargarDatos();
        }

        function cargarDatos() {
            const apiURL = document.body.dataset.apiUrl;
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            categoriaActual = document.getElementById('filtroCategoria').value;
            productoActual = document.getElementById('filtroProducto').value;

            let url = `${apiURL}?periodo=${periodoActual}`;
            if (periodoActual === 'personalizado' && fechaInicio && fechaFin) {
                url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            }
            if (categoriaActual) {
                url += `&categoria=${categoriaActual}`;
            }
            if (productoActual) {
                url += `&producto=${productoActual}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        actualizarUI(data.payload);
                    } else {
                        Swal.fire('Error', data.message || 'Error al cargar datos', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
        }

        function actualizarUI(payload) {
            const meta = payload.meta;
            const resumen = payload.resumen;
            const detalle = payload.detalle_inventario;
            const metricas = payload.metricas_consumo;
            const masUsados = payload.productos_mas_usados;
            const alertas = payload.items_con_alertas;
            const categorias = payload.categorias_disponibles;

            document.getElementById('labelPeriodo').textContent = 
                `${meta.etiqueta_periodo} (${meta.fecha_inicio} a ${meta.fecha_fin})`;

            document.getElementById('statTotalLotes').textContent = resumen.total_lotes;
            document.getElementById('statStockTotal').textContent = resumen.stock_total;
            document.getElementById('statLotesCriticos').textContent = resumen.lotes_criticos;
            document.getElementById('statTotalSalidas').textContent = resumen.total_salidas;
            document.getElementById('statConsumoPromedio').textContent = resumen.consumo_promedio_diario ?? '0';

            actualizarCategoriaSelect(categorias);

            actualizarTablaMetricas(metricas);
            actualizarTablaDetalle(detalle);
            actualizarTablaMasUsados(masUsados);
            actualizarTablaAlertas(alertas);
        }

        function actualizarTablaMetricas(metricas) {
            const tbody = document.getElementById('tbodyMetricas');
            if (!metricas || metricas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin datos de consumo para el periodo</td></tr>';
                return;
            }

            tbody.innerHTML = metricas.map(row => `
                <tr>
                    <td>${row.nombre}</td>
                    <td>${row.categoria}</td>
                    <td>${row.total_salidas}</td>
                    <td>${row.total_entradas}</td>
                    <td>${parseFloat(row.promedio_salida || 0).toFixed(2)}</td>
                    <td>${row.numero_salidas}</td>
                </tr>
            `).join('');
        }

        function actualizarCategoriaSelect(categorias) {
            const select = document.getElementById('filtroCategoria');
            const valorActual = select.value;
            select.innerHTML = '<option value="">Todas</option>';
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
                select.appendChild(option);
            });
            select.value = valorActual;
        }

        function actualizarTablaDetalle(detalle) {
            const tbody = document.getElementById('tbodyDetalle');
            if (!detalle || detalle.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin datos para el periodo seleccionado</td></tr>';
                return;
            }

            tbody.innerHTML = detalle.map(row => {
                const alertaClass = row.alerta_stock == 1 ? 'table-danger' : '';
                const estadoBadge = row.alerta_stock == 1 
                    ? '<span class="badge bg-danger">CRÍTICO</span>' 
                    : '<span class="badge bg-success">NORMAL</span>';

                return `<tr class="${alertaClass}">
                    <td>${row.nombre}</td>
                    <td>${row.categoria}</td>
                    <td>${row.proveedor}</td>
                    <td><strong>${row.cantidad}</strong></td>
                    <td>${row.stock_minimo}</td>
                    <td class="text-center">${estadoBadge}</td>
                    <td>${row.fecha_vencimiento}</td>
                </tr>`;
            }).join('');
        }

        function actualizarTablaMasUsados(masUsados) {
            const tbody = document.getElementById('tbodyMasUsados');
            if (!masUsados || masUsados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos de consumo para el periodo</td></tr>';
                return;
            }

            tbody.innerHTML = masUsados.map(row => `
                <tr>
                    <td>${row.nombre}</td>
                    <td>${row.categoria}</td>
                    <td>${row.total_salidas}</td>
                    <td>${row.numero_salidas}</td>
                </tr>
            `).join('');
        }

        function actualizarTablaAlertas(alertas) {
            const tbody = document.getElementById('tbodyAlertas');
            if (!alertas || alertas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin alertas de stock para el periodo</td></tr>';
                return;
            }

            tbody.innerHTML = alertas.map(row => {
                let alertaClass = '';
                let badgeClass = '';
                if (row.nivel_alerta === 'CRITICO') {
                    alertaClass = 'table-danger';
                    badgeClass = 'bg-danger';
                } else if (row.nivel_alerta === 'ALERTA') {
                    alertaClass = 'table-warning';
                    badgeClass = 'bg-warning';
                }

                return `<tr class="${alertaClass}">
                    <td>${row.nombre}</td>
                    <td>${row.categoria}</td>
                    <td>${row.cantidad}</td>
                    <td>${row.stock_minimo}</td>
                    <td><span class="badge ${badgeClass}">${row.nivel_alerta}</span></td>
                </tr>`;
            }).join('');
        }

        function exportarPDF() {
            const pdfURL = document.body.dataset.pdfUrl;
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            categoriaActual = document.getElementById('filtroCategoria').value;
            productoActual = document.getElementById('filtroProducto').value;

            let url = `${pdfURL}?periodo=${periodoActual}`;
            if (periodoActual === 'personalizado' && fechaInicio && fechaFin) {
                url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            }
            if (categoriaActual) {
                url += `&categoria=${categoriaActual}`;
            }
            if (productoActual) {
                url += `&producto=${productoActual}`;
            }

            window.open(url, '_blank');
        }

        function exportarExcel() {
            const excelURL = document.body.dataset.excelUrl;
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            categoriaActual = document.getElementById('filtroCategoria').value;
            productoActual = document.getElementById('filtroProducto').value;

            let url = `${excelURL}?periodo=${periodoActual}`;
            if (periodoActual === 'personalizado' && fechaInicio && fechaFin) {
                url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            }
            if (categoriaActual) {
                url += `&categoria=${categoriaActual}`;
            }
            if (productoActual) {
                url += `&producto=${productoActual}`;
            }

            window.open(url, '_blank');
        }
    </script>
</body>

</html>