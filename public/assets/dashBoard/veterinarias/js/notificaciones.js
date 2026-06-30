(function () {
    'use strict';

    const BASE_URL = window.BASE_URL || window.location.origin;
    const pageList = document.getElementById('notificationsPageList');
    const pageEmpty = document.getElementById('notificationsEmpty');
    const btnMarkAllRead = document.getElementById('btnMarkAllRead');
    const notificationBadge = document.getElementById('notificationBadge');
    const apiUrl = `${BASE_URL}/veterinario/api/notificaciones`;

    function escapeHtml(value) {
        if (typeof value !== 'string') return '';
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return 'Sin fecha';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return 'Sin fecha';
        return date.toLocaleString('es-CO', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function showError(message) {
        if (window.Toast && Toast.mostrar) {
            Toast.mostrar(message, 'error');
        } else {
            alert(message);
        }
    }

    const ICONO_MAP = {
        'cita':             'bi-calendar-check',
        'vacuna':           'bi-syringe',
        'seguimiento':      'bi-activity',
        'tratamiento':      'bi-capsule',
        'acceso_historial': 'bi-file-earmark-text',
        'general':          'bi-bell',
    };

    const ETIQUETA_MAP = {
        'cita':             'Cita',
        'vacuna':           'Vacuna',
        'seguimiento':      'Seguimiento',
        'tratamiento':      'Tratamiento',
        'acceso_historial': 'Historial',
        'general':          'General',
    };

    function buildItem(item) {
        const tipo      = item.tipo || 'general';
        const leida     = item.leido || item.leida;
        const estado    = item.estado || 'enviada';
        const cardClass = `notif-card tipo-${escapeHtml(tipo)} ${leida ? 'leida' : 'no-leida'}`;
        const icono     = ICONO_MAP[tipo]    || 'bi-bell';
        const etiqueta  = ETIQUETA_MAP[tipo] || tipo;
        const titulo    = escapeHtml(item.titulo  || item.mensaje || 'Sin título');
        const mensaje   = escapeHtml(item.mensaje || '');
        const tiempo    = escapeHtml(item.tiempo  || formatDate(item.fecha || item.fecha_creacion));

        let html = `
            <div class="${cardClass}" data-notification-id="${item.id}">
                <div class="notif-icon-wrap">
                    <i class="bi ${icono}" aria-hidden="true"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-meta">
                        <div class="notif-title-row">
                            <p class="notif-title">${titulo}</p>
                            <span class="notif-pill">${etiqueta}</span>
                        </div>
                        <span class="notif-time">${tiempo}</span>
                    </div>
                    ${mensaje ? `<p class="notif-message">${mensaje}</p>` : ''}`;

        if (estado === 'cancelada' && item.motivo_cancelacion) {
            html += `<div class="notif-cancel-reason">
                         <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                         ${escapeHtml(item.motivo_cancelacion)}
                     </div>`;
        }

        html += `</div>`;
        if (!leida) {
            html += `<span class="notif-pip" title="Sin leer"></span>`;
        }
        html += `</div>`;
        return html;
    }

    function renderPageNotifications(items) {
        if (!pageList) return;

        if (!Array.isArray(items) || items.length === 0) {
            pageList.innerHTML = '';
            pageEmpty?.classList.remove('is-hidden');
            return;
        }

        pageEmpty?.classList.add('is-hidden');
        pageList.innerHTML = items.map(buildItem).join('');
    }

    function updateBadge(count) {
        if (!notificationBadge) return;
        if (!count) {
            notificationBadge.classList.add('is-hidden');
            return;
        }
        notificationBadge.classList.remove('is-hidden');
        notificationBadge.textContent = String(count);
    }

    async function fetchNotifications() {
        try {
            const response = await fetch(`${apiUrl}?accion=listar&limite=100`, { credentials: 'include' });
            if (!response.ok) throw new Error('Error al cargar notificaciones');
            return await response.json();
        } catch (error) {
            showError('No se pudieron cargar las notificaciones.');
            return null;
        }
    }

    async function markAllRead() {
        try {
            const response = await fetch(`${apiUrl}?accion=todas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({}),
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status !== 'success') showError(data.mensaje || 'No se pudieron marcar todas como leídas.');
            return data.status === 'success';
        } catch (error) {
            showError('No se pudieron marcar todas las notificaciones como leídas.');
            return false;
        }
    }

    async function loadNotifications() {
        const data = await fetchNotifications();
        if (!data) {
            renderPageNotifications([]);
            updateBadge(0);
            return;
        }

        const items = data.notificaciones || [];
        renderPageNotifications(items);
        updateBadge(data.no_leidas || 0);
    }

    async function markAllAsRead() {
        const ok = await markAllRead();
        if (ok) {
            await loadNotifications();
        }
    }

    pageList?.addEventListener('click', async event => {
        const row = event.target.closest('.notif-card');
        if (!row) return;
        const id = row.dataset.notificationId;
        if (!id) return;

        try {
            const response = await fetch(`${apiUrl}?accion=leida`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: Number(id) }),
                credentials: 'include'
            });
            const result = await response.json();
            if (result.status === 'success') {
                await loadNotifications();
            } else {
                showError(result.mensaje || 'No se pudo marcar como leída.');
            }
        } catch (error) {
            showError('No se pudo marcar la notificación como leída.');
        }
    });

    btnMarkAllRead?.addEventListener('click', markAllAsRead);
    loadNotifications();
    window.addEventListener('notificacion:nueva', () => {
        loadNotifications();
    });
    // Polling automático cada 30 segundos como respaldo
    setInterval(loadNotifications, 30000);
})();


/**
 * FRAGMENTO PARA AGREGAR EN notificaciones.js DEL VETERINARIO
 *
 * Agrega esto al final del archivo notificaciones.js existente.
 * Detecta notificaciones de tipo 'acceso_historial' y las renderiza
 * con botones de Aprobar / Rechazar en lugar del formato normal.
 */

/* ── Duración de acceso ── */
const OPCIONES_DURACION = [
    { horas: 24,  label: '24 horas' },
    { horas: 48,  label: '48 horas' },
    { horas: 72,  label: '3 días'   },
    { horas: 168, label: '7 días'   },
];

/**
 * Llama a esta función desde tu renderizador de notificaciones
 * cuando el tipo sea 'acceso_historial'.
 *
 * Ejemplo de uso en tu código existente:
 *
 *   if (notif.tipo === 'acceso_historial') {
 *       return renderizarSolicitudAcceso(notif);
 *   }
 */
function renderizarSolicitudAcceso(notif) {
    const opcionesHTML = OPCIONES_DURACION.map(o =>
        `<option value="${o.horas}">${o.label}</option>`
    ).join('');

    return `
        <div class="notification-item notification-item--acceso" data-id="${notif.id}" data-ref="${notif.referencia_id}">
            <div class="notification-icon">
                <i class="bi bi-folder-symlink" style="color:#f59e0b"></i>
            </div>
            <div class="notification-body" style="flex:1">
                <p class="notification-msg">${escHtml(notif.mensaje)}</p>
                <span class="notification-time">${formatearFecha(notif.fecha)}</span>

                <div class="acceso-actions mt-2 d-flex gap-2 flex-wrap align-items-center">
                    <select class="form-select form-select-sm" id="dur_${notif.referencia_id}"
                            style="width:auto;min-width:120px">
                        ${opcionesHTML}
                    </select>
                    <button class="btn btn-success btn-sm"
                            onclick="aprobarAcceso(${notif.referencia_id}, ${notif.id})">
                        <i class="bi bi-check-circle me-1"></i>Aprobar
                    </button>
                    <button class="btn btn-outline-danger btn-sm"
                            onclick="rechazarAcceso(${notif.referencia_id}, ${notif.id})">
                        <i class="bi bi-x-circle me-1"></i>Rechazar
                    </button>
                </div>
            </div>
        </div>`;
}

async function aprobarAcceso(id_acceso, notif_id) {
    const selectEl = document.getElementById(`dur_${id_acceso}`);
    const duracion  = parseInt(selectEl?.value || '24');

    const btn = selectEl?.closest('.acceso-actions')?.querySelector('.btn-success');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Aprobando...'; }

    try {
        const res  = await fetch(`${window.BASE_URL}/veterinaria/api/historial/acceso/aprobar`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ id_acceso, duracion_horas: duracion }),
        });
        const data = await res.json();

        if (data.status === 'ok') {
            /* Eliminar el item de la lista y marcar notificación como leída */
            const item = document.querySelector(`.notification-item--acceso[data-ref="${id_acceso}"]`);
            if (item) {
                item.style.transition = 'opacity .3s';
                item.style.opacity    = '0';
                setTimeout(() => item.remove(), 300);
            }
            marcarNotificacionLeida(notif_id);
            mostrarToast('Acceso aprobado correctamente.', 'success');
        } else {
            mostrarToast(data.message || 'No se pudo aprobar.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobar'; }
        }
    } catch (e) {
        mostrarToast('Error de conexión.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobar'; }
    }
}

async function rechazarAcceso(id_acceso, notif_id) {
    const motivo = prompt('Motivo del rechazo (opcional):') ?? '';

    try {
        const res  = await fetch(`${window.BASE_URL}/veterinaria/api/historial/acceso/rechazar`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ id_acceso, motivo }),
        });
        const data = await res.json();

        if (data.status === 'ok') {
            const item = document.querySelector(`.notification-item--acceso[data-ref="${id_acceso}"]`);
            if (item) {
                item.style.transition = 'opacity .3s';
                item.style.opacity    = '0';
                setTimeout(() => item.remove(), 300);
            }
            marcarNotificacionLeida(notif_id);
            mostrarToast('Solicitud rechazada.', 'info');
        } else {
            mostrarToast(data.message || 'No se pudo rechazar.', 'error');
        }
    } catch (e) {
        mostrarToast('Error de conexión.', 'error');
    }
}

/**
 * Marca la notificación como leída en el backend.
 * Ajusta el nombre de la función/endpoint a los que ya usas en notificaciones.js
 */
function marcarNotificacionLeida(notif_id) {
    fetch(`${window.BASE_URL}/veterinario/api/notificaciones`, {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ accion: 'marcar_leida', id: notif_id }),
    }).catch(() => {});
}

/* Helpers — si ya los tienes en notificaciones.js, no los dupliques */
function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function formatearFecha(iso) {
    if (!iso) return '';
    return new Date(iso.replace(' ', 'T'))
        .toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric',
                                       hour:'2-digit', minute:'2-digit' });
}

function mostrarToast(msg, tipo = 'success') {
    /* Ajusta esto al sistema de toasts que ya uses en el panel vet */
    console.log(`[${tipo}] ${msg}`);
    alert(msg);
}