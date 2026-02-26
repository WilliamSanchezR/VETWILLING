<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

$id      = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);

$suscripcion = $usuario['suscripcion'] ?? 'Essential';
$sub_slug    = strtolower($suscripcion);

$sub_config = [
    'essential' => ['icon' => 'bi-gift',               'color' => '#6c757d', 'bg' => '#f0f0f0'],
    'basic'     => ['icon' => 'bi-lightning-charge-fill','color' => '#0d6efd', 'bg' => '#e7f0ff'],
    'pro'       => ['icon' => 'bi-stars',               'color' => '#fd7e14', 'bg' => '#fff3e0'],
    'mastervet' => ['icon' => 'bi-gem',                 'color' => '#6f42c1', 'bg' => '#f0e9ff'],
];
$sub = $sub_config[$sub_slug] ?? $sub_config['essential'];

// Iniciales del usuario como fallback del avatar
$iniciales = strtoupper(substr($usuario['nombres'], 0, 1) . substr($usuario['apellidos'], 0, 1));
$img_src   = BASE_URL . '/public/uploads/usuarios/' . $usuario['img_perfil'];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/panelSuperior.css">

<nav class="navbar-superior" id="navbarSuperior" role="navigation" aria-label="Barra de navegación principal">

    <!-- ═══ IZQUIERDA: Breadcrumb ═══ -->
    <div class="navbar-izquierda">
        <div class="breadcrumb-nav">
            <i class="bi bi-house-door-fill breadcrumb-home"></i>
            <i class="bi bi-chevron-right breadcrumb-sep"></i>
            <span class="breadcrumb-actual" id="breadcrumbActual">Dashboard</span>
        </div>
    </div>

    <!-- ═══ CENTRO: Buscador ═══ -->
    <div class="navbar-centro">
        <div class="buscador-avanzado">
            <i class="bi bi-search icono-buscar"></i>
            <input
                type="text"
                placeholder="Buscar veterinarias, usuarios, reportes..."
                class="input-buscar"
                id="inputBusqueda"
                autocomplete="off"
                aria-label="Buscar">
        </div>
    </div>

    <!-- ═══ DERECHA: Acciones ═══ -->
    <div class="navbar-derecha">

        <!-- Notificaciones -->
        <div class="navbar-action-wrapper">
            <button
                class="btn-navbar btn-notif"
                id="btnNotificaciones"
                onclick="toggleDropdown('notificaciones')"
                aria-label="Notificaciones"
                aria-expanded="false">
                <i class="bi bi-bell-fill"></i>
                <span class="badge-notif" id="badgeNotif">3</span>
            </button>

            <!-- Dropdown Notificaciones -->
            <div class="dropdown-menu dropdown-notificaciones" id="dropdownNotificaciones" role="menu">
                <div class="dropdown-header">
                    <div class="dropdown-header-left">
                        <h6>Notificaciones</h6>
                        <span class="notif-count-badge">3 nuevas</span>
                    </div>
                    <button class="btn-marcar-leidas" onclick="marcarTodasLeidas()">
                        <i class="bi bi-check2-all"></i> Marcar leídas
                    </button>
                </div>

                <div class="dropdown-body" id="listaNotificaciones">
                    <a href="#" class="notificacion-item no-leida">
                        <div class="notif-icono notif-azul">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </div>
                        <div class="notif-contenido">
                            <p class="notif-texto">Nuevo ticket de soporte recibido</p>
                            <span class="notif-tiempo"><i class="bi bi-clock"></i> Hace 10 min</span>
                        </div>
                        <div class="notif-dot"></div>
                    </a>

                    <a href="#" class="notificacion-item no-leida">
                        <div class="notif-icono notif-verde">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="notif-contenido">
                            <p class="notif-texto">Se registró un nuevo usuario en el sistema</p>
                            <span class="notif-tiempo"><i class="bi bi-clock"></i> Hace 1 hora</span>
                        </div>
                        <div class="notif-dot"></div>
                    </a>

                    <a href="#" class="notificacion-item no-leida">
                        <div class="notif-icono notif-naranja">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <div class="notif-contenido">
                            <p class="notif-texto">Se ha generado un nuevo ticket de consulta</p>
                            <span class="notif-tiempo"><i class="bi bi-clock"></i> Hace 3 horas</span>
                        </div>
                        <div class="notif-dot"></div>
                    </a>

                    <a href="#" class="notificacion-item">
                        <div class="notif-icono notif-rojo">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <div class="notif-contenido">
                            <p class="notif-texto">Una veterinaria ha sido bloqueada</p>
                            <span class="notif-tiempo"><i class="bi bi-clock"></i> Hace 5 horas</span>
                        </div>
                    </a>
                </div>

                <div class="dropdown-footer">
                    <a href="#" class="btn-ver-todas">
                        Ver todas las notificaciones
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modo Oscuro -->
        <button class="btn-navbar btn-tema" onclick="toggleTheme()" aria-label="Cambiar tema" title="Cambiar tema">
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <!-- Separador -->
        <div class="navbar-separador" aria-hidden="true"></div>

        <!-- Perfil Usuario -->
        <div class="navbar-action-wrapper">
            <button
                class="btn-perfil"
                id="btnPerfil"
                onclick="toggleDropdown('perfil')"
                aria-label="Menú de perfil"
                aria-expanded="false">

                <div class="avatar-usuario" title="<?= htmlspecialchars($usuario['nombres']) ?>">
                    <img
                        src="<?= $img_src ?>"
                        alt="Foto de <?= htmlspecialchars($usuario['nombres']) ?>"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <span class="avatar-iniciales" style="display:none"><?= $iniciales ?></span>
                </div>

                <div class="info-usuario">
                    <span class="nombre-usuario"><?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></span>
                    <span class="rol-usuario">
                        <span class="rol-dot"></span>
                        <?= htmlspecialchars($usuario['rol']) ?>
                    </span>
                </div>

                <i class="bi bi-chevron-down flecha-perfil" aria-hidden="true"></i>
            </button>

            <!-- Dropdown Perfil -->
            <div class="dropdown-menu dropdown-perfil" id="dropdownPerfil" role="menu">

                <!-- Header del perfil -->
                <div class="perfil-header">
                    <div class="avatar-usuario grande">
                        <img
                            src="<?= $img_src ?>"
                            alt="Avatar"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <span class="avatar-iniciales" style="display:none"><?= $iniciales ?></span>
                    </div>
                    <div class="perfil-info">
                        <p class="nombre-completo"><?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></p>
                        <p class="email-usuario"><?= htmlspecialchars($usuario['email']) ?></p>
                        <span class="sub-badge" style="background:<?= $sub['bg'] ?>; color:<?= $sub['color'] ?>">
                            <i class="bi <?= $sub['icon'] ?>"></i>
                            <?= $suscripcion ?>
                        </span>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <!-- Links del menú -->
                <a href="<?= BASE_URL ?>/representante/perfil-representante" class="dropdown-item">
                    <div class="dropdown-item-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="dropdown-item-content">
                        <span class="item-title">Mi Perfil</span>
                        <span class="item-subtitle">Ver y editar tu perfil</span>
                    </div>
                    <i class="bi bi-chevron-right dropdown-item-arrow"></i>
                </a>

                <a href="<?= BASE_URL ?>/representante/suscripcion" class="dropdown-item">
                    <div class="dropdown-item-icon">
                        <i class="bi bi-credit-card-fill"></i>
                    </div>
                    <div class="dropdown-item-content">
                        <span class="item-title">Mi Suscripción</span>
                        <span class="item-subtitle">Ver y cambiar tu plan</span>
                    </div>
                    <i class="bi bi-chevron-right dropdown-item-arrow"></i>
                </a>

                <a href="#" class="dropdown-item" id="btnAbrirSoporte">
                    <div class="dropdown-item-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="dropdown-item-content">
                        <span class="item-title">Soporte</span>
                        <span class="item-subtitle">Reportar un problema</span>
                    </div>
                    <i class="bi bi-chevron-right dropdown-item-arrow"></i>
                </a>

                <div class="dropdown-divider"></div>

                <a href="<?= BASE_URL ?>/logout" class="dropdown-item item-danger">
                    <div class="dropdown-item-icon danger-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div class="dropdown-item-content">
                        <span class="item-title">Cerrar Sesión</span>
                        <span class="item-subtitle">Salir de tu cuenta</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════
     MODAL DE SOPORTE
════════════════════════════════════════ -->
<div id="modalSoporte" class="modal-soporte" role="dialog" aria-modal="true" aria-labelledby="modalSoporteTitulo">
    <div class="modal-contenido">

        <div class="modal-header">
            <div class="modal-header-icon">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <h2 id="modalSoporteTitulo">Centro de Soporte</h2>
                <p class="modal-header-sub">Estamos aquí para ayudarte</p>
            </div>
            <button class="btn-cerrar" id="btnCerrarModal" aria-label="Cerrar modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="modal-descripcion">
                <i class="bi bi-info-circle text-primary"></i>
                ¿Tienes algún problema o sugerencia? Completa el formulario y te responderemos a la brevedad.
            </p>

            <form id="formularioSoporte" novalidate>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombreSoporte">
                            <i class="bi bi-person"></i> Nombre Completo
                            <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="nombreSoporte"
                            name="nombre"
                            placeholder="Tu nombre completo"
                            value="<?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>"
                            required>
                        <span class="error-message">Este campo es obligatorio</span>
                    </div>

                    <div class="form-group">
                        <label for="emailSoporte">
                            <i class="bi bi-envelope"></i> Correo Electrónico
                            <span class="required">*</span>
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="emailSoporte"
                            name="email"
                            placeholder="ejemplo@correo.com"
                            value="<?= htmlspecialchars($usuario['email']) ?>"
                            required>
                        <span class="error-message">Ingresa un correo válido</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tipoProblema">
                        <i class="bi bi-tag"></i> Tipo de Consulta
                        <span class="required">*</span>
                    </label>
                    <select class="form-control" id="tipoProblema" name="tipo_problema" required>
                        <option value="" disabled selected>Selecciona una categoría</option>
                        <option value="tecnico">🔧 Problema Técnico</option>
                        <option value="cuenta">👤 Problema con la Cuenta</option>
                        <option value="funcionalidad">⚙️ Funcionalidad</option>
                        <option value="sugerencia">💡 Sugerencia de mejora</option>
                        <option value="otro">📋 Otro</option>
                    </select>
                    <span class="error-message">Selecciona una opción</span>
                </div>

                <div class="form-group">
                    <label for="descripcionProblema">
                        <i class="bi bi-chat-left-text"></i> Descripción detallada
                        <span class="required">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="descripcionProblema"
                        name="descripcion"
                        rows="4"
                        placeholder="Describe con detalle tu problema o sugerencia..."
                        required></textarea>
                    <div class="char-counter"><span id="charCount">0</span> / 500 caracteres</div>
                    <span class="error-message">La descripción es obligatoria</span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancelar" id="btnCancelar">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-enviar" id="btnEnviar">
                        <i class="bi bi-send-fill"></i> Enviar Mensaje
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/panelSuperiorAdmin.js"></script>