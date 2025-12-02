// =====================================================
// DASHBOARD MANAGER - VERSIÓN PROFESIONAL FINAL
// =====================================================

class DashboardManager {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.bindEvents();

        this.restoreSidebarState();
        this.restoreSubmenusState();
        this.restoreTheme();
        this.marcarItemActivo();

        console.log("✅ Dashboard cargado correctamente");
    }

    // =====================================================
    // CACHE DE ELEMENTOS
    // =====================================================
    cacheDom() {
        this.sidebar       = document.getElementById("sidebar");
        this.sidebarToggle = document.getElementById("sidebarToggle");
        this.content       = document.getElementById("contenidoPrincipal");
        this.inputBusqueda = document.getElementById("inputBusqueda");
        this.themeIcon     = document.getElementById("themeIcon");
    }

    // =====================================================
    // EVENTOS PRINCIPALES
    // =====================================================
    bindEvents() {

        // Toggle sidebar
        if (this.sidebarToggle)
            this.sidebarToggle.addEventListener("click", () => this.toggleSidebar());

        // Click fuera para cerrar
        document.addEventListener("click", (e) => {
            this.handleOutsideClick(e);
            this.closeDropdowns(e);
        });

        // Búsqueda
        if (this.inputBusqueda)
            this.inputBusqueda.addEventListener("input", this.debounce((e) => {
                const term = e.target.value.trim().toLowerCase();
                this.realizarBusqueda(term);
            }, 300));

        // Delegación global
        document.addEventListener("click", (e) => {
            const { action, id, nombre, tipo } = e.target.dataset;

            if (action === "dropdown") this.toggleDropdown(tipo);
            if (action === "theme") this.toggleTheme();
            if (action === "chat-open") this.abrirChat(nombre, id);
            if (action === "chat-back") this.volverLista();
            if (action === "chat-send") this.enviarMensaje();
        });

        // Auto-expansión del textarea del chat
        const inputMsg = document.getElementById("inputMensaje");
        if (inputMsg) {
            inputMsg.addEventListener("input", () => {
                inputMsg.style.height = "auto";
                inputMsg.style.height = `${inputMsg.scrollHeight}px`;
            });
        }
    }

    // =====================================================
    // SIDEBAR (SIN ANIMACIONES FEAS)
    // =====================================================
    toggleSidebar() {
        this.sidebar.classList.toggle("collapsed");
        this.content?.classList.toggle("contenido-expandido");

        // guardar en localStorage
        localStorage.setItem("sidebarCollapsed", this.sidebar.classList.contains("collapsed"));
    }

    restoreSidebarState() {
        const collapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (collapsed) {
            this.sidebar.classList.add("collapsed");
            this.content?.classList.add("contenido-expandido");
        }
    }

    // Ocultar sidebar en pantallas pequeñas
    handleOutsideClick(e) {
        if (window.innerWidth <= 768) {
            if (!e.target.closest("#sidebar") && !e.target.closest("#sidebarToggle")) {
                this.sidebar.classList.add("collapsed");
            }
        }
    }

    // =====================================================
    // SUBMENÚS
    // =====================================================
    restoreSubmenusState() {
        document.querySelectorAll(".submenu").forEach(submenu => {
            const id = submenu.querySelector(".submenu-toggle")?.dataset.menuId;
            if (localStorage.getItem(`submenu-${id}`) === "true") {
                submenu.classList.add("activo");
            }
        });
    }

    // =====================================================
    // DROPDOWNS
    // =====================================================
    toggleDropdown(tipo) {
        const dropdown = document.getElementById(`dropdown${tipo}`);
        if (!dropdown) return;

        document.querySelectorAll(".dropdown-menu").forEach(d => d.classList.remove("show"));
        dropdown.classList.add("show");
    }

    closeDropdowns(e) {
        if (!e.target.closest(".navbar-derecha")) {
            document.querySelectorAll(".dropdown-menu").forEach(d => d.classList.remove("show"));
        }
    }

    // =====================================================
    // TEMA
    // =====================================================
    toggleTheme() {
        document.body.classList.toggle("dark-mode");

        const dark = document.body.classList.contains("dark-mode");
        this.themeIcon?.classList.toggle("bi-sun-fill", dark);
        this.themeIcon?.classList.toggle("bi-moon-stars-fill", !dark);

        localStorage.setItem("theme", dark ? "dark" : "light");
    }

    restoreTheme() {
        const saved = localStorage.getItem("theme");
        if (saved === "dark") {
            document.body.classList.add("dark-mode");
            this.themeIcon?.classList.replace("bi-moon-stars-fill", "bi-sun-fill");
        }
    }

    // =====================================================
    // BÚSQUEDA
    // =====================================================
    realizarBusqueda(termino) {
        document.querySelectorAll("[data-searchable]").forEach(el => {
            el.style.display = el.textContent.toLowerCase().includes(termino)
                ? ""
                : "none";
        });
    }

    debounce(fn, delay) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // =====================================================
    // CHAT
    // =====================================================
    abrirChat(nombre, id) {
        const chatWindow = document.getElementById("chatWindow");
        const lista = document.getElementById("conversacionesList");

        if (!chatWindow || !lista) return;

        document.getElementById("chatNombre").textContent = nombre;

        lista.style.display = "none";
        chatWindow.classList.add("show");

        this.scrollChatBottom();
        this.cargarMensajes(id);
    }

    volverLista() {
        document.getElementById("chatWindow")?.classList.remove("show");
        document.getElementById("conversacionesList").style.display = "block";
    }

    enviarMensaje() {
        const input = document.getElementById("inputMensaje");
        const cont = document.getElementById("mensajesContainer");

        if (!input || !cont) return;

        const msg = input.value.trim();
        if (!msg) return;

        cont.insertAdjacentHTML("beforeend", this.renderMensaje(msg));

        input.value = "";
        input.style.height = "auto";

        this.scrollChatBottom();
        this.enviarMensajeServidor(msg);
    }

    renderMensaje(texto) {
        const hora = new Date().toLocaleTimeString("es-CO", { hour: "2-digit", minute: "2-digit" });

        return `
            <div class="mensaje enviado">
                <div>
                    <div class="mensaje-bubble">${this.escapeHtml(texto)}</div>
                    <div class="mensaje-hora">${hora}</div>
                </div>
            </div>
        `;
    }

    scrollChatBottom() {
        const cont = document.getElementById("mensajesContainer");
        if (cont) cont.scrollTop = cont.scrollHeight;
    }

    enviarMensajeServidor(msg) {
        console.log("📨 Mensaje enviado al servidor:", msg);
    }

    cargarMensajes(idChat) {
        console.log("📥 Cargar mensajes del chat:", idChat);
    }

    escapeHtml(text) {
        return text.replace(/[&<>"']/g, (m) =>
            ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[m])
        );
    }

    // =====================================================
    // MARCAR ELEMENTO ACTIVO
    // =====================================================
    marcarItemActivo() {
        const actual = window.location.pathname;

        document.querySelectorAll(".item-sidebar").forEach(item => {
            const url = new URL(item.href).pathname;
            item.classList.toggle("active", url === actual);
        });
    }
}

// Inicializar
new DashboardManager();
