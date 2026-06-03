/* global Chart is loaded from CDN */

const API_URL  = document.body.dataset.apiUrl;
const PDF_URL  = document.body.dataset.pdfUrl;
const EXCEL_URL = document.body.dataset.excelUrl;

let estadoActual = {
    periodo: 'mes',
    fechaInicio: '',
    fechaFin: '',
    idVeterinaria: ''
};

let chartIngresos = null;

// ─── Bootstrap ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    // Periodo buttons
    document.querySelectorAll('.btn-periodo').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            estadoActual.periodo = btn.dataset.periodo;
            if (estadoActual.periodo === 'personalizado') {
                document.getElementById('rangoPersonalizado').style.display = 'flex';
            } else {
                document.getElementById('rangoPersonalizado').style.display = 'none';
                cargarReportes();
            }
        });
    });

    document.getElementById('btnAplicarRango')?.addEventListener('click', () => {
        estadoActual.fechaInicio = document.getElementById('fechaInicio').value;
        estadoActual.fechaFin    = document.getElementById('fechaFin').value;
        if (!estadoActual.fechaInicio || !estadoActual.fechaFin) {
            Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'Selecciona fecha de inicio y fin.', confirmButtonColor: '#0a932c' });
            return;
        }
        cargarReportes();
    });

    document.getElementById('selVeterinaria')?.addEventListener('change', (e) => {
        estadoActual.idVeterinaria = e.target.value;
        cargarReportes();
    });

    cargarReportes();
});

// ─── Carga de datos ───────────────────────────────────────────────────────────

async function cargarReportes() {
    mostrarSpinner(true);
    try {
        const params = new URLSearchParams({ periodo: estadoActual.periodo });
        if (estadoActual.idVeterinaria) params.set('id_veterinaria', estadoActual.idVeterinaria);
        if (estadoActual.periodo === 'personalizado') {
            params.set('fecha_inicio', estadoActual.fechaInicio);
            params.set('fecha_fin',    estadoActual.fechaFin);
        }

        const res = await fetch(`${API_URL}?${params.toString()}`);
        if (!res.ok) throw new Error('Error al obtener datos');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message || 'Error desconocido');

        renderTodo(json.payload);
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#0a932c' });
    } finally {
        mostrarSpinner(false);
    }
}

// ─── Render general ───────────────────────────────────────────────────────────

function renderTodo(payload) {
    const meta = payload.meta ?? {};
    document.getElementById('labelPeriodo').textContent =
        `${meta.etiqueta ?? ''} (${meta.fecha_inicio ?? ''} — ${meta.fecha_fin ?? ''})`;

    // Poblar selector de veterinarias sólo la primera vez
    const sel = document.getElementById('selVeterinaria');
    if (sel && sel.options.length <= 1) {
        (payload.veterinarias ?? []).forEach(v => {
            const opt = new Option(v.nombre, v.id_veterinaria);
            sel.add(opt);
        });
    }

    renderResumen(payload.resumen ?? {}, payload.estados_citas ?? {});
    renderIngresosChart(payload.ingresos_mensuales ?? {});
    renderDesempeno(payload.desempeno ?? []);
    renderInventario(payload.inventario ?? {}, payload.productos_vencer ?? []);
    renderTopVeterinarias(payload.top_veterinarias ?? []);
}

// ─── Tarjetas resumen ─────────────────────────────────────────────────────────

function renderResumen(resumen, estados) {
    setText('statIngresos',    '$' + formatNum(resumen.ingresos_totales ?? 0));
    setText('statTotalCitas',  resumen.total_citas ?? 0);
    setText('statAtendidas',   resumen.citas_atendidas ?? 0);
    setText('statCanceladas',  resumen.citas_canceladas ?? 0);
    setText('statCumplimiento', (resumen.cumplimiento ?? 0).toFixed(1) + '%');
    setText('statPacientes',   resumen.pacientes_atendidos ?? 0);

    // mini donut estados citas
    const total = estados.total || 1;
    const pAtendidas  = Math.round((estados.atendidas  / total) * 100);
    const pCanceladas = Math.round((estados.canceladas / total) * 100);
    const pPendientes = 100 - pAtendidas - pCanceladas;

    setText('lblAtendidas',  estados.atendidas  ?? 0);
    setText('lblCanceladas', estados.canceladas ?? 0);
    setText('lblPendientes', estados.pendientes ?? 0);
    setText('lblTotalCitas', estados.total ?? 0);

    actualizarBarra('barraAtendidas',  pAtendidas,  '#0a932c');
    actualizarBarra('barraCanceladas', pCanceladas, '#ef4444');
    actualizarBarra('barraPendientes', pPendientes, '#f59e0b');
}

// ─── Gráfica ingresos ─────────────────────────────────────────────────────────

function renderIngresosChart(ingresos) {
    const canvas = document.getElementById('chartIngresos');
    if (!canvas) return;
    const labels = ingresos.labels ?? [];
    const data   = ingresos.data   ?? [];

    if (chartIngresos) chartIngresos.destroy();
    chartIngresos = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ingresos',
                data,
                backgroundColor: 'rgba(10,147,44,.7)',
                borderColor:     '#0a932c',
                borderWidth: 1.5,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '$' + formatNum(v)
                    }
                }
            }
        }
    });
}

// ─── Desempeño del personal ────────────────────────────────────────────────────

function renderDesempeno(rows) {
    const tbody = document.getElementById('tbodyDesempeno');
    if (!tbody) return;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="estado-vacio"><i class="bi bi-person-x"></i>Sin datos en el período</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((d, i) => {
        const tc = parseFloat(d.tasa_cumplimiento ?? 0);
        const badgeClass = tc >= 80 ? 'badge-ok' : (tc >= 50 ? 'badge-warn' : 'badge-danger');
        const min = d.promedio_minutos !== null ? d.promedio_minutos + ' min' : '—';
        return `<tr>
            <td>${i + 1}</td>
            <td>${esc(d.nombre_profesional)}</td>
            <td>${esc(d.veterinaria)}</td>
            <td class="text-center">${d.total_citas}</td>
            <td class="text-center">${d.atendidas}</td>
            <td class="text-center">${d.canceladas}</td>
            <td class="text-center"><span class="${badgeClass}">${tc.toFixed(1)}%</span></td>
            <td class="text-center">${min}</td>
        </tr>`;
    }).join('');
}

// ─── Inventario ────────────────────────────────────────────────────────────────

function renderInventario(inv, productos) {
    setText('invTotal',    inv.total_productos ?? 0);
    setText('invVigentes', inv.vigentes ?? 0);
    setText('invVencer',   inv.por_vencer ?? 0);
    setText('invVencidos', inv.vencidos ?? 0);

    const tbody = document.getElementById('tbodyInventario');
    if (!tbody) return;
    if (!productos.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="estado-vacio"><i class="bi bi-box-seam"></i>No hay insumos próximos a vencer</td></tr>';
        return;
    }
    tbody.innerHTML = productos.map(p => {
        const dias = parseInt(p.dias_restantes ?? 0);
        let badgeClass, badgeLabel;
        if (dias < 0)       { badgeClass = 'badge-danger'; badgeLabel = 'Vencido'; }
        else if (dias <= 15){ badgeClass = 'badge-warn';   badgeLabel = 'Crítico'; }
        else                { badgeClass = 'badge-ok';     badgeLabel = 'Próximo'; }

        return `<tr>
            <td>${esc(p.nombre)}</td>
            <td>${esc(p.veterinaria)}</td>
            <td>${esc(p.numero_lote ?? '—')}</td>
            <td class="text-center">${p.cantidad ?? 0}</td>
            <td>${esc(p.fecha_vencimiento ?? '')}</td>
            <td class="text-center"><span class="${badgeClass}">${badgeLabel} (${dias}d)</span></td>
        </tr>`;
    }).join('');
}

// ─── Top veterinarias ─────────────────────────────────────────────────────────

function renderTopVeterinarias(rows) {
    const tbody = document.getElementById('tbodyTopVets');
    if (!tbody) return;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="estado-vacio"><i class="bi bi-hospital"></i>Sin datos</td></tr>';
        return;
    }
    const maxIngresos = Math.max(...rows.map(r => parseFloat(r.ingresos ?? 0)), 1);
    tbody.innerHTML = rows.map((v, i) => {
        const pct = Math.round((parseFloat(v.ingresos ?? 0) / maxIngresos) * 100);
        return `<tr>
            <td class="text-center"><strong>${i + 1}</strong></td>
            <td>${esc(v.veterinaria)}</td>
            <td class="text-center">${v.atendidas}</td>
            <td class="text-center">${v.canceladas}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span>$${formatNum(v.ingresos ?? 0)}</span>
                    <div class="barra-progreso flex-grow-1" style="min-width:60px">
                        <div class="barra-progreso-fill" style="width:${pct}%"></div>
                    </div>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ─── Exportar ─────────────────────────────────────────────────────────────────

function exportarPDF() {
    window.open(PDF_URL + '?' + buildParams(), '_blank');
}

function exportarExcel() {
    window.location.href = EXCEL_URL + '?' + buildParams();
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function buildParams() {
    const params = new URLSearchParams({ periodo: estadoActual.periodo });
    if (estadoActual.idVeterinaria) params.set('id_veterinaria', estadoActual.idVeterinaria);
    if (estadoActual.periodo === 'personalizado') {
        params.set('fecha_inicio', estadoActual.fechaInicio);
        params.set('fecha_fin',    estadoActual.fechaFin);
    }
    return params.toString();
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function actualizarBarra(id, pct, color) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.width = Math.max(0, Math.min(100, pct)) + '%';
    el.style.background = color;
}

function formatNum(n) {
    return Number(n).toLocaleString('es-CO');
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function mostrarSpinner(show) {
    const el = document.getElementById('spinnerOverlay');
    if (el) el.style.display = show ? 'flex' : 'none';
}
