<?php

// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

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
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/panelSuperior.css">

<div class="barra-navegacion-superior">
    <div class="navegacion-izquierda">
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
            <span class="badge-notif">3</span>
        </button>

        <!-- Dropdown Notificaciones -->
        <div class="dropdown-menu dropdown-notificaciones" id="dropdownNotificaciones">
            <div class="dropdown-header">
                <h6>Notificaciones</h6>
                <button class="btn-marcar-leidas">Marcar todas como leídas</button>
            </div>
            <div class="dropdown-body">
                <a href="#" class="notificacion-item no-leida">
                    <div class="notif-icono notif-azul">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto"></p>
                        <span class="notif-tiempo">Nuevo ticket</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-verde">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Se ha registrado un usuario</p>
                        <span class="notif-tiempo">Hace 1 hora</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-naranja">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Se ha generado un nuevo ticket</p>
                        <span class="notif-tiempo">Hace 3 horas</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-rojo">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Se ha bloqueado una veterinaria</p>
                        <span class="notif-tiempo">Hace 5 horas</span>
                    </div>
                </a>
            </div>
            <div class="dropdown-footer">
                <a href="#" class="btn-ver-todas">Ver todas las notificaciones</a>
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
                <span> <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt="">
                </span>
            </div>
            <div class="info-usuario">
                <span class="nombre-usuario"><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></span>
                <span class="rol-usuario"><?= $usuario['rol'] ?></span>
            </div>
            <i class="bi bi-chevron-down flecha-perfil"></i>
        </button>

        <!-- Dropdown Perfil -->
        <div class="dropdown-menu dropdown-perfil" id="dropdownPerfil">
            <div class="perfil-header">
                <div class="avatar-usuario grande">
                    <span> <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt="">
                    </span>
                </div>
                <div>
                    <p class="nombre-completo"><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></p>
                    <p class="email-usuario"><?= $usuario['email'] ?></p>
                </div>
            </div>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/representante/perfil-representante" class="dropdown-item">
                <i class="bi bi-person-fill"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="#" class="dropdown-item" id="btnAbrirSoporte">
                <i class="bi bi-question-circle"></i>
                <span>Soporte</span>
            </a>



            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/logout" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

</div>

<!-- Modal de Soporte -->
<div id="modalSoporte" class="modal-soporte">
    <div class="modal-contenido">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="bi bi-headset"></i>
            </div>
            <h2>Centro de Soporte</h2>
            <button class="btn-cerrar" id="btnCerrarModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="modal-descripcion">¿Tienes algún problema o sugerencia? Completa el formulario y te responderemos pronto.</p>

            <form id="formularioSoporte">
                <div class="form-group">
                    <label for="nombreSoporte">
                        <i class="bi bi-person"></i> Nombre Completo
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="nombreSoporte"
                        name="nombre"
                        placeholder="Tu nombre"
                        required>
                </div>

                <div class="form-group">
                    <label for="emailSoporte">
                        <i class="bi bi-envelope"></i> Correo Electrónico
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        id="emailSoporte"
                        name="email"
                        placeholder="ejemplo@correo.com"
                        required>
                </div>

                <div class="form-group">
                    <label for="tipoProblema">
                        <i class="bi bi-tag"></i> Tipo de Consulta
                    </label>
                    <select class="form-control" id="tipoProblema" name="tipo_problema" required>
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="tecnico">🔧 Problema Técnico</option>
                        <option value="cuenta">👤 Problema con la Cuenta</option>
                        <option value="funcionalidad">⚙️ Funcionalidad</option>
                        <option value="sugerencia">💡 Sugerencia</option>
                        <option value="otro">📋 Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcionProblema">
                        <i class="bi bi-chat-left-text"></i> Descripción
                    </label>
                    <textarea
                        class="form-control"
                        id="descripcionProblema"
                        name="descripcion"
                        rows="5"
                        placeholder="Describe tu problema o sugerencia detalladamente..."
                        required></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancelar" id="btnCancelar">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-enviar">
                        <i class="bi bi-send-fill"></i>
                        Enviar Mensaje
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/panelSuperiorAdmin.js"></script>