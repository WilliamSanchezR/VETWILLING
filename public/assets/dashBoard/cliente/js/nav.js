/**
 * Navbar Manager - Sistema completo de navegación
 * @version 2.0
 */

class NavbarManager {
    constructor() {
        // Helpers de selección
        this.$ = (s) => document.querySelector(s);
        this.$$ = (s) => document.querySelectorAll(s);

        // Cache de elementos DOM
        this.elements = {
            navbar: this.$('.navbar-superior'),
            dropdowns: {
                notificaciones: this.$('#dropdownNotificaciones'),
                perfil: this.$('#dropdownPerfil')
            },
            buttons: {
                notificaciones: this.$('[data-dropdown="notificaciones"]'),
                perfil: this.$('[data-dropdown="perfil"]'),
                marcarLeidas: this.$('[data-action="marcar-leidas"]')
            },
            search: this.$('#inputBusqueda'),
            modals: {
                soporte: this.$('#modalSoporte')
            }
        };

        // Estado de la aplicación
        this.state = {
            activeDropdown: null,
            notificacionesSinLeer: 0,
            searchDebounce: null,
            theme: localStorage.getItem('theme') || 'light',
            notificaciones: [],
            loadingNotifications: false
        };

        // Configuración
        this.config = {
            searchDebounceTime: 300,
            notificationRefreshInterval: 30000, // 30 segundos
            animationDuration: 300
        };

        this.init();
    }

    /* ==================== INICIALIZACIÓN ==================== */

    init() {
        try {
            this.initDropdowns();
            this.initNotifications();
            this.initSearch();
            this.initScrollEffects();
            this.initKeyboardNavigation();
            this.initAccessibility();
            
            console.log('✅ Navbar Manager inicializado correctamente');
        } catch (error) {
            console.error('❌ Error al inicializar NavbarManager:', error);
        }
    }

    /* ==================== DROPDOWNS ==================== */

    initDropdowns() {
        // Event delegation para botones con dropdown
        this.$$('[data-dropdown]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const tipo = btn.dataset.dropdown;
                this.toggleDropdown(tipo, btn);
            });
        });

        // Cerrar dropdowns al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar-derecha')) {
                this.closeAllDropdowns();
            }
        });

        // Prevenir cierre al hacer click dentro del dropdown
        this.$$('.dropdown-menu').forEach(menu => {
            menu.addEventListener('click', (e) => e.stopPropagation());
        });
    }

    toggleDropdown(tipo, button) {
        const dropdown = this.elements.dropdowns[tipo];
        if (!dropdown) return;

        const isOpen = dropdown.classList.contains('show');
        
        // Cerrar todos primero
        this.closeAllDropdowns();

        if (!isOpen) {
            // Abrir el dropdown
            dropdown.classList.add('show');
            this.state.activeDropdown = tipo;

            // Actualizar ARIA
            if (button) {
                button.setAttribute('aria-expanded', 'true');
            }

            // Cargar notificaciones si es necesario
            if (tipo === 'notificaciones' && !this.state.loadingNotifications) {
                this.cargarNotificaciones();
            }

            // Manejar foco
            this.setFocusInDropdown(dropdown);
        } else if (button) {
            button.setAttribute('aria-expanded', 'false');
        }
    }

    closeAllDropdowns() {
        Object.entries(this.elements.dropdowns).forEach(([tipo, dropdown]) => {
            dropdown?.classList.remove('show');
            
            const button = this.$(`[data-dropdown="${tipo}"]`);
            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
        
        this.state.activeDropdown = null;
    }

    setFocusInDropdown(dropdown) {
        setTimeout(() => {
            const firstFocusable = dropdown.querySelector('a, button, input');
            firstFocusable?.focus();
        }, 100);
    }

    /* ==================== NOTIFICACIONES ==================== */

    initNotifications() {
        // Marcar todas como leídas
        this.elements.buttons.marcarLeidas?.addEventListener('click', () => {
            this.marcarTodasComoLeidas();
        });

        // Cargar notificaciones inicial
        this.cargarNotificaciones();

        // Auto-refresh cada 30 segundos
        setInterval(() => {
            if (!this.state.activeDropdown) {
                this.cargarNotificaciones(true); // silent refresh
            }
        }, this.config.notificationRefreshInterval);
    }

    async cargarNotificaciones(silent = false) {
        if (this.state.loadingNotifications && !silent) return;

        this.state.loadingNotifications = true;
        const container = this.$('#listaNotificaciones');
        
        if (!silent && container) {
            container.innerHTML = this.getLoadingTemplate();
        }

        try {
            // Aquí harías la petición real a tu API
            const notificaciones = await this.fetchNotificaciones();
            
            this.state.notificaciones = notificaciones;
            this.renderNotificaciones(notificaciones);
            this.actualizarContadorNotificaciones();

        } catch (error) {
            console.error('Error al cargar notificaciones:', error);
            if (!silent && container) {
                container.innerHTML = this.getErrorTemplate();
            }
        } finally {
            this.state.loadingNotifications = false;
        }
    }

    async fetchNotificaciones() {
        // Simulación - reemplazar con llamada real a API
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve([
                    {
                        id: 1,
                        tipo: 'recordatorio',
                        titulo: 'Recordatorio de vacuna para Max',
                        tiempo: 'Hace 5 min',
                        leida: false,
                        icono: 'bi-bell-fill',
                        color: 'azul'
                    },
                    {
                        id: 2,
                        tipo: 'confirmacion',
                        titulo: 'Cita confirmada para mañana',
                        tiempo: 'Hace 1 hora',
                        leida: true,
                        icono: 'bi-check-circle-fill',
                        color: 'verde'
                    },
                    {
                        id: 3,
                        tipo: 'mensaje',
                        titulo: 'Nuevo mensaje del veterinario',
                        tiempo: 'Hace 3 horas',
                        leida: false,
                        icono: 'bi-chat-dots-fill',
                        color: 'naranja'
                    }
                ]);
            }, 500);
        });

        /* CÓDIGO REAL PARA API:
        const response = await fetch('/api/notificaciones', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Error al cargar notificaciones');
        return await response.json();
        */
    }

    renderNotificaciones(notificaciones) {
        const container = this.$('#listaNotificaciones');
        if (!container) return;

        if (notificaciones.length === 0) {
            container.innerHTML = this.getEmptyNotificationsTemplate();
            return;
        }

        container.innerHTML = notificaciones.map(n => `
            <a href="#" 
               class="notificacion-item ${!n.leida ? 'no-leida' : ''}"
               data-notif-id="${n.id}"
               role="menuitem">
                <div class="notif-icono notif-${n.color}">
                    <i class="${n.icono}" aria-hidden="true"></i>
                </div>
                <div class="notif-contenido">
                    <p class="notif-texto">${this.escapeHtml(n.titulo)}</p>
                    <span class="notif-tiempo">${this.escapeHtml(n.tiempo)}</span>
                </div>
            </a>
        `).join('');

        // Agregar event listeners a las notificaciones
        this.$$('.notificacion-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleNotificacionClick(item);
            });
        });
    }

    handleNotificacionClick(item) {
        const notifId = parseInt(item.dataset.notifId);
        
        if (item.classList.contains('no-leida')) {
            this.marcarComoLeida(notifId);
            item.classList.remove('no-leida');
        }

        // Aquí puedes agregar lógica para navegar o mostrar detalles
        console.log('Notificación clickeada:', notifId);
    }

    async marcarComoLeida(notifId) {
        try {
            // Actualizar estado local
            const notif = this.state.notificaciones.find(n => n.id === notifId);
            if (notif) {
                notif.leida = true;
            }

            this.actualizarContadorNotificaciones();

            // Enviar a servidor
            /* await fetch(`/api/notificaciones/${notifId}/marcar-leida`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }); */

        } catch (error) {
            console.error('Error al marcar notificación:', error);
        }
    }

    async marcarTodasComoLeidas() {
        try {
            // Actualizar estado local
            this.state.notificaciones.forEach(n => n.leida = true);
            
            // Actualizar UI
            this.$$('.notificacion-item.no-leida').forEach(item => {
                item.classList.remove('no-leida');
            });

            this.actualizarContadorNotificaciones();

            // Enviar a servidor
            /* await fetch('/api/notificaciones/marcar-todas-leidas', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }); */

        } catch (error) {
            console.error('Error al marcar todas como leídas:', error);
        }
    }

    actualizarContadorNotificaciones() {
        const sinLeer = this.state.notificaciones.filter(n => !n.leida).length;
        const badge = this.$('#badgeNotificaciones');

        if (badge) {
            if (sinLeer > 0) {
                badge.textContent = sinLeer;
                badge.style.display = 'flex';
                badge.setAttribute('aria-label', `${sinLeer} notificaciones sin leer`);
            } else {
                badge.style.display = 'none';
                badge.setAttribute('aria-label', 'Sin notificaciones');
            }
        }

        this.state.notificacionesSinLeer = sinLeer;
    }

    /* ==================== TEMPLATES ==================== */

    getLoadingTemplate() {
        return `
            <div class="loading-notificaciones">
                <div class="spinner"></div>
                <p>Cargando notificaciones...</p>
            </div>
        `;
    }

    getErrorTemplate() {
        return `
            <div class="error-notificaciones">
                <i class="bi bi-exclamation-triangle"></i>
                <p>Error al cargar notificaciones</p>
                <button onclick="window.navbarManager.cargarNotificaciones()" class="btn-retry">
                    Reintentar
                </button>
            </div>
        `;
    }

    getEmptyNotificationsTemplate() {
        return `
            <div class="empty-notificaciones">
                <i class="bi bi-bell-slash"></i>
                <p>No tienes notificaciones</p>
            </div>
        `;
    }

    /* ==================== BÚSQUEDA ==================== */

    initSearch() {
        const searchInput = this.elements.search;
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.state.searchDebounce);

            const query = e.target.value.trim();

            this.state.searchDebounce = setTimeout(() => {
                this.performSearch(query);
            }, this.config.searchDebounceTime);
        });

        // Limpiar búsqueda con ESC
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                this.performSearch('');
                searchInput.blur();
            }
        });
    }

    async performSearch(query) {
        if (!query) {
            this.clearSearchResults();
            return;
        }

        console.log('Buscando:', query);

        // Aquí implementarías la búsqueda real
        // Podría ser búsqueda local o llamada a API
        
        /* Ejemplo con API:
        try {
            const response = await fetch(`/api/buscar?q=${encodeURIComponent(query)}`);
            const resultados = await response.json();
            this.mostrarResultadosBusqueda(resultados);
        } catch (error) {
            console.error('Error en búsqueda:', error);
        }
        */
    }

    clearSearchResults() {
        // Limpiar resultados de búsqueda
        const items = this.$$('[data-searchable]');
        items.forEach(el => el.style.display = '');
    }

    /* ==================== EFECTOS DE SCROLL ==================== */

    initScrollEffects() {
        let lastScroll = 0;
        let ticking = false;

        window.addEventListener('scroll', () => {
            lastScroll = window.pageYOffset;

            if (!ticking) {
                window.requestAnimationFrame(() => {
                    this.handleScroll(lastScroll);
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    handleScroll(scrollPos) {
        const navbar = this.elements.navbar;
        if (!navbar) return;

        if (scrollPos > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    /* ==================== NAVEGACIÓN POR TECLADO ==================== */

    initKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // ESC para cerrar dropdowns
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
            }

            // Tab trap en dropdowns abiertos
            if (e.key === 'Tab' && this.state.activeDropdown) {
                this.handleTabInDropdown(e);
            }
        });
    }

    handleTabInDropdown(e) {
        const activeDropdown = this.elements.dropdowns[this.state.activeDropdown];
        if (!activeDropdown) return;

        const focusableElements = activeDropdown.querySelectorAll(
            'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
        }
    }

    /* ==================== ACCESIBILIDAD ==================== */

    initAccessibility() {
        // Anunciar cambios dinámicos a lectores de pantalla
        this.createAriaLiveRegion();
    }

    createAriaLiveRegion() {
        if (this.$('#aria-live-region')) return;

        const liveRegion = document.createElement('div');
        liveRegion.id = 'aria-live-region';
        liveRegion.className = 'sr-only';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        document.body.appendChild(liveRegion);
    }

    announceToScreenReader(message) {
        const liveRegion = this.$('#aria-live-region');
        if (liveRegion) {
            liveRegion.textContent = message;
            setTimeout(() => liveRegion.textContent = '', 1000);
        }
    }

    /* ==================== UTILIDADES ==================== */

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

/* ==================== CARRITO ==================== */

class CarritoManager {
    constructor() {
        this.carrito = this.loadFromStorage() || [];
        this.elements = {
            sidebar: document.getElementById('carritoSidebar'),
            items: document.getElementById('carritoItems'),
            total: document.getElementById('totalCarrito'),
            contador: document.getElementById('contadorCarrito'),
            btnPagar: document.querySelector('.btn-pagar')
        };

        this.init();
    }

    init() {
        this.initEventListeners();
        this.actualizar();
    }

    initEventListeners() {
        // Toggle carrito
        document.querySelectorAll('[data-action="toggle-carrito"]').forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
        });

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.elements.sidebar?.classList.contains('open')) {
                this.toggle();
            }
        });

        // Proceder al pago
        this.elements.btnPagar?.addEventListener('click', () => this.procesarPago());
    }

    toggle() {
        this.elements.sidebar?.classList.toggle('open');
        
        if (this.elements.sidebar?.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    agregar(producto) {
        const existe = this.carrito.find(item => item.id === producto.id);
        
        if (existe) {
            existe.cantidad++;
        } else {
            this.carrito.push({
                ...producto,
                cantidad: 1
            });
        }

        this.actualizar();
        this.saveToStorage();
        this.mostrarNotificacion(`${producto.nombre} agregado al carrito`);
    }

    eliminar(id) {
        this.carrito = this.carrito.filter(item => item.id !== id);
        this.actualizar();
        this.saveToStorage();
    }

    cambiarCantidad(id, cantidad) {
        const item = this.carrito.find(item => item.id === id);
        if (item) {
            item.cantidad = Math.max(1, cantidad);
            this.actualizar();
            this.saveToStorage();
        }
    }

    actualizar() {
        if (!this.elements.items) return;

        if (this.carrito.length === 0) {
            this.elements.items.innerHTML = `
                <div class="carrito-vacio">
                    <i class="bi bi-cart-x"></i>
                    <p>Tu carrito está vacío</p>
                </div>
            `;
            this.elements.btnPagar.disabled = true;
        } else {
            this.elements.items.innerHTML = this.carrito.map(item => `
                <div class="carrito-item" data-item-id="${item.id}">
                    <div class="item-info">
                        <h4>${this.escapeHtml(item.nombre)}</h4>
                        <span class="item-precio">$${item.precio}</span>
                    </div>
                    <div class="item-controles">
                        <button class="btn-cantidad" onclick="window.carritoManager.cambiarCantidad(${item.id}, ${item.cantidad - 1})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <span class="cantidad">${item.cantidad}</span>
                        <button class="btn-cantidad" onclick="window.carritoManager.cambiarCantidad(${item.id}, ${item.cantidad + 1})">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button class="btn-eliminar" onclick="window.carritoManager.eliminar(${item.id})" aria-label="Eliminar ${item.nombre}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            this.elements.btnPagar.disabled = false;
        }

        const total = this.carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        this.elements.total.textContent = `$${total.toFixed(2)}`;

        const totalItems = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
        this.elements.contador.textContent = totalItems;
        this.elements.contador.style.display = totalItems > 0 ? 'flex' : 'none';
    }

    procesarPago() {
        if (this.carrito.length === 0) return;
        
        console.log('Procesando pago...', this.carrito);
        // Aquí iría la lógica de pago
        alert('Funcionalidad de pago en desarrollo');
    }

    saveToStorage() {
        try {
            localStorage.setItem('carrito', JSON.stringify(this.carrito));
        } catch (error) {
            console.error('Error al guardar carrito:', error);
        }
    }

    loadFromStorage() {
        try {
            const data = localStorage.getItem('carrito');
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error('Error al cargar carrito:', error);
            return [];
        }
    }

    mostrarNotificacion(mensaje) {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <i class="bi bi-check-circle-fill"></i>
            <span>${this.escapeHtml(mensaje)}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/* ==================== MODAL DE SOPORTE ==================== */

class SoporteModal {
    constructor() {
        this.elements = {
            modal: document.getElementById('modalSoporte'),
            form: document.getElementById('formularioSoporte'),
            btnEnviar: null
        };

        if (this.elements.modal && this.elements.form) {
            this.elements.btnEnviar = this.elements.form.querySelector('.btn-enviar');
            this.init();
        }
    }

    init() {
        this.initEventListeners();
        this.initValidacion();
    }

    initEventListeners() {
        // Abrir modal
        document.querySelectorAll('[data-modal="soporte"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.abrir();
            });
        });

        // Cerrar modal
        document.querySelectorAll('[data-modal-close="soporte"]').forEach(btn => {
            btn.addEventListener('click', () => this.cerrar());
        });

        // Cerrar con click fuera
        this.elements.modal?.addEventListener('click', (e) => {
            if (e.target === this.elements.modal) {
                this.cerrar();
            }
        });

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.elements.modal?.style.display === 'flex') {
                this.cerrar();
            }
        });

        // Submit form
        this.elements.form?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.enviarFormulario();
        });
    }

    initValidacion() {
        const campos = this.elements.form?.querySelectorAll('.form-control');
        
        campos?.forEach(campo => {
            campo.addEventListener('blur', () => this.validarCampo(campo));
            campo.addEventListener('input', () => {
                if (campo.classList.contains('error')) {
                    this.validarCampo(campo);
                }
            });
        });
    }

    validarCampo(campo) {
        const valor = campo.value.trim();
        const errorSpan = campo.nextElementSibling;
        let error = '';

        if (campo.hasAttribute('required') && !valor) {
            error = 'Este campo es obligatorio';
        } else if (campo.type === 'email' && valor) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(valor)) {
                error = 'Email inválido';
            }
        } else if (campo.tagName === 'TEXTAREA' && valor.length < 10) {
            error = 'Mínimo 10 caracteres';
        }

        if (error) {
            campo.classList.add('error');
            if (errorSpan && errorSpan.classList.contains('error-message')) {
                errorSpan.textContent = error;
                errorSpan.style.display = 'block';
            }
            return false;
        } else {
            campo.classList.remove('error');
            if (errorSpan && errorSpan.classList.contains('error-message')) {
                errorSpan.style.display = 'none';
            }
            return true;
        }
    }

    validarFormulario() {
        const campos = this.elements.form.querySelectorAll('.form-control[required]');
        let valido = true;

        campos.forEach(campo => {
            if (!this.validarCampo(campo)) {
                valido = false;
            }
        });

        return valido;
    }

    async enviarFormulario() {
        if (!this.validarFormulario()) {
            return;
        }

        const formData = new FormData(this.elements.form);
        const data = Object.fromEntries(formData.entries());

        // Deshabilitar botón
        this.elements.btnEnviar.disabled = true;
        const spinner = this.elements.btnEnviar.querySelector('.loading-spinner');
        const span = this.elements.btnEnviar.querySelector('span');
        
        if (spinner) spinner.style.display = 'inline-block';
        if (span) span.textContent = 'Enviando...';

        try {
            // Simular envío
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            /* CÓDIGO REAL:
            const response = await fetch('/api/soporte', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Error al enviar');
            */

            this.mostrarExito();
            setTimeout(() => this.cerrar(), 2000);

        } catch (error) {
            console.error('Error:', error);
            alert('Error al enviar el mensaje. Por favor, intenta de nuevo.');
        } finally {
            this.elements.btnEnviar.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (span) span.textContent = 'Enviar Mensaje';
        }
    }

    mostrarExito() {
        const mensajeExito = this.elements.modal?.querySelector('.mensaje-exito');
        if (mensajeExito) {
            mensajeExito.style.display = 'block';
        }
    }

    abrir() {
        this.elements.modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            const primerInput = this.elements.form.querySelector('input');
            primerInput?.focus();
        }, 100);
    }

    cerrar() {
        this.elements.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.elements.form?.reset();
        
        // Limpiar errores
        this.elements.form?.querySelectorAll('.error').forEach(el => {
            el.classList.remove('error');
        });
        this.elements.form?.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        const mensajeExito = this.elements.modal?.querySelector('.mensaje-exito');
        if (mensajeExito) {
            mensajeExito.style.display = 'none';
        }
    }
}

/* ==================== INICIALIZACIÓN GLOBAL ==================== */

document.addEventListener('DOMContentLoaded', () => {
    window.navbarManager = new NavbarManager();
    window.carritoManager = new CarritoManager();
    window.soporteModal = new SoporteModal();

    console.log('🚀 Sistema de navegación cargado completamente');
});
// reloj
// ===================================
// MÓDULO DE RELOJ EN VIVO
// ===================================

class RelojEnVivo {
    constructor() {
        this.modalReloj = document.getElementById('modalReloj');
        this.btnReloj = document.querySelector('[data-modal="reloj"]');
        this.btnCerrar = document.querySelector('[data-modal-close="reloj"]');
        this.horaNavbar = document.getElementById('horaNavbar');
        this.relojDigital = document.getElementById('relojDigital');
        this.relojFecha = document.getElementById('relojFecha');
        this.diaSemana = document.getElementById('diaSemana');
        this.periodo = document.getElementById('periodo');
        this.zonaHoraria = document.getElementById('zonaHoraria');
        
        this.diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        this.meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        this.init();
    }
    
    init() {
        // Iniciar actualización del reloj
        this.actualizarReloj();
        setInterval(() => this.actualizarReloj(), 1000);
        
        // Event listeners
        if (this.btnReloj) {
            this.btnReloj.addEventListener('click', () => this.abrirModal());
        }
        
        if (this.btnCerrar) {
            this.btnCerrar.addEventListener('click', () => this.cerrarModal());
        }
        
        if (this.modalReloj) {
            this.modalReloj.addEventListener('click', (e) => {
                if (e.target === this.modalReloj) {
                    this.cerrarModal();
                }
            });
        }
        
        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modalReloj.classList.contains('activo')) {
                this.cerrarModal();
            }
        });
        
        // Actualizar zona horaria
        this.actualizarZonaHoraria();
    }
    
    actualizarReloj() {
        const ahora = new Date();
        
        // Actualizar hora en navbar (formato 12h con AM/PM)
        const horas12 = ahora.getHours() % 12 || 12;
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const ampm = ahora.getHours() >= 12 ? 'PM' : 'AM';
        
        if (this.horaNavbar) {
            this.horaNavbar.textContent = `${horas12}:${minutos} ${ampm}`;
        }
        
        // Actualizar reloj digital en modal (formato 24h simple)
        const horas24 = String(ahora.getHours()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        
        if (this.relojDigital) {
            this.relojDigital.textContent = `${horas24}:${minutos}:${segundos}`;
        }
        
        // Actualizar fecha
        const dia = ahora.getDate();
        const mes = this.meses[ahora.getMonth()];
        const año = ahora.getFullYear();
        
        if (this.relojFecha) {
            this.relojFecha.textContent = `${dia} de ${mes} de ${año}`;
        }
        
        // Actualizar día de la semana
        if (this.diaSemana) {
            this.diaSemana.textContent = this.diasSemana[ahora.getDay()];
        }
        
        // Actualizar período del día
        if (this.periodo) {
            this.periodo.textContent = this.obtenerPeriodo(ahora.getHours());
        }
    }
    
    obtenerPeriodo(hora) {
        if (hora >= 5 && hora < 12) return 'Mañana';
        if (hora >= 12 && hora < 18) return 'Tarde';
        if (hora >= 18 && hora < 22) return 'Noche';
        return 'Madrugada';
    }
    
    actualizarZonaHoraria() {
        if (this.zonaHoraria) {
            try {
                const zona = Intl.DateTimeFormat().resolvedOptions().timeZone;
                const offset = new Date().getTimezoneOffset();
                const offsetHoras = Math.abs(offset / 60);
                const signo = offset <= 0 ? '+' : '-';
                this.zonaHoraria.textContent = `UTC${signo}${offsetHoras}`;
            } catch (e) {
                this.zonaHoraria.textContent = 'Local';
            }
        }
    }
    
    abrirModal() {
        if (this.modalReloj) {
            this.modalReloj.classList.add('activo');
            document.body.style.overflow = 'hidden';
        }
    }
    
    cerrarModal() {
        if (this.modalReloj) {
            this.modalReloj.classList.remove('activo');
            document.body.style.overflow = '';
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new RelojEnVivo();
});

// ===================================
// INTEGRACIÓN CON nav.js EXISTENTE
// ===================================

// Si ya tienes un nav.js, agrega esta función al final
// o integra la clase RelojEnVivo en tu código existente

// Ejemplo de integración:
/*
// En tu nav.js existente, agrega:

document.addEventListener('DOMContentLoaded', () => {
    // Tu código existente...
    
    // Inicializar reloj
    const reloj = new RelojEnVivo();
    
    // Continúa con tu código...
});
*/