<?php

$path = $_SERVER['REQUEST_URI'];
// Si quieres la parte del directorio
$path_parts = explode('/', $path);
$path_parts = array_filter($path_parts); // Elimina elementos vacíos
$final_path = end($path_parts); // Obtiene el último elemento

?>
<!-- ESTILOS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">


<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- HEADER CON LOGO -->
    <a href="<?= BASE_URL ?>/admin/dashBoard" class="sidebar-header">
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

            <a href="<?= BASE_URL ?>/admin/dashBoard"
                class="nav-item <?= $final_path == 'dashBoard' ? 'active' : '' ?>"
                data-section="dashboard" data-tooltip="Inicio">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>

            <div class="submenu <?= $final_path == 'registro-usuario' || $final_path == 'listar-usuarios' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Usuario">
                    <i class="bi bi-person-plus"></i>
                    <span class="texto-item-sidebar">Usuario</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-usuario' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/registro-usuario" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-usuarios' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/listar-usuarios" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

            <!-- Menu Veterinaria -->
            <div class="submenu <?= $final_path == 'registro-veterinaria' || $final_path == 'listar-veterinarias' ? 'active open' : '' ?>">

                <div class="submenu-toggle" data-tooltip="Veterinaria">
                    <i class="bi bi-hospital"></i>
                    <span class="texto-item-sidebar">Veterinaria</span>
                    <i class="bi bi-chevron-down flecha"></i>
                </div>

                <ul class="submenu-items">
                    <li><a class="nav-item <?= $final_path == 'registro-veterinaria' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/registro-veterinaria" data-tooltip="Registrar">Registrar </a></li>
                    <li><a class="nav-item <?= $final_path == 'listar-veterinarias' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/listar-veterinarias" data-tooltip="Listar">Listar</a></li>
                </ul>
            </div>

            <a href="#"
                class="nav-item <?= $final_path == '' ? 'active' : '' ?>"
                data-tooltip="Ticket de Soporte">
                <i class="bi bi-ticket-perforated"></i>
                <span class="nav-text">Ticket de Soporte</span>
            </a>
        </div>

    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>