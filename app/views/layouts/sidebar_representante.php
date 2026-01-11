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

            <!-- Sección Especialidades -->
            <a href="<?= BASE_URL ?>/representante/listar-especialidades"
                class="nav-item <?= $final_path == 'listar-especialidades' ? 'active' : '' ?>"
                data-section="Especialidades" data-tooltip="Especialidades">
                <i class="bi bi-person-plus"></i>
                <span class="nav-text">Especialidades</span>
            </a>


            <!-- Sección con submenú Profesionales  -->
            <div class="submenu <?= $final_path == 'registro-profesionales' || $final_path == 'listar-profesionales' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Profesionales">
                    <i class="bi bi-people-fill"></i>
                    <span class="texto-item-sidebar">Profesionales</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-profesionales' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/registro-profesionales" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-profesionales' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/listar-profesionales" data-tooltip="Listar">Listar</a></li>
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
                    <li><a class="nav-item <?= $final_path == 'registro-servicios' ? 'active' : '' ?>" href="<?= BASE_URL ?>/representante/registro-servicio" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-servicios' ? 'active' : '' ?>" href="#" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

            <!-- Sessión de submenu de horarios de atencion -->
            <div class="submenu <?= $final_path == 'registro-servicios' || $final_path == 'listar-servicios' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Horarios de Atención">
                    <i class="bi bi-person-plus"></i>
                    <span class="texto-item-sidebar">Horarios de Atención</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-horarios' ? 'active' : '' ?>" href="#" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-horarios' ? 'active' : '' ?>" href="#" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>
    </nav>



    <!-- NAVEGACION COMO VETERINARIO -->



    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>