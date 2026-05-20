/**
 * sidebar-vet.js - VetWilling
 * Maneja el sidebar de veterinario en escritorio y el bottom sheet en móvil
 */

(function () {
    'use strict';

    // ── Elementos ──────────────────────────────────────────────────────────
    const sidebar   = document.getElementById('sidebarVet');
    const toggle    = document.getElementById('svToggle');
    const fab       = document.getElementById('svFab');
    const sheet     = document.getElementById('svBottomSheet');
    const overlay   = document.getElementById('svOverlay');
    const closeBtn  = document.getElementById('svSheetClose');
    const handle    = document.getElementById('svSheetHandle');

    // ── Estado ─────────────────────────────────────────────────────────────
    const STORAGE_KEY = 'vet_sidebar_collapsed';
    let isCollapsed   = localStorage.getItem(STORAGE_KEY) === 'true';
    let sheetOpen     = false;

    // ── Utilidad ───────────────────────────────────────────────────────────
    const isMobile = () => window.innerWidth <= 768;

    // ======================================================================
    // SIDEBAR ESCRITORIO
    // ======================================================================

    function applyCollapsed(collapsed, animate = true) {
        if (!sidebar) return;
        if (!animate) sidebar.classList.add('no-transition');
        sidebar.classList.toggle('collapsed', collapsed);
        if (!animate) requestAnimationFrame(() => sidebar.classList.remove('no-transition'));

        // Actualizar el toggle interno
        if (toggle) {
            const icon = toggle.querySelector('i');
            if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : '';
        }
    }

    function toggleSidebar() {
        if (isMobile()) {
            toggleSheet();
            return;
        }

        isCollapsed = !isCollapsed;
        localStorage.setItem(STORAGE_KEY, isCollapsed);
        applyCollapsed(isCollapsed);
    }

    // Inicializar estado guardado
    if (!isMobile()) applyCollapsed(isCollapsed, false);

    toggle?.addEventListener('click', toggleSidebar);

    // ── Marcar ítem activo según la URL actual ──────────────────────────
    function markActiveItem() {
        const path = window.location.pathname;

        // Items del sidebar escritorio
        document.querySelectorAll('.sv-item[href]').forEach(item => {
            const href = item.getAttribute('href');
            if (href && path.includes(href.split('/').pop())) {
                item.classList.add('active');
            }
        });

        // Items del bottom sheet
        document.querySelectorAll('.sv-sheet-grid-item[href], .sv-sheet-list-item[href]').forEach(item => {
            const href = item.getAttribute('href');
            if (href && path.includes(href.split('/').pop())) {
                item.classList.add('active');
            }
        });
    }

    markActiveItem();

    // ── Tooltips al colapsar (se activan solo con CSS, esto es para accesibilidad) ──
    document.querySelectorAll('.sv-item[data-tooltip]').forEach(item => {
        item.setAttribute('title', ''); // evitar doble tooltip nativo
    });

    // ── Cerrar dropdowns al hacer click fuera del sidebar ──────────────
    document.addEventListener('click', e => {
        if (isMobile()) return;
        if (sidebar && !sidebar.contains(e.target)) {
            // Aquí puedes cerrar submenús si los agregas en el futuro
        }
    });

    // ── Resize: limpiar estado al cambiar entre móvil y escritorio ──────
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (!isMobile()) {
                closeSheet();
                applyCollapsed(isCollapsed, false);
            }
        }, 150);
    });

    // ======================================================================
    // BOTTOM SHEET MÓVIL
    // ======================================================================

    function openSheet() {
        if (!sheet || !overlay) return;
        sheetOpen = true;
        overlay.classList.add('open');
        sheet.classList.add('open');
        fab?.classList.add('open');
        document.body.style.overflow = 'hidden';

        // Trap focus dentro del sheet
        requestAnimationFrame(() => {
            const firstFocusable = sheet.querySelector('a, button');
            firstFocusable?.focus();
        });
    }

    function closeSheet() {
        if (!sheet || !overlay) return;
        sheetOpen = false;
        overlay.classList.remove('open');
        sheet.classList.remove('open');
        fab?.classList.remove('open');
        document.body.style.overflow = '';
    }

    function toggleSheet() {
        sheetOpen ? closeSheet() : openSheet();
    }

    // Eventos de apertura/cierre
    fab?.addEventListener('click', toggleSheet);
    overlay?.addEventListener('click', closeSheet);
    closeBtn?.addEventListener('click', closeSheet);

    // Cerrar con Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && sheetOpen) closeSheet();
    });

    // ── Drag-to-dismiss en el handle ─────────────────────────────────────
    if (handle && sheet) {
        let startY = 0;
        let currentY = 0;
        let isDragging = false;

        function onDragStart(e) {
            isDragging = true;
            startY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            sheet.style.transition = 'none';
        }

        function onDragMove(e) {
            if (!isDragging) return;
            currentY = (e.type === 'touchmove' ? e.touches[0].clientY : e.clientY) - startY;
            if (currentY < 0) currentY = 0;
            sheet.style.transform = `translateY(${currentY}px)`;
        }

        function onDragEnd() {
            if (!isDragging) return;
            isDragging = false;
            sheet.style.transition = '';
            sheet.style.transform = '';

            // Si arrastró más de 100px hacia abajo, cerrar
            if (currentY > 100) {
                closeSheet();
            }
            currentY = 0;
        }

        // Touch
        handle.addEventListener('touchstart', onDragStart, { passive: true });
        document.addEventListener('touchmove', onDragMove, { passive: true });
        document.addEventListener('touchend', onDragEnd);

        // Mouse (para desktop testing)
        handle.addEventListener('mousedown', onDragStart);
        document.addEventListener('mousemove', onDragMove);
        document.addEventListener('mouseup', onDragEnd);
    }

    // ── Cerrar el sheet al navegar a un ítem ─────────────────────────────
    sheet?.querySelectorAll('a.sv-sheet-grid-item, a.sv-sheet-list-item').forEach(item => {
        item.addEventListener('click', () => {
            setTimeout(closeSheet, 200); // pequeño delay para que se vea el efecto activo
        });
    });

    // ======================================================================
    // MARCAR SECCIÓN ACTIVA EN LA URL
    // ======================================================================

    // Resalta el ítem del sidebar que coincida con data-section si la URL lo contiene
    const currentPath = window.location.pathname;

    document.querySelectorAll('.sv-item[data-section]').forEach(item => {
        const section = item.getAttribute('data-section');
        if (section && currentPath.includes(section)) {
            item.classList.add('active');
        }
    });

    // ======================================================================
    // BADGE DE NOTIFICACIONES (opcional, extensible)
    // ======================================================================

    /**
     * Actualiza un badge en el sidebar.
     * Uso desde otro script: window.svSetBadge('citas', 3)
     *   itemSelector: valor del data-section del ítem
     *   count: número a mostrar (0 para ocultar)
     */
    window.svSetBadge = function (section, count) {
        const item = document.querySelector(`.sv-item[data-section="${section}"] .sv-badge`);
        if (!item) return;
        if (count > 0) {
            item.textContent = count > 99 ? '99+' : count;
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    };

    // ======================================================================
    // ACCESIBILIDAD - Reducir movimiento
    // ======================================================================

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.sv-item, .sidebar-vet').forEach(el => {
            el.classList.add('no-transition');
        });
    }

    // ── Exponer API pública ────────────────────────────────────────────────
    window.sidebarVet = {
        collapse:  () => { isCollapsed = true;  localStorage.setItem(STORAGE_KEY, true);  applyCollapsed(true);  },
        expand:    () => { isCollapsed = false; localStorage.setItem(STORAGE_KEY, false); applyCollapsed(false); },
        toggle:    toggleSidebar,
        openSheet, closeSheet,
    };

})();