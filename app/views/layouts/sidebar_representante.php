<?php

$path = $_SERVER['REQUEST_URI'];
// Si quieres la parte del directorio
$path_parts = explode('/', $path);
$path_parts = array_filter($path_parts); // Elimina elementos vacíos
$final_path = end($path_parts); // Obtiene el último elemento del array


?>
<!-- ESTILOS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- HEADER CON LOGO -->
    <a class="sidebar-header" href="<?= BASE_URL ?>/representante/dashboard">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
            alt="VetWilling"
            class="sidebar-logo-full">

        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
            alt="VW"
            class="sidebar-logo-icon">
    </a>

    <!-- NAVEGACIÓN COMO ADMINISTARDOR-->
    <nav class="sidebar-nav">

        <div class="nav-section">
            <span class="nav-section-title">Menu Administrador</span>

            <!-- Sección Dashboard -->
            <a href="<?= BASE_URL ?>/representante/dashboard"
                class="nav-item <?= $final_path == 'dashBoard' ? 'active' : '' ?>"
                data-section="dashboard" data-tooltip="Inicio">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>

            <div class="submenu">
                <div class="submenu-toggle" data-tooltip="Gestión Administrador">
                    <i class="bi bi-database-up"></i>
                    <span class="texto-item-sidebar">Gestión Administrador</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <!-- Sección Especialidades -->
                    <li>
                        <a href="<?= BASE_URL ?>/representante/listar-especialidades"
                            class="nav-item <?= $final_path == 'listar-especialidades' ? 'active' : '' ?>"
                            data-section="Especialidades" data-tooltip="Especialidades">
                            <i class="bi bi-person-plus"></i>
                            <span class="nav-text">Especialidades</span>
                        </a>
                    </li>

                    <li>
                        <!-- Sección con submenú Profesionales  -->
                        <div class="submenu-seccond <?= $final_path == 'registro-profesionales' || $final_path == 'listar-profesionales' ? 'active open' : '' ?>">

                            <div class="submenu-seccond-toggle" data-tooltip="Profesionales">
                                <i class="bi bi-people-fill"></i>
                                <span class="texto-item-sidebar">Profesionales</span>
                                <i class="bi bi-chevron-down flecha-second"></i>
                            </div>

                            <ul class="submenu-seccond-items">
                                <li><a class="nav-item <?= $final_path == 'registro-profesionales' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/registro-profesionales" data-tooltip="Registrar">Registrar </a></li>
                                <li><a class="nav-item <?= $final_path == 'listar-profesionales' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/listar-profesionales" data-tooltip="Listar">Listar</a></li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <!-- Sessión con submenu Servicios y costos-->
                        <div class="submenu-seccond <?= $final_path == 'registro-servicio' || $final_path == 'listar-servicios' ? 'active open' : '' ?>">

                            <div class="submenu-seccond-toggle" data-tooltip="Servicios y Costos">
                                <i class="bi bi-database-up"></i>
                                <span class="texto-item-sidebar">Servicios y Costos</span>
                                <i class="bi bi-chevron-down flecha-second"></i>
                            </div>

                            <ul class="submenu-seccond-items">
                                <li><a class="nav-item <?= $final_path == 'listar-servicios' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/listar-servicios" data-tooltip="Listar">Servicios</a></li>
                                <li><a class="nav-item <?= $final_path == 'listar-subservicios' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/listar-subservicios" data-tooltip="Listar">Subservicios</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>






            <!-- NAVEGACION COMO VETERINARIO -->
            <div class="submenu">

                <div class="submenu-toggle" data-tooltip="Gestión veterinario">
                    <i class="bi bi-database-up"></i>
                    <span class="texto-item-sidebar">Gestión veterinario</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a href="<?= BASE_URL ?>/veterinaria/seguimientos"
                            class="nav-item"
                            data-section="citas">
                            <i class="bi bi-card-checklist"></i>
                            <span class="nav-text">Seguimineto</span>
                        </a></li>

                    <li><a href="<?= BASE_URL ?>/veterinaria/calendario"
                            class="nav-item"
                            data-section="tienda">
                            <i class="bi bi-calendar-week"></i>
                            <span class="nav-text">Calendario</span>
                        </a></li>
                    <li><a href="<?= BASE_URL ?>/veterinaria/gestion_clinica"
                            class="nav-item"
                            data-section="tienda">
                            <i class="bi bi-hospital"></i>
                            <span class="nav-text">Gestion Clinicas</span>
                        </a></li>
                    <li><a href="<?= BASE_URL ?>/veterinaria/laboratorio"
                            class="nav-item"
                            data-section="tienda">
                            <i class="bi bi-beaker"></i>
                            <span class="nav-text">Laboratorio</span>
                        </a></li>
                    <li><a href="<?= BASE_URL ?>/veterinaria/recetas"
                            class="nav-item"
                            data-section="tienda">
                            <i class="bi bi-journal-text"></i>
                            <span class="nav-text">Recetas</span>
                        </a></li>
                    <li><a href="<?= BASE_URL ?>/veterinaria/reportes"
                            class="nav-item"
                            data-section="tienda">
                            <i class="bi bi-bar-chart"></i>
                            <span class="nav-text">Reportes</span>
                        </a></li>
                </ul>
            </div>
    </nav>



    <!-- NAVEGACION COMO VETERINARIO -->



    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>