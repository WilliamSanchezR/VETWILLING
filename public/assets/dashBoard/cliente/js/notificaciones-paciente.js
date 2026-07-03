(function () {
    'use strict';

    /* ── Configuración ── */
    const BASE_URL  = window.BASE_URL || window.location.origin;
    const API_URL   = `${BASE_URL}/paciente/api/notificaciones`;

    /* ── Nodos del DOM ── */
    const skeleton      = document.getElementById('pacSkeleton');
    const listEl        = document.getElementById('pacNotifList');
    const emptyEl       = document.getElementById('pacEmpty');
    const btnMarkAll    = document.getElementById('pacBtnMarkAll');
    const filtersEl     = document.getElementById('pacFilters');

    /* Contadores de filtros */
    const cntTodas    = document.getElementById('cntTodas');
    const cntNoLeidas = document.getElementById('cntNoLeidas');
    const cntCitas    = document.getElementById('cntCitas');
    const cntRecuerdos= document.getElementById('cntRecuerdos');
    const cntResultados=document.getElementById('cntResultados');
    const cntAlertas  = document.getElementById('cntAlertas');

    /* Badge del navbar (puede estar en el layout) */
    const navBadge = document.getElementById('notificationBadge');

    /* ── Estado ── */
    let allItems     = [];
    let activeFilter = 'todas';

    /* ================================================================
       UTILIDADES
    ================================================================ */
    function escHtml(v) {
        if (typeof v !== 'string') return '';
        return v
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

        const now    = new Date();
        const diffMs = now - date;
        const diffM  = Math.floor(diffMs / 60000);
        const diffH  = Math.floor(diffMs / 3600000);
        const diffD  = Math.floor(diffMs / 86400000);

        if (diffM  <  1)  return 'Ahora mismo';
        if (diffM  < 60)  return `Hace ${diffM} min`;
        if (diffH  < 24)  return `Hace ${diffH} h`;
        if (diffD  ===  1)return 'Ayer';
        if (diffD  <   7) return `Hace ${diffD} días`;

        return date.toLocaleString('es-CO', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    /**
     * Devuelve metadatos visuales según el tipo de notificación.
     * Se adapta a los valores que devuelva tu backend en `item.tipo`.
     */
    function getMeta(tipo) {
        const t = (tipo || '').toLowerCase();

        if (t.includes('cita') || t.includes('appointment'))
            return { icon: 'bi-calendar-check', iconClass: 'ic-cita',    pillClass: 'p-cita',     pillLabel: 'Cita',        typeClass: 'cita' };
        if (t.includes('recuerdo') || t.includes('recordatorio') || t.includes('vacuna') || t.includes('reminder'))
            return { icon: 'bi-alarm',           iconClass: 'ic-recuerdo', pillClass: 'p-recuerdo', pillLabel: 'Recordatorio',typeClass: 'recuerdo' };
        if (t.includes('resultado') || t.includes('examen') || t.includes('result'))
            return { icon: 'bi-clipboard2-pulse',iconClass: 'ic-resultado',pillClass: 'p-resultado',pillLabel: 'Resultado',   typeClass: 'resultado' };
        if (t.includes('alerta') || t.includes('urgente') || t.includes('alert'))
            return { icon: 'bi-exclamation-triangle', iconClass: 'ic-alerta', pillClass: 'p-alerta', pillLabel: 'Alerta',    typeClass: 'alerta' };

        return { icon: 'bi-info-circle', iconClass: 'ic-info', pillClass: 'p-info', pillLabel: escHtml(tipo || 'Aviso'), typeClass: 'info' };
    }

    /* ================================================================
       RENDER
    ================================================================ */

    /** Agrupa los items por "hoy / ayer / esta semana / anterior" */
    function groupByDate(items) {
        const now   = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const yest  = new Date(today); yest.setDate(yest.getDate() - 1);
        const week  = new Date(today); week.setDate(week.getDate() - 7);

        const groups = { 'Hoy': [], 'Ayer': [], 'Esta semana': [], 'Anterior': [] };

        items.forEach(item => {
            const d = item.fecha ? new Date(item.fecha) : null;
            if (!d || Number.isNaN(d.getTime())) { groups['Anterior'].push(item); return; }
            const day = new Date(d.getFullYear(), d.getMonth(), d.getDate());
            if (+day === +today)      groups['Hoy'].push(item);
            else if (+day === +yest)  groups['Ayer'].push(item);
            else if (day >= week)     groups['Esta semana'].push(item);
            else                      groups['Anterior'].push(item);
        });

        return groups;
    }

    function buildItem(item) {
        const meta      = getMeta(item.tipo);
        const isUnread  = !item.leido;
        const message   = escHtml(item.mensaje || 'Sin mensaje');
        const desc      = item.canal
            ? `Canal: ${escHtml(item.canal)}`
            : item.referencia_id
                ? `Ref: ${escHtml(String(item.referencia_id))}`
                : '';
        let html = `
        <article
            class="pac-notif-item t-${meta.typeClass}${isUnread ? ' unread' : ''}"
            data-id="${item.id}"
            data-type="${meta.typeClass}"
            role="button"
            tabindex="0"
            aria-label="${message}">

            <div class="pac-notif-icon ${meta.iconClass}">
                <i class="bi ${meta.icon}"></i>
            </div>

            <div class="pac-notif-body">
                <div class="pac-notif-top">
                    <span class="pac-notif-title">${message}</span>
                    <span class="pac-notif-time">${formatDate(item.fecha)}</span>
                </div>
                ${desc ? `<p class="pac-notif-desc">${desc}</p>` : ''}
                <span class="pac-notif-pill ${meta.pillClass}">${meta.pillLabel}</span>
        `;
        // Mostrar motivo de cancelación si aplica
        if (item.estado === 'cancelada' && item.motivo_cancelacion) {
            html += `<div class="notification-cancel-reason"><i class="bi bi-x-octagon"></i> ${escHtml(item.motivo_cancelacion)}</div>`;
        }
        html += `</div><div class="pac-unread-dot" aria-hidden="true"></div></article>`;
        return html;
    }

    function buildDivider(label) {
        return `<div class="pac-date-divider"><span>${label}</span></div>`;
    }

    function filterItems(items, filter) {
        if (filter === 'todas')     return items;
        if (filter === 'no-leidas') return items.filter(i => !i.leido);
        return items.filter(i => getMeta(i.tipo).typeClass === filter);
    }

    function renderList(items) {
        const filtered = filterItems(items, activeFilter);

        if (!filtered.length) {
            listEl.classList.add('is-hidden');
            emptyEl.classList.remove('is-hidden');
            return;
        }

        emptyEl.classList.add('is-hidden');

        const groups = groupByDate(filtered);
        let html = '';

        Object.entries(groups).forEach(([label, group]) => {
            if (!group.length) return;
            html += buildDivider(label);
            html += group.map(buildItem).join('');
        });

        listEl.innerHTML = html;
        listEl.classList.remove('is-hidden');
    }

    function updateCounters(items) {
        const noLeidas  = items.filter(i => !i.leido).length;
        const citas     = items.filter(i => getMeta(i.tipo).typeClass === 'cita').length;
        const recuerdos = items.filter(i => getMeta(i.tipo).typeClass === 'recuerdo').length;
        const resultados= items.filter(i => getMeta(i.tipo).typeClass === 'resultado').length;
        const alertas   = items.filter(i => getMeta(i.tipo).typeClass === 'alerta').length;

        if (cntTodas)     cntTodas.textContent     = items.length;
        if (cntNoLeidas)  cntNoLeidas.textContent   = noLeidas;
        if (cntCitas)     cntCitas.textContent       = citas;
        if (cntRecuerdos) cntRecuerdos.textContent   = recuerdos;
        if (cntResultados)cntResultados.textContent  = resultados;
        if (cntAlertas)   cntAlertas.textContent     = alertas;

        if (navBadge) {
            if (noLeidas > 0) {
                navBadge.textContent = noLeidas;
                navBadge.classList.remove('is-hidden');
            } else {
                navBadge.classList.add('is-hidden');
            }
        }
    }

    /* ================================================================
       API
    ================================================================ */
    function showError(message) {
        // Puedes reemplazar alert por tu sistema de toast si tienes uno
        if (window.Toast && Toast.mostrar) {
            Toast.mostrar(message, 'error');
        } else {
            alert(message);
        }
    }

    async function fetchNotifications() {
        try {
            const res = await fetch(`${API_URL}?accion=listar&limite=100`, { credentials: 'include' });
            if (!res.ok) throw new Error('Error al cargar');
            return await res.json();
        } catch (e) {
            showError('No se pudieron cargar las notificaciones.');
            return null;
        }
    }

    const notificationSoundEnabled = localStorage.getItem('vetwilling_notifications_sound') !== 'false';

    function requestNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }
    }

    function showBrowserNotification(title, body) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            new Notification(title || 'Nueva notificación', {
                body: body || 'Tienes una nueva notificación.',
                silent: false
            });
        } catch (error) {
            console.error('Error mostrando notificación del navegador:', error);
        }
    }

    function playNotificationTone() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = 520;
            gain.gain.value = 0.12;
            oscillator.connect(gain);
            gain.connect(ctx.destination);
            oscillator.start();
            oscillator.stop(ctx.currentTime + 0.08);
        } catch (error) {
            console.error('Error reproducir sonido de notificación:', error);
        }
    }

    function handleNotificationStreamEvent(event) {
        try {
            const data = JSON.parse(event.data);
            if (Array.isArray(data.notificaciones) && data.notificaciones.length > 0) {
                const latest = data.notificaciones[data.notificaciones.length - 1];
                const title = latest.tipo || 'Nueva notificación';
                const body = latest.mensaje || 'Tienes una nueva notificación.';

                if (document.hidden) {
                    showBrowserNotification(title, body);
                    if (notificationSoundEnabled) {
                        playNotificationTone();
                    }
                }

                if (window.Toast && Toast.mostrar) {
                    Toast.mostrar(body, 'info');
                }
            }

            loadNotifications();
            window.dispatchEvent(new CustomEvent('notificacion:nueva', { detail: data }));
        } catch (error) {
            console.error('Error parsing notification stream event:', error);
        }
    }

    function connectNotificationStream() {
        if (!window.EventSource) return;

        const es = new EventSource(`${API_URL}?accion=stream`, { withCredentials: true });

        es.addEventListener('notificacion', () => loadNotifications());

        window.addEventListener('beforeunload', () => es.close(), { once: true });
    }

    async function markRead(id) {
        try {
            const res = await fetch(`${API_URL}?accion=leida`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ id: Number(id) }),
                credentials: 'include'
            });
            const data = await res.json();
            if (data.status !== 'success') showError(data.mensaje || 'No se pudo marcar como leída.');
            return data.status === 'success';
        } catch (e) {
            showError('No se pudo marcar la notificación como leída.');
            return false;
        }
    }

    async function markAllRead() {
        try {
            const res = await fetch(`${API_URL}?accion=todas`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({}),
                credentials: 'include'
            });
            const data = await res.json();
            if (data.status !== 'success') showError(data.mensaje || 'No se pudieron marcar todas como leídas.');
            return data.status === 'success';
        } catch (e) {
            showError('No se pudieron marcar todas las notificaciones como leídas.');
            return false;
        }
    }

    /* ================================================================
       CARGA PRINCIPAL
    ================================================================ */
    async function loadNotifications() {
        skeleton?.classList.remove('is-hidden');
        listEl?.classList.add('is-hidden');
        emptyEl?.classList.add('is-hidden');

        const data = await fetchNotifications();

        skeleton?.classList.add('is-hidden');

        if (!data) {
            renderList([]);
            updateCounters([]);
            return;
        }

        allItems = data.notificaciones || [];
        updateCounters(allItems);
        renderList(allItems);
    }

    /* ================================================================
       EVENTOS
    ================================================================ */

    /* Clic en un item — marcar como leído */
    listEl?.addEventListener('click', async e => {
        const row = e.target.closest('.pac-notif-item');
        if (!row) return;
        const id = row.dataset.id;
        if (!id || !row.classList.contains('unread')) return;

        const ok = await markRead(id);
        if (ok) {
            /* Actualiza localmente sin recargar todo */
            const item = allItems.find(i => String(i.id) === id);
            if (item) item.leido = true;
            updateCounters(allItems);
            row.classList.remove('unread');
            const dot = row.querySelector('.pac-unread-dot');
            if (dot) { dot.style.background = 'var(--pac-border)'; dot.style.boxShadow = 'none'; dot.style.animation = 'none'; }
            row.style.background = '';
        }
    });

    /* Clic en un item via teclado */
    listEl?.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            e.target.closest('.pac-notif-item')?.click();
        }
    });

    /* Marcar todas leídas */
    btnMarkAll?.addEventListener('click', async () => {
        btnMarkAll.disabled = true;
        const ok = await markAllRead();
        if (ok) await loadNotifications();
        btnMarkAll.disabled = false;
    });

    /* Filtros */
    filtersEl?.addEventListener('click', e => {
        const btn = e.target.closest('.pac-filter-btn');
        if (!btn) return;
        filtersEl.querySelectorAll('.pac-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        renderList(allItems);
    });

    /* ── Arranque ── */
    loadNotifications();
    connectNotificationStream();
    // Polling de respaldo: cubre fallos silenciosos de SSE y navegadores sin EventSource
    const _pollingId = setInterval(loadNotifications, 30000);
    window.addEventListener('beforeunload', () => clearInterval(_pollingId), { once: true });

})();