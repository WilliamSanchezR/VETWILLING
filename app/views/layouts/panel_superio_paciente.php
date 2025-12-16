<?php

// 1. Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Validar que el usuario esté logueado ANTES de cargar controlador
if (!isset($_SESSION['user'])) {
    // No hay usuario → redirigir al login
    header('Location: /vetwilling/login');
    exit();
}

// 3. Cargar controlador SOLO si existe sesión activa
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

// 4. Obtener datos del usuario
$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);
?>


<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/nav.css">


<!-- NAVBAR SUPERIOR -->
<nav class="navbar-superior">
    <div class="info-veterinaria">
        <i class="bi bi-hospital"></i>
        <span class="nombre-veterinaria"><?= $usuario['nombre_veterinaria'] ?></span>
    </div>

    <!-- Sección Centro - Buscador -->
    <div class="navbar-centro">
        <div class="buscador-avanzado">
            <i class="bi bi-search icono-buscar"></i>
            <input
                type="text"
                placeholder="Buscar mascotas, citas, servicios..."
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
                        <p class="notif-texto">Recordatorio de vacuna para Max</p>
                        <span class="notif-tiempo">Hace 5 min</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-verde">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Cita confirmada para mañana</p>
                        <span class="notif-tiempo">Hace 1 hora</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-naranja">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Nuevo mensaje del veterinario</p>
                        <span class="notif-tiempo">Hace 3 horas</span>
                    </div>
                </a>
                <a href="#" class="notificacion-item">
                    <div class="notif-icono notif-rojo">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-texto">Cita pendiente de confirmación</p>
                        <span class="notif-tiempo">Hace 5 horas</span>
                    </div>
                </a>
            </div>
            <div class="dropdown-footer">
                <a href="#" class="btn-ver-todas">Ver todas las notificaciones</a>
            </div>
        </div>

        <!-- Botón carrito -->
        <button class="btn-navbar tienda" aria-label="Tienda" onclick="toggleCarrito()">
            <i class="bi bi-cart-fill"></i>
            <span id="contadorCarrito" class="badge-notif">0</span>
        </button>

        <!-- Carrito lateral -->
        <div id="carritoSidebar" class="carrito-sidebar">
            <div class="carrito-header">
                <h3>Mi Carrito</h3>
                <button onclick="toggleCarrito()" class="cerrar-btn">✖</button>
            </div>

            <div id="carritoItems" class="carrito-items">
                <!-- Los productos agregados aparecerán aquí -->
            </div>

            <div class="carrito-footer">
                <p>Total: <span id="totalCarrito">$0</span></p>
                <button class="btn-pagar">Proceder al pago</button>
            </div>
        </div>


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
            <a href="perfil" class="dropdown-item">
                <i class="bi bi-person-fill"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/mascotas" class="dropdown-item">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Mis Mascotas</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/citas" class="dropdown-item">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Mis Citas</span>
            </a>
            <a href="<?= BASE_URL ?>/cliente/configuracion" class="dropdown-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configuración</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/logout" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/nav.js"></script>