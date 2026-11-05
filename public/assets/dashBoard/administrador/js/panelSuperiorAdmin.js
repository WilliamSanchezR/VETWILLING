class panelSuperiorAdmin {

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
        this.btnAbrirSoporte = document.getElementById('btnAbrirSoporte');
        this.modalSoporte = document.getElementById('modalSoporte');

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
        this.initGetRoute();

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

        this.btnAbrirSoporte?.addEventListener('click', (e) => {
            e.preventDefault();
            this.modalSoporte.classList.add('active');
            document.body.style.overflow = 'hidden';
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
        this.apiNotificaciones = `${window.BASE_URL}/soporte/api/ticket?action=notificaciones`;
        this.listaNotificaciones = this.$('#listaNotificaciones');
        this.tickets = [];

        if (this.buttons.marcarLeidas) {
            this.buttons.marcarLeidas.onclick = () => this.marcarTodasComoLeidas();
        }

        this.buttons.notificaciones?.addEventListener('click', () => this.cargarNotificaciones());
        this.cargarNotificaciones();
    }

    // El último ticket visto se recuerda en este navegador: la campanita no escribe en la base de datos
    ultimoTicketVisto() {
        try {
            return Number(localStorage.getItem('adminUltimoTicketVisto')) || 0;
        } catch (error) {
            return 0;
        }
    }

    guardarUltimoTicketVisto(id) {
        try {
            localStorage.setItem('adminUltimoTicketVisto', String(id));
        } catch (error) {
            console.warn('No se pudo recordar el último ticket visto.');
        }
    }

    async cargarNotificaciones() {
        try {
            const respuesta = await fetch(this.apiNotificaciones, { headers: { Accept: 'application/json' } });
            const datos = await respuesta.json();
            if (!respuesta.ok || !datos.ok) throw new Error(datos.mensaje || 'Respuesta no válida');

            this.tickets = datos.tickets || [];
            this.renderNotificaciones();
        } catch (error) {
            console.error('Error al cargar notificaciones:', error);
            this.mostrarMensajeNotificaciones('No se pudieron cargar las notificaciones.');
        }
    }

    mostrarMensajeNotificaciones(texto) {
        if (!this.listaNotificaciones) return;
        const mensaje = document.createElement('div');
        mensaje.className = 'empty-notificaciones';
        mensaje.textContent = texto;
        this.listaNotificaciones.replaceChildren(mensaje);
    }

    renderNotificaciones() {
        const visto = this.ultimoTicketVisto();
        this.state.notificacionesSinLeer = this.tickets.filter(t => Number(t.id) > visto).length;
        this.updateNotifBadge();

        if (!this.listaNotificaciones) return;
        if (!this.tickets.length) {
            this.mostrarMensajeNotificaciones('No hay tickets pendientes.');
            return;
        }
        this.listaNotificaciones.replaceChildren(...this.tickets.map(t => this.crearItemNotificacion(t, visto)));
    }

    crearItemNotificacion(ticket, visto) {
        const colores = { critica: 'rojo', alta: 'rojo', media: 'naranja', baja: 'azul' };
        const item = document.createElement('a');
        item.href = `${window.BASE_URL}/admin/gestion-tickets?id=${encodeURIComponent(ticket.id)}`;
        item.className = `notificacion-item${Number(ticket.id) > visto ? ' no-leida' : ''}`;

        const icono = document.createElement('div');
        icono.className = `notif-icono notif-${colores[ticket.prioridad] || 'azul'}`;
        const simbolo = document.createElement('i');
        simbolo.className = 'bi bi-chat-dots-fill';
        icono.appendChild(simbolo);

        const contenido = document.createElement('div');
        contenido.className = 'notif-contenido';
        const titulo = document.createElement('p');
        titulo.className = 'notif-texto';
        titulo.textContent = ticket.titulo || `Ticket #${ticket.id}`;
        const detalle = document.createElement('span');
        detalle.className = 'notif-tiempo';
        detalle.textContent = `${String(ticket.estado || '').replaceAll('_', ' ')} · prioridad ${ticket.prioridad || 'media'} · ${ticket.fecha_creacion || ''}`;
        contenido.append(titulo, detalle);

        item.append(icono, contenido);
        return item;
    }

    updateNotifBadge() {
        const b = this.badges.notificaciones;
        if (!b) return;

        b.style.display = (this.state.notificacionesSinLeer > 0) ? "flex" : "none";
        b.textContent = this.state.notificacionesSinLeer;
    }

    marcarTodasComoLeidas() {
        const mayor = this.tickets.reduce((max, t) => Math.max(max, Number(t.id) || 0), this.ultimoTicketVisto());
        this.guardarUltimoTicketVisto(mayor);
        this.renderNotificaciones();
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

    initGetRoute() {
        const path = window.location.pathname;
        const routes = path.split('/');
        let titlePage = '';
        const routePage = routes[routes.length - 1];

        switch (routePage) {
            case 'registro-usuario':
            case 'listar-usuarios':
            case 'editar-usuario':
                titlePage = 'Usuarios';
                break;
            case 'registro-veterinaria':
            case 'listar-veterinarias':
            case 'editar-veterinaria':
                titlePage = 'Veterinarias';
                break;
            case 'perfil-administrador':
                titlePage = 'Perfil';
                break;
            case 'directorio':
            case 'ver-perfil':
                titlePage = 'Directorio';
                break;
            case 'listar-propietarios':
                titlePage = 'Propietarios';
                break;
            case 'listar-tickets':
            case 'gestion-tickets':
                titlePage = 'Tickets de soporte';
                break;
            case 'reportes':
                titlePage = 'Reportes';
                break;
            case 'medios-envio':
                titlePage = 'Medios de envío';
                break;
            case 'directorio':
            case 'ver-perfil':
                titlePage = 'Directorio';
                break;
            default:
                titlePage = 'Inicio';
                break;
        }

        document.querySelector('.fw-semibold').textContent = titlePage;
    }
}

/* =============== INICIALIZACIÓN =============== */

document.addEventListener("DOMContentLoaded", () => {
    window.navbarManager = new panelSuperiorAdmin();
});