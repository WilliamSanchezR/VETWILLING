<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- HEADER CON LOGO -->
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
            alt="VetWilling"
            class="sidebar-logo-full">

        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
            alt="VW"
            class="sidebar-logo-icon">
    </div>

    <!-- NAVEGACIÓN -->
    <nav class="sidebar-nav">

        <div class="nav-section">
            <span class="nav-section-title">General</span>

            <a href="<?= BASE_URL ?>/cliente/dashboard"
                class="nav-item"
                data-section="dashboard">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/mascotas"
                class="nav-item"
                data-section="mascotas">
                <i class="bi bi-bluesky"></i>
                <span class="nav-text">Mis Mascotas</span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/citas"
                class="nav-item"
                data-section="citas">
                <i class="bi bi-calendar-check"></i>
                <span class="nav-text">Citas</span>
            </a>

            <a href="<?= BASE_URL ?>/cliente/tienda"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-bag-plus"></i>
                <span class="nav-text">Tienda</span>
            </a>
        </div>

    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

</aside>

<script>
    // ── Toggle sidebar con detección de móvil ──
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle'); // tu botón toggle de escritorio
    const overlay = document.getElementById('sidebarOverlay');

    function isMobile() {
        return window.innerWidth <= 768;
    }

    // Este es el toggle de ESCRITORIO (colapsar/expandir)
    sidebarToggle?.addEventListener('click', () => {
        if (isMobile()) return; // en móvil no hace nada
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Cerrar sidebar móvil al tocar el overlay
    overlay?.addEventListener('click', cerrarSidebarMobile);

    function cerrarSidebarMobile() {
        sidebar?.classList.remove('show');
        overlay?.classList.remove('active'); // si usas clase en overlay directamente
        document.body.style.overflow = '';
    }

    // Al cambiar de tamaño de ventana, limpiar estados
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar?.classList.remove('show');
            document.body.style.overflow = '';
            // Restaurar estado colapsado guardado
            const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            sidebar?.classList.toggle('collapsed', wasCollapsed);
        } else {
            // En móvil quitar collapsed para que el panel se vea completo cuando se abra
            sidebar?.classList.remove('collapsed');
        }
    });

    // Al cargar, aplicar estado guardado solo en escritorio
    document.addEventListener('DOMContentLoaded', () => {
        if (!isMobile()) {
            const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            sidebar?.classList.toggle('collapsed', wasCollapsed);
        } else {
            sidebar?.classList.remove('collapsed');
        }
    });
</script>