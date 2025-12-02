<?php

$path = $_SERVER['REQUEST_URI'];
// Si quieres la parte del directorio
$path_parts = explode('/', $path);
$path_parts = array_filter($path_parts); // Elimina elementos vacíos
$final_path = end($path_parts); // Obtiene el último elemento

?>

<div class="barra-lateral-izquierda" id="barraLateralIzquierda">
    <div class="marca-sidebar">
        <span class=""><img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-POSITIVO.png" alt="logo" class="logoDas" width="200">
            <img src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/img/LOGO-VERTICAL-POSITIVA-DASHBOARD.png" alt="logo"
                class="logoDas logo-icono-sidebar" width="40"></span>
    </div>

    <div class="menu-sidebar">
        <div class="seccion-sidebar">Menu Administrador</div>

        <a href="<?= BASE_URL ?>/admin/dashBoard" class="item-sidebar <?= $final_path == 'dashBoard' ? 'active': '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span class="texto-item-sidebar">Dashboard</span>
        </a>

        <div class="item-sidebar submenu <?= $final_path == 'registro-usuario' || $final_path == 'listar-usuarios' ? 'active': '' ?>">

            <a href="#" class="submenu-toggle">
                <i class="bi bi-person-plus"></i>
                <span class="texto-item-sidebar">Usuario</span>
                <i class="bi bi-chevron-down flecha"></i>
            </a>

            <ul class="submenu-items">
                <li><a class="<?= $final_path == 'registro-usuario' ? 'active': '' ?>" href="<?= BASE_URL ?>/admin/registro-usuario">Registrar </a></li>
                <li><a class="<?= $final_path == 'listar-usuarios' ? 'active': '' ?>" href="<?= BASE_URL ?>/admin/listar-usuarios">Listar</a></li>
            </ul>
        </div>

        <a href="<?= BASE_URL ?>/login" class="item-sidebar">
            <i class="bi bi-box-arrow-in-left"></i>
            <span class="texto-item-sidebar">Cerrar Seción</span>
        </a>
    </div>

    <button class="boton-colapsar" onclick="alternarBarraIzquierda()">
        <i class="bi bi-chevron-left" id="iconoColapsar"></i>
    </button>
</div>