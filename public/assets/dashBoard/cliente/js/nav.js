
class NavbarManager {

    constructor() {
        // Selector rápido
        this.$ = (s) => document.querySelector(s);
        this.$$ = (s) => document.querySelectorAll(s);

        // DOM cacheado
        this.navbar = this.$('.navbar-superior');

        this.dropdowns = {
            notificaciones: this.$('#dropdownNotificaciones'),
            perfil: this.$('#dropdownPerfil')
        };

        this.buttons = {
            notificaciones: this.$('.btn-navbar.notificaciones'),
            perfil: this.$('.btn-perfil'),
            marcarLeidas: this.$('.btn-marcar-leidas'),
        };

        this.badges = {
            notificaciones: this.$('.badge-notif')
        };

        this.search = this.$('#inputBusqueda');
        this.themeIcon = this.$('#themeIcon');

        // Estado
        this.state = {
            activeDropdown: null,
            notificacionesSinLeer: 0,
            searchDebounce: null,
            theme: localStorage.getItem('theme') || 'light'
        };

        this.init();
    }

    /* ================= INIT ================= */

    init() {
        this.initTheme();
        this.initDropdowns();
        this.initNotifications();
        this.initSearch();
        this.initScrollEffects();

        console.log("Navbar Manager listo ✔");
    }

    /* ================= DROPDOWNS ================= */

    initDropdowns() {
        window.toggleDropdown = (tipo) => this.toggleDropdown(tipo);

        document.addEventListener("click", (e) => {
            if (!e.target.closest(".navbar-derecha")) {
                this.closeAllDropdowns();
            }
        });

        this.$$('.dropdown').forEach(d => {
            d.addEventListener('click', (e) => e.stopPropagation());
        });
    }

    toggleDropdown(tipo) {
        const dd = this.dropdowns[tipo];
        if (!dd) return;

        const open = dd.classList.contains("show");
        this.closeAllDropdowns();

        if (!open) {
            dd.classList.add("show");
            this.state.activeDropdown = tipo;
        }
    }

    closeAllDropdowns() {
        Object.values(this.dropdowns).forEach(dd => dd?.classList.remove("show"));
        this.state.activeDropdown = null;
    }

    /* ================= THEME ================= */

    initTheme() {
        document.documentElement.setAttribute("data-theme", this.state.theme);
        this.updateThemeIcon();

        window.toggleTheme = () => {
            this.state.theme = (this.state.theme === "dark") ? "light" : "dark";

            document.documentElement.setAttribute("data-theme", this.state.theme);
            localStorage.setItem("theme", this.state.theme);
            this.updateThemeIcon();
        };
    }

    updateThemeIcon() {
        if (!this.themeIcon) return;

        this.themeIcon.className =
            (this.state.theme === "dark") ?
                "bi bi-sun-fill" :
                "bi bi-moon-stars-fill";
    }

    /* ================= NOTIFICACIONES ================= */

    initNotifications() {
        if (this.buttons.marcarLeidas) {
            this.buttons.marcarLeidas.onclick = () => this.marcarTodasComoLeidas();
        }

        this.$$('.notificacion-item.no-leida').forEach(item => {
            item.addEventListener("click", () => this.marcarComoLeida(item));
        });

        this.contarNotificaciones();
    }

    contarNotificaciones() {
        this.state.notificacionesSinLeer =
            this.$$('.notificacion-item.no-leida').length;

        this.updateNotifBadge();
    }

    updateNotifBadge() {
        const b = this.badges.notificaciones;
        if (!b) return;

        b.style.display = (this.state.notificacionesSinLeer > 0) ? "flex" : "none";
        b.textContent = this.state.notificacionesSinLeer;
    }

    marcarComoLeida(item) {
        item.classList.remove("no-leida");
        this.state.notificacionesSinLeer--;
        this.updateNotifBadge();
    }

    marcarTodasComoLeidas() {
        this.$$('.notificacion-item.no-leida').forEach(item =>
            item.classList.remove("no-leida")
        );
        this.state.notificacionesSinLeer = 0;
        this.updateNotifBadge();
    }

    /* ================= BUSCADOR ================= */

    initSearch() {
        if (!this.search) return;

        this.search.addEventListener("input", (e) => {
            clearTimeout(this.state.searchDebounce);

            this.state.searchDebounce = setTimeout(() => {
                const term = e.target.value.trim().toLowerCase();
                this.performSearch(term);
            }, 300);
        });
    }

    performSearch(term) {
        const items = this.$$('[data-searchable]');
        items.forEach(el => {
            const match = el.textContent.toLowerCase().includes(term);
            el.style.display = match ? "" : "none";
        });
    }

    /* ================= SCROLL ================= */

    initScrollEffects() {
        window.addEventListener("scroll", () => {
            if (window.pageYOffset > 50) {
                this.navbar?.classList.add("scrolled");
            } else {
                this.navbar?.classList.remove("scrolled");
            }
        });
    }
}

/* =============== INICIALIZACIÓN =============== */

document.addEventListener("DOMContentLoaded", () => {
    window.navbarManager = new NavbarManager();
});
let carrito = [];

function toggleCarrito() {
    document.getElementById("carritoSidebar").classList.toggle("open");
}

function agregarAlCarrito(nombre, precio) {
    carrito.push({
        nombre,
        precio
    });
    actualizarCarrito();
}

function eliminarItem(index) {
    carrito.splice(index, 1);
    actualizarCarrito();
}

function actualizarCarrito() {
    const contenedor = document.getElementById("carritoItems");
    const totalCarrito = document.getElementById("totalCarrito");
    const contador = document.getElementById("contadorCarrito");

    contenedor.innerHTML = "";
    let total = 0;

    carrito.forEach((item, i) => {
        total += item.precio;

        contenedor.innerHTML += `
            <div class="carrito-item">
                <h4>${item.nombre}</h4>
                <div>
                    <span>$${item.precio}</span>
                    <button class="btn-eliminar" onclick="eliminarItem(${i})">X</button>
                </div>
            </div>
        `;
    });

    totalCarrito.textContent = `$${total}`;
    contador.textContent = carrito.length;
}

// este script del soporte fue añadido en la edición anterior
// JavaScript para el Modal de Soporte - MEJORADO

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const btnAbrirSoporte = document.getElementById('btnAbrirSoporte');
    const modalSoporte = document.getElementById('modalSoporte');
    const btnCerrarModal = document.getElementById('btnCerrarModal');
    const btnCancelar = document.getElementById('btnCancelar');
    const formularioSoporte = document.getElementById('formularioSoporte');

    // Verificar que existan todos los elementos
    if (!btnAbrirSoporte || !modalSoporte || !btnCerrarModal || !btnCancelar || !formularioSoporte) {
        console.warn('Algunos elementos del modal de soporte no se encontraron en el DOM');
        return;
    }

    // Función para abrir el modal
    function abrirModal(e) {
        e.preventDefault();
        e.stopPropagation();
        
        modalSoporte.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evita scroll del body
        
        // Focus en el primer input
        setTimeout(() => {
            const primerInput = document.getElementById('nombreSoporte');
            if (primerInput) primerInput.focus();
        }, 300);
    }

    // Función para cerrar el modal
    function cerrarModal() {
        modalSoporte.classList.remove('active');
        document.body.style.overflow = ''; // Restaura scroll del body
        formularioSoporte.reset(); // Limpia el formulario
        
        // Limpiar estilos de validación
        const inputs = formularioSoporte.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.style.borderColor = '#e0e0e0';
        });
    }

    // Event Listeners
    btnAbrirSoporte.addEventListener('click', abrirModal);
    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);

    // Cerrar modal al hacer click fuera del contenido
    modalSoporte.addEventListener('click', function(e) {
        if (e.target === modalSoporte) {
            cerrarModal();
        }
    });

    // Cerrar modal con la tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalSoporte.classList.contains('active')) {
            cerrarModal();
        }
    });

    // Manejar el envío del formulario
    formularioSoporte.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar campos
        const nombre = document.getElementById('nombreSoporte').value.trim();
        const email = document.getElementById('emailSoporte').value.trim();
        const tipo = document.getElementById('tipoProblema').value;
        const descripcion = document.getElementById('descripcionProblema').value.trim();

        if (!nombre || !email || !tipo || !descripcion) {
            alert('Por favor completa todos los campos');
            return;
        }

        // Obtener los datos del formulario
        const formData = {
            nombre: nombre,
            email: email,
            tipo: tipo,
            descripcion: descripcion,
            fecha: new Date().toLocaleString('es-ES')
        };

        // Deshabilitar botón de envío mientras se procesa
        const btnEnviar = formularioSoporte.querySelector('.btn-enviar');
        const textoOriginal = btnEnviar.innerHTML;
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';

        // Simular envío (aquí conectarías con tu backend)
        setTimeout(() => {
            console.log('Datos del formulario:', formData);
            
            // Restaurar botón
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = textoOriginal;
            
            // Mensaje de éxito
            alert('¡Mensaje enviado correctamente! Te responderemos pronto.');
            
            // Cerrar el modal
            cerrarModal();
        }, 1500);

        // CÓDIGO PARA CONECTAR CON TU BACKEND:
        /*
        fetch('/api/soporte', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
            return response.json();
        })
        .then(data => {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = textoOriginal;
            alert('¡Mensaje enviado correctamente! Te responderemos pronto.');
            cerrarModal();
        })
        .catch(error => {
            console.error('Error:', error);
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = textoOriginal;
            alert('Hubo un error al enviar el mensaje. Por favor, intenta de nuevo.');
        });
        */
    });

    // Validación en tiempo real del email
    const emailInput = document.getElementById('emailSoporte');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
            } else if (email) {
                this.style.borderColor = '#4caf50';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });

        emailInput.addEventListener('focus', function() {
            if (this.style.borderColor === 'rgb(231, 76, 60)') {
                this.style.borderColor = '#e0e0e0';
            }
        });
    }

    // Validación en tiempo real de campos requeridos
    const camposRequeridos = formularioSoporte.querySelectorAll('[required]');
    camposRequeridos.forEach(campo => {
        campo.addEventListener('blur', function() {
            if (!this.value.trim() && this.id !== 'emailSoporte') {
                this.style.borderColor = '#e74c3c';
            } else if (this.value.trim() && this.id !== 'emailSoporte') {
                this.style.borderColor = '#4caf50';
            }
        });

        campo.addEventListener('focus', function() {
            if (this.style.borderColor === 'rgb(231, 76, 60)') {
                this.style.borderColor = '#e0e0e0';
            }
        });
    });

    // Contador de caracteres para el textarea (opcional)
    const descripcionTextarea = document.getElementById('descripcionProblema');
    if (descripcionTextarea) {
        descripcionTextarea.addEventListener('input', function() {
            const maxLength = 500;
            const currentLength = this.value.length;
            
            // Puedes agregar un contador visual aquí si lo deseas
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });
    }
});