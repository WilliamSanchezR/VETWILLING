let chartIngresos = null;
let chartServicios = null;
let periodoActual = 'hoy';

const coloresServicios = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];

function formatoMoneda(valor) {
    return '$' + Number(valor || 0).toLocaleString('es-CO');
}

function crearGraficoIngresos(labels, data) {
    const canvas = document.getElementById('graficoIngresos');
    if (!canvas) return;

    if (chartIngresos) {
        chartIngresos.destroy();
    }

    chartIngresos = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ingresos',
                data,
                borderColor: '#0A932c',
                backgroundColor: 'rgba(10, 147, 44, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return formatoMoneda(value);
                        }
                    }
                }
            }
        }
    });
}

function crearGraficoServicios(servicios) {
    const canvas = document.getElementById('graficoServicios');
    if (!canvas) return;

    if (chartServicios) {
        chartServicios.destroy();
    }

    const labels = servicios.map(item => item.nombre);
    const data = servicios.map(item => Number(item.total || 0));

    chartServicios = new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: labels.map((_, idx) => coloresServicios[idx % coloresServicios.length]),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
}

function renderLeyendaServicios(servicios) {
    const contenedor = document.getElementById('leyendaServiciosReporte');
    if (!contenedor) return;

    if (!servicios.length) {
        contenedor.innerHTML = '<div class="text-muted small p-3">Sin datos para el periodo seleccionado.</div>';
        return;
    }

    contenedor.innerHTML = servicios.map((item, idx) => `
        <div class="item-leyenda-servicio">
            <div class="nombre-servicio-leyenda">
                <span class="color-servicio" style="background: ${coloresServicios[idx % coloresServicios.length]};"></span>
                <span>${item.nombre}</span>
            </div>
            <span class="porcentaje-servicio">${Number(item.porcentaje || 0).toFixed(1)}%</span>
        </div>
    `).join('');
}

function renderResumen(resumen, meta) {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };

    setText('reporteIngresosTotales', formatoMoneda(resumen.ingresos_totales));
    setText('reporteCitasAtendidas', Number(resumen.citas_atendidas || 0).toLocaleString('es-CO'));
    setText('reporteNuevosPacientes', Number(resumen.nuevos_pacientes || 0).toLocaleString('es-CO'));
    setText('reporteCumplimiento', `${Number(resumen.cumplimiento || 0).toFixed(1)}%`);
    setText('reporteTotalCitas', `${Number(resumen.total_citas || 0).toLocaleString('es-CO')} citas`);

    const etiqueta = meta.etiqueta_periodo || 'Cargando...';
    setText('reportePeriodoEtiqueta1', etiqueta);
    setText('reportePeriodoEtiqueta2', etiqueta);
    setText('reportePeriodoEtiqueta3', etiqueta);
    setText('reportePeriodoEtiqueta4', etiqueta);
    setText('reportePeriodoEtiqueta5', etiqueta);
    
    // Actualizar periodo seleccionado en barra de filtros
    const periodoVisual = document.getElementById('textoperiodoSeleccionado');
    if (periodoVisual) periodoVisual.textContent = etiqueta;
}

function renderTratamientos(tratamientos) {
    const contenedor = document.getElementById('listaTratamientosReporte');
    if (!contenedor) return;

    const maximo = tratamientos.length ? Math.max(...tratamientos.map(item => Number(item.total || 0))) : 0;

    if (!tratamientos.length) {
        contenedor.innerHTML = '<div class="text-muted small p-3">Sin tratamientos registrados para el periodo seleccionado.</div>';
        return;
    }

    contenedor.innerHTML = tratamientos.map(item => {
        const total = Number(item.total || 0);
        const porcentaje = maximo > 0 ? Math.round((total / maximo) * 100) : 0;

        return `
            <div class="item-estadistica">
                <span class="nombre-item">${item.nombre}</span>
                <span class="valor-item">${total.toLocaleString('es-CO')}</span>
                <div class="barra-progreso-item">
                    <div class="progreso-inner" style="width: ${porcentaje}%;"></div>
                </div>
            </div>
        `;
    }).join('');
}

function colorEspecie(especie) {
    const nombre = String(especie || '').toLowerCase();
    if (nombre.includes('perro')) return { bg: 'bg-warning-soft', text: 'text-warning' };
    if (nombre.includes('gato')) return { bg: 'bg-info-soft', text: 'text-info' };
    if (nombre.includes('ave')) return { bg: 'bg-success-soft', text: 'text-success' };
    return { bg: 'bg-danger-soft', text: 'text-danger' };
}

function renderEspecies(especies) {
    const contenedor = document.getElementById('listaEspeciesReporte');
    if (!contenedor) return;

    if (!especies.length) {
        contenedor.innerHTML = '<div class="text-muted small p-3">Sin especies registradas para el periodo seleccionado.</div>';
        return;
    }

    contenedor.innerHTML = especies.map(item => {
        const porcentaje = Number(item.porcentaje || 0);
        const total = Number(item.total || 0);

        return `
            <div class="item-estadistica">
                <span class="nombre-item">${item.especie}</span>
                <span class="valor-item">${total.toLocaleString('es-CO')}</span>
                <div class="barra-progreso-item">
                    <div class="progreso-inner" style="width: ${Math.min(100, porcentaje)}%;"></div>
                </div>
            </div>
        `;
    }).join('');
}

function renderFinanciero(financiero) {
    const tbody = document.getElementById('tablaFinancieraBody');
    if (!tbody) return;

    const meses = financiero.meses || [];
    for (let i = 0; i < 5; i++) {
        const th = document.getElementById(`reporteMes${i + 1}`);
        if (th) {
            th.textContent = meses[i] || '-';
        }
    }

    const filas = financiero.filas || [];
    if (!filas.length) {
        tbody.innerHTML = '<tr><td colspan="7">Sin información financiera para el año seleccionado.</td></tr>';
        return;
    }

    const filasHtml = filas.map(fila => {
        const valores = fila.valores || [];
        const celdasMes = [];
        for (let i = 0; i < 5; i++) {
            celdasMes.push(`<td>${formatoMoneda(valores[i] || 0)}</td>`);
        }

        return `
            <tr>
                <td class="concepto">${fila.concepto}</td>
                ${celdasMes.join('')}
                <td class="total">${formatoMoneda(fila.total || 0)}</td>
            </tr>
        `;
    }).join('');

    const totales = financiero.totales_mes || [];
    const celdasTotales = [];
    for (let i = 0; i < 5; i++) {
        celdasTotales.push(`<td><strong>${formatoMoneda(totales[i] || 0)}</strong></td>`);
    }

    tbody.innerHTML = `
        ${filasHtml}
        <tr class="fila-total">
            <td class="concepto"><strong>Total Mensual</strong></td>
            ${celdasTotales.join('')}
            <td class="total"><strong>${formatoMoneda(financiero.gran_total || 0)}</strong></td>
        </tr>
    `;
}

function renderAsignacionesActivas(asignaciones) {
    const contenedor = document.getElementById('listaAsignacionesActivasReporte');
    if (!contenedor) return;

    if (!asignaciones || !asignaciones.length) {
        contenedor.innerHTML = '<div class="text-muted small p-3">No hay pacientes asignados activos.</div>';
        return;
    }

    contenedor.innerHTML = asignaciones.map(item => {
        const ultimaVisita = item.ultima_visita ? new Date(item.ultima_visita).toLocaleDateString('es-CO') : 'Sin visitas';
        const especie = item.especie || 'N/A';
        const raza = item.raza || 'N/A';
        return `
            <div class="item-estadistica">
                <span class="nombre-item">${item.paciente_nombre} (${especie} - ${raza})</span>
                <span class="valor-item">${item.propietario_nombre || 'Sin propietario'}</span>
                <div class="text-muted small mt-1">Última visita: ${ultimaVisita}</div>
            </div>
        `;
    }).join('');
}

function renderHistorialAsignaciones(historial) {
    const contenedor = document.getElementById('listaHistorialAsignacionesReporte');
    if (!contenedor) return;

    if (!historial || !historial.length) {
        contenedor.innerHTML = '<div class="text-muted small p-3">Sin movimientos de asignación en el periodo.</div>';
        return;
    }

    contenedor.innerHTML = historial.map(item => {
        const inicio = item.fecha_inicio ? new Date(item.fecha_inicio).toLocaleDateString('es-CO') : 'N/A';
        const fin = item.fecha_fin ? new Date(item.fecha_fin).toLocaleDateString('es-CO') : 'Activo';
        const motivo = item.motivo_cambio || 'Sin motivo';
        return `
            <div class="item-estadistica">
                <span class="nombre-item">${item.paciente_nombre}</span>
                <span class="valor-item">${item.estado}</span>
                <div class="text-muted small mt-1">Inicio: ${inicio} | Fin: ${fin}</div>
                <div class="text-muted small">Motivo: ${motivo}</div>
            </div>
        `;
    }).join('');
}

function renderResumenEstados(resumenEstados) {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };

    setText('reporteCitasCanceladas', Number(resumenEstados.canceladas || 0).toLocaleString('es-CO'));
    setText('reporteCitasPendientes', Number(resumenEstados.pendientes || 0).toLocaleString('es-CO'));
}

function renderDetalleCitas(detalle) {
    const tbody = document.getElementById('tablaDetalleCitasBody');
    if (!tbody) return;

    if (!detalle || !detalle.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-muted text-center p-3">Sin citas para el periodo y filtros seleccionados.</td></tr>';
        return;
    }

    tbody.innerHTML = detalle.map(item => {
        const estado = (item.estado || 'PENDIENTE').toUpperCase();
        let claseEstado = 'estado-pendiente';
        if (estado === 'ATENDIDA') claseEstado = 'estado-atendida';
        else if (estado === 'CANCELADA') claseEstado = 'estado-cancelada';

        const fecha = item.fecha ? new Date(item.fecha.replace(' ', 'T')).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';

        return `
            <tr>
                <td>${fecha}</td>
                <td>${item.paciente || 'Sin paciente'}</td>
                <td>${item.propietario || 'Sin propietario'}</td>
                <td>${item.servicio || 'Sin servicio'}</td>
                <td>${item.subservicio || '-'}</td>
                <td><span class="badge-estado ${claseEstado}">${estado}</span></td>
                <td>${item.observaciones || '-'}</td>
            </tr>
        `;
    }).join('');
}

function obtenerFiltrosAvanzados() {
    const params = new URLSearchParams();
    const estadoCita = document.getElementById('filtroEstadoCita');
    if (estadoCita && estadoCita.value) {
        params.set('estado_cita', estadoCita.value);
    }
    return params.toString();
}

async function cargarDashboardReportes() {
    try {
        const selectorAnio = document.getElementById('selectorAnioReporte');
        const anio = selectorAnio ? selectorAnio.value : new Date().getFullYear();
        const filtrosExtra = obtenerFiltrosAvanzados();
        let url = `${window.REPORTES_API_URL}?action=data&periodo=${periodoActual}&anio=${anio}`;
        if (filtrosExtra) url += `&${filtrosExtra}`;

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('No se pudo cargar la data de reportes');
        }

        const data = await response.json();
        if (data.status !== 'success' || !data.payload) {
            throw new Error('Respuesta inválida del servidor');
        }

        const payload = data.payload;

        renderResumen(payload.resumen || {}, payload.meta || {});
        renderResumenEstados(payload.resumen_estados || {});
        crearGraficoIngresos(payload.ingresos_mensuales?.labels || [], payload.ingresos_mensuales?.data || []);
        crearGraficoServicios(payload.servicios || []);
        renderLeyendaServicios(payload.servicios || []);
        renderTratamientos(payload.tratamientos || []);
        renderEspecies(payload.especies || []);
        renderFinanciero(payload.financiero || {});
        renderAsignacionesActivas(payload.asignaciones_activas || []);
        renderHistorialAsignaciones(payload.historial_asignaciones || []);
        renderDetalleCitas(payload.detalle_citas || []);
    } catch (error) {
        console.error(error);
    }
}

function configurarEventos() {
    document.querySelectorAll('.boton-periodo').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.boton-periodo').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            periodoActual = this.dataset.periodo || 'mes';
            cargarDashboardReportes();
        });
    });

    const selectorAnio = document.getElementById('selectorAnioReporte');
    if (selectorAnio) {
        selectorAnio.addEventListener('change', cargarDashboardReportes);
    }

    // Filtro avanzado de estado de cita (RFS 39)
    const filtroEstado = document.getElementById('filtroEstadoCita');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', cargarDashboardReportes);
    }
}

function exportarPDF() {
    const selectorAnio = document.getElementById('selectorAnioReporte');
    const anio = selectorAnio ? selectorAnio.value : new Date().getFullYear();
    const filtrosExtra = obtenerFiltrosAvanzados();
    let url = `${window.REPORTES_PDF_URL}?action=pdf&periodo=${periodoActual}&anio=${anio}`;
    if (filtrosExtra) url += `&${filtrosExtra}`;
    window.open(url, '_blank');
}

function exportarExcel() {
    const selectorAnio = document.getElementById('selectorAnioReporte');
    const anio = selectorAnio ? selectorAnio.value : new Date().getFullYear();
    const filtrosExtra = obtenerFiltrosAvanzados();
    let url = `${window.REPORTES_EXCEL_URL}?action=excel&periodo=${periodoActual}&anio=${anio}`;
    if (filtrosExtra) url += `&${filtrosExtra}`;
    window.open(url, '_blank');
}

document.addEventListener('DOMContentLoaded', function () {
    const btnHoy = document.querySelector('.boton-periodo[data-periodo="hoy"]');
    if (btnHoy) {
        document.querySelectorAll('.boton-periodo').forEach(btn => btn.classList.remove('active'));
        btnHoy.classList.add('active');
        periodoActual = 'hoy';
    }

    configurarEventos();
    cargarDashboardReportes();
});
