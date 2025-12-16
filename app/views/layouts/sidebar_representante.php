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
    <a class="sidebar-header" href="<?= BASE_URL ?>/admin/dashBoard">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
            alt="VetWilling"
            class="sidebar-logo-full">

        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
            alt="VW"
            class="sidebar-logo-icon">
    </a>

    <!-- NAVEGACIÓN -->
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

            <!-- Sección Usuario con Submenú especialidades -->
            <div class="submenu <?= $final_path == 'registro-especialidades' || $final_path == 'listar-usuarios' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Usuario">
                    <i class="bi bi-person-plus"></i>
                    <span class="texto-item-sidebar">Especialidades</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-especialidades' ? 'active' : '' ?>" href="#" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-especialidades' ? 'active' : '' ?>" href="#" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

            <!-- Sección con submenú Profesionales  -->
            <div class="submenu <?= $final_path == 'registro-profesionales' || $final_path == 'listar-profesionales' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Profesionales">
                    <i class="bi bi-people-fill"></i>
                    <span class="texto-item-sidebar">Profesionales</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-profesionales' ? 'active' : '' ?>" href="#" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-profesionales' ? 'active' : '' ?>" href="#" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

            <!-- Sessión con submenu Servicios y costos-->
            <div class="submenu <?= $final_path == 'registro-servicios' || $final_path == 'listar-servicios' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Servicios y Costos">
                    <i class="bi bi-person-plus"></i>
                    <span class="texto-item-sidebar">Servicios y Costos</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-servicios' ? 'active' : '' ?>" href="#" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-servicios' ? 'active' : '' ?>" href="#" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>