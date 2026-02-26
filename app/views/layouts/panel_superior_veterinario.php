<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$id         = $_SESSION['user']['id_usuario'];
$usuario    = mostrarPerfil($id);
$veterinaria = consultarVeterinariaPorId($_SESSION['user']['id_veterinaria']);


?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">

<!-- ╔══════════════════════════════════════════════════╗ -->
<!-- ║         NAVBAR SUPERIOR — VetWilling            ║ -->
<!-- ╚══════════════════════════════════════════════════╝ -->
<nav class="navbar-superior" id="navbarSuperior">
    <div class="navbar-container">

        <!-- ─── IZQUIERDA ────────────────────────────────── -->
        <div class="navbar-left">

            <!-- Logo veterinaria -->
            <?php if (!empty($veterinaria['foto'])): ?>
                <div class="vet-logo-wrap">
                    <img
                        src="<?= BASE_URL ?>/public/uploads/veterinaria/<?= $veterinaria['foto'] ?>"
                        alt="<?= htmlspecialchars($veterinaria['nombre']) ?>"
                        class="vet-logo"
                        title="<?= htmlspecialchars($veterinaria['nombre']) ?>">
                </div>
            <?php endif; ?>

            <!-- Botón menú móvil -->
            <button class="btn-menu-mobile" onclick="abrirSidebarMovil()" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>

            <!-- Saludo + reloj -->
            <div class="greeting-section">
                <span class="greeting-icon" id="saludoEmoji">👋</span>
                <div class="greeting-text">
                    <span class="greeting-label" id="saludoTexto">Bienvenido</span>
                    <span class="greeting-time">
                        <i class="bi bi-clock"></i>
                        <span id="horaActual">00:00:00</span>
                    </span>
                </div>
            </div>

            <div class="nav-sep"></div>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">
                        <span id="paginaActual">Dashboard</span>
                    </li>
                </ol>
            </nav>

        </div>
        <!-- ─── FIN IZQUIERDA ─────────────────────────────── -->


        <!-- ─── CENTRO — Buscador ────────────────────────── -->
        <div class="navbar-center">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input
                        type="text"
                        id="searchInput"
                        class="search-input"
                        placeholder="Buscar pacientes, citas, historiales…"
                        autocomplete="off"
                        aria-label="Buscar">
                    <button class="btn-clear is-hidden" id="btnClearSearch" aria-label="Limpiar">
                        <i class="bi bi-x"></i>
                    </button>
                    <kbd class="search-shortcut">Ctrl K</kbd>
                </div>

                <!-- Panel de resultados -->
                <div class="search-results-panel is-hidden" id="searchResults">
                    <div class="search-results-header">
                        <span class="results-title">Resultados</span>
                        <span class="results-count" id="resultsCount">0 resultados</span>
                    </div>
                    <div class="search-results-body" id="searchItems"></div>
                    <div class="search-results-footer">
                        <span class="search-tip">
                            <i class="bi bi-lightbulb"></i>
                            Usa <kbd>↑</kbd> <kbd>↓</kbd> para navegar
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- ─── FIN CENTRO ────────────────────────────────── -->


        <!-- ─── DERECHA — Acciones ───────────────────────── -->
        <div class="navbar-right">

            <!-- Tema oscuro/claro -->
            <button class="btn-icon" onclick="toggleTheme()" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </button>

            <!-- Notificaciones -->
            <div class="navbar-action notifications-wrapper">
                <button class="btn-icon" onclick="toggleNotificaciones()" aria-label="Notificaciones">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">3</span>
                </button>

                <div class="dropdown-panel notifications-panel is-hidden" id="notificationsPanel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div>
                                <i class="bi bi-bell-fill"></i>
                                <h3>Notificaciones</h3>
                            </div>
                            <button class="btn-text-sm" onclick="marcarTodasLeidas()">
                                Marcar todas leídas
                            </button>
                        </div>
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
                                    <p>Max — Consulta general mañana 10:00 AM</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)" aria-label="Eliminar">
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
                                        <span class="notification-time">Hace 1 h</span>
                                    </div>
                                    <p>Luna — Antirrábica con vencimiento próximo</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)" aria-label="Eliminar">
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
                                        <span class="notification-time">Hace 2 h</span>
                                    </div>
                                    <p>Rocky — Análisis de sangre listo para revisar</p>
                                </div>
                                <button class="btn-icon-sm" onclick="eliminarNotificacion(this)" aria-label="Eliminar">
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

            <div class="nav-sep-v"></div>

            <!-- Perfil -->
            <div class="navbar-action user-profile-wrapper">
                <button class="btn-profile" onclick="togglePerfilMenu()" aria-label="Menú de usuario">

                    <!-- Avatar -->
                    <div class="profile-avatar">
                        <img
                            src="<?= BASE_URL ?>/public/uploads/<?= $usuario['id_rol'] == 4 ? 'usuarios' : 'profesionales' ?>/<?= $usuario['img_perfil'] ?>"
                            alt="<?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>"
                            class="avatar-img">
                        <span class="status-dot online" title="En línea"></span>
                    </div>

                    <!-- Info -->
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars($usuario['nombres']) ?>
                            <?= htmlspecialchars($usuario['apellidos']) ?>
                        </span>
                        <span>

                        </span>
                    </div>

                    <i class="bi bi-chevron-down profile-arrow"></i>
                </button>

                <!-- ── Dropdown perfil ── -->
                <div class="dropdown-panel profile-panel is-hidden" id="perfilDropdown">

                    <!-- Cabecera con avatar grande -->
                    <div class="profile-header">
                        <div class="profile-avatar-large">
                            <img
                                src="<?= BASE_URL ?>/public/uploads/<?= $usuario['id_rol'] == 4 ? 'usuarios' : 'profesionales' ?>/<?= $usuario['img_perfil'] ?>"
                                alt="<?= htmlspecialchars($usuario['nombres']) ?>">
                            <span class="status-dot online"></span>
                        </div>
                        <div class="profile-details">
                            <h3><?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></h3>
                            <p><?= htmlspecialchars($usuario['email']) ?></p>
                            <div class="profile-badges-row">
                                <span class="profile-badge"><?= htmlspecialchars($usuario['rol']) ?></span>
                                <!-- Badge suscripción en dropdown -->
                              
                            </div>
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
                <!-- ─── FIN PERFIL ─────────────────────────────── -->

            </div>
            <!-- ─── FIN DERECHA ───────────────────────────────── -->

        </div>
</nav>


<!-- ╔══════════════════════════════════════════════════╗ -->
<!-- ║         MODAL DE SOPORTE                        ║ -->
<!-- ╚══════════════════════════════════════════════════╝ -->
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
                    <p>Completa el formulario y nuestro equipo te responderá en máximo 24 horas.</p>
                </div>

                <form id="formularioSoporte" class="soporte-form">
                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombreSoporte" class="form-label">
                                <i class="bi bi-person"></i>
                                Nombre Completo
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                disabled
                                class="form-input"
                                id="nombreSoporte"
                                placeholder="Tu nombre completo"
                                value="<?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?>"
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
                                disabled
                                class="form-input"
                                id="emailSoporte"
                                placeholder="correo@ejemplo.com"
                                value="<?= htmlspecialchars($usuario['email']) ?>"
                                required>
                        </div>
                    </div>

                        <div class="form-group">
                            <label for="telefonoSoporte" class="form-label">
                                Asunto
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-input"
                                id="asunto"
                                placeholder="Asunto del problema"
                                name="asunto"
                                required>
                        </div>

                    <div class="form-group">
                        <label for="tipoProblema" class="form-label">
                            <i class="bi bi-tags"></i>
                            Categoría del Problema
                            <span class="required">*</span>
                        </label>
                        <select class="form-select" id="tipoProblema" name="categoria" required>
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
                            placeholder="Describe tu problema con el mayor detalle posible…"
                            name="descripcion"
                            required></textarea>
                        <span class="form-hint">Mínimo 20 caracteres</span>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEnviarSoporte">
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
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>