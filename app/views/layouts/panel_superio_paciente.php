<?php
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

// 1. Validar si hay sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Si sí hay sesión → obtener perfil
$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/nav.css">


<!-- NAVBAR SUPERIOR -->
<nav class="navbar-superior">
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
            <a href="perfil" class="dropdown-item">
                <i class="bi bi-person-fill"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="<?= BASE_URL ?>/Cliente/mascotas" class="dropdown-item">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Mis Mascotas</span>
            </a>
            <a href="<?= BASE_URL ?>/Cliente/citas" class="dropdown-item">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Mis Citas</span>
            </a>
            <a href="<?= BASE_URL ?>/Cliente/configuracion" class="dropdown-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configuración</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/" class="dropdown-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>

<!-- este es de lo que es la barra de navegacion -->
<script>
    // Toggle Dropdowns
    function toggleDropdown(tipo) {
        const dropdowns = {
            'notificaciones': document.getElementById('dropdownNotificaciones'),
            'perfil': document.getElementById('dropdownPerfil')
        };

        // Cerrar todos los dropdowns
        Object.values(dropdowns).forEach(d => d.classList.remove('show'));

        // Abrir el dropdown seleccionado
        if (dropdowns[tipo]) {
            dropdowns[tipo].classList.add('show');
        }

        // Toggle flecha del perfil
        if (tipo === 'perfil') {
            document.querySelector('.btn-perfil').classList.toggle('active');
        }
    }

    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar-derecha')) {
            document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
            document.querySelector('.btn-perfil').classList.remove('active');
        }
    });

    // Toggle Mobile Sidebar
    function toggleMobileSidebar() {
        // Aquí conectas con tu sidebar
        console.log('Toggle sidebar móvil');
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    }

    // Toggle Theme
    function toggleTheme() {
        const body = document.body;
        const themeIcon = document.getElementById('themeIcon');

        body.classList.toggle('dark-mode');

        if (body.classList.contains('dark-mode')) {
            themeIcon.classList.remove('bi-moon-stars-fill');
            themeIcon.classList.add('bi-sun-fill');
            localStorage.setItem('theme', 'dark');
        } else {
            themeIcon.classList.remove('bi-sun-fill');
            themeIcon.classList.add('bi-moon-stars-fill');
            localStorage.setItem('theme', 'light');
        }
    }

    // Restaurar tema guardado
    window.addEventListener('load', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            document.getElementById('themeIcon').classList.remove('bi-moon-stars-fill');
            document.getElementById('themeIcon').classList.add('bi-sun-fill');
        }
    });

    // Búsqueda
    document.getElementById('inputBusqueda').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        console.log('Buscando:', searchTerm);
        // Aquí implementas la lógica de búsqueda
    });

    // Marcar notificaciones como leídas
    document.querySelector('.btn-marcar-leidas').addEventListener('click', function() {
        document.querySelectorAll('.notificacion-item.no-leida').forEach(item => {
            item.classList.remove('no-leida');
        });
        // Actualizar badge
        const badge = document.querySelector('.badge-notif');
        badge.textContent = '0';
        badge.style.display = 'none';
    });

    console.log('✅ Navbar Superior cargado correctamente');
</script>
