<?php

// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

// asignamos el valo id del registro segun la tabla
$id = $_SESSION['user']['id_usuario'];

// Llamamos la funcion especifica que existe en dicho controlador y le pasamos los datos a una variable que podamos manipular en este archivo
$usuario = mostrarPerfil($id);

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
            <a href="<?= BASE_URL ?>/veterinario/consultar-perfil">


                <div class="avatar-usuario">
                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt="">
                </div>



                <div class="info-usuario">
                    <h4 class="nombre-usuario"><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h4>
                    <p class="rol-usuario"><?= $usuario['rol'] ?></p>
                </div>


                <!-- La ventana flotante (modal) -->
                <div id="myModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <p>Contenido de la ventana flotante.</p>
                    </div>
                </div>


            </a>
        </button>

        <button class="boton-icono-navegacion" onclick="alternarBarraDerecha()">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
</div>