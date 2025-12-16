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
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>