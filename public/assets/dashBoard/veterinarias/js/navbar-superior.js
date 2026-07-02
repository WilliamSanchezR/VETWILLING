/**
 * navbar-superior.js — VetWilling Dashboard Veterinario
 * Versión: 3.0 — Corregido
 *
 * Correcciones aplicadas:
 *  1. btnMenuMobile delega primero a window.sidebarVet.openSheet()
 *     (sidebar del veterinario), luego al del cliente como fallback.
 *  2. toggleTheme alterna data-theme="dark" en <html>, no body.dark-theme.
 *  3. cerrarTodosDropdowns usa aria-controls para sincronizar aria-expanded.
 *  4. Escape cierra el modal de soporte además de los dropdowns.
 *  5. Saludo usa Bootstrap Icons con clase por período del día.
 *  6. Reloj actualiza cada segundo con toLocaleTimeString.
 */

(function () {
    'use strict';

    const BASE_URL = window.BASE_URL || window.location.origin;

    /* ── Helpers ─────────────────────────────────────────────────── */
    const $  = id => document.getElementById(id);
    const $$ = sel => document.querySelectorAll(sel);

    function mostrar(el) {
        if (!el) return;
        el.classList.remove('is-hidden');
        try { el.style.display = ''; } catch (_) {}
    }
    function ocultar(el) {
        if (!el) return;
        el.classList.add('is-hidden');
        try { el.style.display = 'none'; } catch (_) {}
    }
    function estaOculto(el) {
        if (!el) return true;
        if (el.classList.contains('is-hidden')) return true;
        if (el.classList.contains('show')) return false;
        try { return getComputedStyle(el).display === 'none'; } catch (_) { return false; }
    }

    /* ══════════════════════════════════════════════════════════════
       DROPDOWNS
    ══════════════════════════════════════════════════════════════ */

    /**
     * Cierra todos los paneles de dropdown excepto el indicado.
     * @param {Element|null} excepto - panel que NO se debe cerrar
     */
    function cerrarTodosDropdowns(excepto) {
        $$('.dropdown-panel').forEach(panel => {
            if (panel === excepto) return;

            ocultar(panel);

            /* Sincronizar aria-expanded del botón que controla este panel */
            const id  = panel.id;
            const btn = id ? document.querySelector(`[aria-controls="${id}"]`) : null;
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
                btn.classList.remove('active');
            }
        });
    }

    /**
     * Alterna la visibilidad de un panel y sincroniza aria-expanded.
     * @param {Element} panel - el dropdown a alternar
     * @param {Element} btn   - el botón que lo controla
     */
    function toggleDropdown(panel, btn) {
        if (!panel) return;
        cerrarTodosDropdowns(panel);

        const abriendo = estaOculto(panel);
        abriendo ? mostrar(panel) : ocultar(panel);

        // Also keep compatibility with scripts that use `show` class or style.display
        if (abriendo) {
            panel.classList.add('show');
            panel.classList.remove('is-hidden');
            try { panel.style.display = 'block'; } catch (_) {}
        } else {
            panel.classList.remove('show');
            panel.classList.add('is-hidden');
            try { panel.style.display = 'none'; } catch (_) {}
        }

        if (btn) {
            btn.setAttribute('aria-expanded', String(abriendo));
            btn.classList.toggle('active', abriendo);
        }
    }

    /* ══════════════════════════════════════════════════════════════
       RELOJ
    ══════════════════════════════════════════════════════════════ */
    function iniciarReloj() {
        const el = $('horaActual');
        if (!el) return;

        const tick = () => {
            el.textContent = new Date().toLocaleTimeString('es-CO');
        };

        tick();
        setInterval(tick, 1000);
    }

    /* ══════════════════════════════════════════════════════════════
       SALUDO CON BOOTSTRAP ICONS
    ══════════════════════════════════════════════════════════════ */
    function iniciarSaludo() {
        const texto = $('saludoTexto');
        const wrap  = $('saludoIconWrap');
        const icon  = $('saludoIcon');
        if (!texto || !icon) return;

        const hora = new Date().getHours();
        let msg, clase, biClass;

        if (hora >= 5 && hora < 12) {
            msg     = 'Buenos días';
            clase   = 'manana';           /* sin tilde para evitar problemas en CSS */
            biClass = 'bi bi-sun-fill';
        } else if (hora >= 12 && hora < 19) {
            msg     = 'Buenas tardes';
            clase   = 'tarde';
            biClass = 'bi bi-brightness-high-fill';
        } else {
            msg     = 'Buenas noches';
            clase   = 'noche';
            biClass = 'bi bi-moon-stars-fill';
        }

        texto.textContent = msg;
        icon.className    = biClass;
        wrap?.classList.add(clase);
    }

    /* ══════════════════════════════════════════════════════════════
       NOTIFICACIONES
    ══════════════════════════════════════════════════════════════ */
    // Element selection with fallbacks to tolerate different templates
    const btnNotif = $('btnNotificaciones') || document.querySelector('[data-action="notificaciones"]') || document.querySelector('.btn-notificaciones');
    const panelNotif = $('notificationsPanel') || document.querySelector('[data-panel="notificaciones"]') || document.querySelector('#notificationsPanel');
    const dropdownList = $('notificationsDropdownList') || document.querySelector('.notifications-list') || document.querySelector('#listaNotificaciones');
    const dropdownEmpty = $('notificationsDropdownEmpty') || document.querySelector('.notifications-empty');
    const btnMarcarLeidas = $('btnMarcarLeidas') || document.querySelector('[data-action="marcar-leidas"]') || document.querySelector('.btn-marcar-leidas');
    const notificationBadge = $('notificationBadge') || document.querySelector('#badgeNotificaciones') || document.querySelector('.badge-notificaciones');
    const notificationsApiUrl   = `${BASE_URL}/veterinario/api/notificaciones`;

    const notificationTypes = {
        cita: {
            icon: 'bi-calendar-check-fill',
            clase: 'success',
            label: 'Cita'
        },
        vacuna: {
            icon: 'bi-heart-pulse-fill',
            clase: 'warning',
            label: 'Vacuna'
        },
        seguimiento: {
            icon: 'bi-journal-medical',
            clase: 'info',
            label: 'Seguimiento'
        },
        recordatorio: {
            icon: 'bi-bell-fill',
            clase: 'info',
            label: 'Recordatorio'
        },
        default: {
            icon: 'bi-bell-fill',
            clase: 'info',
            label: 'Notificación'
        }
    };

    async function fetchNotifications(params = '') {
        try {
            const response = await fetch(`${notificationsApiUrl}?accion=listar${params}`, { credentials: 'include' });
            if (!response.ok) {
                throw new Error('No se pudo obtener notificaciones');
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    function buildNotificationHtml(item) {
        const meta = notificationTypes[item.tipo] || notificationTypes.default;
        const fecha = item.fecha ? new Date(item.fecha).toLocaleString('es-CO', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : 'Sin fecha';
        const estadoClase = item.leido ? '' : 'unread';

        return `
            <article class="notification-item ${estadoClase}" data-notification-id="${item.id}">
                <div class="notification-indicator ${meta.clase}"></div>
                <div class="notification-icon ${meta.clase}">
                    <i class="bi ${meta.icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4>${escapeHtml(item.mensaje || meta.label)}</h4>
                        <span class="notification-time">${fecha}</span>
                    </div>
                    <p>${escapeHtml(item.referencia_id ? `ID referencia: ${item.referencia_id}` : item.canal || '')}</p>
                </div>
            </article>
        `;
    }

    function escapeHtml(value) {
        if (typeof value !== 'string') return '';
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderNotifications(items) {
        if (!dropdownList) return;

        if (!Array.isArray(items) || items.length === 0) {
            dropdownList.innerHTML = '';
            dropdownEmpty?.classList.remove('is-hidden');
            return;
        }

        dropdownEmpty?.classList.add('is-hidden');
        dropdownList.innerHTML = items.map(buildNotificationHtml).join('');
    }

    function actualizarBadgeNotif(count = 0, unreadCount = 0) {
        if (!notificationBadge) return;

        if (count === 0) {
            ocultar(notificationBadge);
            return;
        }

        mostrar(notificationBadge);
        notificationBadge.textContent = unreadCount > 0 ? String(unreadCount) : String(count);
    }

    async function cargarNotificacionesDropdown() {
        const data = await fetchNotifications('&limite=8');
        if (!data) {
            renderNotifications([]);
            actualizarBadgeNotif(0, 0);
            return;
        }

        const notificaciones = data.notificaciones || [];
        const noLeidas = Number.isInteger(data.no_leidas) ? data.no_leidas : 0;

        renderNotifications(notificaciones);
        actualizarBadgeNotif(noLeidas, noLeidas);
    }

    async function marcarTodasNotificacionesLeidas() {
        try {
            const response = await fetch(`${notificationsApiUrl}?accion=todas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({}),
                credentials: 'include'
            });
            const data = await response.json();
            if (data.status === 'success') {
                await cargarNotificacionesDropdown();
            }
        } catch (error) {
            console.error(error);
        }
    }

    function requestBrowserNotificationPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }
    }

    function showBrowserNotification(title, body) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            new Notification(title, {
                body: body || 'Tienes una nueva notificación',
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
            console.error('Error reproduciendo sonido de notificación:', error);
        }
    }

    function showAppToast(message) {
        if (!message) return;
        if (window.Toast && Toast.mostrar) {
            Toast.mostrar(message, 'info');
        }
    }

    function handleNotificationEvent(data) {
        const noLeidas = Number.isInteger(data.no_leidas) ? data.no_leidas : 0;
        actualizarBadgeNotif(noLeidas, noLeidas);

        if (!panelNotif?.classList.contains('is-hidden')) {
            cargarNotificacionesDropdown();
        }

        if (Array.isArray(data.notificaciones) && data.notificaciones.length > 0) {
            const latest = data.notificaciones[data.notificaciones.length - 1];
            const title = latest.tipo ? escapeHtml(latest.tipo) : 'Nueva notificación';
            const body = latest.mensaje || 'Tienes una nueva notificación.';

            if (document.hidden) {
                showBrowserNotification(title, body);
                playNotificationTone();
            }

            showAppToast(body);
        }

        window.dispatchEvent(new CustomEvent('notificacion:nueva', { detail: data }));
    }

    function connectNotificationStream() {
        requestBrowserNotificationPermission();

        const _pollingId = setInterval(cargarNotificacionesDropdown, 30000);

        if (window.EventSource) {
            const _es = new EventSource(`${notificationsApiUrl}?accion=stream`, { withCredentials: true });

            _es.addEventListener('notificacion', e => {
                try {
                    const data = JSON.parse(e.data);
                    // Actualizar badge inmediatamente con el conteo del servidor
                    const noLeidas = Number.isInteger(data.sin_leer) ? data.sin_leer : 0;
                    actualizarBadgeNotif(noLeidas, noLeidas);
                    // Recargar lista si el panel está abierto
                    if (!panelNotif?.classList.contains('is-hidden')) {
                        cargarNotificacionesDropdown();
                    }
                    // Propagar evento para que notificaciones.js lo capture
                    window.dispatchEvent(new CustomEvent('notificacion:nueva', { detail: data }));
                } catch (_) { /* JSON inválido — ignorar */ }
            });

            window.addEventListener('beforeunload', () => {
                clearInterval(_pollingId);
                _es.close();
            }, { once: true });
        } else {
            window.addEventListener('beforeunload', () => clearInterval(_pollingId), { once: true });
        }
    }

    if (btnNotif && panelNotif) {
        btnNotif.setAttribute('aria-controls', 'notificationsPanel');

        btnNotif.addEventListener('click', e => {
            e.stopPropagation();
            toggleDropdown(panelNotif, btnNotif);
            if (!panelNotif.classList.contains('is-hidden')) {
                cargarNotificacionesDropdown();
            }
        });
    }

    btnMarcarLeidas?.addEventListener('click', async () => {
        await marcarTodasNotificacionesLeidas();
    });

    cargarNotificacionesDropdown();
    connectNotificationStream();

    /* ══════════════════════════════════════════════════════════════
       PERFIL
    ══════════════════════════════════════════════════════════════ */
    const btnPerfil   = $('btnPerfil');
    const panelPerfil = $('perfilDropdown');

    if (btnPerfil && panelPerfil) {
        btnPerfil.setAttribute('aria-controls', 'perfilDropdown');

        btnPerfil.addEventListener('click', e => {
            e.stopPropagation();
            toggleDropdown(panelPerfil, btnPerfil);
        });
    }

    /* ══════════════════════════════════════════════════════════════
       CERRAR DROPDOWNS AL HACER CLICK FUERA
    ══════════════════════════════════════════════════════════════ */
    document.addEventListener('click', e => {
        const dentroDeAccion  = e.target.closest('.navbar-action');
        const dentroDePanel   = e.target.closest('.dropdown-panel');
        const dentroDeSearch  = e.target.closest('.search-container');

        if (!dentroDeAccion && !dentroDePanel && !dentroDeSearch) {
            cerrarTodosDropdowns(null);
        }
    });

    /* ══════════════════════════════════════════════════════════════
       ESCAPE — cierra dropdowns y modal
    ══════════════════════════════════════════════════════════════ */
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        cerrarTodosDropdowns(null);
        cerrarModal();
    });

    /* ══════════════════════════════════════════════════════════════
       ATAJO DE BÚSQUEDA Ctrl+K / Cmd+K
    ══════════════════════════════════════════════════════════════ */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            $('searchInput')?.focus();
        }
    });

    /* ══════════════════════════════════════════════════════════════
       BUSCADOR
    ══════════════════════════════════════════════════════════════ */
    const searchInput   = $('searchInput');
    const btnClear      = $('btnClearSearch');
    const searchResults = $('searchResults');

    searchInput?.addEventListener('input', function () {
        const q = this.value.trim();
        q ? mostrar(btnClear) : ocultar(btnClear);
        q ? mostrar(searchResults) : ocultar(searchResults);
        /* TODO: conectar lógica de búsqueda real aquí */
    });

    btnClear?.addEventListener('click', () => {
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        ocultar(btnClear);
        ocultar(searchResults);
    });

    /* ══════════════════════════════════════════════════════════════
       TEMA OSCURO / CLARO
       Alterna data-theme="dark" en <html> para coincidir
       con los selectores [data-theme="dark"] del CSS.
    ══════════════════════════════════════════════════════════════ */
    const btnTheme  = $('btnToggleTheme');
    const themeIcon = $('themeIcon');

    function aplicarTema(oscuro) {
        document.documentElement.setAttribute('data-theme', oscuro ? 'dark' : 'light');

        if (themeIcon) {
            themeIcon.className = oscuro ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
        }

        try {
            localStorage.setItem('navbar_tema', oscuro ? 'dark' : 'light');
        } catch (_) { /* sin acceso a localStorage */ }
    }

    btnTheme?.addEventListener('click', () => {
        const esOscuro = document.documentElement.getAttribute('data-theme') !== 'dark';
        aplicarTema(esOscuro);
    });

    /* Restaurar tema guardado al cargar */
    try {
        const guardado = localStorage.getItem('navbar_tema');
        if (guardado) aplicarTema(guardado === 'dark');
    } catch (_) { /* sin acceso a localStorage */ }

    /* ══════════════════════════════════════════════════════════════
       BOTÓN MENÚ MÓVIL
       Prioridad:
         1. sidebar veterinario (bottom sheet)
         2. sidebarAPI del cliente
         3. fallback directo de clases DOM
    ══════════════════════════════════════════════════════════════ */
    const btnMenuMobile = $('btnMenuMobile');

    btnMenuMobile?.addEventListener('click', () => {
        /* 1. Sidebar veterinario */
        if (window.sidebarVet?.openSheet) {
            window.sidebarVet.openSheet();
            btnMenuMobile.setAttribute('aria-expanded', 'true');
            return;
        }

        /* 2. API del sidebar cliente */
        if (window.sidebarAPI?.abrirMovil) {
            window.sidebarAPI.abrirMovil();
            const exp = btnMenuMobile.getAttribute('aria-expanded') === 'true';
            btnMenuMobile.setAttribute('aria-expanded', String(!exp));
            return;
        }

        /* 3. Fallback directo */
        document.getElementById('sidebar')?.classList.toggle('mobile-open');
        document.getElementById('sidebarOverlay')?.classList.toggle('active');
        const exp = btnMenuMobile.getAttribute('aria-expanded') === 'true';
        btnMenuMobile.setAttribute('aria-expanded', String(!exp));
    });

    /* Sincronizar aria-expanded cuando el bottom sheet se cierra
       desde fuera (overlay, Escape, drag) */
    document.addEventListener('sv:sheetClosed', () => {
        btnMenuMobile?.setAttribute('aria-expanded', 'false');
    });

    /* ══════════════════════════════════════════════════════════════
       MODAL DE SOPORTE
    ══════════════════════════════════════════════════════════════ */
    const modalSoporte = $('modalSoporte');
    const btnAbrirSop  = $('btnAbrirSoporte');
    const btnCerrarMod = $('btnCerrarModal');
    const btnCancelar  = $('btnCancelar');

    function abrirModal() {
        cerrarTodosDropdowns(null);
        modalSoporte?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        modalSoporte?.classList.remove('active');
        document.body.style.overflow = '';
    }

    btnAbrirSop?.addEventListener('click',  e => { e.preventDefault(); abrirModal(); });
    btnCerrarMod?.addEventListener('click', cerrarModal);
    btnCancelar?.addEventListener('click',  cerrarModal);

    /* Cerrar al click en el fondo del overlay */
    modalSoporte?.addEventListener('click', e => {
        if (e.target === modalSoporte) cerrarModal();
    });

    /* ══════════════════════════════════════════════════════════════
       FORMULARIO DE SOPORTE
    ══════════════════════════════════════════════════════════════ */
    const formSoporte = $('formularioSoporte');

    formSoporte?.addEventListener('submit', async e => {
        e.preventDefault();

        const asunto      = $('asunto')?.value?.trim()              ?? '';
        const categoria   = $('tipoProblema')?.value               ?? '';
        const descripcion = $('descripcionProblema')?.value?.trim() ?? '';

        /* Validaciones */
        if (asunto.length < 3) {
            Swal?.fire({ icon: 'warning', title: 'Asunto requerido', text: 'Ingresa un asunto válido.' });
            return;
        }
        if (!categoria) {
            Swal?.fire({ icon: 'warning', title: 'Categoría requerida', text: 'Selecciona una categoría.' });
            return;
        }
        if (descripcion.length < 20) {
            Swal?.fire({ icon: 'warning', title: 'Descripción muy corta', text: 'Mínimo 20 caracteres.' });
            return;
        }
        if (descripcion.length > 500) {
            Swal?.fire({ icon: 'error', title: 'Descripción muy larga', text: 'Máximo 500 caracteres.' });
            return;
        }

        /* Estado de carga */
        const btnEnviar = $('btnEnviarSoporte');
        if (btnEnviar) {
            btnEnviar.disabled     = true;
            btnEnviar.innerHTML    = '<i class="bi bi-hourglass-split"></i> Enviando…';
        }

        try {
            const fd  = new FormData(formSoporte);
            const res = await fetch(`${BASE_URL}/soporte/api/crear-ticket`, {
                method: 'POST',
                body:   fd,
            });
            const data = await res.json();

            if (data.status === 'error') {
                Swal?.fire({ icon: 'error', title: 'Error', text: data.message });
            } else {
                Swal?.fire({ icon: 'success', title: '¡Enviado!', text: data.message });
                formSoporte.reset();
                cerrarModal();
            }
        } catch {
            Swal?.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo más tarde.' });
        } finally {
            if (btnEnviar) {
                btnEnviar.disabled  = false;
                btnEnviar.innerHTML = '<i class="bi bi-send-fill"></i> Enviar Solicitud';
            }
        }
    });

    /* ══════════════════════════════════════════════════════════════
       INICIALIZACIÓN
    ══════════════════════════════════════════════════════════════ */
    iniciarReloj();
    iniciarSaludo();

})();