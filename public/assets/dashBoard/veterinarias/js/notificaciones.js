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

    function buildItem(item) {
        const statusClass = item.leido ? '' : 'unread';
        const typeLabel = escapeHtml(item.tipo || 'Notificación');
        const message = escapeHtml(item.mensaje || 'Sin mensaje');
        const detail = item.canal ? `Canal: ${escapeHtml(item.canal)}` : item.referencia_id ? `Ref: ${escapeHtml(String(item.referencia_id))}` : '';

        return `
            <article class="notification-item ${statusClass}" data-notification-id="${item.id}">
                <div class="notification-indicator ${item.leido ? 'read' : 'unread'}"></div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h3>${message}</h3>
                        <span class="notification-time">${formatDate(item.fecha)}</span>
                    </div>
                    <p>${detail}</p>
                </div>
                <div class="notification-type">${typeLabel}</div>
            </article>
        `;
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
            const response = await fetch(`${apiUrl}?accion=listar&limite=100`);
            if (!response.ok) throw new Error('Error al cargar notificaciones');
            return await response.json();
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    async function markAllRead() {
        try {
            const response = await fetch(`${apiUrl}?accion=todas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            });
            return response.ok && (await response.json()).status === 'success';
        } catch (error) {
            console.error(error);
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
        const row = event.target.closest('.notification-item');
        if (!row) return;
        const id = row.dataset.notificationId;
        if (!id) return;

        try {
            const response = await fetch(`${apiUrl}?accion=leida`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: Number(id) })
            });
            const result = await response.json();
            if (result.status === 'success') {
                await loadNotifications();
            }
        } catch (error) {
            console.error(error);
        }
    });

    btnMarkAllRead?.addEventListener('click', markAllAsRead);
    loadNotifications();
})();
