/* ======================================================
   PANEL SUPERIOR REPRESENTANTE - ESTRUCTURA DE CLASE
   ====================================================== */

const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

class PanelSuperiorRepresentante {
    constructor() {
        // Selector rápido
        this.$ = (s) => document.querySelector(s);
        this.$$ = (s) => document.querySelectorAll(s);

        // DOM cacheado
        this.navbar = this.$(".navbar-superior");
        this.horaActual = this.$("#horaActual");
        this.saludoTexto = this.$("#saludoTexto");
        this.saludoEmoji = this.$("#saludoEmoji");

        this.dropdowns = {
            perfil: this.$("#perfilDropdown"),
            notificaciones: this.$("#notificationsPanel"),
        };

        this.buttons = {
            perfil: this.$(".btn-profile"),
            notificaciones: this.$(".btn-notificaciones"),
            abrirSoporte: this.$("#btnAbrirSoporte"),
            cerrarModal: this.$("#btnCerrarModal"),
            cancelar: this.$("#btnCancelar"),
        };

        this.modals = {
            soporte: this.$("#modalSoporte"),
        };

        this.forms = {
            soporte: this.$("#formularioSoporte"),
        };

        this.themeIcon = this.$("#themeIcon");

        // Estado
        this.state = {
            activeDropdown: null,
            relojIntervalo: null,
            theme: localStorage.getItem("theme") || "light",
        };

        this.init();
    }

    /* ================= INIT ================= */

    init() {
        this.initReloj();
        this.initSaludo();
        this.initDropdowns();
        this.initModalSoporte();
        this.initFormularioSoporte();
        this.initTheme();
        this.initSidebarMovil();
        this.initEventosGlobales();

        console.log("Panel Superior Representante listo ✔");
    }

    /* ================= RELOJ Y SALUDO ================= */

    initReloj() {
        if (!this.horaActual) return;

        const actualizarReloj = () => {
            const ahora = new Date();
            this.horaActual.textContent = ahora.toLocaleTimeString("es-CO");
        };

        actualizarReloj();
        this.state.relojIntervalo = setInterval(actualizarReloj, 1000);
    }

    initSaludo() {
        if (!this.saludoTexto || !this.saludoEmoji) return;

        const hora = new Date().getHours();

        if (hora < 12) {
            this.saludoTexto.textContent = "Buenos días";
            this.saludoEmoji.textContent = "🌅";
        } else if (hora < 18) {
            this.saludoTexto.textContent = "Buenas tardes";
            this.saludoEmoji.textContent = "☀️";
        } else {
            this.saludoTexto.textContent = "Buenas noches";
            this.saludoEmoji.textContent = "🌙";
        }
    }

    /* ================= MENÚ PERFIL Y DROPDOWNS ================= */

    initDropdowns() {
        // Exponer funciones globalmente para compatibilidad con HTML
        window.togglePerfilMenu = () => this.togglePerfilMenu();
        window.toggleNotificaciones = () => this.toggleNotificaciones();
        window.eliminarNotificacion = (btn) => this.eliminarNotificacion(btn);
        window.marcarTodasLeidas = () => this.marcarTodasLeidas();
    }

    togglePerfilMenu() {
        const menu = this.dropdowns.perfil;
        if (!menu) return;

        this.cerrarOtrosDropdowns(menu);
        menu.style.display = menu.style.display === "block" ? "none" : "block";
        this.state.activeDropdown =
            menu.style.display === "block" ? "perfil" : null;
    }

    toggleNotificaciones() {
        const panel = this.dropdowns.notificaciones;
        if (!panel) return;

        this.cerrarOtrosDropdowns(panel);
        panel.style.display = panel.style.display === "block" ? "none" : "block";
        this.state.activeDropdown =
            panel.style.display === "block" ? "notificaciones" : null;
    }

    cerrarOtrosDropdowns(excepto) {
        this.$$(".dropdown-panel").forEach((panel) => {
            if (panel !== excepto) {
                panel.style.display = "none";
            }
        });
    }

    cerrarTodos() {
        this.$$(".dropdown-panel").forEach((panel) => {
            panel.style.display = "none";
        });
        this.state.activeDropdown = null;
    }

    /* ================= NOTIFICACIONES ================= */

    eliminarNotificacion(btn) {
        const item = btn.closest(".notification-item");
        if (item) {
            item.remove();
        }
    }

    marcarTodasLeidas() {
        this.$$(".notification-item.unread").forEach((item) => {
            item.classList.remove("unread");
        });
    }

    /* ================= MODAL SOPORTE ================= */

    initModalSoporte() {
        if (this.buttons.abrirSoporte) {
            this.buttons.abrirSoporte.addEventListener("click", (e) => {
                e.preventDefault();
                this.abrirModalSoporte();
            });
        }

        if (this.buttons.cerrarModal) {
            this.buttons.cerrarModal.addEventListener("click", () => {
                this.cerrarModalSoporte();
            });
        }

        if (this.buttons.cancelar) {
            this.buttons.cancelar.addEventListener("click", () => {
                this.cerrarModalSoporte();
            });
        }
    }

    abrirModalSoporte() {
        if (!this.modals.soporte) return;

        this.cerrarTodos();
        this.modals.soporte.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    cerrarModalSoporte() {
        if (!this.modals.soporte) return;

        this.modals.soporte.classList.remove("active");
        document.body.style.overflow = "";
    }

    /* ================= FORMULARIO SOPORTE ================= */

    initFormularioSoporte() {
        if (!this.forms.soporte) return;

        this.forms.soporte.addEventListener("submit", (e) => {
            e.preventDefault();
            this.enviarFormularioSoporte();
        });
    }

    async enviarFormularioSoporte() {
        const descripcion = this.$("#descripcionProblema").value;
        const tipoProblema = this.$("#tipoProblema").value;
        const asunto = this.$("#asunto").value.trim();

        // Validaciones
        if (descripcion && descripcion.length > 250) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "La descripción no debe exceder los 250 caracteres.",
            });
            return;
        }

        if (!tipoProblema || !asunto) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor, Ingrese los campos obligatorios",
            });
            return;
        }

        // Enviar formulario
        const formData = new FormData(this.forms.soporte);

        try {
            const response = await fetch(`${baseUrl}/soporte/api/crear-ticket`, {
                method: "POST",
                body: formData,
            });

            const data = await response.json();

            if (data.status === "error") {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message,
                });
            } else {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: `${data.message}`,
                });
                this.forms.soporte.reset();
                this.cerrarModalSoporte();
            }
        } catch (error) {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al enviar la solicitud.",
            });
        }
    }

    /* ================= TEMA OSCURO / CLARO ================= */

    initTheme() {
        window.toggleTheme = () => this.toggleTheme();
    }

    toggleTheme() {
        document.body.classList.toggle("dark-theme");

        // Alternativamente, si se usa data-theme:
        const isDark = document.body.classList.contains("dark-theme");
        this.state.theme = isDark ? "dark" : "light";
        localStorage.setItem("theme", this.state.theme);
    }

    /* ================= SIDEBAR MÓVIL ================= */

    initSidebarMovil() {
        window.abrirSidebarMovil = () => this.abrirSidebarMovil();
    }

    abrirSidebarMovil() {
        document.body.classList.toggle("sidebar-open");
    }

    /* ================= EVENTOS GLOBALES ================= */

    initEventosGlobales() {
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".navbar-action") &&
                !e.target.closest(".dropdown-panel") &&
                !e.target.closest(".modal-container")
            ) {
                this.cerrarTodos();
            }
        });

        // Event listener para el botón de perfil
        if (this.buttons.perfil) {
            this.buttons.perfil.addEventListener("click", () => {
                // Lógica adicional si necesitas cuando se hace click en el perfil
                // this.buttons.perfil.classList.toggle('active');
            });
        }
    }

    /* ================= UTILIDADES ================= */

    // Método para limpiar intervalos al destruir la instancia
    destroy() {
        if (this.state.relojIntervalo) {
            clearInterval(this.state.relojIntervalo);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
    new PanelSuperiorRepresentante();
});