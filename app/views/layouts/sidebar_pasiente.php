<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">

<!-- ============================================================
     SIDEBAR  (PC: colapsable | Móvil: burbuja + panel lateral)
     ============================================================ -->

<!-- SIDEBAR PC — se oculta en móvil via CSS -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
             alt="VetWilling"
             class="sidebar-logo-full">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
             alt="VW"
             class="sidebar-logo-icon">
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">General</span>

            <a href="<?= BASE_URL ?>/cliente/dashboard" class="nav-item" data-section="dashboard" data-tooltip="Inicio">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/mascotas" class="nav-item" data-section="mascotas" data-tooltip="Mascotas">
                <i class="bi bi-bluesky"></i>
                <span class="nav-text">Mis Mascotas</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/citas" class="nav-item" data-section="citas" data-tooltip="Citas">
                <i class="bi bi-calendar-check"></i>
                <span class="nav-text">Citas</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/tienda" class="nav-item" data-section="tienda" data-tooltip="Catalogo">
                <i class="bi bi-bag-plus"></i>
                <span class="nav-text">Catalogo</span>
            </a>
        </div>
    </nav>

    <!-- Toggle solo PC -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" type="button">
        <i class="bi bi-list"></i>
    </button>
</aside>

<!-- ============================================================
     MÓVIL: Overlay + Panel lateral + Burbuja
     ============================================================ -->

<!-- Overlay oscuro detrás del panel -->
<div class="mobile-overlay" id="mobileOverlay" aria-hidden="true"></div>

<!-- Panel lateral móvil (desliza desde la izquierda) -->
<div class="mobile-panel" id="mobilePanel" role="dialog" aria-modal="true" aria-label="Menú de navegación">

    <div class="mobile-panel-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-POSITIVO.png"
             alt="VetWilling"
             class="mobile-panel-logo">
        <button type="button" class="mobile-panel-close" id="mobilePanelClose" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="mobile-panel-nav">
        <span class="mobile-nav-section-title">General</span>

        <a href="<?= BASE_URL ?>/cliente/dashboard" class="mobile-nav-item" data-section="dashboard">
            <i class="bi bi-house-door"></i>
            <span>Inicio</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/mascotas" class="mobile-nav-item" data-section="mascotas">
            <i class="bi bi-bluesky"></i>
            <span>Mis Mascotas</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/citas" class="mobile-nav-item" data-section="citas">
            <i class="bi bi-calendar-check"></i>
            <span>Citas</span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/tienda" class="mobile-nav-item" data-section="tienda">
            <i class="bi bi-bag-plus"></i>
            <span>Tienda</span>
        </a>
    </nav>
</div>


<!-- Burbuja flotante (abajo a la derecha) — solo visible en móvil -->
<button
    type="button"
    class="fab-menu"
    id="fabMenu"
    aria-label="Abrir menú"
    aria-expanded="false"
    aria-controls="mobilePanel">
    <i class="bi bi-grid-fill" id="fabIcon"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Referencias ── */
    var sidebar      = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var fabMenu      = document.getElementById('fabMenu');
    var fabIcon      = document.getElementById('fabIcon');
    var mobilePanel  = document.getElementById('mobilePanel');
    var mobileOverlay = document.getElementById('mobileOverlay');
    var panelClose   = document.getElementById('mobilePanelClose');

    /* ── Helpers ── */
    function isMobile() {
        return window.innerWidth <= 768;
    }

    /* ══════════════════════════════════════
       PC: toggle colapsar / expandir sidebar
       ══════════════════════════════════════ */
    sidebarToggle?.addEventListener('click', function () {
        if (isMobile()) return;
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    /* ══════════════════════════════════════
       MÓVIL: abrir / cerrar panel lateral
       ══════════════════════════════════════ */
    function abrirPanel() {
        mobilePanel.classList.add('open');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        fabMenu.setAttribute('aria-expanded', 'true');
        fabIcon.className = 'bi bi-x-lg';
    }

    function cerrarPanel() {
        mobilePanel.classList.remove('open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
        fabMenu.setAttribute('aria-expanded', 'false');
        fabIcon.className = 'bi bi-grid-fill';
    }

    fabMenu?.addEventListener('click', function () {
        mobilePanel.classList.contains('open') ? cerrarPanel() : abrirPanel();
    });

    panelClose?.addEventListener('click', cerrarPanel);
    mobileOverlay?.addEventListener('click', cerrarPanel);

    /* Cerrar con Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarPanel();
    });

    /* ══════════════════════════════════════
       Resize: limpiar estados correctamente
       ══════════════════════════════════════ */
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            cerrarPanel();
            var wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            sidebar?.classList.toggle('collapsed', wasCollapsed);
        } else {
            sidebar?.classList.remove('collapsed');
        }
    });

    /* ══════════════════════════════════════
       Inicializar estado al cargar
       ══════════════════════════════════════ */
    if (!isMobile()) {
        var wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        sidebar?.classList.toggle('collapsed', wasCollapsed);
    } else {
        sidebar?.classList.remove('collapsed');
    }

    /* ══════════════════════════════════════
       Marcar ítem activo según URL actual
       ══════════════════════════════════════ */
    var currentPath = window.location.pathname;
    document.querySelectorAll('.nav-item, .mobile-nav-item').forEach(function (link) {
        if (currentPath.includes(link.dataset.section)) {
            link.classList.add('active');
        }
    });

});

/* Nota: el listener de sidebarToggle vive únicamente dentro de DOMContentLoaded
   (arriba). El bloque redundante que existía aquí fue eliminado porque
   'sidebarToggle' era undefined en este scope (var declarado dentro del callback)
   y con optional-chaining (?.) era un no-op silencioso. */
</script>
