<?php

// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/helpers/avatar_helper.php';

// asignamos el valo id del registro segun la tabla
$id = $_SESSION['user']['id_usuario'];

// Llamamos la funcion especifica que existe en dicho controlador y le pasamos los datos a una variable que podamos manipular en este archivo

$usuario = mostrarPerfil($id);

// $path = $_SERVER['REQUEST_URI'];
// // Si quieres la parte del directorio
// $path_parts = explode('/', $path);
// $path_parts = array_filter($path_parts); // Elimina elementos vacíos
// $final_path = end($path_parts);

?>


<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/nav.css">
<!-- Modo oscuro común (administrador, representante y veterinario) -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/modo-oscuro.css">
<script src="<?= BASE_URL ?>/public/assets/global/js/modo-oscuro.js"></script>
<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>

<div class="barra-navegacion-superior">
    <div class="navegacion-izquierda">
        <!-- Abre el menú lateral en pantallas pequeñas (estilos en sidebarA.css) -->
        <button type="button" class="sa-burger" id="saBurger" aria-label="Abrir menú" aria-controls="sidebarAdmin"
            aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold"></span>
        </div>
    </div>

    <div class="navbar-centro">
        <div class="buscador-avanzado">
            <i class="bi bi-search icono-buscar"></i>
            <input
                type="text"
                placeholder="Buscar ..."
                class="input-buscar"
                id="inputBusqueda">
        </div>
    </div>

    <!-- Sección Derecha - Acciones -->
    <div class="navbar-derecha">
        <!-- Notificaciones -->
        <button class="btn-navbar notificaciones" onclick="toggleDropdown('notificaciones')" aria-label="Notificaciones">
            <i class="bi bi-bell-fill"></i>
            <span class="badge-notif" style="display:none">0</span>
        </button>

        <!-- Dropdown Notificaciones -->
        <div class="dropdown-menu dropdown-notificaciones" id="dropdownNotificaciones">
            <div class="dropdown-header">
                <h6>Notificaciones</h6>
                <button class="btn-marcar-leidas">Marcar todas como leídas</button>
            </div>
            <div class="dropdown-body" id="listaNotificaciones" aria-live="polite">
                <div class="empty-notificaciones">Cargando notificaciones...</div>
            </div>
        </div>

        <!-- Modo Oscuro -->
        <button class="btn-navbar" onclick="toggleTheme()" aria-label="Cambiar tema">
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <!-- Separador -->
        <div class="navbar-separador"></div>

        <!-- Perfil Usuario -->
        <button class="btn-perfil" onclick="toggleDropdown('perfil')" aria-label="Perfil">
            <div class="avatar-usuario">
                <span> <img src="<?= htmlspecialchars(avatar_src($usuario['img_perfil'] ?? '', 'usuarios', $usuario['nombres'] ?? '', $usuario['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="">
                </span>
            </div>
            <div class="info-usuario">
                <span class="nombre-usuario"><?= e($usuario['nombres']) ?> <?= e($usuario['apellidos']) ?></span>
                <span class="rol-usuario"><?= e($usuario['rol']) ?></span>
            </div>
            <i class="bi bi-chevron-down flecha-perfil"></i>
        </button>

        <!-- Dropdown Perfil -->
        <div class="dropdown-menu dropdown-perfil" id="dropdownPerfil">
            <div class="perfil-header">
                <div class="avatar-usuario grande">
                    <span> <img src="<?= htmlspecialchars(avatar_src($usuario['img_perfil'] ?? '', 'usuarios', $usuario['nombres'] ?? '', $usuario['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </span>
                </div>
                <div>
                    <p class="nombre-completo"><?= e($usuario['nombres']) ?> <?= e($usuario['apellidos']) ?></p>
                    <p class="email-usuario"><?= e($usuario['email']) ?></p>
                </div>
            </div>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/admin/perfil-administrador" class="dropdown-item">
                <i class="bi bi-person-fill"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="#" class="dropdown-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configuración</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/cerrar-sesion" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/panelSuperiorAdmin.js"></script>