// Archivo JavaScript del menú lateral del administrador. Maneja: abrir/cerrar submenús, contraer la barra en escritorio (se recuerda en localStorage), tooltips y submenús flotantes en modo contraído, y el cajón lateral con fondo oscuro en móvil.
class SidebarAdmin {

    constructor() {
        this.cacheDom();
        if (!this.sidebar) return;
        this.bindEvents();
        this.syncCollapseButton();

        // Ya pintada la página, se activan las transiciones
        requestAnimationFrame(() => {
            requestAnimationFrame(() => this.html.classList.remove("sa-sin-animacion"));
        });
    }

    cacheDom() {
        this.html = document.documentElement;
        this.sidebar = document.getElementById("sidebarAdmin");
        this.collapseBtn = document.getElementById("saCollapse");
        this.closeBtn = document.getElementById("saClose");
        this.burger = document.getElementById("saBurger");
        this.overlay = document.getElementById("saOverlay");
        this.tooltip = document.getElementById("saTooltip");
        this.groups = this.sidebar?.querySelectorAll(".sa-group") ?? [];
        this.desktop = window.matchMedia("(min-width: 992px)");
    }

    bindEvents() {
        this.groups.forEach(group => {
            group.querySelector(".sa-group-toggle").addEventListener("click", () => this.onGroupClick(group));
        });

        this.collapseBtn?.addEventListener("click", () => this.toggleCollapsed());
        this.burger?.addEventListener("click", () => this.openDrawer());
        this.closeBtn?.addEventListener("click", () => this.closeDrawer());
        this.overlay?.addEventListener("click", () => this.closeDrawer());

        // Tooltips en modo contraído
        this.sidebar.querySelectorAll("[data-label]").forEach(el => {
            el.addEventListener("mouseenter", () => this.showTooltip(el));
            el.addEventListener("mouseleave", () => this.hideTooltip());
            el.addEventListener("focus", () => this.showTooltip(el));
            el.addEventListener("blur", () => this.hideTooltip());
        });
        this.sidebar.querySelector(".sa-nav").addEventListener("scroll", () => {
            this.hideTooltip();
            this.closeFlyouts();
        }, { passive: true });

        // Clic fuera o Escape cierran el menú flotante / el cajón
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".sa-group")) this.closeFlyouts();
        });
        document.addEventListener("keydown", (e) => {
            if (e.key !== "Escape") return;
            const flyout = this.sidebar.querySelector(".sa-group.is-flyout");
            if (flyout) {
                this.closeFlyouts();
                flyout.querySelector(".sa-group-toggle").focus();
            } else if (this.html.classList.contains("sa-open")) {
                this.closeDrawer();
            }
        });

        // Al pasar a escritorio se cierra el cajón móvil
        this.desktop.addEventListener("change", () => {
            this.closeDrawer();
            this.closeFlyouts();
            this.hideTooltip();
        });
    }

    isCollapsed() {
        return this.desktop.matches && this.html.classList.contains("sa-collapsed");
    }

    /* ---------- Submenús ---------- */

    onGroupClick(group) {
        // Contraída: el submenú se muestra flotando al lado de la barra
        if (this.isCollapsed()) {
            const abierto = group.classList.contains("is-flyout");
            this.closeFlyouts();
            if (!abierto) this.openFlyout(group);
            return;
        }

        const abrir = !group.classList.contains("is-open");
        group.classList.toggle("is-open", abrir);
        group.querySelector(".sa-group-toggle").setAttribute("aria-expanded", String(abrir));
    }

    openFlyout(group) {
        const toggle = group.querySelector(".sa-group-toggle");
        const r = toggle.getBoundingClientRect();
        group.style.setProperty("--sa-flyout-top", Math.max(8, r.top - 6) + "px");
        group.classList.add("is-flyout");
        toggle.setAttribute("aria-expanded", "true");
        this.hideTooltip();
        group.querySelector(".sa-sub a")?.focus({ preventScroll: true });
    }

    closeFlyouts() {
        this.sidebar.querySelectorAll(".sa-group.is-flyout").forEach(group => {
            group.classList.remove("is-flyout");
            group.querySelector(".sa-group-toggle")
                .setAttribute("aria-expanded", String(group.classList.contains("is-open") && !this.isCollapsed()));
        });
    }

    /* ---------- Contraer (escritorio) ---------- */

    toggleCollapsed() {
        const colapsar = !this.html.classList.contains("sa-collapsed");
        this.html.classList.toggle("sa-collapsed", colapsar);
        try {
            localStorage.setItem("saCollapsed", colapsar ? "1" : "0");
        } catch (e) {
            // localStorage bloqueado: el estado solo dura esta página
        }
        this.closeFlyouts();
        this.hideTooltip();
        this.syncCollapseButton();
        this.ajustarContenido();
    }

    // Al cambiar el ancho del contenido, tablas y gráficas recalculan su tamaño
    ajustarContenido() {
        clearTimeout(this.timerAjuste);
        this.timerAjuste = setTimeout(() => {
            window.dispatchEvent(new Event("resize"));
            if (window.jQuery?.fn?.dataTable) {
                window.jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }
        }, 260); // un poco más que la transición del ancho (0.22s)
    }

    syncCollapseButton() {
        if (!this.collapseBtn) return;
        const colapsado = this.html.classList.contains("sa-collapsed");
        this.collapseBtn.setAttribute("aria-pressed", String(colapsado));
        this.collapseBtn.setAttribute("aria-label", colapsado ? "Expandir menú" : "Contraer menú");
        this.collapseBtn.dataset.label = colapsado ? "Expandir menú" : "";
    }

    /* ---------- Tooltip (contraído) ---------- */

    showTooltip(el) {
        if (!this.isCollapsed() || !el.dataset.label) return;
        if (el.closest(".sa-group.is-flyout")) return;
        const r = el.getBoundingClientRect();
        this.tooltip.textContent = el.dataset.label;
        this.tooltip.style.left = (r.right + 12) + "px";
        this.tooltip.style.top = (r.top + r.height / 2) + "px";
        this.tooltip.hidden = false;
    }

    hideTooltip() {
        if (this.tooltip) this.tooltip.hidden = true;
    }

    /* ---------- Cajón (móvil) ---------- */

    openDrawer() {
        this.html.classList.add("sa-open");
        this.overlay.hidden = false;
        this.burger?.setAttribute("aria-expanded", "true");
        this.closeBtn?.focus();
    }

    closeDrawer() {
        if (!this.html.classList.contains("sa-open")) return;
        this.html.classList.remove("sa-open");
        this.overlay.hidden = true;
        this.burger?.setAttribute("aria-expanded", "false");
        if (!this.desktop.matches) this.burger?.focus();
    }
}

new SidebarAdmin();
