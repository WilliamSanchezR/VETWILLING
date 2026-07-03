/* ============================================================
   Gestión de Citas — Veterinario
   ============================================================ */

const BASE_URL = document.querySelector('meta[name="base-url"]')?.content || '';

// Estado en memoria
let _todasLasCitas = [];
let _tabActivo = 'todas';
let _busqueda  = '';

// ─── Inicialización ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarCitas();
    initTabs();
    initBusqueda();
    initModalAtender();
});

// ─── API ─────────────────────────────────────────────────────
async function apiPost(accion, extra = {}) {
    const res = await fetch(BASE_URL + '/veterinaria/api/citas', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion, ...extra }),
    });
    if (!res.ok) throw new Error('Error de red: ' + res.status);
    return res.json();
}

// ─── Cargar y renderizar ─────────────────────────────────────
async function cargarCitas() {
    mostrarEsqueleto();
    try {
        const result = await apiPost('listar');
        if (result.status !== 'success') throw new Error(result.message || 'Error al cargar');

        _todasLasCitas = result.data.citas || [];
        renderStats(result.data.stats || {});
        renderCitas();
    } catch (err) {
        mostrarError(err.message);
    }
}

function renderStats(stats) {
    const set = (id, key) => {
        const el = document.getElementById(id);
        if (el) el.textContent = (stats[key] ?? 0).toLocaleString('es-CO');
    };
    set('statPendientes',  'PENDIENTE');
    set('statConfirmadas', 'CONFIRMADA');
    set('statCompletadas', 'COMPLETADA');
    set('statCanceladas',  'CANCELADA');
}

function renderCitas() {
    const lista = document.getElementById('citasLista');
    if (!lista) return;

    const termino = _busqueda.toLowerCase().trim();

    let filtradas = _todasLasCitas.filter(c => {
        if (_tabActivo !== 'todas' && c.estado.toLowerCase() !== _tabActivo) return false;
        if (termino) {
            const texto = [c.paciente_nombre, c.propietario_nombre, c.tipo, c.especie, c.raza]
                .filter(Boolean).join(' ').toLowerCase();
            if (!texto.includes(termino)) return false;
        }
        return true;
    });

    // Completadas: más recientes primero
    if (_tabActivo === 'completada') {
        filtradas = filtradas.slice().reverse();
    }

    if (filtradas.length === 0) {
        lista.innerHTML = `
            <div class="citas-empty col-12">
                <i class="bi bi-calendar-x"></i>
                <h4>Sin citas</h4>
                <p>No se encontraron citas con los filtros aplicados.</p>
            </div>`;
        return;
    }

    lista.innerHTML = filtradas.map(cardHtml).join('');
    lista.querySelectorAll('[data-accion]').forEach(btn => {
        btn.addEventListener('click', () => manejarAccion(btn));
    });
}

// ─── HTML de tarjeta ─────────────────────────────────────────
function esCitaDeHoy(dt) {
    if (!dt) return false;
    const hoy = new Date();
    return dt.getFullYear() === hoy.getFullYear() &&
           dt.getMonth()    === hoy.getMonth() &&
           dt.getDate()     === hoy.getDate();
}

function cardHtml(c) {
    const dt    = c.fecha_hora ? new Date(c.fecha_hora.replace(' ', 'T')) : null;
    const hora  = dt ? dt.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }) : '--:--';
    const fecha = dt ? dt.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' }) : '--';

    const estadoLow  = (c.estado || '').toLowerCase();
    const estadoLabel = { pendiente: 'Pendiente', confirmada: 'Confirmada', cancelada: 'Cancelada' }[estadoLow] || c.estado;

    const avatarHtml = c.img_mascota
        ? `<img src="${BASE_URL}/public/uploads/mascotas/${esc(c.img_mascota)}" alt="${esc(c.paciente_nombre)}" class="paciente-avatar-cita">`
        : `<div class="paciente-avatar-placeholder"><i class="bi bi-heart-pulse"></i></div>`;

    const btnAtender = esCitaDeHoy(dt)
        ? `<button class="btn-cita btn-cita-atender" data-accion="atender" data-id="${c.id_agendamiento}" data-paciente="${c.id_paciente}">
               <i class="bi bi-clipboard2-pulse"></i> Atender
           </button>`
        : `<button class="btn-cita btn-cita-atender" disabled title="Solo puedes atender citas programadas para el día de hoy">
               <i class="bi bi-clipboard2-pulse"></i> Atender
           </button>`;

    let botones;
    if (estadoLow === 'completada') {
        botones = `<button class="btn-cita" style="background:#f1f5f9;color:#64748b;cursor:default;" disabled>
                       <i class="bi bi-check-circle-fill"></i> Atendida
                   </button>`;
    } else if (estadoLow === 'pendiente') {
        botones = `<button class="btn-cita btn-cita-confirmar" data-accion="confirmar" data-id="${c.id_agendamiento}">
                       <i class="bi bi-check-circle"></i> Confirmar
                   </button>
                   ${btnAtender}`;
    } else {
        botones = `${btnAtender}
                   <button class="btn-cita btn-cita-cancelar" data-accion="cancelar" data-id="${c.id_agendamiento}">
                       <i class="bi bi-x-circle"></i> Cancelar
                   </button>`;
    }

    return `
    <div class="cita-card col-12 col-md-6 col-xl-4" data-estado="${estadoLow}" data-id="${c.id_agendamiento}">

        <div class="cita-header-color">
            <div class="cita-hora-bloque">
                <span class="hora">${hora}</span>
                <span class="fecha-corta">${fecha}</span>
            </div>
            <span class="badge-estado">${estadoLabel}</span>
        </div>

        <div class="cita-body">
            <div class="cita-paciente-row">
                ${avatarHtml}
                <div class="paciente-datos">
                    <h4 title="${esc(c.paciente_nombre || '--')}">${esc(c.paciente_nombre || '--')}</h4>
                    <span class="badge-especie">
                        <i class="bi bi-heart-fill"></i>
                        ${esc(c.especie || '--')}${c.raza ? ' · ' + esc(c.raza) : ''}
                    </span>
                </div>
            </div>

            <div class="cita-divider"></div>

            <div class="cita-detalles">
                ${c.propietario_nombre  ? `<div class="detalle-item"><i class="bi bi-person"></i><span>${esc(c.propietario_nombre)}</span></div>` : ''}
                ${c.propietario_telefono ? `<div class="detalle-item"><i class="bi bi-telephone"></i><span>${esc(c.propietario_telefono)}</span></div>` : ''}
                ${c.tipo                ? `<div class="detalle-item"><i class="bi bi-clipboard-pulse"></i><span>${esc(c.tipo)}</span></div>` : ''}
                ${c.observaciones       ? `<div class="detalle-item"><i class="bi bi-chat-left-text"></i><span>${esc(c.observaciones)}</span></div>` : ''}
            </div>
        </div>

        <div class="cita-acciones">${botones}</div>
    </div>`;
}

// ─── Acciones ─────────────────────────────────────────────────
async function manejarAccion(btn) {
    const accion   = btn.dataset.accion;
    const id       = parseInt(btn.dataset.id, 10);
    const idPaciente = parseInt(btn.dataset.paciente || '0', 10);

    if (accion === 'atender') {
        // Buscar la cita en memoria para prellenar el modal
        const cita = _todasLasCitas.find(c => Number(c.id_agendamiento) === id);
        if (cita) abrirModalAtender(cita);
        return;
    }

    const extra = { id_agendamiento: id };

    if (accion === 'cancelar') {
        const motivo = await pedirMotivoCancelacion();
        if (!motivo) return;
        extra.motivo = motivo;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

    try {
        const result = await apiPost(accion, extra);
        if (result.status !== 'success') throw new Error(result.message);

        mostrarNotificacion(result.message, 'success');
        await cargarCitas();
    } catch (err) {
        mostrarNotificacion(err.message || 'Ocurrió un error', 'error');
        btn.disabled = false;
        renderCitas();
    }
}

// ─── Modal Atender Cita ──────────────────────────────────────
let _bsModalAtender = null;
let _citaAtendiendo = null;

function initModalAtender() {
    const modalEl = document.getElementById('modalAtenderCita');
    if (!modalEl) return;
    _bsModalAtender = new bootstrap.Modal(modalEl);

    const btnGuardar = document.getElementById('btnGuardarAtencionCita');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarAtencionCita);
    }

    modalEl.addEventListener('hidden.bs.modal', () => {
        limpiarModalAtender();
        _citaAtendiendo = null;
    });
}

function abrirModalAtender(cita) {
    if (!_bsModalAtender) initModalAtender();
    if (!_bsModalAtender) return;

    _citaAtendiendo = cita;
    limpiarModalAtender();

    // Prellenar con datos de la cita
    document.getElementById('atenderIdPaciente').value = cita.id_paciente || '';
    document.getElementById('atenderIdCita').value     = cita.id_agendamiento || '';

    document.getElementById('atenderPacienteNombre').textContent =
        cita.paciente_nombre || 'Paciente';
    document.getElementById('atenderPacienteMeta').textContent =
        [(cita.especie || '--'), (cita.raza || '--'), (cita.propietario_nombre || '')]
            .filter(Boolean).join(' · ');

    // Fecha de la cita como fecha de atención
    const fechaCita = cita.fecha_hora ? cita.fecha_hora.slice(0, 10) : new Date().toISOString().slice(0, 10);
    document.getElementById('atenderFecha').value = fechaCita;

    // Motivo: observaciones de la cita si existen
    document.getElementById('atenderMotivo').value = cita.observaciones || '';

    _bsModalAtender.show();
}

function limpiarModalAtender() {
    ['atenderIdPaciente','atenderIdCita','atenderFecha','atenderMotivo',
     'atenderDiagnostico','atenderTratamiento','atenderMedicacion','atenderObservaciones']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = id === 'atenderFecha' ? new Date().toISOString().slice(0,10) : '';
        });
}

async function guardarAtencionCita() {
    const idPaciente    = Number(document.getElementById('atenderIdPaciente').value || 0);
    const idCita        = Number(document.getElementById('atenderIdCita').value || 0);
    const fechaAtencion = (document.getElementById('atenderFecha').value || '').trim();
    const motivo        = (document.getElementById('atenderMotivo').value || '').trim();

    if (idPaciente <= 0) { mostrarNotificacion('No hay paciente seleccionado.', 'error'); return; }
    if (!fechaAtencion || !motivo) {
        mostrarNotificacion('La fecha de atención y el motivo son obligatorios.', 'error');
        return;
    }

    const btn = document.getElementById('btnGuardarAtencionCita');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';

    try {
        // 1. Guardar historial clínico
        const res = await fetch(BASE_URL + '/veterinaria/pacientes/acciones', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion:                    'guardar_historial',
                id_historial:              0,
                id_paciente:               idPaciente,
                fecha_atencion:            fechaAtencion,
                motivo_consulta:           motivo,
                diagnostico:               (document.getElementById('atenderDiagnostico').value || '').trim(),
                tratamientos_aplicados:    (document.getElementById('atenderTratamiento').value || '').trim(),
                medicacion_recetada:       (document.getElementById('atenderMedicacion').value || '').trim(),
                observaciones_adicionales: (document.getElementById('atenderObservaciones').value || '').trim(),
            }),
        });

        const result = await res.json();
        if (result.status !== 'success') throw new Error(result.message || 'Error al guardar');

        // 2. Marcar la cita como Completada
        if (idCita > 0) {
            await apiPost('completar', { id_agendamiento: idCita });
        }

        if (_bsModalAtender) _bsModalAtender.hide();
        mostrarNotificacion('Atención registrada correctamente.', 'success');
        await cargarCitas();

    } catch (err) {
        mostrarNotificacion(err.message || 'Error al guardar la atención.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Guardar atención';
    }
}

// ─── Tabs ─────────────────────────────────────────────────────
function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            _tabActivo = btn.dataset.tab;
            renderCitas();
        });
    });
}

// ─── Búsqueda ─────────────────────────────────────────────────
function initBusqueda() {
    const input = document.getElementById('buscarCita');
    if (!input) return;
    input.addEventListener('input', () => {
        _busqueda = input.value;
        renderCitas();
    });
}

// ─── Utilidades de UI ────────────────────────────────────────
function mostrarEsqueleto() {
    const lista = document.getElementById('citasLista');
    if (lista) lista.innerHTML = '<div class="citas-skeleton col-12"><i class="bi bi-hourglass-split"></i> Cargando citas…</div>';
}

function mostrarError(msg) {
    const lista = document.getElementById('citasLista');
    if (lista) lista.innerHTML = `
        <div class="citas-empty col-12">
            <i class="bi bi-exclamation-circle" style="color:#fca5a5;"></i>
            <h4 style="color:#dc2626;">Error al cargar</h4>
            <p>${esc(msg)}</p>
        </div>`;
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Notificaciones ──────────────────────────────────────────
function mostrarNotificacion(mensaje, tipo = 'info') {
    const colores = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const iconos  = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };

    const el = document.createElement('div');
    el.style.cssText = `position:fixed;top:24px;right:24px;background:#fff;padding:16px 20px;
        border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.14);z-index:9999;
        display:flex;align-items:center;gap:12px;min-width:280px;
        animation:slideInR .35s ease;border-left:4px solid ${colores[tipo]};`;
    el.innerHTML = `<i class="bi ${iconos[tipo]}" style="font-size:1.4rem;color:${colores[tipo]};flex-shrink:0;"></i>
        <span style="flex:1;font-weight:600;font-size:.875rem;color:#0f172a;">${esc(mensaje)}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.1rem;">
            <i class="bi bi-x-lg"></i>
        </button>`;
    document.body.appendChild(el);
    setTimeout(() => { el.style.animation = 'slideOutR .35s ease'; setTimeout(() => el.remove(), 350); }, 4000);
}

const _notifStyle = document.createElement('style');
_notifStyle.textContent = `
@keyframes slideInR  { from { transform:translateX(110%); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes slideOutR { from { transform:translateX(0); opacity:1; } to { transform:translateX(110%); opacity:0; } }`;
document.head.appendChild(_notifStyle);

// ─── SweetAlert2 (carga perezosa compartida) ──────────────────
async function cargarSweetAlert() {
    if (typeof Swal !== 'undefined') return;
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    document.head.appendChild(script);
    await new Promise(r => { script.onload = r; });
}

// ─── Motivo de cancelación ─────────────────────────────────────
async function pedirMotivoCancelacion() {
    await cargarSweetAlert();

    const { value: motivo, isConfirmed } = await Swal.fire({
        title: '<i class="bi bi-x-circle" style="color:#ef4444;"></i> Cancelar cita',
        input: 'textarea',
        inputLabel: 'Indica el motivo de la cancelación',
        inputPlaceholder: 'Describe brevemente por qué se cancela esta cita...',
        inputAttributes: { 'aria-label': 'Motivo de la cancelación' },
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-x-circle"></i> Cancelar cita',
        cancelButtonText: 'Volver',
        customClass: { confirmButton: 'btn btn-danger px-4', cancelButton: 'btn btn-secondary px-4' },
        buttonsStyling: false,
        inputValidator: (value) => {
            if (!value || !value.trim()) return 'Debes indicar el motivo de la cancelación';
        },
    });

    return isConfirmed ? motivo.trim() : null;
}
