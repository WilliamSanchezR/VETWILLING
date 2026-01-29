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

// Validar que se obtuvieron los datos
if (!$usuario) {
    // Manejar error - destruir sesión y redirigir
    session_destroy();
    header('Location: /vetwilling/login');
    exit();
}

// Función helper para sanitizar output
function safe_echo($value, $default = '')
{
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

// Preparar datos con valores por defecto
$datosUsuario = [
    'nombre_veterinaria' => safe_echo($usuario['nombre_veterinaria'] ?? 'Veterinaria'),
    'img_perfil' => safe_echo($usuario['img_perfil'] ?? 'default-avatar.png'),
    'nombres' => safe_echo($usuario['nombres'] ?? ''),
    'apellidos' => safe_echo($usuario['apellidos'] ?? ''),
    'rol' => safe_echo($usuario['rol'] ?? 'Cliente'),
    'email' => safe_echo($usuario['email'] ?? '')
];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/nav.css">

<!-- NAVBAR SUPERIOR -->
<nav class="navbar-superior" role="navigation" aria-label="Navegación principal">
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
        <button
            class="btn-navbar btn-reloj"
            data-modal="reloj"
            aria-label="Ver reloj y fecha"
            title="Hora actual">
            <span class="hora-actual" id="horaNavbar">--:--</span>
        </button>

        <!-- Notificaciones -->
        <button
            class="btn-navbar notificaciones"
            data-dropdown="notificaciones"
            aria-label="Notificaciones"
            aria-haspopup="true"
            aria-expanded="false">
            <i class="bi bi-bell-fill" aria-hidden="true"></i>
            <span class="badge-notif" id="badgeNotificaciones" aria-label="3 notificaciones sin leer">3</span>
        </button>

        <!-- Dropdown Notificaciones -->
        <div
            class="dropdown-menu dropdown-notificaciones"
            id="dropdownNotificaciones"
            role="menu"
            aria-labelledby="notificaciones">
            <div class="dropdown-header">
                <h6>Notificaciones</h6>
                <button class="btn-marcar-leidas" data-action="marcar-leidas">
                    Marcar todas como leídas
                </button>
            </div>
            <div class="dropdown-body" id="listaNotificaciones">
                <!-- Se cargan dinámicamente -->
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

        <!-- Carrito -->
        <button
            class="btn-navbar tienda"
            aria-label="Carrito de compras"
            data-action="toggle-carrito">
            <i class="bi bi-cart-fill" aria-hidden="true"></i>
            <span id="contadorCarrito" class="badge-notif" style="display: none;">0</span>
        </button>

        <!-- Carrito Sidebar -->
        <aside
            id="carritoSidebar"
            class="carrito-sidebar"
            role="complementary"
            aria-label="Carrito de compras">
            <div class="carrito-header">
                <h3>Mi Carrito</h3>
                <button
                    data-action="toggle-carrito"
                    class="cerrar-btn"
                    aria-label="Cerrar carrito">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div id="carritoItems" class="carrito-items">
                <div class="carrito-vacio">
                    <i class="bi bi-cart-x"></i>
                    <p>Tu carrito está vacío</p>
                </div>
            </div>

            <div class="carrito-footer">
                <p>Total: <span id="totalCarrito">$0</span></p>
                <button class="btn-pagar" disabled>Proceder al pago</button>
            </div>
        </aside>

        <!-- Separador -->
        <div class="navbar-separador" role="separator"></div>

        <!-- Perfil Usuario -->
        <button
            class="btn-perfil"
            data-dropdown="perfil"
            aria-label="Menú de perfil"
            aria-haspopup="true"
            aria-expanded="false">

            <div class="avatar-usuario">
                <img
                    src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $datosUsuario['img_perfil'] ?>"
                    alt="Foto de perfil de <?= $datosUsuario['nombres'] ?>"
                    onerror="this.src='<?= BASE_URL ?>/public/uploads/usuarios/default-avatar.png'">
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
            role="menu">
            <div class="perfil-header">
                <div class="avatar-usuario grande">
                    <img
                        src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $datosUsuario['img_perfil'] ?>"
                        alt="Avatar"
                        onerror="this.src='<?= BASE_URL ?>/public/uploads/usuarios/default-avatar.png'">
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
            <button class="dropdown-item" data-modal="soporte" role="menuitem">
                <i class="bi bi-question-circle" aria-hidden="true"></i>
                <span>Soporte</span>
            </button>

            <div class="dropdown-divider" role="separator"></div>

            <a href="<?= BASE_URL ?>/logout" class="dropdown-item text-danger" role="menuitem">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>

<!-- Modal de Reloj -->
<div id="modalReloj" class="modal-reloj" role="dialog" aria-modal="true" aria-labelledby="tituloReloj">
    <div class="modal-reloj-contenido">
        <button class="btn-cerrar-reloj" data-modal-close="reloj" aria-label="Cerrar reloj">
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
            <button class="btn-cerrar" data-modal-close="soporte" aria-label="Cerrar modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="modal-descripcion">
                ¿Tienes algún problema o sugerencia? Completa el formulario y te responderemos pronto.
            </p>

            <form id="formularioSoporte" novalidate>
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
                        required
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
                        required
                        aria-required="true">
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-group">
                    <label for="tipoProblema">
                        <i class="bi bi-tag" aria-hidden="true"></i>
                        Tipo de Consulta <span class="required">*</span>
                    </label>
                    <select class="form-control" id="tipoProblema" name="tipo_problema" required aria-required="true">
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
                        required
                        aria-required="true"
                        minlength="10"></textarea>
                    <span class="error-message" role="alert"></span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancelar" data-modal-close="soporte">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-enviar">
                        <i class="bi bi-send-fill" aria-hidden="true"></i>
                        <span>Enviar Mensaje</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                </div>
            </form>

            <div class="mensaje-exito" style="display: none;" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <p>¡Mensaje enviado con éxito! Te responderemos pronto.</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/nav.js" defer></script>

<!-- Datos del usuario para JavaScript -->
<script>
    window.usuarioData = {
        id: <?= (int)$id ?>,
        nombre: <?= json_encode($datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos']) ?>,
        email: <?= json_encode($datosUsuario['email']) ?>
    };
</script>