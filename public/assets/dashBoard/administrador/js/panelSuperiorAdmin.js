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

    // El tema lo aplica y guarda global/js/modo-oscuro.js (window.vwTema); aquí solo el botón y el icono
    initTheme() {
        const sincronizar = () => {
            this.state.theme = window.vwTema && window.vwTema.obtener() === "oscuro" ? "dark" : "light";
            this.updateThemeIcon();
        };
        sincronizar();
        window.addEventListener("vw:tema", sincronizar);

        window.toggleTheme = () => window.vwTema && window.vwTema.alternar();
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