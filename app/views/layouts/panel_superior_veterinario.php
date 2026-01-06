<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">

<!-- Navbar Superior -->
<div class="navbar-superior" id="navbarSuperior">

    <!-- Sección Izquierda - Breadcrumb -->
    <div class="navegacion-izquierda">
        <button class="btn-menu-movil" onclick="abrirSidebarMovil()" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>

        <nav class="breadcrumb-container" aria-label="breadcrumb">
            <ol class="breadcrumb-custom">
                <li class="breadcrumb-item">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Inicio</span>
                </li>
                <li class="breadcrumb-divider">
                    <i class="bi bi-chevron-right"></i>
                </li>
                <li class="breadcrumb-item active">
                    <span id="paginaActual">Dashboard</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Sección Centro - Buscador -->
    <div class="buscador-navegacion">
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input
                type="text"
                id="searchInput"
                class="search-input"
                placeholder="Buscar pacientes, citas, registros..."
                autocomplete="off">
            <button class="btn-clear-search" id="btnClearSearch" style="display: none;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>

        <!-- Resultados de búsqueda -->
        <div class="search-results" id="searchResults" style="display: none;">
            <div class="search-section">
                <div class="search-section-title">Sugerencias</div>
                <div class="search-items" id="searchItems">
                    <!-- Se llenarán dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Derecha - Acciones -->
    <div class="acciones-navegacion">

        <!-- Tema (Modo oscuro/claro) -->
        <div class="action-item" data-tooltip="Cambiar tema">
            <button class="btn-action" onclick="toggleTheme()" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
            </button>
        </div>

        <!-- Notificaciones -->
        <div class="action-item" data-tooltip="Notificaciones">
            <button class="btn-action" onclick="toggleNotificaciones()" aria-label="Notificaciones">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-badge">3</span>
            </button>
        </div>

        <!-- Panel de notificaciones -->
        <div class="notifications-panel" id="notificationsPanel" style="display: none;">
            <div class="panel-header">
                <h4>Notificaciones</h4>
                <button class="btn-mark-read">Marcar todas como leídas</button>
            </div>
            <div class="panel-body">
                <div class="notification-item unread">
                    <div class="notification-icon bg-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">Nueva cita agendada</p>
                        <p class="notification-text">Max - Consulta general</p>
                        <span class="notification-time">Hace 5 min</span>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="notification-icon bg-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">Recordatorio de vacuna</p>
                        <p class="notification-text">Luna - Vacuna antirrábica</p>
                        <span class="notification-time">Hace 1 hora</span>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="notification-icon bg-info">
                        <i class="bi bi-file-medical"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">Resultados de laboratorio</p>
                        <p class="notification-text">Rocky - Análisis de sangre</p>
                        <span class="notification-time">Hace 2 horas</span>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <a href="#" class="btn-view-all">Ver todas las notificaciones</a>
            </div>
        </div>

        <!-- Perfil de usuario -->
        <div class="user-profile-container">
            <button class="btn-perfil" onclick="togglePerfilMenu()" aria-label="Menú de perfil">
                <div class="avatar-usuario">
                    <img src="<?= BASE_URL ?>/public/uploads/veterinarios/<?= $usuario['img_perfil'] ?>"
                        alt="<?= $usuario['nombres'] ?>">
                    <span class="status-indicator online"></span>
                </div>
                <div class="info-usuario">
                    <h4 class="nombre-usuario"><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h4>
                    <p class="rol-usuario"><?= $usuario['rol'] ?></p>
                </div>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>

            <!-- Menú desplegable de perfil -->
            <div class="perfil-dropdown" id="perfilDropdown" style="display: none;">
                <div class="dropdown-header">
                    <div class="avatar-large">
                        <img src="<?= BASE_URL ?>/public/uploads/veterinarios/<?= $usuario['img_perfil'] ?>"
                            alt="<?= $usuario['nombres'] ?>">
                    </div>
                    <div class="user-info-dropdown">
                        <h4><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h4>
                        <p><?= $usuario['email'] ?></p>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-body">
                    <a href="<?= BASE_URL ?>/veterinario/consultar-perfil" class="dropdown-item">
                        <i class="bi bi-person-circle"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="#" class="dropdown-item" id="btnAbrirSoporte">
                        <i class="bi bi-question-circle"></i>
                        <span>Soporte</span>
                    </a>

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
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-footer">
                    <a href="<?= BASE_URL ?>/logout" class="dropdown-item logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Panel lateral derecho (toggle)
        <div class="action-item" data-tooltip="Panel lateral">
            <button class="btn-action" onclick="alternarBarraDerecha()" aria-label="Panel lateral">
                <i class="bi bi-layout-sidebar-inset-reverse"></i>
            </button>
        </div> -->
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/navbar-superior.js"></script>