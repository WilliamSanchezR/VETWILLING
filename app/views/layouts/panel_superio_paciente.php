<?php
// 1. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Validar sesión
if (!isset($_SESSION['user'])) {
    header('Location: /vetwilling/login');
    exit();
}

// 3. Cargar controlador
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

// 4. Obtener y validar datos
$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);

// Función helper para sanitizar output (solo para imprimir en HTML)
function safe_echo($value, $default = '')
{
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

// Preparar datos usando los valores crudos del array $usuario
// CORRECCIÓN: safe_echo solo se aplica aquí, no al imprimir con <?=
$datosUsuario = [
    'nombre_veterinaria' => safe_echo($usuario['nombre_veterinaria'] ?? 'Veterinaria'),
    'img_perfil'         => safe_echo($usuario['img_perfil']         ?? 'default-avatar.png'),
    'nombres'            => safe_echo($usuario['nombres']            ?? ''),
    'apellidos'          => safe_echo($usuario['apellidos']          ?? ''),
    'rol'                => safe_echo($usuario['rol']                ?? 'Cliente'),
    'email'              => safe_echo($usuario['email']              ?? '')
];

// CORRECCIÓN: Usar el valor crudo para verificar rutas del sistema de archivos,
// evitando que htmlspecialchars corrompa el nombre del archivo (ej: & → &amp;)
$fotoRaw = $usuario['img_perfil'] ?? '';

$imagenFallback = "https://ui-avatars.com/api/?name="
    . urlencode(($usuario['nombres'] ?? '') . '+' . ($usuario['apellidos'] ?? ''))
    . "&background=4e9af1&color=fff&size=128";

$rutaImagen = $imagenFallback;

if (!empty($fotoRaw) && $fotoRaw !== 'default-avatar.png') {
    $rutaAbsoluta = BASE_PATH . "/public/uploads/usuarios/" . $fotoRaw;
    if (file_exists($rutaAbsoluta)) {
        // Al imprimir en HTML usamos safe_echo implícitamente ya que $datosUsuario['img_perfil'] fue escapado
        $rutaImagen = BASE_URL . "/public/uploads/usuarios/" . $datosUsuario['img_perfil'];
    }
} else {
    $defaultLocal = BASE_PATH . "/public/uploads/usuarios/default-avatar.png";
    if (file_exists($defaultLocal)) {
        $rutaImagen = BASE_URL . "/public/uploads/usuarios/default-avatar.png";
    }
}

$fallbackOnerror = "https://ui-avatars.com/api/?name="
    . urlencode(($usuario['nombres'] ?? '') . '+' . ($usuario['apellidos'] ?? ''))
    . "&background=4e9af1&color=fff&size=128";
?>

<script>
    window.BASE_URL = "<?= BASE_URL ?>";
</script>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/nav.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

<!-- NAVBAR SUPERIOR -->
<nav class="navbar-superior" role="navigation" aria-label="Navegación principal">

    <!-- BURBUJA MÓVIL -->
    <div class="bubble-panel" id="bubblePanel">
        <div class="bubble-panel-logo">
            <i class="bi bi-hospital"></i>
            <span><?= $datosUsuario['nombre_veterinaria'] ?></span>
        </div>

        <a href="<?= BASE_URL ?>/cliente/perfil" class="bubble-item">
            <span class="bubble-icon"><i class="bi bi-person-fill"></i></span> Mi Perfil
        </a>
        <a href="<?= BASE_URL ?>/cliente/mascotas" class="bubble-item">
            <span class="bubble-icon"><i class="bi bi-heart-pulse-fill"></i></span> Mis Mascotas
        </a>
        <a href="<?= BASE_URL ?>/cliente/citas" class="bubble-item">
            <span class="bubble-icon"><i class="bi bi-calendar-check-fill"></i></span> Mis Citas
        </a>

        <div class="bubble-divider"></div>

        <a href="<?= BASE_URL ?>/cliente/configuracion" class="bubble-item">
            <span class="bubble-icon"><i class="bi bi-gear-fill"></i></span> Configuración
        </a>
        <!-- CORRECCIÓN: type="button" para evitar submit accidental dentro de formularios -->
        <button type="button" class="bubble-item" data-modal="soporte">
            <span class="bubble-icon"><i class="bi bi-question-circle"></i></span> Soporte
        </button>

        <div class="bubble-spacer"></div>
        <div class="bubble-divider"></div>

        <a href="<?= BASE_URL ?>/cerrar-sesion" class="bubble-item bubble-danger">
            <span class="bubble-icon"><i class="bi bi-box-arrow-right"></i></span> Cerrar Sesión
        </a>
    </div>


    <!-- Info Veterinaria -->
    <div class="info-veterinaria">
        <i class="bi bi-hospital" aria-hidden="true"></i>
        <span class="nombre-veterinaria"><?= $datosUsuario['nombre_veterinaria'] ?></span>
    </div>

    <!-- Buscador -->
    <div class="navbar-centro">
        <div class="buscador-avanzado" role="search">
            <label for="inputBusqueda" class="sr-only">Buscar</label>
            <i class="bi bi-search icono-buscar" aria-hidden="true"></i>
            <input
                type="search"
                placeholder="Buscar mascotas, citas, servicios..."
                class="input-buscar"
                id="inputBusqueda"
                autocomplete="off"
                aria-label="Buscar mascotas, citas o servicios">
        </div>
    </div>

    <!-- Acciones -->
    <div class="navbar-derecha">

        <!-- Reloj en Vivo -->
        <!-- CORRECCIÓN: type="button" explícito -->
        <button
            type="button"
            class="btn-navbar btn-reloj"
            data-modal="reloj"
            aria-label="Ver reloj y fecha"
            title="Hora actual">
            <span class="hora-actual" id="horaNavbar">--:--</span>
        </button>

        <!-- Notificaciones -->
        <!-- CORRECCIÓN: type="button" explícito -->
        <button
            type="button"
            class="btn-navbar notificaciones"
            data-dropdown="notificaciones"
            aria-label="Notificaciones"
            aria-haspopup="true"
            aria-expanded="false"
            id="btnNotificaciones">
            <i class="bi bi-bell-fill" aria-hidden="true"></i>
            <span class="badge-notif" id="badgeNotificaciones" aria-label="3 notificaciones sin leer">3</span>
        </button>

        <!-- Dropdown Notificaciones -->
        <div
            class="dropdown-menu dropdown-notificaciones"
            id="dropdownNotificaciones"
            role="menu"
            aria-labelledby="btnNotificaciones">
            <div class="dropdown-header">
                <h6>Notificaciones</h6>
                <!-- CORRECCIÓN: type="button" explícito -->
                <button type="button" class="btn-marcar-leidas" data-action="marcar-leidas">
                    Marcar todas como leídas
                </button>
            </div>
            <div class="dropdown-body" id="listaNotificaciones">
                <div class="loading-notificaciones">
                    <div class="spinner"></div>
                    <p>Cargando notificaciones...</p>
                </div>
            </div>
            <div class="dropdown-footer">
                <a href="<?= BASE_URL ?>/cliente/notificaciones" class="btn-ver-todas">
                    Ver todas las notificaciones
                </a>
            </div>
        </div>

        <!-- Separador (oculto en móvil via CSS) -->
        <div class="navbar-separador" role="separator"></div>

        <!-- Perfil Usuario -->
        <!-- CORRECCIÓN: type="button" explícito + id para aria-labelledby -->
        <button
            type="button"
            class="btn-perfil"
            id="btnPerfil"
            data-dropdown="perfil"
            aria-label="Menú de perfil"
            aria-haspopup="true"
            aria-expanded="false">

            <div class="avatar-usuario">
                <img
                    src="<?= $rutaImagen ?>"
                    alt="Foto de perfil de <?= $datosUsuario['nombres'] ?>"
                    onerror="this.onerror=null; this.src='<?= $fallbackOnerror ?>'">
            </div>

            <div class="info-usuario">
                <span class="nombre-usuario">
                    <?= $datosUsuario['nombres'] ?> <?= $datosUsuario['apellidos'] ?>
                </span>
                <span class="rol-usuario"><?= $datosUsuario['rol'] ?></span>
            </div>

            <i class="bi bi-chevron-down flecha-perfil" aria-hidden="true"></i>
        </button>

        <!-- Dropdown Perfil -->
        <div
            class="dropdown-menu dropdown-perfil"
            id="dropdownPerfil"
            role="menu"
            aria-labelledby="btnPerfil">
            <div class="perfil-header">
                <div class="avatar-usuario grande">
                    <img
                        src="<?= $rutaImagen ?>"
                        alt="Avatar"
                        onerror="this.onerror=null; this.src='<?= $fallbackOnerror ?>'">
                </div>
                <div>
                    <p class="nombre-completo">
                        <?= $datosUsuario['nombres'] ?> <?= $datosUsuario['apellidos'] ?>
                    </p>
                    <p class="email-usuario"><?= $datosUsuario['email'] ?></p>
                </div>
            </div>

            <div class="dropdown-divider" role="separator"></div>

            <a href="<?= BASE_URL ?>/cliente/perfil" class="dropdown-item" role="menuitem">
                <i class="bi bi-person-fill" aria-hidden="true"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/mascotas" class="dropdown-item" role="menuitem">
                <i class="bi bi-heart-pulse-fill" aria-hidden="true"></i>
                <span>Mis Mascotas</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/citas" class="dropdown-item" role="menuitem">
                <i class="bi bi-calendar-check-fill" aria-hidden="true"></i>
                <span>Mis Citas</span>
            </a>

            <div class="dropdown-divider" role="separator"></div>

            <a href="<?= BASE_URL ?>/cliente/configuracion" class="dropdown-item" role="menuitem">
                <i class="bi bi-gear-fill" aria-hidden="true"></i>
                <span>Configuración</span>
            </a>
            <!-- CORRECCIÓN: type="button" explícito -->
            <button type="button" class="dropdown-item" data-modal="soporte" role="menuitem">
                <i class="bi bi-question-circle" aria-hidden="true"></i>
                <span>Soporte</span>
            </button>

            <div class="dropdown-divider" role="separator"></div>

            <a href="<?= BASE_URL ?>/cerrar-sesion" class="dropdown-item text-danger" role="menuitem">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>

    </div>
</nav>

<!-- Overlay para cerrar sidebar en móvil -->
<!-- CORRECCIÓN: id corregido de "bubbleOverlay" (inexistente) a "sidebarOverlay" -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal de Reloj -->
<div id="modalReloj" class="modal-reloj" role="dialog" aria-modal="true" aria-labelledby="tituloReloj">
    <div class="modal-reloj-contenido">
        <!-- CORRECCIÓN: type="button" explícito -->
        <button type="button" class="btn-cerrar-reloj" data-modal-close="reloj" aria-label="Cerrar reloj">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="reloj-display">
            <div class="reloj-digital" id="relojDigital">
                00:00:00
            </div>
            <div class="reloj-fecha" id="relojFecha">
                Cargando fecha...
            </div>

            <div class="reloj-info">
                <div class="info-item">
                    <i class="bi bi-calendar-event"></i>
                    <div class="info-label">Día</div>
                    <div class="info-value" id="diaSemana">---</div>
                </div>
                <div class="info-item">
                    <i class="bi bi-sunrise"></i>
                    <div class="info-label">Período</div>
                    <div class="info-value" id="periodo">---</div>
                </div>
                <div class="info-item">
                    <i class="bi bi-globe"></i>
                    <div class="info-label">Zona</div>
                    <div class="info-value" id="zonaHoraria">UTC</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Soporte -->
<div id="modalSoporte" class="modal-soporte" role="dialog" aria-modal="true" aria-labelledby="tituloSoporte" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="bi bi-headset" aria-hidden="true"></i>
            </div>
            <h2 id="tituloSoporte">Centro de Soporte</h2>
            <!-- CORRECCIÓN: type="button" explícito -->
            <button type="button" class="btn-cerrar" data-modal-close="soporte" aria-label="Cerrar modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="modal-descripcion">
                ¿Tienes algún problema o sugerencia? Completa el formulario y te responderemos pronto.
            </p>

            <!-- CORRECCIÓN: Se elimina <form> y se usa <div> para evitar submit accidental.
                 El envío se maneja 100% por JS desde nav.js mediante el botón #btnEnviarSoporte -->
            <div id="formularioSoporte">
                <div class="form-group">
                    <label for="nombreSoporte">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        Nombre Completo <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="nombreSoporte"
                        name="nombre"
                        placeholder="Tu nombre"
                        value="<?= $datosUsuario['nombres'] ?> <?= $datosUsuario['apellidos'] ?>"
                        aria-required="true">
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-group">
                    <label for="emailSoporte">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        Correo Electrónico <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        id="emailSoporte"
                        name="email"
                        placeholder="ejemplo@correo.com"
                        value="<?= $datosUsuario['email'] ?>"
                        aria-required="true">
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-group">
                    <label for="tipoProblema">
                        <i class="bi bi-tag" aria-hidden="true"></i>
                        Tipo de Consulta <span class="required">*</span>
                    </label>
                    <select class="form-control" id="tipoProblema" name="tipo_problema" aria-required="true">
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="tecnico">Problema Técnico</option>
                        <option value="cuenta">Problema con la Cuenta</option>
                        <option value="funcionalidad">Funcionalidad</option>
                        <option value="sugerencia">Sugerencia</option>
                        <option value="otro">Otro</option>
                    </select>
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-group">
                    <label for="descripcionProblema">
                        <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                        Descripción <span class="required">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="descripcionProblema"
                        name="descripcion"
                        rows="5"
                        placeholder="Describe tu problema o sugerencia detalladamente..."
                        aria-required="true"
                        minlength="10"></textarea>
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-actions">
                    <!-- CORRECCIÓN: type="button" explícito en ambos -->
                    <button type="button" class="btn-cancelar" data-modal-close="soporte">
                        Cancelar
                    </button>
                    <button type="button" id="btnEnviarSoporte" class="btn-enviar">
                        <i class="bi bi-send-fill" aria-hidden="true"></i>
                        <span>Enviar Mensaje</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                </div>
            </div>

            <div class="mensaje-exito" style="display: none;" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <p>¡Mensaje enviado con éxito! Te responderemos pronto.</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/nav.js" defer></script>

<!-- Datos del usuario para JavaScript -->
<!-- CORRECCIÓN: Solo se expone el id y el nombre; el email se elimina del objeto global
     para no exponerlo a scripts de terceros. Si nav.js lo necesita, pasarlo por otro medio. -->
<script>
    window.usuarioData = {
        id: <?= (int)$id ?>,
        nombre: <?= json_encode($datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos']) ?>
    };
</script>

<!-- CORRECCIÓN: Todo el código que manipula el DOM se envuelve en DOMContentLoaded
     para garantizar que los elementos existen antes de referenciarlos.
     Además se corrige bubbleOverlay: el id real en el HTML es "sidebarOverlay". -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const bubbleBtn     = document.getElementById('bubbleBtn');
        const bubblePanel   = document.getElementById('bubblePanel');
        const bubbleOverlay = document.getElementById('sidebarOverlay'); // CORRECCIÓN: id corregido
        const bubbleIcon    = document.getElementById('bubbleIcon');

        function toggleBubble(forceClose = false) {
            const isOpen = forceClose ? false : !bubblePanel.classList.contains('open');
            bubblePanel.classList.toggle('open', isOpen);
            bubbleBtn.classList.toggle('open', isOpen);
            bubbleOverlay.classList.toggle('open', isOpen);
            bubbleIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';

            // CORRECCIÓN: Actualizar aria-expanded dinámicamente
            bubbleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        bubbleBtn?.addEventListener('click', () => toggleBubble());
        bubbleOverlay?.addEventListener('click', () => toggleBubble(true));

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') toggleBubble(true);
        });

        // CORRECCIÓN: Actualizar aria-expanded en los dropdowns de la navbar
        document.querySelectorAll('[data-dropdown]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const expanded = this.getAttribute('aria-expanded') === 'true';
                // Cerrar todos primero
                document.querySelectorAll('[data-dropdown]').forEach(function (b) {
                    b.setAttribute('aria-expanded', 'false');
                });
                // Abrir/cerrar el clickeado
                this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            });
        });

    });
</script>