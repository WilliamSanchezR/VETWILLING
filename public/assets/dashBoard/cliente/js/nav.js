
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