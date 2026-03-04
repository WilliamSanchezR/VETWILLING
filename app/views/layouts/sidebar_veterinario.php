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

            <?php if ($_SESSION['user']['id_rol'] === 4): ?>
                <a href="<?= BASE_URL ?>/representante/dashboard"
                    class="nav-item <?= $final_path == 'dashBoard' ? 'active' : '' ?>"
                    data-section="dashboard" data-tooltip="Inicio">
                    <i class="bi bi-house-door"></i>
                    <span class="nav-text">Inicio</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/veterinaria/dashboard"
                    class="nav-item"
                    data-section="dashboard">
                    <i class="bi bi-house-door"></i>
                    <span class="nav-text">Inicio</span>
                </a>
            <?php endif; ?>

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

            <a href="<?= BASE_URL ?>/veterinaria/mi-agenda"
                class="nav-item"
                data-section="agenda">
                <i class="bi bi-clock"></i>
                <span class="nav-text">Mi Disponibilidad</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/gestion_clinica"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-hospital"></i>
                <span class="nav-text">Gestion Pacientes</span>
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

            <!-- INTERFAZ COMENTADA: Registro de Veterinarios
            <a href="<?= BASE_URL ?>/veterinario/registrar-veterinarios"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-clipboard-plus"></i>
                <span class="nav-text">Registro Veterinarios</span>
            </a>
            -->
            <!-- INTERFAZ COMENTADA: Listar Veterinarios
            <a href="<?= BASE_URL ?>/veterinario/consultar-veterinarios"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-calendar-check"></i>
                <span class="nav-text">Listar Veterinarios</span>
            </a>
            -->
            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-pencil-square"></i>
                <span class="nav-text">Registro Pacientes</span>
            </a>
            <a href="<?= BASE_URL ?>/veterinario/consultar-citas"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-search"></i>
                <span class="nav-text">Citas</span>
            </a>
        </div>

    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>
<script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>