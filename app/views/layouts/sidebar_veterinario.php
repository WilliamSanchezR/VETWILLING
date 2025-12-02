<div class="barra-lateral-izquierda" id="barraLateralIzquierda">
    <div class="marca-sidebar">
        <span class=""><img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-POSITIVO.png" alt="logo" class="logoDas" width="200">
            <img src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/img/LOGO-VERTICAL-POSITIVA-DASHBOARD.png" alt="logo"
                class="logoDas logo-icono-sidebar" width="40"></span>
    </div>


    <div class="menu-sidebar">
        <div class="seccion-sidebar">Menu</div>
        <a href="<?= BASE_URL ?>/veterinaria/dashboard" class="item-sidebar active">
            <i class="bi bi-speedometer2"></i>
            <span class="texto-item-sidebar">Dashboard</span>
        </a>
        <a href="views/veterinarias/dashBoardSeguimientos.html" class="item-sidebar">
            <i class="bi bi-arrow-repeat"></i>
            <span class="texto-item-sidebar">Seguimientos</span>
        </a>
        <a href="views/veterinarias/dashBoardCalendario.html" class="item-sidebar">
            <i class="bi bi-calendar3"></i>
            <span class="texto-item-sidebar">Calendario</span>
        </a>
        <a href="<?= BASE_URL ?>/veterinario/consultar-veterinario" class="item-sidebar">
            <i class="bi bi-calendar-check"></i>
            <span class="texto-item-sidebar">Citas</span>
        </a>
        <a href="views/veterinarias/dashBoardLaboratorio.html" class="item-sidebar">
            <i class="bi bi-flask"></i>
            <span class="texto-item-sidebar">Laboratorio</span>
        </a>

        <div class="seccion-sidebar">Opciones</div>
        <a href="<?= BASE_URL ?>/veterinario/registrar-veterinario" class="item-sidebar">
            <i class="bi bi-person-plus"></i>
            <span class="texto-item-sidebar">Registro</span>
        </a>
        <a href="views/veterinarias/dashBoardGestClinica.html" class="item-sidebar">
            <i class="bi bi-hospital"></i>
            <span class="texto-item-sidebar">Gestión clínica</span>
        </a>
        <a href="views/veterinarias/dashBoardReportes.html" class="item-sidebar">
            <i class="bi bi-file-earmark-text"></i>
            <span class="texto-item-sidebar">Reportes</span>
        </a>
        <a href="views/veterinarias/dashBoardRecetas.html" class="item-sidebar">
            <i class="bi bi-receipt"></i>
            <span class="texto-item-sidebar">Recetas</span>
        </a>
        <a href="<?= BASE_URL ?>/logout" class="item-sidebar">
            <i class="bi bi-box-arrow-in-left"></i>
            <span class="texto-item-sidebar">Cerrar Sesión</span>
        </a>
    </div>

    <button class="boton-colapsar" onclick="alternarBarraIzquierda()">
        <i class="bi bi-chevron-left" id="iconoColapsar"></i>
    </button>
        
</div>