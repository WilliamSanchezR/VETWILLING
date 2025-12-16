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

            <a href="<?= BASE_URL ?>/veterinaria/dashboard"
                class="nav-item"
                data-section="dashboard">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/seguimientos"
                class="nav-item"
                data-section="citas">
                <i class="bi bi-card-checklist"></i>
                <span class="nav-text">Seguimineto</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/calendario"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-calendar-week"></i>
                <span class="nav-text">Calendario</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/gestion_clinica"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-hospital"></i>
                <span class="nav-text">Gestion Clinicas</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/laboratorio"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-beaker"></i>
                <span class="nav-text">Laboratorio</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/recetas"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-journal-text"></i>
                <span class="nav-text">Recetas</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/reportes"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-bar-chart"></i>
                <span class="nav-text">Reportes</span>
            </a>
        </div>

        <!-- Divisor -->
        <div class="menu-divider"></div>

        <!-- Sección Gestión -->
        <div class="menu-seccion">
            <div class="seccion-titulo">
                <span class="nav-section-title">Gestión</span>
            </div>

            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-clipboard-plus"></i>
                <span class="nav-text">Registrar Veterinario</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinario/consultar-veterinario"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-search"></i>
                <span class="nav-text">Consultar Veterinario</span>
            </a>
        </div>

    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>
<script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>