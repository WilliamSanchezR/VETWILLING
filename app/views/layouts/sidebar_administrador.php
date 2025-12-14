<?php

$path = $_SERVER['REQUEST_URI'];
// Si quieres la parte del directorio
$path_parts = explode('/', $path);
$path_parts = array_filter($path_parts); // Elimina elementos vacíos
$final_path = end($path_parts); // Obtiene el último elemento

?>

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
            <a href="<?= BASE_URL ?>/admin/dashBoard"
                class="nav-item"
                data-section="dashboard">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Inicio</span>
            </a>
            <hr>
            <span class="nav-section-title">Reguisto Usuarios</span>

            <a href="<?= BASE_URL ?>/admin/registro-usuario"
                class="nav-item"
                data-section="dashboard">
                <i class="bi bi-clipboard-plus"></i>
                <span class="nav-text">Reguistrar Usuario</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/listar-usuarios"
                class="nav-item"
                data-section="mascotas">
                <i class="bi bi-file-text-fill"></i>
                <span class="nav-text">Listar Usuario</span>
            </a>
            <hr>
            <span class="nav-section-title">Reguisto Veterinarias</span>
            <a href="<?= BASE_URL ?>/admin/registro-veterinaria"
                class="nav-item"
                data-section="citas">
                <i class="bi bi-clipboard-plus"></i>
                <span class="nav-text">Reguistrar Veterinaria</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/listar-veterinarias"
                class="nav-item"
                data-section="tienda">
                <i class="bi bi-file-text-fill"></i>
                <span class="nav-text">Listar Veterinaria</span>
            </a>
        </div>
    </nav>

    <!-- BOTÓN TOGGLE -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>
<script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>