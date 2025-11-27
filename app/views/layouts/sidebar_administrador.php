<div class="barra-lateral-izquierda" id="barraLateralIzquierda">
    <div class="marca-sidebar">
        <span class=""><img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-POSITIVO.png" alt="logo" class="logoDas" width="200">
            <img src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/img/LOGO-VERTICAL-POSITIVA-DASHBOARD.png" alt="logo"
                class="logoDas logo-icono-sidebar" width="40"></span>
    </div>

    <div class="menu-sidebar">
        <div class="seccion-sidebar">Menu Administrador</div>

        <div class="item-sidebar submenu">

            <a href="#" class="submenu-toggle">
                <i class="bi bi-person-plus"></i>
                <span class="texto-item-sidebar">Usuario</span>
                <i class="bi bi-chevron-down flecha"></i>
            </a>

            <ul class="submenu-items">
                <li><a href="<?= BASE_URL ?>/admin/registro-usuario">Registrar </a></li>
                <li><a href="<?= BASE_URL ?>/admin/listar-usuarios">Listar</a></li>
            </ul>
        </div>

        <a href="../../index.html" class="item-sidebar">
            <i class="bi bi-box-arrow-in-left"></i>
            <span class="texto-item-sidebar">Cerrar Seción</span>
        </a>
    </div>

    <button class="boton-colapsar" onclick="alternarBarraIzquierda()">
        <i class="bi bi-chevron-left" id="iconoColapsar"></i>
    </button>
</div>