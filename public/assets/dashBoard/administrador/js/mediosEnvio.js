/* ── Medios de Envío — lógica del panel ─────────────────────── */

const API = document.body.dataset.api;
let canalesCache = [];

const ICONOS_CANAL = {
    email:  'bi-envelope-at-fill',
    in_app: 'bi-bell-fill',
    push:   'bi-phone-fill',
};

const TIPOS_NOTIFICACION = [
    { value: 'cita',         label: 'Cita agendada' },
    { value: 'recordatorio', label: 'Recordatorio de cita' },
    { value: 'vacuna',       label: 'Alerta de vacuna' },
    { value: 'tratamiento',  label: 'Tratamiento' },
    { value: 'inventario',   label: 'Alerta de inventario' },
    { value: 'general',      label: 'General' },
];

// ── Utilidades ────────────────────────────────────────────────

async function apiGet(accion, params = {}) {
    const qs = new URLSearchParams({ accion, ...params }).toString();
    const r = await fetch(`${API}?${qs}`);
    return r.json();
}

async function apiPost(accion, body = {}) {
    const r = await fetch(`${API}?accion=${accion}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...body, accion }),
    });
    return r.json();
}

function toast(msg, tipo = 'success') {
    const el  = document.getElementById('toastMensaje');
    el.className = `toast align-items-center text-white border-0 bg-${tipo}`;
    document.getElementById('toastBody').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
}

function togglePass(id) {
    const i = document.getElementById(id);
    i.type = i.type === 'password' ? 'text' : 'password';
}
window.togglePass = togglePass;

// ── TAB 1: CANALES ────────────────────────────────────────────

async function cargarCanales() {
    const res = await apiGet('canales');
    if (!res.ok) return;
    canalesCache = res.data;

    const cont = document.getElementById('listaCanales');
    cont.innerHTML = '';

    res.data.forEach(c => {
        const icono = ICONOS_CANAL[c.codigo_canal] ?? 'bi-send';
        const habilitado = +c.habilitado;

        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
        <div class="canal-card card h-100 p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="canal-icon">
                    <i class="bi ${icono}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-0 fw-semibold">${c.nombre_canal}</h6>
                            <small class="text-muted">${c.descripcion ?? ''}</small>
                        </div>
                        <span class="badge badge-estado ${habilitado ? 'bg-success' : 'bg-secondary'}">
                            ${habilitado ? 'Activo' : 'Inactivo'}
                        </span>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox"
                               id="toggle_${c.id_canal}"
                               data-id="${c.id_canal}"
                               ${habilitado ? 'checked' : ''}
                               onchange="toggleCanal(this)">
                        <label class="form-check-label" for="toggle_${c.id_canal}">
                            ${habilitado ? 'Habilitar' : 'Deshabilitar'}
                        </label>
                    </div>
                </div>
            </div>
        </div>`;
        cont.appendChild(col);
    });

    poblarSelectoresCanal(res.data);
}

async function toggleCanal(chk) {
    const idCanal   = +chk.dataset.id;
    const habilitado = chk.checked;
    const res = await apiPost('toggle_canal', { id_canal: idCanal, habilitado });
    if (res.ok) {
        toast(res.mensaje);
        const badge = chk.closest('.canal-card').querySelector('.badge-estado');
        badge.className = `badge badge-estado ${habilitado ? 'bg-success' : 'bg-secondary'}`;
        badge.textContent = habilitado ? 'Activo' : 'Inactivo';
        chk.nextElementSibling.textContent = habilitado ? 'Habilitar' : 'Deshabilitar';
    } else {
        toast(res.mensaje, 'danger');
        chk.checked = !habilitado;
    }
}
window.toggleCanal = toggleCanal;

// ── TAB 2: PARÁMETROS TÉCNICOS ────────────────────────────────

function poblarSelectoresCanal(canales) {
    const btnsCfg  = document.getElementById('btnsCanalConfig');
    const selPlant = document.getElementById('selectCanalPlantilla');

    btnsCfg.innerHTML  = '';
    selPlant.innerHTML = '<option value="">-- Seleccionar --</option>';

    canales.forEach(c => {
        // Botones Tab 2
        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'btn-canal-sel';
        btn.dataset.codigo = c.codigo_canal;
        btn.dataset.id     = c.id_canal;
        btn.innerHTML = `<i class="bi ${ICONOS_CANAL[c.codigo_canal] ?? 'bi-send'} me-1"></i>${c.nombre_canal}`;
        btn.addEventListener('click', () => seleccionarCanalConfig(c, btn));
        btnsCfg.appendChild(btn);

        // Select Tab 3
        const opt = document.createElement('option');
        opt.value       = c.id_canal;
        opt.textContent = c.nombre_canal;
        opt.dataset.codigo = c.codigo_canal;
        selPlant.appendChild(opt);
    });
}

function seleccionarCanalConfig(canal, btnClicado) {
    document.querySelectorAll('.btn-canal-sel').forEach(b => b.classList.remove('activo'));
    btnClicado.classList.add('activo');

    ['formSmtpWrap', 'formPushWrap', 'formInAppWrap'].forEach(id =>
        document.getElementById(id).classList.add('d-none'));
    document.getElementById('seleccionaCanalMsg').classList.add('d-none');

    if (canal.codigo_canal === 'email') {
        cargarFormSmtp(canal);
    } else if (canal.codigo_canal === 'push') {
        cargarFormPush(canal);
    } else if (canal.codigo_canal === 'in_app') {
        cargarFormInApp(canal);
    }
}

function cargarFormSmtp(c) {
    const wrap = document.getElementById('formSmtpWrap');
    wrap.classList.remove('d-none');
    const form = document.getElementById('formSmtp');
    form.querySelector('[name=id_canal]').value              = c.id_canal;
    form.querySelector('[name=smtp_host]').value             = c.smtp_host ?? '';
    form.querySelector('[name=smtp_port]').value             = c.smtp_port ?? '';
    form.querySelector('[name=smtp_encriptacion]').value     = c.smtp_encriptacion ?? 'smtps';
    form.querySelector('[name=smtp_usuario]').value          = c.smtp_usuario ?? '';
    form.querySelector('[name=smtp_remitente]').value        = c.smtp_remitente ?? '';
    form.querySelector('[name=smtp_nombre_remitente]').value = c.smtp_nombre_remitente ?? '';
    form.querySelector('[name=max_reintentos]').value        = c.max_reintentos ?? 3;
    form.querySelector('[name=intervalo_reintento_seg]').value = c.intervalo_reintento_seg ?? 30;
    document.getElementById('inputSmtpPass').value = '';
}

function cargarFormPush(c) {
    const wrap = document.getElementById('formPushWrap');
    wrap.classList.remove('d-none');
    const form = document.getElementById('formPush');
    form.querySelector('[name=id_canal]').value              = c.id_canal;
    form.querySelector('[name=push_endpoint]').value         = c.push_endpoint ?? '';
    form.querySelector('[name=max_reintentos]').value        = c.max_reintentos ?? 3;
    form.querySelector('[name=intervalo_reintento_seg]').value = c.intervalo_reintento_seg ?? 30;
    document.getElementById('inputPushKey').value = '';
}

function cargarFormInApp(c) {
    const wrap = document.getElementById('formInAppWrap');
    wrap.classList.remove('d-none');
    const form = document.getElementById('formInApp');
    form.querySelector('[name=id_canal]').value       = c.id_canal;
    form.querySelector('[name=max_reintentos]').value = c.max_reintentos ?? 1;
}

// Guardar config genérica (todos los formularios de Tab 2 usan esto)
async function guardarFormConfig(form) {
    const data = Object.fromEntries(new FormData(form).entries());
    // No enviar password vacío
    if ('smtp_password' in data && data.smtp_password === '') delete data.smtp_password;
    if ('push_api_key'  in data && data.push_api_key  === '') delete data.push_api_key;

    const res = await apiPost('actualizar_config', data);
    toast(res.mensaje, res.ok ? 'success' : 'danger');
}

document.getElementById('formSmtp').addEventListener('submit',  e => { e.preventDefault(); guardarFormConfig(e.target); });
document.getElementById('formPush').addEventListener('submit',  e => { e.preventDefault(); guardarFormConfig(e.target); });
document.getElementById('formInApp').addEventListener('submit', e => { e.preventDefault(); guardarFormConfig(e.target); });

async function probarSmtp() {
    toast('Función de prueba SMTP disponible vía cron de diagnóstico.', 'warning');
}
window.probarSmtp = probarSmtp;

// ── TAB 3: PLANTILLAS ─────────────────────────────────────────

const selCanalPlant = document.getElementById('selectCanalPlantilla');
const selTipoPlant  = document.getElementById('selectTipoPlantilla');

// Poblar tipos al seleccionar canal
selCanalPlant.addEventListener('change', async () => {
    selTipoPlant.innerHTML = '<option value="">-- Seleccionar --</option>';
    selTipoPlant.disabled  = true;
    document.getElementById('editorPlantilla').classList.add('d-none');

    if (!selCanalPlant.value) return;

    const res = await apiGet('plantillas', { id_canal: selCanalPlant.value });
    const tiposConPlantilla = (res.data ?? []).map(p => p.tipo_notificacion);

    TIPOS_NOTIFICACION.forEach(t => {
        const opt = document.createElement('option');
        opt.value       = t.value;
        opt.textContent = t.label + (tiposConPlantilla.includes(t.value) ? ' ✓' : '');
        selTipoPlant.appendChild(opt);
    });
    selTipoPlant.disabled = false;

    // Guardar data de plantillas para cargar al seleccionar tipo
    selTipoPlant._plantillas = res.data ?? [];
});

selTipoPlant.addEventListener('change', () => {
    const idCanal = selCanalPlant.value;
    const tipo    = selTipoPlant.value;
    const codigo  = selCanalPlant.selectedOptions[0]?.dataset.codigo ?? '';
    if (!idCanal || !tipo) return;

    const plantilla = (selTipoPlant._plantillas ?? []).find(p => p.tipo_notificacion === tipo);
    cargarEditorPlantilla(idCanal, tipo, codigo, plantilla);
});

function cargarEditorPlantilla(idCanal, tipo, codigoCanal, plantilla) {
    document.getElementById('plantIdCanal').value = idCanal;
    document.getElementById('plantTipo').value    = tipo;
    document.getElementById('plantAsunto').value  = plantilla?.asunto       ?? '';
    document.getElementById('plantHtml').value    = plantilla?.cuerpo_html  ?? '';
    document.getElementById('plantTexto').value   = plantilla?.cuerpo_texto ?? '';

    // Mostrar asunto y HTML sólo para email
    const esEmail = codigoCanal === 'email';
    document.getElementById('wrapAsunto').classList.toggle('d-none', !esEmail);
    document.getElementById('wrapHtml').classList.toggle('d-none',   !esEmail);

    // Variables disponibles
    const varsEl = document.getElementById('varsDisponibles');
    varsEl.innerHTML = '';
    let vars = [];
    try { vars = JSON.parse(plantilla?.variables_disponibles ?? '[]'); } catch (_) {}
    if (vars.length) {
        const titulo = document.createElement('div');
        titulo.className = 'text-muted small mb-1';
        titulo.textContent = 'Variables disponibles (clic para insertar):';
        varsEl.appendChild(titulo);
        vars.forEach(v => {
            const badge = document.createElement('span');
            badge.className = 'var-badge';
            badge.textContent = `{${v}}`;
            badge.title = 'Clic para copiar al portapapeles';
            badge.addEventListener('click', () => {
                navigator.clipboard.writeText(`{${v}}`).then(() => toast(`{${v}} copiado`));
            });
            varsEl.appendChild(badge);
        });
    }

    document.getElementById('editorPlantilla').classList.remove('d-none');
}

document.getElementById('formPlantilla').addEventListener('submit', async e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());
    const res  = await apiPost('guardar_plantilla', data);
    toast(res.mensaje, res.ok ? 'success' : 'danger');
    if (res.ok) selCanalPlant.dispatchEvent(new Event('change')); // refresca marcas ✓
});

// ── TAB 4: MONITOR DE FALLOS ──────────────────────────────────

async function cargarFallos() {
    const horas = document.getElementById('selectHorasMonitor').value;
    const res   = await apiGet('fallos', { horas, limite: 200 });
    renderResumen(res.resumen ?? []);
    renderTablaFallos(res.data ?? []);
}
window.cargarFallos = cargarFallos;

function renderResumen(resumen) {
    const cont = document.getElementById('resumenMonitoreo');
    cont.innerHTML = '';

    let totFallidos = 0, totExitosos = 0, totPendientes = 0;
    resumen.forEach(r => {
        totFallidos  += +r.fallidos;
        totExitosos  += +r.exitosos;
        totPendientes+= +r.pendientes;
    });

    const tarjetas = [
        { label: 'Exitosos',   cifra: totExitosos,   clase: 'success' },
        { label: 'Fallidos',   cifra: totFallidos,   clase: 'danger'  },
        { label: 'Pendientes', cifra: totPendientes, clase: 'warning' },
    ];

    tarjetas.forEach(t => {
        const col = document.createElement('div');
        col.className = 'col-md-3 col-6';
        col.innerHTML = `
        <div class="card-resumen">
            <div class="cifra ${t.clase}">${t.cifra}</div>
            <div class="etiqueta">${t.label}</div>
        </div>`;
        cont.appendChild(col);
    });

    // Desglose por canal
    resumen.forEach(r => {
        const col = document.createElement('div');
        col.className = 'col-md-3 col-6';
        col.innerHTML = `
        <div class="card-resumen">
            <div class="cifra ${+r.fallidos > 0 ? 'danger' : 'success'}">${r.total}</div>
            <div class="etiqueta">${r.canal} · ${r.fallidos} fallos</div>
        </div>`;
        cont.appendChild(col);
    });
}

function renderTablaFallos(filas) {
    const tbody = document.getElementById('tbodyFallos');
    if (!filas.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin fallos en el período seleccionado.</td></tr>';
        return;
    }
    tbody.innerHTML = filas.map((f, i) => `
    <tr>
        <td>${i + 1}</td>
        <td><span class="badge bg-secondary">${f.medio_notificacion}</span></td>
        <td>${f.destinatario ?? '—'}</td>
        <td>${f.nombre_propietario ?? '—'}</td>
        <td>${formatFecha(f.fecha_envio)}</td>
        <td><span class="badge bg-warning text-dark">${f.intentos_envio}</span></td>
        <td class="text-danger small" style="max-width:220px;word-break:break-word">${f.mensaje_error ?? '—'}</td>
    </tr>`).join('');
}

function formatFecha(str) {
    if (!str) return '—';
    const d = new Date(str);
    return d.toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' });
}

function abrirModalReintentar() {
    new bootstrap.Modal(document.getElementById('modalReintentar')).show();
}
window.abrirModalReintentar = abrirModalReintentar;

async function ejecutarReintento() {
    const canal = document.getElementById('selectReintentarCanal').value;
    const res   = await apiPost('reintentar', { canal });
    bootstrap.Modal.getInstance(document.getElementById('modalReintentar')).hide();
    toast(res.mensaje, res.ok ? 'success' : 'danger');
    if (res.ok) cargarFallos();
}
window.ejecutarReintento = ejecutarReintento;

// ── Bootstrap de tabs y carga inicial ────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    cargarCanales();

    // Cargar fallos al activar ese tab
    document.querySelector('[data-bs-target="#tab-monitor"]')
        .addEventListener('shown.bs.tab', cargarFallos);

    document.getElementById('selectHorasMonitor')
        .addEventListener('change', cargarFallos);
});
