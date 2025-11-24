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

        <!-- Mensajes -->
        <button class="btn-mensajes-float" onclick="toggleChat()">
            <i class="bi bi-chat-dots-fill"></i>
            <span class="badge-msg">3</span>
        </button>

        <!-- MODAL CHAT -->
        <div class="modal-chat" id="modalChat">
            <!-- HEADER -->
            <div class="modal-header">
                <h3>Mensajes</h3>
                <button class="btn-cerrar-modal" onclick="toggleChat()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <!-- BÚSQUEDA -->
            <div class="chat-search">
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Buscar conversación...">
                </div>
            </div>

            <!-- CONTENEDOR -->
            <div class="chat-container">

                <!-- LISTA DE CONVERSACIONES -->
                <div class="conversaciones-list" id="conversacionesList">

                    <div class="conversacion no-leida" onclick="abrirChat('Dr. Juan Martínez', 'martinez')">
                        <div style="position: relative;">
                            <img src="https://ui-avatars.com/api/?name=Dr+Martinez&background=667eea&color=fff" class="conv-avatar-chat" alt="Dr. Martínez">
                            <span class="estado-online-chat"></span>
                        </div>
                        <div class="conv-info">
                            <div class="conv-header-chat">
                                <span class="conv-nombre-chat">Dr. Juan Martínez</span>
                                <span class="conv-tiempo-chat">10:30</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <p class="conv-ultimo">Los resultados están listos...</p>
                                <span class="badge-unread">2</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversacion no-leida" onclick="abrirChat('Recepción', 'recepcion')">
                        <div style="position: relative;">
                            <img src="https://ui-avatars.com/api/?name=Recepcion&background=4caf50&color=fff" class="conv-avatar-chat" alt="Recepción">
                            <span class="estado-online-chat"></span>
                        </div>
                        <div class="conv-info">
                            <div class="conv-header-chat">
                                <span class="conv-nombre-chat">Recepción VetWilling</span>
                                <span class="conv-tiempo-chat">Ayer</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <p class="conv-ultimo">Tu cita ha sido confirmada...</p>
                                <span class="badge-unread">1</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversacion" onclick="abrirChat('Dra. Ana García', 'garcia')">
                        <img src="https://ui-avatars.com/api/?name=Dra+Garcia&background=9c27b0&color=fff" class="conv-avatar-chat" alt="Dra. García">
                        <div class="conv-info">
                            <div class="conv-header-chat">
                                <span class="conv-nombre-chat">Dra. Ana García</span>
                                <span class="conv-tiempo-chat">15 Nov</span>
                            </div>
                            <p class="conv-ultimo">Recuerda traer el carnet...</p>
                        </div>
                    </div>

                    <div class="conversacion" onclick="abrirChat('Peluquería', 'peluqueria')">
                        <img src="https://ui-avatars.com/api/?name=Peluqueria&background=ff9800&color=fff" class="conv-avatar-chat" alt="Peluquería">
                        <div class="conv-info">
                            <div class="conv-header-chat">
                                <span class="conv-nombre-chat">Peluquería Canina</span>
                                <span class="conv-tiempo-chat">14 Nov</span>
                            </div>
                            <p class="conv-ultimo">¡Luna quedó hermosa!</p>
                        </div>
                    </div>

                </div>

                <!-- VENTANA DE CHAT -->
                <div class="chat-window" id="chatWindow">
                    <!-- HEADER CONVERSACIÓN -->
                    <div class="chat-header-conversacion">
                        <button class="btn-volver" onclick="volverLista()">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <img src="https://ui-avatars.com/api/?name=Dr+Martinez&background=667eea&color=fff" class="chat-avatar" id="chatAvatar" alt="">
                        <div class="chat-info">
                            <div class="chat-nombre" id="chatNombre">Dr. Juan Martínez</div>
                            <div class="chat-estado">En línea</div>
                        </div>
                    </div>

                    <!-- MENSAJES -->
                    <div class="mensajes-container" id="mensajesContainer">
                        <div class="fecha-separador">
                            <span>Hoy</span>
                        </div>

                        <div class="mensaje recibido">
                            <div>
                                <div class="mensaje-bubble">
                                    Hola Carlos, ¿cómo está Max hoy?
                                </div>
                                <div class="mensaje-hora">10:15</div>
                            </div>
                        </div>

                        <div class="mensaje enviado">
                            <div>
                                <div class="mensaje-bubble">
                                    Hola Doctor, está mucho mejor. Ya no vomita.
                                </div>
                                <div class="mensaje-hora">10:20</div>
                            </div>
                        </div>

                        <div class="mensaje recibido">
                            <div>
                                <div class="mensaje-bubble">
                                    Excelente noticia. Los resultados de los exámenes están listos. ¿Puedes pasar mañana?
                                </div>
                                <div class="mensaje-hora">10:30</div>
                            </div>
                        </div>
                    </div>

                    <!-- INPUT -->
                    <div class="input-mensaje-container">
                        <div class="input-mensaje-wrapper">
                            <button class="btn-adjuntar">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <textarea
                                class="input-mensaje"
                                id="inputMensaje"
                                placeholder="Escribe un mensaje..."
                                rows="1"></textarea>
                            <button class="btn-enviar" onclick="enviarMensaje()">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Separador -->
        <div class="navbar-separador"></div>

        <!-- Perfil Usuario -->
        <button class="btn-perfil" onclick="toggleDropdown('perfil')" aria-label="Perfil">
            <div class="avatar-usuario">
                <span>CR</span>
            </div>
            <div class="info-usuario">
                <span class="nombre-usuario">Carlos Ramírez</span>
                <span class="rol-usuario">Cliente</span>
            </div>
            <i class="bi bi-chevron-down flecha-perfil"></i>
        </button>

        <!-- Dropdown Perfil -->
        <div class="dropdown-menu dropdown-perfil" id="dropdownPerfil">
            <div class="perfil-header">
                <div class="avatar-usuario grande">
                    <span>CR</span>
                </div>
                <div>
                    <p class="nombre-completo">Carlos Ramírez</p>
                    <p class="email-usuario">carlos.ramirez@email.com</p>
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

<!-- este es el de los mensajes -->
<script>
    // Toggle Modal Chat
    function toggleChat() {
        const modal = document.getElementById('modalChat');
        modal.classList.toggle('show');
    }

    // Abrir Chat Específico
    function abrirChat(nombre, id) {
        const chatWindow = document.getElementById('chatWindow');
        const conversacionesList = document.getElementById('conversacionesList');
        const chatNombre = document.getElementById('chatNombre');

        // Cambiar nombre
        chatNombre.textContent = nombre;

        // Mostrar ventana de chat
        chatWindow.classList.add('show');
        conversacionesList.style.display = 'none';

        // Scroll al final de mensajes
        const mensajesContainer = document.getElementById('mensajesContainer');
        mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
    }

    // Volver a lista
    function volverLista() {
        const chatWindow = document.getElementById('chatWindow');
        const conversacionesList = document.getElementById('conversacionesList');

        chatWindow.classList.remove('show');
        conversacionesList.style.display = 'block';
    }

    // Enviar Mensaje
    function enviarMensaje() {
        const input = document.getElementById('inputMensaje');
        const mensaje = input.value.trim();

        if (mensaje === '') return;

        const mensajesContainer = document.getElementById('mensajesContainer');
        const now = new Date();
        const hora = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        // Crear elemento de mensaje
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = 'mensaje enviado';
        mensajeDiv.innerHTML = `
                <div>
                    <div class="mensaje-bubble">${mensaje}</div>
                    <div class="mensaje-hora">${hora}</div>
                </div>
            `;

        mensajesContainer.appendChild(mensajeDiv);

        // Limpiar input
        input.value = '';
        input.style.height = 'auto';

        // Scroll al final
        mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
    }

    // Auto-resize textarea
    document.getElementById('inputMensaje').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Enter para enviar
    document.getElementById('inputMensaje').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
        }
    });

    console.log('✅ Chat cargado correctamente');
</script>