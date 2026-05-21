<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$id          = $_SESSION['user']['id_usuario'];
$usuario     = mostrarPerfil($id);
$veterinaria = consultarVeterinariaPorId($_SESSION['user']['id_veterinaria']);

/* ── Imagen de perfil del usuario ── */
$carpetaUsuario    = ($usuario['id_rol'] == 4) ? 'usuarios' : 'profesionales';
$fotoUsuario       = $usuario['img_perfil'] ?? '';
$nombreCompleto    = trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));
$fallbackAvatar    = "https://ui-avatars.com/api/?name=" . urlencode($nombreCompleto) . "&background=0a932c&color=fff&size=128";
$rutaImagenUsuario = $fallbackAvatar;

if (!empty($fotoUsuario) && $fotoUsuario !== 'default-avatar.png') {
    $rutaAbs = BASE_PATH . "/public/uploads/{$carpetaUsuario}/{$fotoUsuario}";
    if (file_exists($rutaAbs)) {
        $rutaImagenUsuario = BASE_URL . "/public/uploads/{$carpetaUsuario}/{$fotoUsuario}";
    }
} else {
    $defLocal = BASE_PATH . "/public/uploads/{$carpetaUsuario}/default-avatar.png";
    if (file_exists($defLocal)) {
        $rutaImagenUsuario = BASE_URL . "/public/uploads/{$carpetaUsuario}/default-avatar.png";
    }
}

/* ── Iniciales y color para el avatar de la veterinaria ──
   Se usa cuando no hay foto de la clínica.
   Se genera un color de acento basado en el nombre para que
   cada clínica tenga su propio color identificador.           */
$nombreVet   = $veterinaria['nombre'] ?? 'VW';
$palabrasVet = preg_split('/\s+/', trim($nombreVet));
$inicialesVet = mb_strtoupper(
    count($palabrasVet) >= 2
        ? mb_substr($palabrasVet[0], 0, 1) . mb_substr($palabrasVet[1], 0, 1)
        : mb_substr($nombreVet, 0, 2)
);

/* Color de acento basado en hash del nombre (siempre el mismo para la misma clínica) */
$coloresVet = ['#0a932c','#0891b2','#7c3aed','#b45309','#be185d','#0e7490'];
$colorVet   = $coloresVet[crc32($nombreVet) % count($coloresVet)];
if ($colorVet < 0) $colorVet = $coloresVet[abs(crc32($nombreVet)) % count($coloresVet)];
$colorVet = $coloresVet[abs(crc32($nombreVet)) % count($coloresVet)];

$hayFotoVet  = !empty($veterinaria['foto']);
$fotoVetUrl  = $hayFotoVet
    ? BASE_URL . "/public/uploads/veterinaria/" . htmlspecialchars($veterinaria['foto'])
    : '';
?>

<script>window.BASE_URL = "<?= BASE_URL ?>";</script>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

<nav class="navbar-superior" id="navbarSuperior">
    <div class="navbar-container">

        <!-- ── IZQUIERDA ───────────────────────────────────── -->
        <div class="navbar-left">

            <!-- Logo / iniciales de la veterinaria -->
            <?php if ($hayFotoVet): ?>
                <div class="vet-logo-wrap" title="<?= htmlspecialchars($nombreVet) ?>">
                    <img src="<?= $fotoVetUrl ?>"
                         alt="<?= htmlspecialchars($nombreVet) ?>"
                         class="vet-logo"
                         onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'vet-iniciales\' style=\'background:<?= $colorVet ?>\'><?= $inicialesVet ?></span>'">
                </div>
            <?php else: ?>
                <!-- CORRECCIÓN: iniciales cuando no hay foto de la veterinaria -->
                <div class="vet-logo-wrap vet-logo-iniciales"
                     title="<?= htmlspecialchars($nombreVet) ?>"
                     style="background: <?= $colorVet ?>">
                    <span class="vet-iniciales"><?= $inicialesVet ?></span>
                </div>
            <?php endif; ?>

            <!-- Botón menú móvil (FAB alternativo en el navbar) -->
            <button class="btn-menu-mobile"
                    id="btnMenuMobile"
                    aria-label="Abrir menú"
                    aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>

            <!-- Saludo + reloj -->
            <!-- CORRECCIÓN: iconos Bootstrap en lugar de emojis -->
            <div class="greeting-section">
                <div class="greeting-icon-wrap" id="saludoIconWrap">
                    <i class="bi bi-sun-fill" id="saludoIcon"></i>
                </div>
                <div class="greeting-text">
                    <span class="greeting-label" id="saludoTexto">Bienvenido</span>
                    <span class="greeting-time">
                        <i class="bi bi-clock"></i>
                        <span id="horaActual">00:00:00</span>
                    </span>
                </div>
            </div>

            <div class="nav-sep"></div>

        </div>

        <!-- ── CENTRO — Buscador ────────────────────────────── -->
        <div class="navbar-center">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text"
                           id="searchInput"
                           class="search-input"
                           placeholder="Buscar pacientes, citas, historiales…"
                           autocomplete="off"
                           aria-label="Buscar">
                    <button class="btn-clear is-hidden"
                            id="btnClearSearch"
                            aria-label="Limpiar búsqueda">
                        <i class="bi bi-x"></i>
                    </button>
                    <kbd class="search-shortcut" aria-hidden="true">Ctrl K</kbd>
                </div>

                <div class="search-results-panel is-hidden" id="searchResults" role="listbox">
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

        <!-- ── DERECHA — Acciones ───────────────────────────── -->
        <div class="navbar-right">

            <!-- Tema oscuro/claro -->
            <button class="btn-icon"
                    id="btnToggleTheme"
                    aria-label="Cambiar tema">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </button>

            <!-- Notificaciones -->
            <div class="navbar-action notifications-wrapper">
                <button class="btn-icon"
                        id="btnNotificaciones"
                        aria-label="Notificaciones"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">3</span>
                </button>

                <div class="dropdown-panel notifications-panel is-hidden"
                     id="notificationsPanel"
                     role="menu">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div>
                                <i class="bi bi-bell-fill"></i>
                                <h3>Notificaciones</h3>
                            </div>
                            <button class="btn-text-sm" id="btnMarcarLeidas">
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
                                <button class="btn-icon-sm btn-eliminar-notif" aria-label="Eliminar">
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
                                <button class="btn-icon-sm btn-eliminar-notif" aria-label="Eliminar">
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
                                <button class="btn-icon-sm btn-eliminar-notif" aria-label="Eliminar">
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
                <button class="btn-profile"
                        id="btnPerfil"
                        aria-label="Menú de usuario"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <div class="profile-avatar">
                        <img src="<?= $rutaImagenUsuario ?>"
                             alt="<?= htmlspecialchars($nombreCompleto) ?>"
                             class="avatar-img"
                             onerror="this.onerror=null;this.src='<?= $fallbackAvatar ?>'">
                        <span class="status-dot online"></span>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars($usuario['nombres']) ?>
                            <?= htmlspecialchars($usuario['apellidos']) ?>
                        </span>
                    </div>
                    <i class="bi bi-chevron-down profile-arrow"></i>
                </button>

                <div class="dropdown-panel profile-panel is-hidden"
                     id="perfilDropdown"
                     role="menu">

                    <div class="profile-header">
                        <div class="profile-avatar-large">
                            <img src="<?= $rutaImagenUsuario ?>"
                                 alt="<?= htmlspecialchars($nombreCompleto) ?>"
                                 onerror="this.onerror=null;this.src='<?= $fallbackAvatar ?>'">
                            <span class="status-dot online"></span>
                        </div>
                        <div class="profile-details">
                            <h3><?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></h3>
                            <p><?= htmlspecialchars($usuario['email']) ?></p>
                            <div class="profile-badges-row">
                                <span class="profile-badge"><?= htmlspecialchars($usuario['rol'] ?? 'Veterinario') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="panel-divider"></div>

                    <div class="panel-body">
                        <a href="<?= BASE_URL ?>/veterinario/consultar-perfil" class="dropdown-item">
                            <div class="item-icon"><i class="bi bi-person-circle"></i></div>
                            <div class="item-content">
                                <span class="item-title">Mi Perfil</span>
                                <span class="item-subtitle">Ver y editar información personal</span>
                            </div>
                        </a>
                        <button class="dropdown-item" id="btnAbrirSoporte" type="button">
                            <div class="item-icon"><i class="bi bi-headset"></i></div>
                            <div class="item-content">
                                <span class="item-title">Centro de Soporte</span>
                                <span class="item-subtitle">Ayuda y asistencia técnica</span>
                            </div>
                        </button>
                    </div>

                    <div class="panel-divider"></div>

                    <div class="panel-footer">
                        <a href="<?= BASE_URL ?>/cerrar-sesion" class="dropdown-item logout-item">
                            <div class="item-icon"><i class="bi bi-box-arrow-right"></i></div>
                            <span class="item-title">Cerrar Sesión</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</nav>

<!-- ── MODAL DE SOPORTE ── -->
<div class="modal-overlay" id="modalSoporte" role="dialog" aria-modal="true" aria-label="Centro de soporte">
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

                <form id="formularioSoporte" class="soporte-form" novalidate>
                    <input type="hidden" name="id_usuario" value="<?= (int)($usuario['id_usuario'] ?? 0) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombreSoporte" class="form-label">
                                <i class="bi bi-person"></i> Nombre Completo
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="nombreSoporte"
                                   value="<?= htmlspecialchars($nombreCompleto) ?>"
                                   disabled readonly>
                        </div>
                        <div class="form-group">
                            <label for="emailSoporte" class="form-label">
                                <i class="bi bi-envelope"></i> Correo Electrónico
                                <span class="required">*</span>
                            </label>
                            <input type="email" class="form-input" id="emailSoporte"
                                   value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                                   disabled readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="asunto" class="form-label">
                            Asunto <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="asunto"
                               name="asunto" placeholder="Asunto del problema" required>
                    </div>

                    <div class="form-group">
                        <label for="tipoProblema" class="form-label">
                            <i class="bi bi-tags"></i> Categoría
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
                            <i class="bi bi-chat-left-text"></i> Descripción Detallada
                            <span class="required">*</span>
                        </label>
                        <textarea class="form-textarea" id="descripcionProblema"
                                  name="descripcion" rows="5" maxlength="500"
                                  placeholder="Describe tu problema con el mayor detalle posible…"
                                  required></textarea>
                        <span class="form-hint">Mínimo 20 caracteres · máximo 500</span>
                    </div>

                    <div class="form-group">
                        <label for="archivoProblema" class="form-label">
                            <i class="bi bi-file-earmark-image"></i> Adjuntar imagen (opcional)
                        </label>
                        <input type="file" class="form-input" id="archivoProblema"
                               name="archivo"
                               accept="image/png,image/jpeg,image/jpg,image/gif,application/pdf">
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelar">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnEnviarSoporte">
                            <i class="bi bi-send-fill"></i> Enviar Solicitud
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/navbar-superior.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>