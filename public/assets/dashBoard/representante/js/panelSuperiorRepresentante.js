/* ======================================================
   PANEL SUPERIOR REPRESENTANTE - ESTRUCTURA DE CLASE
   ====================================================== */

const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

/* Safety stubs for inline onclick usage from templates.
   These provide no-op / delegated behavior if the real scripts haven't initialized yet. */
if (!window.togglePerfilMenu) {
    window.togglePerfilMenu = function() {
        const btn = document.querySelector('#btnPerfil, [data-dropdown="perfil"], .btn-profile');
        if (btn) btn.click();
    };
}
if (!window.toggleNotificaciones) {
    window.toggleNotificaciones = function() {
        const btn = document.querySelector('#btnNotificaciones, [data-action="notificaciones"], .btn-notificaciones');
        if (btn) btn.click();
    };
}
if (!window.eliminarNotificacion) {
    window.eliminarNotificacion = function(btn) {
        try { btn?.closest && btn.closest('.notification-item')?.remove(); } catch(_) {}
    };
}
if (!window.marcarTodasLeidas) {
    window.marcarTodasLeidas = function() {
        document.querySelectorAll('.notification-item.unread').forEach(i => i.classList.remove('unread'));
    };
}

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

        this.notificationsApiUrl = `${baseUrl}/representante/api/notificaciones`;
        this.notificationBadge = this.$("#notificationBadge");
        this.notificationsContainer = this.$(".notifications-list");
        this.notifications = [];

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
        this.initNotificaciones();

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
        const abrirMenu = menu.style.display !== "block";
        menu.style.display = abrirMenu ? "block" : "none";
        // También sincronizamos la clase usada por otros scripts
        if (abrirMenu) {
            menu.classList.remove('is-hidden');
        } else {
            menu.classList.add('is-hidden');
        }
        this.state.activeDropdown = abrirMenu ? 'perfil' : null;
    }

    toggleNotificaciones() {
        const panel = this.dropdowns.notificaciones;
        if (!panel) return;

        this.cerrarOtrosDropdowns(panel);
        const abrir = panel.style.display !== "block";
        panel.style.display = abrir ? "block" : "none";
        if (abrir) {
            panel.classList.remove('is-hidden');
        } else {
            panel.classList.add('is-hidden');
        }
        this.state.activeDropdown = abrir ? "notificaciones" : null;

        if (abrir) {
            this.loadNotifications();
        }
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

    /* ================= NOTIFICACIONES ================= */

    async initNotificaciones() {
        this.loadNotifications();
        this.connectNotificationStream();
    }

    async loadNotifications() {
        const data = await this.fetchNotifications('&limite=10');
        if (!data) return;

        this.notifications = Array.isArray(data.notificaciones) ? data.notificaciones : [];
        const noLeidas = Number.isInteger(data.no_leidas) ? data.no_leidas : 0;

        this.renderNotifications(this.notifications);
        this.updateBadge(noLeidas);
    }

    async fetchNotifications(params = '') {
        try {
            const response = await fetch(`${this.notificationsApiUrl}?accion=listar${params}`);
            if (!response.ok) return null;
            return await response.json();
        } catch (error) {
            console.error('Error cargando notificaciones del representante:', error);
            return null;
        }
    }

    renderNotifications(items) {
        if (!this.notificationsContainer) return;

        if (!Array.isArray(items) || items.length === 0) {
            this.notificationsContainer.innerHTML = `
                <div class="notification-empty">
                    <div class="notification-empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <p>No tienes notificaciones recientes.</p>
                </div>`;
            return;
        }

        this.notificationsContainer.innerHTML = items.map(item => {
            const isUnread = item.leido == 0 || item.leido === false;
            const iconClass = item.tipo === 'vacuna' ? 'bi-heart-pulse-fill' :
                item.tipo === 'cita' ? 'bi-calendar-check-fill' :
                item.tipo === 'seguimiento' ? 'bi-journal-medical' :
                item.tipo === 'recordatorio' ? 'bi-bell-fill' :
                'bi-bell-fill';
            const statusClass = isUnread ? 'unread' : '';
            const fecha = item.fecha ? new Date(item.fecha).toLocaleString('es-CO', {
                day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : 'Sin fecha';
            const detail = item.canal ? item.canal : item.referencia_id ? `Ref: ${item.referencia_id}` : '';

            return `
                <div class="notification-item ${statusClass}" data-notification-id="${item.id}">
                    <div class="notification-indicator ${statusClass ? 'success' : 'info'}"></div>
                    <div class="notification-icon ${statusClass ? 'success' : 'info'}">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <h4>${this.escapeHtml(item.mensaje || 'Nueva notificación')}</h4>
                            <span class="notification-time">${this.escapeHtml(fecha)}</span>
                        </div>
                        <p>${this.escapeHtml(detail)}</p>
                    </div>
                    <button class="btn-icon-sm" onclick="eliminarNotificacion(this)" aria-label="Eliminar">
                        <i class="bi bi-x"></i>
                    </button>
                </div>`;
        }).join('');
    }

    updateBadge(count = 0) {
        if (!this.notificationBadge) return;
        if (count <= 0) {
            this.notificationBadge.style.display = 'none';
            this.notificationBadge.textContent = '';
            return;
        }
        this.notificationBadge.style.display = 'inline-flex';
        this.notificationBadge.textContent = String(count);
    }

    async connectNotificationStream() {
        setInterval(() => this.loadNotifications(), 30000);
    }

    async marcarTodasLeidas() {
        try {
            const response = await fetch(`${this.notificationsApiUrl}?accion=todas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            });
            if (!response.ok) return;
            const data = await response.json();
            if (data.status === 'success') {
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marcando todas las notificaciones leídas:', error);
        }
    }

    async eliminarNotificacion(btn) {
        const item = btn.closest('.notification-item');
        const id = item?.dataset.notificationId;
        if (!id) {
            if (item) item.remove();
            return;
        }

        try {
            const response = await fetch(`${this.notificationsApiUrl}?accion=cancelar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: Number(id) })
            });
            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    item.remove();
                    this.notifications = this.notifications.filter(n => String(n.id) !== String(id));
                    const noLeidas = this.notifications.filter(n => !n.leido).length;
                    this.updateBadge(noLeidas);
                }
            }
        } catch (error) {
            console.error('Error eliminando notificación representante:', error);
        }
    }

    escapeHtml(value) {
        if (typeof value !== 'string') return '';
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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