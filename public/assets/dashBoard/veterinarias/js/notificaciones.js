/**
 * notificaciones.js — Panel Veterinario
 * ---------------------------------------------------------------------
 * Consume /veterinario/api/notificaciones y pinta el listado de la vista
 * notificaciones_veterinario.php usando las clases definidas en
 * notificaciones.css (.notification-item, .notification-indicator, etc.)
 *
 * Cambios respecto a la versión anterior:
 *   - Unifica el marcado de las tarjetas con las clases que el CSS
 *     realmente estiliza (antes generaba .notif-card/.notif-body/...,
 *     que no tenían ningún estilo asociado).
 *   - Integra el renderizado de solicitudes de acceso a historial
 *     directamente en buildItem(), en vez de un bloque de funciones
 *     sueltas al final del archivo que nunca se invocaban.
 *   - Agrega manejo de los estados #notificationsLoading y
 *     #notificationsError que ahora existen en la vista.
 *   - Elimina duplicados (escapeHtml/escHtml, formatDate/formatearFecha).
 * ---------------------------------------------------------------------
 */
(function () {
    'use strict';

    const BASE_URL = window.BASE_URL || window.location.origin;
    const API_NOTIFICACIONES = `${BASE_URL}/veterinario/api/notificaciones`;
    const API_ACCESO_HISTORIAL = `${BASE_URL}/veterinaria/api/historial/acceso`;

    // ---------- Referencias al DOM ----------
    const els = {
        list:        document.getElementById('notificationsPageList'),
        empty:       document.getElementById('notificationsEmpty'),
        loading:     document.getElementById('notificationsLoading'),
        error:       document.getElementById('notificationsError'),
        btnRetry:    document.getElementById('btnRetryNotifications'),
        btnMarkAll:  document.getElementById('btnMarkAllRead'),
        badge:       document.getElementById('notificationBadge'), // vive en el navbar, puede no existir aquí
    };

    // ---------- Catálogos ----------
    const ICONO_POR_TIPO = {
        cita:             'bi-calendar-check',
        vacuna:           'bi-syringe',
        seguimiento:      'bi-activity',
        tratamiento:      'bi-capsule',
        acceso_historial: 'bi-folder-symlink',
        general:          'bi-bell',
    };

    const ETIQUETA_POR_TIPO = {
        cita:             'Cita',
        vacuna:           'Vacuna',
        seguimiento:      'Seguimiento',
        tratamiento:      'Tratamiento',
        acceso_historial: 'Historial',
        general:          'General',
    };

    const OPCIONES_DURACION_ACCESO = [
        { horas: 24,  label: '24 horas' },
        { horas: 48,  label: '48 horas' },
        { horas: 72,  label: '3 días' },
        { horas: 168, label: '7 días' },
    ];

    // =====================================================================
    // Helpers
    // =====================================================================

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
        const normalized = typeof value === 'string' ? value.replace(' ', 'T') : value;
        const date = new Date(normalized);
        if (Number.isNaN(date.getTime())) return 'Sin fecha';
        return date.toLocaleString('es-CO', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    }

    function showToast(message, type = 'success') {
        if (window.Toast && typeof Toast.mostrar === 'function') {
            Toast.mostrar(message, type);
            return;
        }
        // Fallback si el sistema de toasts del panel no está cargado en esta vista
        alert(message);
    }

    function showError(message) {
        showToast(message, 'error');
    }

    function setLoading(isLoading) {
        els.loading?.classList.toggle('is-hidden', !isLoading);
        if (els.list) els.list.setAttribute('aria-busy', String(isLoading));
    }

    function setErrorState(hasError) {
        els.error?.classList.toggle('is-hidden', !hasError);
    }

    // =====================================================================
    // Render
    // =====================================================================

    function buildDurationOptions() {
        return OPCIONES_DURACION_ACCESO
            .map(({ horas, label }) => `<option value="${horas}">${escapeHtml(label)}</option>`)
            .join('');
    }

    /**
     * Construye el HTML de una notificación individual.
     * Usa las clases .notification-item / .notification-indicator / etc.
     * que sí están estilizadas en notificaciones.css.
     */
    function buildItem(item) {
        const tipo    = item.tipo || 'general';
        const leida   = Boolean(item.leido ?? item.leida);
        const icono   = ICONO_POR_TIPO[tipo] || 'bi-bell';
        const etiqueta = ETIQUETA_POR_TIPO[tipo] || tipo;

        const titulo  = escapeHtml(item.titulo || item.mensaje || 'Sin título');
        const mensaje = escapeHtml(item.mensaje || '');
        const tiempo  = escapeHtml(item.tiempo || formatDate(item.fecha || item.fecha_creacion));

        const itemClass = `notification-item tipo-${escapeHtml(tipo)} ${leida ? 'read' : 'unread'}`;
        const indicatorClass = `notification-indicator ${leida ? 'read' : 'unread'}`;

        let extraBlock = '';

        if (tipo === 'acceso_historial' && item.referencia_id) {
            extraBlock = `
                <div class="notification-access-actions" data-ref="${item.referencia_id}">
                    <select class="form-select form-select-sm" id="dur_${item.referencia_id}" aria-label="Duración del acceso">
                        ${buildDurationOptions()}
                    </select>
                    <button type="button" class="btn btn-primary btn-sm js-aprobar-acceso" data-ref="${item.referencia_id}" data-notif="${item.id}">
                        <i class="bi bi-check-circle me-1" aria-hidden="true"></i>Aprobar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm js-rechazar-acceso" data-ref="${item.referencia_id}" data-notif="${item.id}">
                        <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Rechazar
                    </button>
                </div>`;
        } else if (item.estado === 'cancelada' && item.motivo_cancelacion) {
            extraBlock = `
                <div class="notification-cancel-reason">
                    <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                    ${escapeHtml(item.motivo_cancelacion)}
                </div>`;
        }

        return `
            <article class="${itemClass}" data-notification-id="${item.id}" aria-label="${leida ? 'Notificación leída' : 'Notificación sin leer'}">
                <span class="${indicatorClass}" aria-hidden="true"></span>
                <div class="notification-content">
                    <div class="notification-header">
                        <h3><i class="bi ${icono}" aria-hidden="true"></i> ${titulo}</h3>
                        <span class="notification-time">${tiempo}</span>
                    </div>
                    ${mensaje ? `<p>${mensaje}</p>` : ''}
                    ${extraBlock}
                </div>
                <span class="notification-type">${escapeHtml(etiqueta)}</span>
            </article>`;
    }

    function renderList(items) {
        if (!els.list) return;

        if (!Array.isArray(items) || items.length === 0) {
            els.list.innerHTML = '';
            els.empty?.classList.remove('is-hidden');
            return;
        }

        els.empty?.classList.add('is-hidden');
        els.list.innerHTML = items.map(buildItem).join('');
    }

    function updateBadge(count) {
        if (!els.badge) return;
        const hasUnread = Boolean(count);
        els.badge.classList.toggle('is-hidden', !hasUnread);
        if (hasUnread) els.badge.textContent = String(count);
    }

    // =====================================================================
    // Llamadas a la API
    // =====================================================================

    async function fetchNotifications() {
        const response = await fetch(`${API_NOTIFICACIONES}?accion=listar&limite=100`, {
            credentials: 'include',
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    }

    async function postAccion(accion, payload) {
        const response = await fetch(`${API_NOTIFICACIONES}?accion=${accion}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload || {}),
            credentials: 'include',
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    }

    async function loadNotifications() {
        setLoading(true);
        setErrorState(false);

        try {
            const data = await fetchNotifications();
            const items = data?.notificaciones || [];
            renderList(items);
            updateBadge(data?.no_leidas || 0);
        } catch (error) {
            renderList([]);
            setErrorState(true);
        } finally {
            setLoading(false);
        }
    }

    async function markAsRead(id) {
        try {
            const result = await postAccion('leida', { id: Number(id) });
            if (result.status !== 'success') {
                showError(result.mensaje || 'No se pudo marcar como leída.');
                return;
            }
            await loadNotifications();
        } catch (error) {
            showError('No se pudo marcar la notificación como leída.');
        }
    }

    async function markAllAsRead() {
        try {
            const result = await postAccion('todas');
            if (result.status !== 'success') {
                showError(result.mensaje || 'No se pudieron marcar todas como leídas.');
                return;
            }
            await loadNotifications();
        } catch (error) {
            showError('No se pudieron marcar todas las notificaciones como leídas.');
        }
    }

    async function aprobarAcceso(idAcceso, notifId, duracionHoras) {
        try {
            const response = await fetch(`${API_ACCESO_HISTORIAL}/aprobar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_acceso: idAcceso, duracion_horas: duracionHoras }),
                credentials: 'include',
            });
            const data = await response.json();

            if (data.status !== 'ok') {
                showError(data.message || 'No se pudo aprobar el acceso.');
                return;
            }

            showToast('Acceso aprobado correctamente.', 'success');
            await markAsRead(notifId);
        } catch (error) {
            showError('Error de conexión al aprobar el acceso.');
        }
    }

    async function rechazarAcceso(idAcceso, notifId) {
        const motivo = window.prompt('Motivo del rechazo (opcional):') ?? '';

        try {
            const response = await fetch(`${API_ACCESO_HISTORIAL}/rechazar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_acceso: idAcceso, motivo }),
                credentials: 'include',
            });
            const data = await response.json();

            if (data.status !== 'ok') {
                showError(data.message || 'No se pudo rechazar la solicitud.');
                return;
            }

            showToast('Solicitud rechazada.', 'info');
            await markAsRead(notifId);
        } catch (error) {
            showError('Error de conexión al rechazar la solicitud.');
        }
    }

    // =====================================================================
    // Eventos
    // =====================================================================

    els.list?.addEventListener('click', event => {
        const approveBtn = event.target.closest('.js-aprobar-acceso');
        if (approveBtn) {
            event.stopPropagation();
            const ref = approveBtn.dataset.ref;
            const notifId = approveBtn.dataset.notif;
            const duracion = parseInt(document.getElementById(`dur_${ref}`)?.value || '24', 10);
            aprobarAcceso(ref, notifId, duracion);
            return;
        }

        const rejectBtn = event.target.closest('.js-rechazar-acceso');
        if (rejectBtn) {
            event.stopPropagation();
            rechazarAcceso(rejectBtn.dataset.ref, rejectBtn.dataset.notif);
            return;
        }

        // Click en cualquier otra parte de la tarjeta: marcar como leída
        const card = event.target.closest('.notification-item');
        if (card?.dataset.notificationId) {
            markAsRead(card.dataset.notificationId);
        }
    });

    els.btnMarkAll?.addEventListener('click', markAllAsRead);
    els.btnRetry?.addEventListener('click', loadNotifications);

    window.addEventListener('notificacion:nueva', () => loadNotifications());

    // =====================================================================
    // Arranque: carga inicial + polling de respaldo + SSE
    // =====================================================================

    loadNotifications();

    const POLL_INTERVAL_MS = 30000;
    const pollingId = setInterval(loadNotifications, POLL_INTERVAL_MS);

    let eventSource = null;
    if (window.EventSource) {
        eventSource = new EventSource(`${API_NOTIFICACIONES}?accion=stream`, { withCredentials: true });
        eventSource.addEventListener('notificacion', () => loadNotifications());
    }

    window.addEventListener('beforeunload', () => {
        clearInterval(pollingId);
        eventSource?.close();
    }, { once: true });
})();