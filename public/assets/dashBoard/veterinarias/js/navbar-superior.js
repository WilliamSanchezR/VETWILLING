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

    function mostrar(el)    { el?.classList.remove('is-hidden'); }
    function ocultar(el)    { el?.classList.add('is-hidden'); }
    function estaOculto(el) { return el?.classList.contains('is-hidden'); }

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
        cerrarTodosDropdowns(panel);

        const abriendo = estaOculto(panel);
        abriendo ? mostrar(panel) : ocultar(panel);

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
    const btnNotif   = $('btnNotificaciones');
    const panelNotif = $('notificationsPanel');

    if (btnNotif && panelNotif) {
        btnNotif.setAttribute('aria-controls', 'notificationsPanel');

        btnNotif.addEventListener('click', e => {
            e.stopPropagation();
            toggleDropdown(panelNotif, btnNotif);
        });
    }

    /* Eliminar notificación individual */
    $$('.btn-eliminar-notif').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            this.closest('.notification-item')?.remove();
            actualizarBadgeNotif();
        });
    });

    /* Marcar todas como leídas */
    $('btnMarcarLeidas')?.addEventListener('click', () => {
        $$('.notification-item.unread').forEach(i => i.classList.remove('unread'));
        actualizarBadgeNotif();
    });

    function actualizarBadgeNotif() {
        const badge  = $('notificationBadge');
        const unread = $$('.notification-item.unread').length;
        const total  = $$('.notification-item').length;
        if (!badge) return;

        if (total === 0) {
            ocultar(badge);
        } else {
            mostrar(badge);
            badge.textContent = unread > 0 ? String(unread) : String(total);
        }
    }

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