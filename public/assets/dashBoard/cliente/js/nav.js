/**
 * Navbar Manager - Sistema completo de navegación
 * @version 2.1 (consolidado)
 *
 * NOTA IMPORTANTE: las notificaciones ahora se manejan EXCLUSIVAMENTE dentro de
 * NavbarManager (un solo sistema). Se eliminó el bloque suelto duplicado que
 * usaba otro markup/endpoint y se pisaba con este.
 *
 * >>> VERIFICA QUE COINCIDA CON TU BACKEND <<<
 * - Endpoint usado: `${BASE_URL}/preferencias-notificacion?accion=obtener`
 * - Marcar una:     `?accion=marcar_leida`  (POST, body JSON {id})
 * - Marcar todas:   `?accion=marcar_todas`  (POST)
 * - Respuesta esperada: { status:"success", no_leidas:Number, notificaciones:[
 *     { id, tipo, titulo, mensaje, fecha, leido } ] }
 * El render maneja de forma defensiva tanto `leido` (0/1) como `leida` (bool).
 */

class NavbarManager {
  constructor() {
    this.$ = (s) => document.querySelector(s);
    this.$$ = (s) => document.querySelectorAll(s);

    this.elements = {
      navbar: this.$(".navbar-superior"),
      dropdowns: {
        notificaciones:
          this.$("#dropdownNotificaciones") ||
          this.$('[data-panel="notificaciones"]') ||
          this.$("#notificationsPanel") ||
          this.$(".notifications-list"),
        perfil:
          this.$("#dropdownPerfil") ||
          this.$('[data-panel="perfil"]') ||
          this.$("#perfilDropdown"),
      },
      buttons: {
        notificaciones:
          this.$('[data-dropdown="notificaciones"]') ||
          this.$("#btnNotificaciones") ||
          this.$(".btn-notificaciones"),
        perfil:
          this.$('[data-dropdown="perfil"]') ||
          this.$("#btnPerfil") ||
          this.$(".btn-profile"),
        marcarLeidas:
          this.$('[data-action="marcar-leidas"]') || this.$("#btnMarcarLeidas"),
      },
      search: this.$("#inputBusqueda"),
      modals: {
        soporte: this.$("#modalSoporte"),
      },
    };

    this.state = {
      activeDropdown: null,
      notificacionesSinLeer: 0,
      searchDebounce: null,
      theme: localStorage.getItem("theme") || "light",
      notificaciones: [],
      loadingNotifications: false,
    };

    this.config = {
      searchDebounceTime: 300,
      notificationRefreshInterval: 60000, // 60 segundos
      animationDuration: 300,
      // Base de la API. Usa la global definida en la vista o cadena vacía.
      baseUrl: (typeof window !== "undefined" && window.BASE_URL) ? window.BASE_URL : "",
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
      console.log("✅ Navbar Manager inicializado correctamente");
    } catch (error) {
      console.error("❌ Error al inicializar NavbarManager:", error);
    }
  }

  /* ==================== DROPDOWNS ==================== */

  initDropdowns() {
    this.$$("[data-dropdown]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        const tipo = btn.dataset.dropdown;
        this.toggleDropdown(tipo, btn);
      });
    });

    document.addEventListener("click", (e) => {
      if (!e.target.closest(".navbar-derecha")) {
        this.closeAllDropdowns();
      }
    });

    this.$$(".dropdown-menu").forEach((menu) => {
      menu.addEventListener("click", (e) => e.stopPropagation());
    });
  }

  toggleDropdown(tipo, button) {
    const dropdown = this.elements.dropdowns[tipo];
    if (!dropdown) return;

    const isOpen = dropdown.classList.contains("show");
    this.closeAllDropdowns();

    if (!isOpen) {
      dropdown.classList.add("show");
      this.state.activeDropdown = tipo;
      if (button) button.setAttribute("aria-expanded", "true");

      if (tipo === "notificaciones" && !this.state.loadingNotifications) {
        this.cargarNotificaciones();
      }
      this.setFocusInDropdown(dropdown);
    } else if (button) {
      button.setAttribute("aria-expanded", "false");
    }
  }

  closeAllDropdowns() {
    Object.entries(this.elements.dropdowns).forEach(([tipo, dropdown]) => {
      dropdown?.classList.remove("show");
      const button = this.$(`[data-dropdown="${tipo}"]`);
      if (button) button.setAttribute("aria-expanded", "false");
    });
    this.state.activeDropdown = null;
  }

  setFocusInDropdown(dropdown) {
    setTimeout(() => {
      const firstFocusable = dropdown.querySelector("a, button, input");
      firstFocusable?.focus();
    }, 100);
  }

  /* ==================== NOTIFICACIONES (sistema único) ==================== */

  initNotifications() {
    this.elements.buttons.marcarLeidas?.addEventListener("click", () => {
      this.marcarTodasComoLeidas();
    });

    // Carga inicial (solo badge + lista en caché)
    this.cargarNotificaciones(true);

    // Auto-refresh silencioso
    setInterval(() => {
      if (!this.state.activeDropdown) {
        this.cargarNotificaciones(true);
      }
    }, this.config.notificationRefreshInterval);
  }

  async cargarNotificaciones(silent = false) {
    if (this.state.loadingNotifications && !silent) return;

    this.state.loadingNotifications = true;
    const container = this.$("#listaNotificaciones");

    if (!silent && container) {
      container.innerHTML = this.getLoadingTemplate();
    }

    try {
      const notificaciones = await this.fetchNotificaciones();
      this.state.notificaciones = notificaciones;
      this.renderNotificaciones(notificaciones);
      this.actualizarContadorNotificaciones();
    } catch (error) {
      console.error("Error al cargar notificaciones:", error);
      if (!silent && container) {
        container.innerHTML = this.getErrorTemplate();
      }
    } finally {
      this.state.loadingNotifications = false;
    }
  }

  async fetchNotificaciones() {
    const res = await fetch(
      `${this.config.baseUrl}/preferencias-notificacion?accion=obtener`,
      { credentials: "include", headers: { Accept: "application/json" } },
    );
    if (!res.ok) throw new Error("Error al cargar notificaciones");

    const data = await res.json();
    if (data.status && data.status !== "success") return [];

    return Array.isArray(data.notificaciones) ? data.notificaciones : [];
  }

  // Normaliza el estado "no leída" aceptando varios formatos del backend.
  esNoLeida(n) {
    if (typeof n.leida === "boolean") return !n.leida;
    if (n.leido !== undefined) return Number(n.leido) === 0;
    if (n.leida !== undefined) return Number(n.leida) === 0;
    return false;
  }

  iconoTipo(tipo) {
    const iconos = {
      INFO: "bi bi-info-circle-fill",
      info: "bi bi-info-circle-fill",
      alerta: "bi bi-exclamation-triangle-fill",
      advertencia: "bi bi-exclamation-circle-fill",
      error: "bi bi-x-circle-fill",
    };
    return iconos[tipo] || "bi bi-bell-fill";
  }

  colorTipo(tipo) {
    const colores = {
      INFO: "azul",
      info: "azul",
      alerta: "naranja",
      advertencia: "naranja",
      error: "rojo",
      exito: "verde",
    };
    return colores[tipo] || "azul";
  }

  formatearFecha(fecha) {
    if (!fecha) return "";
    const d = new Date(fecha);
    if (isNaN(d.getTime())) return String(fecha);
    return d.toLocaleDateString("es-CO", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  renderNotificaciones(notificaciones) {
    const container = this.$("#listaNotificaciones");
    if (!container) return;

    if (!notificaciones || notificaciones.length === 0) {
      container.innerHTML = this.getEmptyNotificationsTemplate();
      return;
    }

    container.innerHTML = notificaciones
      .map((n) => {
        const noLeida = this.esNoLeida(n);
        const color = n.color || this.colorTipo(n.tipo);
        const icono = n.icono || this.iconoTipo(n.tipo);
        const titulo = n.titulo ?? n.tipo ?? "Notificación";
        const tiempo = n.tiempo ?? this.formatearFecha(n.fecha);
        return `
            <a href="#"
               class="notificacion-item ${noLeida ? "no-leida" : ""}"
               data-notif-id="${n.id}"
               role="menuitem">
                <div class="notif-icono notif-${color}">
                    <i class="${icono}" aria-hidden="true"></i>
                </div>
                <div class="notif-contenido">
                    <p class="notif-texto">${this.escapeHtml(titulo)}</p>
                    ${n.mensaje ? `<p class="notif-texto" style="font-weight:400">${this.escapeHtml(n.mensaje)}</p>` : ""}
                    <span class="notif-tiempo">${this.escapeHtml(tiempo)}</span>
                </div>
            </a>`;
      })
      .join("");

    this.$$(".notificacion-item").forEach((item) => {
      item.addEventListener("click", (e) => {
        e.preventDefault();
        this.handleNotificacionClick(item);
      });
    });
  }

  handleNotificacionClick(item) {
    const notifId = parseInt(item.dataset.notifId, 10);
    if (item.classList.contains("no-leida")) {
      this.marcarComoLeida(notifId);
      item.classList.remove("no-leida");
    }
    console.log("Notificación clickeada:", notifId);
  }

  async marcarComoLeida(notifId) {
    try {
      const notif = this.state.notificaciones.find((n) => n.id == notifId);
      if (notif) {
        notif.leida = true;
        notif.leido = 1;
      }
      this.actualizarContadorNotificaciones();

      await fetch(
        `${this.config.baseUrl}/preferencias-notificacion?accion=marcar_leida`,
        {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: notifId }),
        },
      );
    } catch (error) {
      console.error("Error al marcar notificación:", error);
    }
  }

  async marcarTodasComoLeidas() {
    try {
      this.state.notificaciones.forEach((n) => {
        n.leida = true;
        n.leido = 1;
      });
      this.$$(".notificacion-item.no-leida").forEach((item) =>
        item.classList.remove("no-leida"),
      );
      this.actualizarContadorNotificaciones();

      await fetch(
        `${this.config.baseUrl}/preferencias-notificacion?accion=marcar_todas`,
        {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
        },
      );
    } catch (error) {
      console.error("Error al marcar todas como leídas:", error);
    }
  }

  actualizarContadorNotificaciones() {
    const sinLeer = this.state.notificaciones.filter((n) =>
      this.esNoLeida(n),
    ).length;
    const badge = this.$("#badgeNotificaciones");

    if (badge) {
      if (sinLeer > 0) {
        badge.textContent = sinLeer;
        badge.style.display = "flex";
        badge.setAttribute("aria-label", `${sinLeer} notificaciones sin leer`);
      } else {
        badge.textContent = "";
        badge.style.display = "none";
        badge.setAttribute("aria-label", "Sin notificaciones");
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
            </div>`;
  }

  getErrorTemplate() {
    return `
            <div class="error-notificaciones">
                <i class="bi bi-exclamation-triangle"></i>
                <p>Error al cargar notificaciones</p>
                <button onclick="window.navbarManager.cargarNotificaciones()" class="btn-retry">
                    Reintentar
                </button>
            </div>`;
  }

  getEmptyNotificationsTemplate() {
    return `
            <div class="empty-notificaciones">
                <i class="bi bi-bell-slash"></i>
                <p>No tienes notificaciones</p>
            </div>`;
  }

  /* ==================== BÚSQUEDA ==================== */

  initSearch() {
    const searchInput = this.elements.search;
    if (!searchInput) return;

    searchInput.addEventListener("input", (e) => {
      clearTimeout(this.state.searchDebounce);
      const query = e.target.value.trim();
      this.state.searchDebounce = setTimeout(() => {
        this.performSearch(query);
      }, this.config.searchDebounceTime);
    });

    searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        searchInput.value = "";
        this.performSearch("");
        searchInput.blur();
      }
    });
  }

  async performSearch(query) {
    if (!query) {
      this.clearSearchResults();
      return;
    }
    console.log("Buscando:", query);
    // Implementa aquí la búsqueda real (local o por API).
  }

  clearSearchResults() {
    const items = this.$$("[data-searchable]");
    items.forEach((el) => (el.style.display = ""));
  }

  /* ==================== EFECTOS DE SCROLL ==================== */

  initScrollEffects() {
    let lastScroll = 0;
    let ticking = false;

    window.addEventListener(
      "scroll",
      () => {
        lastScroll = window.pageYOffset;
        if (!ticking) {
          window.requestAnimationFrame(() => {
            this.handleScroll(lastScroll);
            ticking = false;
          });
          ticking = true;
        }
      },
      { passive: true },
    );
  }

  handleScroll(scrollPos) {
    const navbar = this.elements.navbar;
    if (!navbar) return;
    navbar.classList.toggle("scrolled", scrollPos > 50);
  }

  /* ==================== NAVEGACIÓN POR TECLADO ==================== */

  initKeyboardNavigation() {
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") this.closeAllDropdowns();
      if (e.key === "Tab" && this.state.activeDropdown) {
        this.handleTabInDropdown(e);
      }
    });
  }

  handleTabInDropdown(e) {
    const activeDropdown = this.elements.dropdowns[this.state.activeDropdown];
    if (!activeDropdown) return;

    const focusableElements = activeDropdown.querySelectorAll(
      'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])',
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
    this.createAriaLiveRegion();
  }

  createAriaLiveRegion() {
    if (this.$("#aria-live-region")) return;
    const liveRegion = document.createElement("div");
    liveRegion.id = "aria-live-region";
    liveRegion.className = "sr-only";
    liveRegion.setAttribute("aria-live", "polite");
    liveRegion.setAttribute("aria-atomic", "true");
    document.body.appendChild(liveRegion);
  }

  announceToScreenReader(message) {
    const liveRegion = this.$("#aria-live-region");
    if (liveRegion) {
      liveRegion.textContent = message;
      setTimeout(() => (liveRegion.textContent = ""), 1000);
    }
  }

  /* ==================== UTILIDADES ==================== */

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
  }
}

/* ==================== CARRITO ==================== */
/* Nota: este módulo solo actúa si el HTML del carrito existe en la página.
   En vistas sin carrito queda inactivo (todos los guards devuelven temprano). */

class CarritoManager {
  constructor() {
    this.carrito = this.loadFromStorage() || [];
    this.elements = {
      sidebar: document.getElementById("carritoSidebar"),
      items: document.getElementById("carritoItems"),
      total: document.getElementById("totalCarrito"),
      contador: document.getElementById("contadorCarrito"),
      btnPagar: document.querySelector(".btn-pagar"),
    };

    // Si no hay contenedor de items, el carrito no existe en esta vista.
    if (!this.elements.items) return;
    this.init();
  }

  init() {
    this.initEventListeners();
    this.actualizar();
  }

  initEventListeners() {
    document
      .querySelectorAll('[data-action="toggle-carrito"]')
      .forEach((btn) => btn.addEventListener("click", () => this.toggle()));

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.elements.sidebar?.classList.contains("open")) {
        this.toggle();
      }
    });

    this.elements.btnPagar?.addEventListener("click", () => this.procesarPago());
  }

  toggle() {
    this.elements.sidebar?.classList.toggle("open");
    document.body.style.overflow = this.elements.sidebar?.classList.contains("open")
      ? "hidden"
      : "";
  }

  agregar(producto) {
    const existe = this.carrito.find((item) => item.id === producto.id);
    if (existe) {
      existe.cantidad++;
    } else {
      this.carrito.push({ ...producto, cantidad: 1 });
    }
    this.actualizar();
    this.saveToStorage();
    this.mostrarNotificacion(`${producto.nombre} agregado al carrito`);
  }

  eliminar(id) {
    this.carrito = this.carrito.filter((item) => item.id !== id);
    this.actualizar();
    this.saveToStorage();
  }

  cambiarCantidad(id, cantidad) {
    const item = this.carrito.find((item) => item.id === id);
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
                </div>`;
      if (this.elements.btnPagar) this.elements.btnPagar.disabled = true;
    } else {
      this.elements.items.innerHTML = this.carrito
        .map(
          (item) => `
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
                        <button class="btn-eliminar" onclick="window.carritoManager.eliminar(${item.id})" aria-label="Eliminar ${this.escapeHtml(item.nombre)}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>`,
        )
        .join("");
      if (this.elements.btnPagar) this.elements.btnPagar.disabled = false;
    }

    const total = this.carrito.reduce(
      (sum, item) => sum + item.precio * item.cantidad,
      0,
    );
    if (this.elements.total) this.elements.total.textContent = `$${total.toFixed(2)}`;

    const totalItems = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
    if (this.elements.contador) {
      this.elements.contador.textContent = totalItems;
      this.elements.contador.style.display = totalItems > 0 ? "flex" : "none";
    }
  }

  procesarPago() {
    if (this.carrito.length === 0) return;
    console.log("Procesando pago...", this.carrito);
    alert("Funcionalidad de pago en desarrollo");
  }

  saveToStorage() {
    try {
      localStorage.setItem("carrito", JSON.stringify(this.carrito));
    } catch (error) {
      console.error("Error al guardar carrito:", error);
    }
  }

  loadFromStorage() {
    try {
      const data = localStorage.getItem("carrito");
      return data ? JSON.parse(data) : [];
    } catch (error) {
      console.error("Error al cargar carrito:", error);
      return [];
    }
  }

  mostrarNotificacion(mensaje) {
    const toast = document.createElement("div");
    toast.className = "toast-notification";
    toast.innerHTML = `
            <i class="bi bi-check-circle-fill"></i>
            <span>${this.escapeHtml(mensaje)}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
  }
}

/* ==================== MODAL DE SOPORTE ==================== */

class SoporteModal {
  constructor() {
    this.elements = {
      modal: document.getElementById("modalSoporte"),
      // OJO: #formularioSoporte es un <div>, NO un <form>.
      form: document.getElementById("formularioSoporte"),
      btnEnviar: document.getElementById("btnEnviarSoporte"),
    };

    if (this.elements.modal && this.elements.form) {
      this.init();
    }
  }

  init() {
    this.initEventListeners();
    this.initValidacion();
  }

  initEventListeners() {
    document.querySelectorAll('[data-modal="soporte"]').forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        this.abrir();
      });
    });

    document.querySelectorAll('[data-modal-close="soporte"]').forEach((btn) => {
      btn.addEventListener("click", () => this.cerrar());
    });

    this.elements.modal?.addEventListener("click", (e) => {
      if (e.target === this.elements.modal) this.cerrar();
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.elements.modal?.style.display === "flex") {
        this.cerrar();
      }
    });

    // CORRECCIÓN CLAVE: un <div> nunca dispara "submit".
    // El envío se engancha al clic del botón.
    this.elements.btnEnviar?.addEventListener("click", () =>
      this.enviarFormulario(),
    );
  }

  initValidacion() {
    const campos = this.elements.form?.querySelectorAll(".form-control");
    campos?.forEach((campo) => {
      campo.addEventListener("blur", () => this.validarCampo(campo));
      campo.addEventListener("input", () => {
        if (campo.classList.contains("error")) this.validarCampo(campo);
      });
    });
  }

  validarCampo(campo) {
    const valor = campo.value.trim();
    const errorSpan = campo.nextElementSibling;
    let error = "";

    const esRequerido =
      campo.hasAttribute("required") || campo.getAttribute("aria-required") === "true";

    if (esRequerido && !valor) {
      error = "Este campo es obligatorio";
    } else if (campo.type === "email" && valor) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(valor)) error = "Email inválido";
    } else if (campo.tagName === "TEXTAREA" && valor.length < 10) {
      error = "Mínimo 10 caracteres";
    }

    if (error) {
      campo.classList.add("error");
      if (errorSpan && errorSpan.classList.contains("error-message")) {
        errorSpan.textContent = error;
        errorSpan.style.display = "block";
      }
      return false;
    }
    campo.classList.remove("error");
    if (errorSpan && errorSpan.classList.contains("error-message")) {
      errorSpan.style.display = "none";
    }
    return true;
  }

  validarFormulario() {
    // Validamos todos los .form-control (todos llevan aria-required en el HTML).
    const campos = this.elements.form.querySelectorAll(".form-control");
    let valido = true;
    campos.forEach((campo) => {
      if (!this.validarCampo(campo)) valido = false;
    });
    return valido;
  }

  // Recolecta los datos manualmente (no usamos FormData porque no hay <form>).
  recolectarDatos() {
    const f = this.elements.form;
    return {
      nombre: f.querySelector("#nombreSoporte")?.value.trim() || "",
      email: f.querySelector("#emailSoporte")?.value.trim() || "",
      tipo_problema: f.querySelector("#tipoProblema")?.value || "",
      descripcion: f.querySelector("#descripcionProblema")?.value.trim() || "",
    };
  }

  async enviarFormulario() {
    if (!this.validarFormulario()) return;

    const data = this.recolectarDatos();
    const btn = this.elements.btnEnviar;
    const spinner = btn?.querySelector(".loading-spinner");
    const span = btn?.querySelector("span");

    if (btn) btn.disabled = true;
    if (spinner) spinner.style.display = "inline-block";
    if (span) span.textContent = "Enviando...";

    try {
      // Simulación de envío. Reemplaza por tu petición real:
      await new Promise((resolve) => setTimeout(resolve, 1500));

      /* CÓDIGO REAL (ejemplo):
      const baseUrl = window.BASE_URL || "";
      const response = await fetch(`${baseUrl}/api/soporte`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(data),
      });
      if (!response.ok) throw new Error("Error al enviar");
      */

      this.mostrarExito();
      setTimeout(() => this.cerrar(), 2000);
    } catch (error) {
      console.error("Error:", error);
      alert("Error al enviar el mensaje. Por favor, intenta de nuevo.");
    } finally {
      if (btn) btn.disabled = false;
      if (spinner) spinner.style.display = "none";
      if (span) span.textContent = "Enviar Mensaje";
    }
  }

  mostrarExito() {
    const mensajeExito = this.elements.modal?.querySelector(".mensaje-exito");
    const formContainer = this.elements.form;
    if (mensajeExito) mensajeExito.style.display = "block";
    if (formContainer) formContainer.style.display = "none";
  }

  resetCampos() {
    this.elements.form?.querySelectorAll(".form-control").forEach((campo) => {
      if (campo.tagName === "SELECT") {
        campo.selectedIndex = 0;
      } else {
        campo.value = "";
      }
    });
  }

  abrir() {
    this.elements.modal.style.display = "flex";
    document.body.style.overflow = "hidden";
    setTimeout(() => {
      this.elements.form.querySelector("input")?.focus();
    }, 100);
  }

  cerrar() {
    this.elements.modal.style.display = "none";
    document.body.style.overflow = "";

    // Limpiar errores y volver a mostrar el formulario por si quedó en "éxito"
    this.elements.form?.querySelectorAll(".error").forEach((el) =>
      el.classList.remove("error"),
    );
    this.elements.form?.querySelectorAll(".error-message").forEach((el) => {
      el.style.display = "none";
    });

    const mensajeExito = this.elements.modal?.querySelector(".mensaje-exito");
    if (mensajeExito) mensajeExito.style.display = "none";
    if (this.elements.form) this.elements.form.style.display = "";
  }
}

/* ==================== MÓDULO DE RELOJ EN VIVO ==================== */

class RelojEnVivo {
  constructor() {
    this.modalReloj = document.getElementById("modalReloj");
    this.btnReloj = document.querySelector('[data-modal="reloj"]');
    this.btnCerrar = document.querySelector('[data-modal-close="reloj"]');
    this.horaNavbar = document.getElementById("horaNavbar");
    this.relojDigital = document.getElementById("relojDigital");
    this.relojFecha = document.getElementById("relojFecha");
    this.diaSemana = document.getElementById("diaSemana");
    this.periodo = document.getElementById("periodo");
    this.zonaHoraria = document.getElementById("zonaHoraria");

    this.diasSemana = [
      "Domingo", "Lunes", "Martes", "Miércoles",
      "Jueves", "Viernes", "Sábado",
    ];
    this.meses = [
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
    ];

    this.init();
  }

  init() {
    this.actualizarReloj();
    setInterval(() => this.actualizarReloj(), 1000);

    this.btnReloj?.addEventListener("click", () => this.abrirModal());
    this.btnCerrar?.addEventListener("click", () => this.cerrarModal());

    this.modalReloj?.addEventListener("click", (e) => {
      if (e.target === this.modalReloj) this.cerrarModal();
    });

    document.addEventListener("keydown", (e) => {
      // CORRECCIÓN: guard contra modalReloj null.
      if (e.key === "Escape" && this.modalReloj?.classList.contains("activo")) {
        this.cerrarModal();
      }
    });

    this.actualizarZonaHoraria();
  }

  actualizarReloj() {
    const ahora = new Date();

    const horas12 = ahora.getHours() % 12 || 12;
    const minutos = String(ahora.getMinutes()).padStart(2, "0");
    const ampm = ahora.getHours() >= 12 ? "PM" : "AM";
    if (this.horaNavbar) this.horaNavbar.textContent = `${horas12}:${minutos} ${ampm}`;

    const horas24 = String(ahora.getHours()).padStart(2, "0");
    const segundos = String(ahora.getSeconds()).padStart(2, "0");
    if (this.relojDigital) this.relojDigital.textContent = `${horas24}:${minutos}:${segundos}`;

    const dia = ahora.getDate();
    const mes = this.meses[ahora.getMonth()];
    const anio = ahora.getFullYear();
    if (this.relojFecha) this.relojFecha.textContent = `${dia} de ${mes} de ${anio}`;
    if (this.diaSemana) this.diaSemana.textContent = this.diasSemana[ahora.getDay()];
    if (this.periodo) this.periodo.textContent = this.obtenerPeriodo(ahora.getHours());
  }

  obtenerPeriodo(hora) {
    if (hora >= 5 && hora < 12) return "Mañana";
    if (hora >= 12 && hora < 18) return "Tarde";
    if (hora >= 18 && hora < 22) return "Noche";
    return "Madrugada";
  }

  actualizarZonaHoraria() {
    if (!this.zonaHoraria) return;
    try {
      const offset = new Date().getTimezoneOffset();
      const offsetHoras = Math.abs(offset / 60);
      const signo = offset <= 0 ? "+" : "-";
      this.zonaHoraria.textContent = `UTC${signo}${offsetHoras}`;
    } catch (e) {
      this.zonaHoraria.textContent = "Local";
    }
  }

  abrirModal() {
    if (this.modalReloj) {
      this.modalReloj.classList.add("activo");
      document.body.style.overflow = "hidden";
    }
  }

  cerrarModal() {
    if (this.modalReloj) {
      this.modalReloj.classList.remove("activo");
      document.body.style.overflow = "";
    }
  }
}

/* ==================== INICIALIZACIÓN GLOBAL ==================== */

document.addEventListener("DOMContentLoaded", () => {
  window.navbarManager = new NavbarManager();
  window.carritoManager = new CarritoManager();
  window.soporteModal = new SoporteModal();
  window.relojEnVivo = new RelojEnVivo();

  console.log("🚀 Sistema de navegación cargado completamente");
});