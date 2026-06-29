<?php
// ── Cargar preferencias e i18n si la vista no lo hizo ya ──────────────────
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) {
        require_once BASE_PATH . '/app/services/PreferenciasManager.php';
    }
    $_pm_sb = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs  = $_pm_sb->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) {
        require_once BASE_PATH . '/app/lang/i18n.php';
    }
    $t = I18n::cargar($prefs['idioma']);
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css?v=<?= APP_VERSION ?>">

<?php
// ── Inyectar prefs y strings en window para que theme.js e i18n.js los usen
// Solo se inyecta si aún no lo hizo la vista (confi.php lo hace en <head>)
if (!isset($vw_prefs_injected)):
    $vw_prefs_injected = true;
?>
<script>
if (typeof window.__prefs === 'undefined') {
    window.__prefs    = <?= json_encode($prefs) ?>;
    window.__lang_all = <?= json_encode([
        'es' => I18n::obtenerStrings('es'),
        'en' => I18n::obtenerStrings('en'),
        'pt' => I18n::obtenerStrings('pt'),
    ]) ?>;
}
</script>
<?php endif; ?>

<!-- ============================================================
     SIDEBAR  (PC: colapsable | Móvil: burbuja + panel lateral)
     ============================================================ -->

<!-- SIDEBAR PC — se oculta en móvil via CSS -->
<aside class="sidebar" id="sidebar">

    <!-- ► HEADER: no se modifica, usa tus logos originales -->
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
            alt="VetWilling"
            class="sidebar-logo-full">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
            alt="VW"
            class="sidebar-logo-icon">
    </div>

    <!-- ► NAV: rediseñado con contenedor de ícono -->
    <nav class="sidebar-nav">
        <div class="nav-section">

            <span class="nav-section-title" data-i18n="sidebar.seccion.general"><?= $t('sidebar.seccion.general') ?></span>

            <a href="<?= BASE_URL ?>/cliente/dashboard"
                class="nav-item"
                data-section="dashboard"
                data-tooltip="<?= $t('sidebar.inicio') ?>">
                <div class="nav-icon"><i class="bi bi-house-door"></i></div>
                <span class="nav-text" data-i18n="sidebar.inicio"><?= $t('sidebar.inicio') ?></span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/mascotas"
                class="nav-item"
                data-section="mascotas"
                data-tooltip="<?= $t('sidebar.mascotas') ?>">
                <div class="nav-icon"><i class="bi bi-bluesky"></i></div>
                <span class="nav-text" data-i18n="sidebar.mascotas"><?= $t('sidebar.mascotas') ?></span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/citas"
                class="nav-item"
                data-section="citas"
                data-tooltip="<?= $t('sidebar.citas') ?>">
                <div class="nav-icon"><i class="bi bi-calendar-check"></i></div>
                <span class="nav-text" data-i18n="sidebar.citas"><?= $t('sidebar.citas') ?></span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/tienda"
                class="nav-item"
                data-section="tienda"
                data-tooltip="<?= $t('sidebar.catalogo') ?>">
                <div class="nav-icon"><i class="bi bi-bag-plus"></i></div>
                <span class="nav-text" data-i18n="sidebar.catalogo"><?= $t('sidebar.catalogo') ?></span>
            </a>

            <a href="<?= BASE_URL ?>/directorio"
                class="nav-item"
                data-section="directorio"
                data-tooltip="Directorio">
                <div class="nav-icon"><i class="bi bi-people-fill"></i></div>
                <span class="nav-text">Directorio</span>
            </a>
            <span class="nav-section-title">Comunicación</span>

            <a href="<?= BASE_URL ?>/cliente/notificaciones"
                class="nav-item"
                data-section="notificaciones"
                data-tooltip="<?= $t('nav.notificaciones') ?>">
                <div class="nav-icon"><i class="bi bi-bell"></i></div>
                <span class="nav-text" data-i18n="nav.notificaciones"><?= $t('nav.notificaciones') ?></span>
            </a>

        </div>
    </nav>

    <!-- Toggle solo PC -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" type="button">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>

<!-- ============================================================
     MÓVIL: Overlay + Panel lateral + Burbuja
     ============================================================ -->

<!-- Overlay oscuro detrás del panel -->
<div class="mobile-overlay" id="mobileOverlay" aria-hidden="true"></div>

<!-- Panel lateral móvil (desliza desde la izquierda) -->
<div class="mobile-panel" id="mobilePanel" role="dialog" aria-modal="true" aria-label="Menú de navegación">

    <!-- ► HEADER MÓVIL: usa tu logo positivo original -->
    <div class="mobile-panel-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-POSITIVO.png"
            alt="VetWilling"
            class="mobile-panel-logo">
        <button type="button" class="mobile-panel-close" id="mobilePanelClose" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- ► NAV MÓVIL -->
    <nav class="mobile-panel-nav">

        <span class="mobile-nav-section-title" data-i18n="sidebar.seccion.general"><?= $t('sidebar.seccion.general') ?></span>

        <a href="<?= BASE_URL ?>/cliente/dashboard" class="mobile-nav-item" data-section="dashboard">
            <div class="mobile-nav-icon"><i class="bi bi-house-door"></i></div>
            <span data-i18n="sidebar.inicio"><?= $t('sidebar.inicio') ?></span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/mascotas" class="mobile-nav-item" data-section="mascotas">
            <div class="mobile-nav-icon"><i class="bi bi-bluesky"></i></div>
            <span data-i18n="sidebar.mascotas"><?= $t('sidebar.mascotas') ?></span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/citas" class="mobile-nav-item" data-section="citas">
            <div class="mobile-nav-icon"><i class="bi bi-calendar-check"></i></div>
            <span data-i18n="sidebar.citas"><?= $t('sidebar.citas') ?></span>
        </a>
        <a href="<?= BASE_URL ?>/cliente/tienda" class="mobile-nav-item" data-section="tienda">
            <div class="mobile-nav-icon"><i class="bi bi-bag-plus"></i></div>
            <span data-i18n="sidebar.catalogo"><?= $t('sidebar.catalogo') ?></span>
        </a>

        <span class="mobile-nav-section-title">Comunicación</span>

        <a href="<?= BASE_URL ?>/cliente/notificaciones" class="mobile-nav-item" data-section="notificaciones">
            <div class="mobile-nav-icon"><i class="bi bi-bell"></i></div>
            <span>Notificaciones</span>
        </a>

        <a href="<?= BASE_URL ?>/directorio" class="mobile-nav-item" data-section="directorio">
            <div class="mobile-nav-icon"><i class="bi bi-people-fill"></i></div>
            <span>Directorio</span>
        </a>
    </nav>
</div>

<!-- Burbuja flotante — solo visible en móvil -->
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
    document.addEventListener('DOMContentLoaded', function() {

        /* ── Referencias ── */
        var sidebar = document.getElementById('sidebar');
        var sidebarToggle = document.getElementById('sidebarToggle');
        var fabMenu = document.getElementById('fabMenu');
        var fabIcon = document.getElementById('fabIcon');
        var mobilePanel = document.getElementById('mobilePanel');
        var mobileOverlay = document.getElementById('mobileOverlay');
        var panelClose = document.getElementById('mobilePanelClose');

        /* ── Helper ── */
        function isMobile() {
            return window.innerWidth <= 768;
        }

        /* ══════════════════════════════════════
           PC: toggle colapsar / expandir sidebar
           ══════════════════════════════════════ */
        sidebarToggle?.addEventListener('click', function() {
            if (isMobile()) return;
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            /* Rotar ícono del toggle */
            var icon = sidebarToggle.querySelector('i');
            if (icon) {
                icon.className = sidebar.classList.contains('collapsed') ?
                    'bi bi-chevron-right' :
                    'bi bi-chevron-left';
            }
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

        fabMenu?.addEventListener('click', function() {
            mobilePanel.classList.contains('open') ? cerrarPanel() : abrirPanel();
        });

        panelClose?.addEventListener('click', cerrarPanel);
        mobileOverlay?.addEventListener('click', cerrarPanel);

        /* Cerrar con Escape */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarPanel();
        });

        /* ══════════════════════════════════════
           Resize: limpiar estados
           ══════════════════════════════════════ */
        window.addEventListener('resize', function() {
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
            /* Ícono correcto al cargar */
            var icon = sidebarToggle?.querySelector('i');
            if (icon && wasCollapsed) icon.className = 'bi bi-chevron-right';
        }

        /* ══════════════════════════════════════
           Marcar ítem activo según URL actual
           ══════════════════════════════════════ */
        var currentPath = window.location.pathname;
        document.querySelectorAll('.nav-item, .mobile-nav-item').forEach(function(link) {
            if (link.dataset.section && currentPath.includes(link.dataset.section)) {
                link.classList.add('active');
            }
        });

    });
</script>