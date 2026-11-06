// ── Soporte de cuentas (superadministrador) ──────────────────
// Los datos del servidor se escriben con textContent, nunca como HTML.

const API = document.body.dataset.api;
let cuentaActual = null;

const NIVELES_BREVO = { ok: 'success', aviso: 'warning', error: 'danger', info: 'info' };
const ESTADOS_ENVIO = { exitoso: 'success', fallido: 'danger', rechazado: 'danger', omitido: 'secondary', pendiente: 'warning', reintentando: 'warning' };

// ── Utilidades ────────────────────────────────────────────────

async function apiGet(accion, params = {}) {
    const r = await fetch(`${API}?${new URLSearchParams({ accion, ...params })}`);
    return r.json();
}

async function apiPost(accion, datos = {}) {
    const r = await fetch(API, {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.body.dataset.csrf },
        body: new URLSearchParams({ accion, ...datos }),
    });
    return r.json();
}

function toast(msg, tipo = 'success') {
    const el = document.getElementById('toastMensaje');
    el.className = `toast align-items-center text-white border-0 bg-${tipo}`;
    document.getElementById('toastBody').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(el, { delay: 5000 }).show();
}

async function conBotonOcupado(boton, accion) {
    boton.disabled = true;
    boton.setAttribute('aria-busy', 'true');
    try {
        await accion();
    } catch (_) {
        toast('No se pudo contactar al servidor. Intenta de nuevo.', 'danger');
    } finally {
        boton.disabled = false;
        boton.removeAttribute('aria-busy');
    }
}

function celda(texto, clase = '') {
    const td = document.createElement('td');
    td.textContent = texto ?? '';
    if (clase) td.className = clase;
    return td;
}

function etiqueta(texto, color) {
    const span = document.createElement('span');
    span.className = `badge text-bg-${color}`;
    span.textContent = texto;
    return span;
}

function filaVacia(tbody, columnas, texto) {
    const tr = document.createElement('tr');
    const td = celda(texto, 'text-center text-muted py-3');
    td.colSpan = columnas;
    tr.append(td);
    tbody.replaceChildren(tr);
}

function formatFecha(valor) {
    const fecha = new Date(String(valor).replace(' ', 'T'));
    return isNaN(fecha) ? valor : fecha.toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' });
}

// ── Búsqueda ──────────────────────────────────────────────────

document.getElementById('formBuscar').addEventListener('submit', e => {
    e.preventDefault();
    const boton = e.submitter;
    conBotonOcupado(boton, async () => {
        const res = await apiGet('buscar', { q: document.getElementById('inputBuscar').value.trim() });
        const lista = document.getElementById('resultados');
        lista.replaceChildren();
        if (!res.ok) { toast(res.mensaje, 'danger'); return; }
        if (!res.data.length) {
            const vacio = document.createElement('div');
            vacio.className = 'list-group-item text-muted';
            vacio.textContent = 'No hay cuentas con ese correo o nombre.';
            lista.append(vacio);
            return;
        }
        for (const c of res.data) {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 flex-wrap';
            const texto = document.createElement('span');
            const nombre = document.createElement('strong');
            nombre.textContent = `${c.nombres} ${c.apellidos}`.trim() || '(sin nombre)';
            texto.append(nombre, ` · ${c.email}`);
            const etiquetas = document.createElement('span');
            etiquetas.className = 'd-flex gap-1';
            etiquetas.append(etiqueta(c.rol, 'secondary'));
            if (String(c.estado).toLowerCase() !== 'activo') etiquetas.append(etiqueta(c.estado, 'danger'));
            item.append(texto, etiquetas);
            item.addEventListener('click', () => abrirCuenta(c.id_usuario));
            lista.append(item);
        }
    });
});

// ── Detalle ───────────────────────────────────────────────────

async function abrirCuenta(id) {
    const res = await apiGet('cuenta', { id });
    if (!res.ok) { toast(res.mensaje, 'danger'); return; }
    cuentaActual = res.cuenta;
    document.getElementById('resultados').replaceChildren();
    document.getElementById('cajaClave').classList.add('d-none');

    const c = res.cuenta;
    document.getElementById('cuentaNombre').textContent = `${c.nombres} ${c.apellidos}`.trim() || '(sin nombre)';
    document.getElementById('cuentaEmail').textContent = c.email;
    const etiquetas = document.getElementById('cuentaEtiquetas');
    etiquetas.replaceChildren(etiqueta(c.rol, 'secondary'));
    etiquetas.append(String(c.estado).toLowerCase() === 'activo' ? etiqueta('Activa', 'success') : etiqueta(c.estado, 'danger'));
    if (c.debe_cambiar_clave) etiquetas.append(etiqueta('Debe cambiar su clave temporal', 'warning'));

    const btnClave = document.getElementById('btnClaveTemporal');
    btnClave.disabled = res.es_yo;
    btnClave.title = res.es_yo ? 'Para tu cuenta usa Mi perfil' : '';

    pintarBrevo(res.brevo);
    pintarHistorial(res.historial);
    document.getElementById('detalleCuenta').classList.remove('d-none');
}

function pintarBrevo(brevo) {
    const resumen = document.getElementById('resumenBrevo');
    const tbody = document.getElementById('tbodyBrevo');
    if (!brevo.disponible) {
        resumen.className = 'alert alert-secondary mb-3';
        resumen.textContent = brevo.mensaje;
        filaVacia(tbody, 3, 'Sin datos');
        return;
    }
    resumen.className = `alert alert-${NIVELES_BREVO[brevo.resumen.nivel] || 'info'} mb-3`;
    resumen.textContent = brevo.resumen.texto;
    if (!brevo.eventos.length) { filaVacia(tbody, 3, 'Sin envíos'); return; }
    tbody.replaceChildren(...brevo.eventos.map(ev => {
        const tr = document.createElement('tr');
        const tdEvento = document.createElement('td');
        tdEvento.append(etiqueta(ev.evento, NIVELES_BREVO[ev.nivel] || 'info'));
        if (ev.motivo) {
            const motivo = document.createElement('span');
            motivo.className = 'motivo';
            motivo.textContent = ev.motivo;
            tdEvento.append(motivo);
        }
        tr.append(celda(formatFecha(ev.fecha)), tdEvento, celda(ev.asunto));
        return tr;
    }));
}

function pintarHistorial(filas) {
    const tbody = document.getElementById('tbodyHistorial');
    if (!filas.length) { filaVacia(tbody, 3, 'El sistema no le ha enviado correos'); return; }
    tbody.replaceChildren(...filas.map(f => {
        const tr = document.createElement('tr');
        const tdEstado = document.createElement('td');
        tdEstado.append(etiqueta(f.estado_envio, ESTADOS_ENVIO[f.estado_envio] || 'secondary'));
        if (f.mensaje_error) {
            const motivo = document.createElement('span');
            motivo.className = 'd-block small text-muted';
            motivo.textContent = f.mensaje_error;
            tdEstado.append(motivo);
        }
        tr.append(celda(formatFecha(f.fecha_envio)), celda(f.tipo_notificacion || 'general'), tdEstado);
        return tr;
    }));
}

// ── Acciones ──────────────────────────────────────────────────

document.getElementById('btnRefrescar').addEventListener('click', e => {
    if (cuentaActual) conBotonOcupado(e.currentTarget, () => abrirCuenta(cuentaActual.id_usuario));
});

document.getElementById('btnPrueba').addEventListener('click', e => {
    if (!cuentaActual) return;
    if (!confirm(`Se enviará un correo REAL de prueba a ${cuentaActual.email}. ¿Continuar?`)) return;
    conBotonOcupado(e.currentTarget, async () => {
        const res = await apiPost('enviar_prueba', { id: cuentaActual.id_usuario });
        toast(res.mensaje, res.ok ? 'success' : 'danger');
        if (res.ok) setTimeout(() => abrirCuenta(cuentaActual.id_usuario), 8000);
    });
});

document.getElementById('btnClaveTemporal').addEventListener('click', e => {
    if (!cuentaActual) return;
    const aviso = `Se reemplazará la contraseña de ${cuentaActual.email} por una clave temporal y se cerrarán sus sesiones abiertas.\n\n`
        + 'El usuario deberá cambiarla al entrar. ¿Continuar?';
    if (!confirm(aviso)) return;
    conBotonOcupado(e.currentTarget, async () => {
        const res = await apiPost('clave_temporal', { id: cuentaActual.id_usuario });
        if (!res.ok) { toast(res.mensaje, 'danger'); return; }
        document.getElementById('claveTemporal').textContent = res.clave;
        document.getElementById('cajaClave').classList.remove('d-none');
        toast(res.mensaje);
        const etiquetas = document.getElementById('cuentaEtiquetas');
        if (!etiquetas.textContent.includes('clave temporal')) etiquetas.append(etiqueta('Debe cambiar su clave temporal', 'warning'));
    });
});

document.getElementById('btnCopiarClave').addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(document.getElementById('claveTemporal').textContent);
        toast('Clave copiada');
    } catch (_) {
        toast('No se pudo copiar: selecciónala y cópiala a mano.', 'warning');
    }
});
