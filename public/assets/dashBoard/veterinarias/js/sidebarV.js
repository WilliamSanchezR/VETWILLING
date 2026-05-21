/**
 * sidebarV.js — VetWilling · Sidebar Veterinario
 * Versión: 2.1 — Corregido
 *
 * Correcciones:
 *  1. closeSheet despacha el evento sv:sheetClosed para que el navbar
 *     pueda sincronizar aria-expanded del btnMenuMobile.
 *  2. markActive limpia activos previos antes de marcar nuevos.
 *  3. Drag-to-dismiss con umbral de 120px y snap-back correcto.
 *  4. API pública expone openSheet y closeSheet.
 */

(function () {
    'use strict';

    /* ── Elementos ─────────────────────────────────────────────── */
    const sidebar  = document.getElementById('sidebarVet');
    const toggle   = document.getElementById('svToggle');
    const fab      = document.getElementById('svFab');
    const sheet    = document.getElementById('svBottomSheet');
    const overlay  = document.getElementById('svOverlay');
    const closeBtn = document.getElementById('svSheetClose');
    const handle   = document.getElementById('svSheetHandle');

    /* ── Estado ────────────────────────────────────────────────── */
    const STORAGE_KEY = 'vet_sidebar_collapsed';
    let collapsed = localStorage.getItem(STORAGE_KEY) === 'true';
    let sheetOpen = false;

    /* ── Utilidades ────────────────────────────────────────────── */
    const isMobile = () => window.innerWidth <= 768;

    /* ════════════════════════════════════════════════════════════
       SIDEBAR ESCRITORIO — colapsar / expandir
    ════════════════════════════════════════════════════════════ */
    function setCollapsed(val, animated = true) {
        if (!sidebar) return;

        if (!animated) {
            /* Desactivar transición un frame para el estado inicial */
            sidebar.style.transition = 'none';
            requestAnimationFrame(() => { sidebar.style.transition = ''; });
        }

        sidebar.classList.toggle('collapsed', val);

        /* Rotar ícono del toggle */
        const icon = toggle?.querySelector('i');
        if (icon) icon.style.transform = val ? 'rotate(180deg)' : '';
    }

    function toggleCollapse() {
        if (isMobile()) return;
        collapsed = !collapsed;
        localStorage.setItem(STORAGE_KEY, String(collapsed));
        setCollapsed(collapsed);
    }

    /* Aplicar estado guardado al cargar (solo en escritorio) */
    if (!isMobile()) setCollapsed(collapsed, false);

    toggle?.addEventListener('click', toggleCollapse);

    /* ════════════════════════════════════════════════════════════
       MARCAR ÍTEM ACTIVO SEGÚN URL
    ════════════════════════════════════════════════════════════ */
    function markActive() {
        const path = window.location.pathname;

        /* Limpiar estado previo */
        document.querySelectorAll(
            '.sv-item.active, .sv-sheet-grid-item.active, .sv-sheet-list-item.active'
        ).forEach(el => el.classList.remove('active'));

        /* Sidebar escritorio — por href */
        document.querySelectorAll('.sv-item[href]').forEach(el => {
            const seg = el.getAttribute('href').split('/').filter(Boolean).pop();
            if (seg && path.includes(seg)) el.classList.add('active');
        });

        /* Sidebar escritorio — por data-section (respaldo) */
        document.querySelectorAll('.sv-item[data-section]').forEach(el => {
            if (el.classList.contains('active')) return;
            const sec = el.getAttribute('data-section');
            if (sec && path.includes(sec)) el.classList.add('active');
        });

        /* Bottom sheet */
        document.querySelectorAll(
            '.sv-sheet-grid-item[href], .sv-sheet-list-item[href]'
        ).forEach(el => {
            const seg = el.getAttribute('href').split('/').filter(Boolean).pop();
            if (seg && path.includes(seg)) el.classList.add('active');
        });
    }

    markActive();

    /* Quitar title nativo para evitar doble tooltip en modo colapsado */
    document.querySelectorAll('.sv-item[data-tooltip]')
        .forEach(el => el.setAttribute('title', ''));

    /* ════════════════════════════════════════════════════════════
       BOTTOM SHEET MÓVIL
    ════════════════════════════════════════════════════════════ */
    function openSheet() {
        if (!sheet || !overlay) return;

        sheetOpen = true;
        overlay.classList.add('open');
        sheet.classList.add('open');
        fab?.classList.add('open');
        document.body.style.overflow = 'hidden';

        /* Mover el foco al primer elemento interactivo */
        requestAnimationFrame(() => {
            sheet.querySelector('button, a')?.focus({ preventScroll: true });
        });
    }

    function closeSheet() {
        if (!sheet || !overlay) return;

        sheetOpen = false;
        overlay.classList.remove('open');
        sheet.classList.remove('open');
        fab?.classList.remove('open');
        document.body.style.overflow = '';

        /* Restaurar transform por si quedó a medias el drag */
        sheet.style.transform  = '';
        sheet.style.transition = '';

        /* Notificar al navbar para sincronizar aria-expanded */
        document.dispatchEvent(new CustomEvent('sv:sheetClosed'));
    }

    function toggleSheet() {
        sheetOpen ? closeSheet() : openSheet();
    }

    fab?.addEventListener('click',    toggleSheet);
    overlay?.addEventListener('click', closeSheet);
    closeBtn?.addEventListener('click', closeSheet);

    /* Cerrar con Escape */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && sheetOpen) closeSheet();
    });

    /* Cerrar al navegar desde un ítem del sheet */
    sheet?.querySelectorAll('a.sv-sheet-grid-item, a.sv-sheet-list-item')
        .forEach(el => {
            el.addEventListener('click', () => {
                /* Delay breve para que el usuario vea el estado activo */
                setTimeout(closeSheet, 180);
            });
        });

    /* ════════════════════════════════════════════════════════════
       DRAG-TO-DISMISS
    ════════════════════════════════════════════════════════════ */
    if (handle && sheet) {
        const DISMISS_THRESHOLD = 120; /* px hacia abajo para cerrar */

        let startY   = 0;
        let deltaY   = 0;
        let dragging = false;

        function dragStart(e) {
            dragging = true;
            startY   = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            deltaY   = 0;
            sheet.style.transition = 'none';
        }

        function dragMove(e) {
            if (!dragging) return;
            const y = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
            deltaY = Math.max(0, y - startY);          /* solo hacia abajo */
            sheet.style.transform = `translateY(${deltaY}px)`;
        }

        function dragEnd() {
            if (!dragging) return;
            dragging = false;
            sheet.style.transition = '';               /* restaurar transición CSS */

            if (deltaY > DISMISS_THRESHOLD) {
                closeSheet();
            } else {
                sheet.style.transform = '';            /* snap-back */
            }

            deltaY = 0;
        }

        /* Touch */
        handle.addEventListener('touchstart', dragStart, { passive: true });
        window.addEventListener('touchmove',  dragMove,  { passive: true });
        window.addEventListener('touchend',   dragEnd);

        /* Mouse (para pruebas en escritorio) */
        handle.addEventListener('mousedown', dragStart);
        window.addEventListener('mousemove', dragMove);
        window.addEventListener('mouseup',   dragEnd);
    }

    /* ════════════════════════════════════════════════════════════
       RESIZE — limpiar al cambiar entre móvil ↔ escritorio
    ════════════════════════════════════════════════════════════ */
    let resizeTO;

    window.addEventListener('resize', () => {
        clearTimeout(resizeTO);
        resizeTO = setTimeout(() => {
            if (!isMobile()) {
                /* Escritorio: cerrar sheet si estaba abierto y restaurar sidebar */
                if (sheetOpen) closeSheet();
                setCollapsed(collapsed, false);
            } else {
                /* Móvil: nunca mostrar el sidebar colapsado */
                sidebar?.classList.remove('collapsed');
            }
        }, 150);
    });

    /* ════════════════════════════════════════════════════════════
       API DE BADGES
       Uso desde otro script:  window.svSetBadge('consultar-citas', 5)
    ════════════════════════════════════════════════════════════ */
    window.svSetBadge = function (section, count) {
        const badge = document.querySelector(
            `.sv-item[data-section="${section}"] .sv-badge`
        );
        if (!badge) return;

        if (count > 0) {
            badge.textContent  = count > 99 ? '99+' : String(count);
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    };

    /* ════════════════════════════════════════════════════════════
       API PÚBLICA
    ════════════════════════════════════════════════════════════ */
    window.sidebarVet = {
        collapse:   () => { collapsed = true;  localStorage.setItem(STORAGE_KEY, 'true');  setCollapsed(true);  },
        expand:     () => { collapsed = false; localStorage.setItem(STORAGE_KEY, 'false'); setCollapsed(false); },
        toggle:     toggleCollapse,
        openSheet,
        closeSheet,
        markActive,
    };

})();