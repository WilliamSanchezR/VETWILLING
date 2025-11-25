<?php

// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos

require_once BASE_PATH . '/app/controllers/perfilControllers.php';

// asignamos el valo id del registro segun la tabla

$id = $_SESSION['user']['id'];

// Llamamos la funcion especifica que existe en dicho controlador y le pasamos los datos a una variable que podamos manipular en este archivo

$usuario = mostrarPerfilVeteri($id);

?>

<div class="barra-navegacion-superior">
    <div class="navegacion-izquierda">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-heart text-danger"></i>
            <span class="fw-semibold">Dashboards</span>
            <span class="text-muted">/</span>
            <span>Por defecto</span>
        </div>
    </div>
    <div class="buscador-navegacion">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search" class="form-control">
    </div>
    <div class="acciones-navegacion">
        <!-- Dentro de .acciones-navegacion -->
        <button class="boton-icono-navegacion" onclick="toggleTheme()">
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>
        <button class="boton-icono-navegacion">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>

        <button class="btn-perfil" onclick="togglePerfilMenu()" aria-label="Perfil">
            <div class="avatar-usuario">
                <img src="<?= BASE_URL ?>/public/uploads/veterinario/<?= $veterinario['img_perfil'] ?>" alt="">
            </div>

            <div class="info-usuario">
                <h4 class="nombre-usuario"><?= $usuario['nombres'] ?></h4>
                <p class="rol-usuario"><?= $usuario['tipo_usuario'] ?></p>
            </div>

            <i class="bi bi-chevron-down flecha-perfil"></i>
        </button>


        <button class="boton-icono-navegacion" onclick="alternarBarraDerecha()">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
</div>