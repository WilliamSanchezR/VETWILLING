<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/sidebar.css">

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="cerrarSidebarMovil()"></div>

<!-- Sidebar -->
<div class="barra-lateral-izquierda" id="barraLateralIzquierda">

    <button class="boton-toggle-flotante" onclick="alternarBarraIzquierda()" aria-label="Contraer/Expandir menú">
        <i class="bi bi-chevron-left" id="iconoToggleFlotante"></i>
    </button>
    <!-- Logo / Marca -->
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
            alt="VetWilling"
            class="sidebar-logo-full">

        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
            alt="VW"
            class="sidebar-logo-icon">
    </div>

    <!-- Toggle Button - Estilo Flotante (Recomendado) -->


    <!-- Menú de navegación -->
    <nav class="menu-sidebar">

        <!-- Sección Principal -->
        <div class="menu-seccion">
            <div class="seccion-titulo">
                <i class="bi bi-grid-fill"></i>
                <span class="texto-seccion">Principal</span>
            </div>

            <a href="<?= BASE_URL ?>/veterinaria/dashboard" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <span class="item-texto">Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/seguimientos" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <span class="item-texto">Seguimientos</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/calendario" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-calendar3"></i>
                </div>
                <span class="item-texto">Calendario</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinario/consultar-veterinario" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <span class="item-texto">Citas</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/laboratorio" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-flask"></i>
                </div>
                <span class="item-texto">Laboratorio</span>
            </a>
        </div>

        <!-- Divisor -->
        <div class="menu-divider"></div>

        <!-- Sección Gestión -->
        <div class="menu-seccion">
            <div class="seccion-titulo">
                <i class="bi bi-gear-fill"></i>
                <span class="texto-seccion">Gestión</span>
            </div>

            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-person-plus"></i>
                </div>
                <span class="item-texto">Registro</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/gestion_clinica" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-hospital"></i>
                </div>
                <span class="item-texto">Gestión Clínica</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/reportes" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <span class="item-texto">Reportes</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/recetas" class="item-sidebar">
                <div class="item-icono">
                    <i class="bi bi-receipt"></i>
                </div>
                <span class="item-texto">Recetas</span>
            </a>
        </div>

    </nav>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/sidebar.js"></script>