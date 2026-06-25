<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
</head>

<body data-api-url="<?= BASE_URL ?>/representante/historial-ventas/data"
      data-pdf-url="<?= BASE_URL ?>/representante/historial-ventas/pdf"
      data-excel-url="<?= BASE_URL ?>/representante/historial-ventas/excel">

    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php'; ?>

        <div class="area-contenido">
            <div class="header-admin">
                <div class="header-info">
                    <h1>Historial de Ventas</h1>
                    <p>Consulta y exporta el historial de ventas realizadas</p>
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

            <!-- Filtros de período y criterios -->
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
                            <i class="bi bi-search"></i> Aplicar
                        </button>
                    </div>
                </div>

                <div class="acciones-inferior">
                    <div class="filtro-grupo">
                        <label class="filtro-label">Cliente:</label>
                        <select id="filtroCliente" class="filtro-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label class="filtro-label">Producto:</label>
                        <input type="text" id="filtroProducto" class="filtro-select" placeholder="Buscar producto...">
                    </div>
                    <div class="filtro-grupo">
                        <label class="filtro-label">Estado:</label>
                        <select id="filtroEstado" class="filtro-select">
                            <option value="todos">Todos</option>
                            <option value="completada">Completada</option>
                            <option value="anulada">Anulada</option>
                        </select>
                    </div>
                    <button class="btn-accion btn-primary" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                    <button class="btn-accion btn-secondary" id="btnLimpiarFiltros">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- Tarjetas de resumen -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                        <div class="stat-trend up"><i class="bi bi-graph-up"></i> Total</div>
                    </div>
                    <div class="stat-value" id="statTotalVentas">—</div>
                    <div class="stat-label">Ventas Realizadas</div>
                    <div class="stat-footer"><i class="bi bi-bag-check"></i> Transacciones</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                        <div class="stat-trend up"><i class="bi bi-arrow-up"></i> Ingresos</div>
                    </div>
                    <div class="stat-value" id="statMontoTotal">—</div>
                    <div class="stat-label">Monto Total</div>
                    <div class="stat-footer"><i class="bi bi-currency-dollar"></i> Facturado</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="bi bi-tag"></i></div>
                        <div class="stat-trend"><i class="bi bi-dash"></i> Descuentos</div>
                    </div>
                    <div class="stat-value" id="statDescuentos">—</div>
                    <div class="stat-label">Total Descuentos</div>
                    <div class="stat-footer"><i class="bi bi-percent"></i> Aplicados</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="bi bi-calculator"></i></div>
                        <div class="stat-trend"><i class="bi bi-bar-chart"></i> Promedio</div>
                    </div>
                    <div class="stat-value" id="statPromedioVenta">—</div>
                    <div class="stat-label">Promedio por Venta</div>
                    <div class="stat-footer"><i class="bi bi-graph-up-arrow"></i> Por transacción</div>
                </div>
            </div>

            <!-- Tabla de ventas -->
            <div class="card-tabla">
                <div class="tabla-header">
                    <h3><i class="bi bi-table"></i> Detalle de Ventas</h3>
                </div>
                <div class="tabla-contenido">
                    <table class="tabla-datos" id="tablaVentas">
                        <thead>
                            <tr>
                                <th>#Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Usuario</th>
                                <th>Subtotal</th>
                                <th>Descuento</th>
                                <th>Impuesto</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyVentas">
                            <tr><td colspan="9" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /.area-contenido -->
    </div><!-- /.contenido-principal -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

    <script>
        let periodoActual  = 'mes';
        let clienteActual  = '';
        let productoActual = '';
        let estadoActual   = 'todos';

        document.addEventListener('DOMContentLoaded', function () {
            cargarDatos();
            configurarEventos();
        });

        function configurarEventos() {
            document.querySelectorAll('.btn-periodo').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.btn-periodo').forEach(b => {
                        b.classList.replace('btn-primary', 'btn-secondary');
                    });
                    this.classList.replace('btn-secondary', 'btn-primary');
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
            clienteActual  = '';
            productoActual = '';
            estadoActual   = 'todos';
            document.getElementById('filtroCliente').value  = '';
            document.getElementById('filtroProducto').value = '';
            document.getElementById('filtroEstado').value   = 'todos';
            cargarDatos();
        }

        function construirUrl(base) {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin    = document.getElementById('fechaFin').value;
            clienteActual     = document.getElementById('filtroCliente').value;
            productoActual    = document.getElementById('filtroProducto').value;
            estadoActual      = document.getElementById('filtroEstado').value;

            let url = `${base}?periodo=${periodoActual}`;
            if (periodoActual === 'personalizado' && fechaInicio && fechaFin) {
                url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            }
            if (clienteActual)                  url += `&id_cliente=${clienteActual}`;
            if (productoActual)                 url += `&producto=${encodeURIComponent(productoActual)}`;
            if (estadoActual && estadoActual !== 'todos') url += `&estado=${estadoActual}`;
            return url;
        }

        function cargarDatos() {
            const url = construirUrl(document.body.dataset.apiUrl);

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        actualizarUI(data.payload);
                    } else {
                        Swal.fire('Error', data.message || 'Error al cargar datos', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
        }

        function actualizarUI(payload) {
            const meta    = payload.meta;
            const resumen = payload.resumen;
            const ventas  = payload.ventas;
            const clientes= payload.clientes;

            document.getElementById('labelPeriodo').textContent =
                `${meta.etiqueta_periodo} (${meta.fecha_inicio} a ${meta.fecha_fin})`;

            document.getElementById('statTotalVentas').textContent  = resumen.total_ventas ?? '0';
            document.getElementById('statMontoTotal').textContent    = '$' + parseFloat(resumen.monto_total    ?? 0).toFixed(2);
            document.getElementById('statDescuentos').textContent    = '$' + parseFloat(resumen.total_descuentos ?? 0).toFixed(2);
            document.getElementById('statPromedioVenta').textContent = '$' + parseFloat(resumen.promedio_venta  ?? 0).toFixed(2);

            actualizarSelectClientes(clientes, meta.id_cliente);
            actualizarTablaVentas(ventas);
        }

        function actualizarSelectClientes(clientes, seleccionado) {
            const select = document.getElementById('filtroCliente');
            const valorActual = seleccionado || select.value;
            select.innerHTML = '<option value="">Todos</option>';
            clientes.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.id_propietario;
                opt.textContent = c.nombre;
                select.appendChild(opt);
            });
            if (valorActual) select.value = valorActual;
        }

        function actualizarTablaVentas(ventas) {
            const tbody = document.getElementById('tbodyVentas');
            if (!ventas || ventas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Sin ventas para el período seleccionado</td></tr>';
                return;
            }

            tbody.innerHTML = ventas.map(v => {
                const estadoBadge = v.estado === 'anulada'
                    ? '<span class="badge bg-danger">Anulada</span>'
                    : '<span class="badge bg-success">Completada</span>';

                return `<tr>
                    <td><strong>#${v.id_venta}</strong></td>
                    <td>${v.fecha_venta}</td>
                    <td>${v.cliente ?? '<span class="text-muted">—</span>'}</td>
                    <td>${v.usuario ?? ''}</td>
                    <td>$${parseFloat(v.subtotal  ?? 0).toFixed(2)}</td>
                    <td>$${parseFloat(v.descuento ?? 0).toFixed(2)}</td>
                    <td>$${parseFloat(v.impuesto  ?? 0).toFixed(2)}</td>
                    <td><strong>$${parseFloat(v.total ?? 0).toFixed(2)}</strong></td>
                    <td class="text-center">${estadoBadge}</td>
                </tr>`;
            }).join('');
        }

        function exportarPDF() {
            window.open(construirUrl(document.body.dataset.pdfUrl), '_blank');
        }

        function exportarExcel() {
            window.open(construirUrl(document.body.dataset.excelUrl), '_blank');
        }
    </script>
</body>
</html>
