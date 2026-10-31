/* ── Medios de Envío — lógica del panel ─────────────────────── */

const API = document.body.dataset.api;
let canalesCache = [];

const ICONOS_CANAL = {
    email:  'bi-envelope-at-fill',
    in_app: 'bi-bell-fill',
    push:   'bi-phone-fill',
};

// Solo los correos que el sistema envía de verdad usando estas plantillas
const TIPOS_NOTIFICACION = [
    { value: 'cita',         label: 'Cita agendada' },
    { value: 'recordatorio', label: 'Recordatorio de cita' },
    { value: 'cancelacion',  label: 'Cita cancelada' },
];

const NOMBRES_TIPO = {
    cita: 'Cita', recordatorio: 'Recordatorio', cancelacion: 'Cancelación',
    bienvenida: 'Bienvenida', reporte: 'Ficha clínica', seguridad: 'Contraseña', prueba: 'Prueba',
};

let transporteCache = null;

// ── Utilidades ────────────────────────────────────────────────

async function apiGet(accion, params = {}) {
    const qs = new URLSearchParams({ accion, ...params }).toString();
    const r = await fetch(`${API}?${qs}`);
    return r.json();
}

async function apiPost(accion, body = {}) {
    const r = await fetch(`${API}?accion=${accion}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.body.dataset.csrf },
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
    transporteCache = res.transporte ?? null;

    const cont = document.getElementById('listaCanales');
    cont.innerHTML = '';

    res.data.forEach(c => {
        const icono = ICONOS_CANAL[c.codigo_canal] ?? 'bi-send';
        const habilitado = +c.habilitado;
        const disponible = c.codigo_canal !== 'push'; // el envío push aún no existe

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
                        <span class="badge badge-estado ${!disponible ? 'bg-warning text-dark' : habilitado ? 'bg-success' : 'bg-secondary'}">
                            ${!disponible ? 'No disponible' : habilitado ? 'Activo' : 'Inactivo'}
                        </span>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox"
                               id="toggle_${c.id_canal}"
                               data-id="${c.id_canal}"
                               ${habilitado ? 'checked' : ''}
                               ${disponible ? '' : 'disabled'}
                               onchange="toggleCanal(this)">
                        <label class="form-check-label" for="toggle_${c.id_canal}">
                            ${!disponible ? 'Próximamente' : habilitado ? 'Habilitado' : 'Deshabilitado'}
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
        chk.nextElementSibling.textContent = habilitado ? 'Habilitado' : 'Deshabilitado';
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

        // Select Tab 3: solo correo (las notificaciones internas y push no usan plantillas)
        if (c.codigo_canal !== 'email') return;
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
    const info = document.querySelector('#infoTransporte span');
    if (transporteCache) {
        info.textContent = 'Transporte en uso: ' + transporteCache.detalle;
    }
    // Estado de la contraseña guardada (nunca se envía al navegador, solo su estado)
    const aviso = document.getElementById('avisoCredencial');
    const avisos = {
        texto_plano: 'La contraseña SMTP guardada está en texto plano. Configura APP_ENCRYPTION_KEY en .env y ejecuta scripts/cifrar-credenciales-smtp.php, o vuelve a escribirla aquí para guardarla cifrada.',
        vacia: 'No hay contraseña SMTP guardada aquí: se usa SMTP_PASS de .env, solo si el servidor y el usuario son los de .env. Con otro servidor o usuario, escribe la contraseña aquí.',
        ilegible: 'La contraseña SMTP guardada está cifrada pero no se puede descifrar con la APP_ENCRYPTION_KEY actual (¿cambió la clave?). El correo por SMTP no se enviará hasta que vuelvas a escribir la contraseña aquí.',
    };
    aviso.classList.toggle('d-none', !avisos[c.smtp_password_estado]);
    aviso.querySelector('span').textContent = avisos[c.smtp_password_estado] ?? '';
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

function cargarFormPush() {
    document.getElementById('formPushWrap').classList.remove('d-none');
}

function cargarFormInApp() {
    document.getElementById('formInAppWrap').classList.remove('d-none');
}

// Guardar config genérica (todos los formularios de Tab 2 usan esto)
async function guardarFormConfig(form) {
    const data = Object.fromEntries(new FormData(form).entries());
    // No enviar password vacío
    if ('smtp_password' in data && data.smtp_password === '') delete data.smtp_password;

    await conBotonOcupado(form.querySelector('[type=submit]'), async () => {
        const res = await apiPost('actualizar_config', data);
        toast(res.mensaje, res.ok ? 'success' : 'danger');
        if (res.ok) {
            // Recarga los datos guardados y el estado de la credencial, manteniendo el canal seleccionado
            await cargarCanales();
            const email = canalesCache.find(c => c.codigo_canal === 'email');
            const boton = document.querySelector('.btn-canal-sel[data-codigo="email"]');
            if (email && boton) seleccionarCanalConfig(email, boton);
        }
    });
}

// Deshabilita el botón mientras dura la petición y avisa si falla la conexión
async function conBotonOcupado(boton, accion) {
    if (boton) {
        boton.disabled = true;
        boton.setAttribute('aria-busy', 'true');
    }
    try {
        await accion();
    } catch (_) {
        toast('No se pudo contactar al servidor. Intenta de nuevo.', 'danger');
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.removeAttribute('aria-busy');
        }
    }
}

document.getElementById('formSmtp').addEventListener('submit',  e => { e.preventDefault(); guardarFormConfig(e.target); });

// Envía un correo real al administrador con la configuración guardada
async function probarSmtp() {
    const destino = document.body.dataset.adminEmail || 'tu correo';
    // Confirmación explícita: este botón envía un correo REAL
    if (!confirm(`Se enviará un correo REAL de prueba a ${destino} con la configuración guardada (no la que tengas sin guardar en el formulario). ¿Continuar?`)) {
        return;
    }
    const btn = document.getElementById('btnProbarCorreo');
    btn.disabled = true;
    try {
        const res = await apiPost('probar_correo');
        toast(res.mensaje, res.ok ? 'success' : 'danger');
    } catch (_) {
        toast('No se pudo contactar al servidor.', 'danger');
    } finally {
        btn.disabled = false;
    }
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
    await conBotonOcupado(e.target.querySelector('[type=submit]'), async () => {
        const res = await apiPost('guardar_plantilla', data);
        toast(res.mensaje, res.ok ? 'success' : 'danger');
        if (res.ok) selCanalPlant.dispatchEvent(new Event('change')); // refresca marcas ✓
    });
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

    let totFallidos = 0, totExitosos = 0, totPendientes = 0, totOmitidos = 0;
    resumen.forEach(r => {
        totFallidos  += +r.fallidos + +(r.rechazados ?? 0);
        totExitosos  += +r.exitosos;
        totPendientes+= +r.pendientes;
        totOmitidos  += +(r.omitidos ?? 0);
    });

    const tarjetas = [
        { label: 'Exitosos',   cifra: totExitosos,   clase: 'success' },
        { label: 'Fallidos',   cifra: totFallidos,   clase: 'danger'  },
        { label: 'Pendientes', cifra: totPendientes, clase: 'warning' },
        { label: 'Omitidos (canal apagado)', cifra: totOmitidos, clase: 'secondary' },
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

    // Desglose por canal (solo si hay más de uno)
    if (resumen.length < 2) return;
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
        <td><span class="badge bg-secondary">${escapar(NOMBRES_TIPO[f.tipo_notificacion] ?? f.tipo_notificacion ?? f.medio_notificacion)}</span></td>
        <td>${escapar(f.destinatario ?? '—')}</td>
        <td>${escapar(f.asunto ?? '—')}${+f.reintentable ? '' : ' <span class="badge bg-light text-muted" title="No guarda contenido: no se puede reenviar">sin reenvío</span>'}</td>
        <td>${formatFecha(f.fecha_envio)}</td>
        <td><span class="badge bg-warning text-dark">${f.intentos_envio}</span>${f.estado_envio === 'rechazado' ? ' <span class="badge bg-danger" title="Error permanente: no se reintenta">rechazado</span>' : ''}</td>
        <td class="text-danger small" style="max-width:240px;word-break:break-word">${escapar(f.mensaje_error ?? '—')}</td>
    </tr>`).join('');
}

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = String(texto);
    return div.innerHTML;
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
    const btn = document.getElementById('btnEjecutarReintento');
    btn.disabled = true;
    try {
        const res = await apiPost('reintentar');
        bootstrap.Modal.getInstance(document.getElementById('modalReintentar')).hide();
        toast(res.mensaje, res.ok ? (res.fallidos > 0 ? 'warning' : 'success') : 'danger');
        cargarFallos();
    } catch (_) {
        toast('No se pudo contactar al servidor.', 'danger');
    } finally {
        btn.disabled = false;
    }
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
