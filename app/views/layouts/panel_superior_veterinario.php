<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);

$veterinaria = consultarVeterinariaPorId($_SESSION['user']['id_veterinaria']);

?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">

<!-- Navbar Superior Profesional -->
<nav class="navbar-superior" id="navbarSuperior">
    <div class="navbar-container">
        
        <!-- Sección Izquierda -->
        <div class="navbar-left">
            <div class="image-avatar">
                <?php
                if (isset($veterinaria['foto']) && !empty($veterinaria['foto'])): ?>
                    <img 
                        src="<?= BASE_URL ?>/public/uploads/veterinaria/<?= $veterinaria['foto'] ?>" 
                        alt="Logo de <?= $veterinaria['nombre'] ?>" 
                        class="navbar-logo" title="<?= $veterinaria['nombre'] ?>">                    
                <?php endif; ?> 
            </div>
            <button class="btn-menu-mobile" onclick="abrirSidebarMovil()" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>

            <div class="greeting-section">
                <div class="greeting-content">
                    <span class="greeting-icon" id="saludoEmoji">👋</span>
                    <div class="greeting-text">
                        <span class="greeting-label" id="saludoTexto">Bienvenido</span>
                        <span class="greeting-time">
                            <i class="bi bi-clock"></i>
                            <span id="horaActual">00:00:00</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="breadcrumb-divider"></div>

            <nav class="breadcrumb-nav" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <span id="paginaActual">Dashboard</span>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Sección Centro - Búsqueda -->
        <div class="navbar-center">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input
                        type="text"
                        id="searchInput"
                        class="search-input"
                        placeholder="Buscar pacientes, citas, historiales médicos..."
                        autocomplete="off"
                        aria-label="Buscar">
                    <button class="btn-clear" id="btnClearSearch" style="display: none;" aria-label="Limpiar búsqueda">
                        <i class="bi bi-x"></i>
                    </button>
                    <kbd class="search-shortcut">Ctrl K</kbd>
                </div>

                <!-- Panel de Resultados -->
                <div class="search-results-panel" id="searchResults" style="display: none;">
                    <div class="search-results-header">
                        <span class="results-title">Resultados de búsqueda</span>
                        <span class="results-count" id="resultsCount">0 resultados</span>
                    </div>
                    <div class="search-results-body" id="searchItems">
                        <!-- Resultados dinámicos aquí -->
                    </div>
                    <div class="search-results-footer">
                        <span class="search-tip">
                            <i class="bi bi-lightbulb"></i>
                            Usa <kbd>↑</kbd> <kbd>↓</kbd> para navegar
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección Derecha - Acciones -->
        <div class="navbar-right">
            
            <!-- Botón Tema -->
            <div class="navbar-action" data-tooltip="Cambiar tema">
                <button class="btn-icon" onclick="toggleTheme()" aria-label="Cambiar tema">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>
            </div>

            <!-- Botón Notificaciones -->
            <div class="navbar-action notifications-wrapper" data-tooltip="Notificaciones">
                <button class="btn-icon" onclick="toggleNotificaciones()" aria-label="Ver notificaciones">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">3</span>
                </button>

                <!-- Panel de Notificaciones -->
                <div class="dropdown-panel notifications-panel" id="notificationsPanel" style="display: none;">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="bi bi-bell-fill"></i>
                            <h3>Notificaciones</h3>
                        </div>
                        <button class="btn-text-sm" onclick="marcarTodasLeidas()">
                            Marcar todas como leídas
                        </button>
                    </div>

                    <div class="panel-body">
                        <div class="notifications-list">
                            <div class="notification-item unread">
                                <div class="notification-indicator success"></div>
                                <div class="notification-icon success">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <h4>Nueva cita agendada</h4>
                                        <span class="notification-time">Hace 5 min</span>
                                    </div>
                                    <p>Max - Consulta general programada para mañana 10:00 AM</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>

                            <div class="notification-item">
                                <div class="notification-indicator warning"></div>
                                <div class="notification-icon warning">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <h4>Recordatorio de vacuna</h4>
                                        <span class="notification-time">Hace 1 hora</span>
                                    </div>
                                    <p>Luna requiere vacuna antirrábica - Vencimiento próximo</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>

                            <div class="notification-item">
                                <div class="notification-indicator info"></div>
                                <div class="notification-icon info">
                                    <i class="bi bi-file-medical-fill"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <h4>Resultados disponibles</h4>
                                        <span class="notification-time">Hace 2 horas</span>
                                    </div>
                                    <p>Rocky - Análisis de sangre completado y listo para revisar</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <a href="<?= BASE_URL ?>/veterinario/notificaciones" class="btn-link">
                            Ver todas las notificaciones
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="navbar-divider"></div>

            <!-- Perfil de Usuario -->
            <div class="navbar-action user-profile-wrapper">
                <button class="btn-profile" onclick="togglePerfilMenu()" aria-label="Menú de usuario">
                    <div class="profile-avatar">
                        <?php if ($usuario['id_rol'] == 4): ?>
                            <img 
                            src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>"
                            alt="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"
                            class="avatar-img">
                        <?php else: ?>
                        <img 
                            src="<?= BASE_URL ?>/public/uploads/profesionales/<?= $usuario['img_perfil'] ?>"
                            alt="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"
                            class="avatar-img">
                        <?php endif; ?>
                        <span class="status-dot online" title="En línea"></span>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?>
                        </span>
                        <span class="profile-role"><?= $usuario['rol'] ?></span>
                    </div>
                    <i class="bi bi-chevron-down profile-arrow"></i>
                </button>

                <!-- Dropdown de Perfil -->
                <div class="dropdown-panel profile-panel" id="perfilDropdown" style="display: none;">
                    <div class="panel-header profile-header">
                        <div class="profile-avatar-large">
                            <?php if ($usuario['id_rol'] == 4): ?>
                            <img 
                            src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>"
                            alt="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"
                            >
                        <?php else: ?>
                        <img 
                            src="<?= BASE_URL ?>/public/uploads/profesionales/<?= $usuario['img_perfil'] ?>"
                            alt="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"
                            >
                        <?php endif; ?>
                            <span class="status-dot online"></span>
                        </div>
                        <div class="profile-details">
                            <h3><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h3>
                            <p><?= $usuario['email'] ?></p>
                            <span class="profile-badge"><?= $usuario['rol'] ?></span>
                        </div>
                    </div>

                    <div class="panel-divider"></div>

                    <div class="panel-body">
                        <a href="<?= BASE_URL ?>/veterinario/consultar-perfil" class="dropdown-item">
                            <div class="item-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="item-content">
                                <span class="item-title">Mi Perfil</span>
                                <span class="item-subtitle">Ver y editar información personal</span>
                            </div>
                        </a>

                        <a href="#" class="dropdown-item" id="btnAbrirSoporte">
                            <div class="item-icon">
                                <i class="bi bi-headset"></i>
                            </div>
                            <div class="item-content">
                                <span class="item-title">Centro de Soporte</span>
                                <span class="item-subtitle">Ayuda y asistencia técnica</span>
                            </div>
                        </a>
                    </div>

                    <div class="panel-divider"></div>

                    <div class="panel-footer">
                        <a href="<?= BASE_URL ?>/cerrar-sesion" class="dropdown-item logout-item">
                            <div class="item-icon">
                                <i class="bi bi-box-arrow-right"></i>
                            </div>
                            <span class="item-title">Cerrar Sesión</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>


<!-- Modal de Soporte Profesional -->
<div class="modal-overlay" id="modalSoporte">
    <div class="modal-container">
        <div class="modal-content">
            
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon-wrapper">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="modal-title-wrapper">
                        <h2>Centro de Soporte</h2>
                        <p>Estamos aquí para ayudarte</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" id="btnCerrarModal" aria-label="Cerrar">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert-info">
                    <i class="bi bi-info-circle"></i>
                    <p>Completa el formulario y nuestro equipo te responderá en un máximo de 24 horas.</p>
                </div>

                <form id="formularioSoporte" class="soporte-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombreSoporte" class="form-label">
                                <i class="bi bi-person"></i>
                                Nombre Completo
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-input"
                                id="nombreSoporte"
                                placeholder="Tu nombre completo"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="emailSoporte" class="form-label">
                                <i class="bi bi-envelope"></i>
                                Correo Electrónico
                                <span class="required">*</span>
                            </label>
                            <input
                                type="email"
                                class="form-input"
                                id="emailSoporte"
                                placeholder="correo@ejemplo.com"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tipoProblema" class="form-label">
                            <i class="bi bi-tags"></i>
                            Categoría del Problema
                            <span class="required">*</span>
                        </label>
                        <select class="form-select" id="tipoProblema" required>
                            <option value="">Selecciona una categoría</option>
                            <option value="tecnico">🔧 Problema Técnico</option>
                            <option value="cuenta">👤 Gestión de Cuenta</option>
                            <option value="facturacion">💳 Facturación y Pagos</option>
                            <option value="funcionalidad">⚡ Funcionalidades</option>
                            <option value="otro">📋 Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcionProblema" class="form-label">
                            <i class="bi bi-chat-left-text"></i>
                            Descripción Detallada
                            <span class="required">*</span>
                        </label>
                        <textarea
                            class="form-textarea"
                            id="descripcionProblema"
                            rows="5"
                            placeholder="Describe tu problema con el mayor detalle posible. Incluye pasos para reproducir el error si aplica..."
                            required></textarea>
                        <span class="form-hint">Mínimo 20 caracteres</span>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill"></i>
                            Enviar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/navbar-superior.js"></script>