// Archivo JavaScript para la página principal del sitio web de VetWilling. Maneja los botones de solicitud de prueba (guarda el plan seleccionado y redirige al registro) y las interacciones de la landing: navbar al hacer scroll, enlace activo, aparición de secciones y movimiento reducido.
class WebSite {

    constructor() {
        this.cacheDom();
        this.init();
        this.bindEvents();
    }

    init() {
        // Solo limpiamos el plan anterior; borrar todo el localStorage afectaba otras partes de la app
        this.storage("remove", "Bawm_Plan_Select");
        this.onScroll();
        this.initReveal();
        this.initActiveLink();
        this.respectReducedMotion();
    }

    cacheDom() {
        this.btns_planes = document.querySelectorAll(".btn_planes");
        this.navbar = document.querySelector(".custom-navbar");
        this.whatsapp = document.querySelector(".whatsapp-float");
        this.navCollapse = document.getElementById("navbarNav");
        this.navLinks = document.querySelectorAll(".custom-navbar .nav-link[href^='#']");
        this.teamCards = document.querySelectorAll(".team-card");
        this.canHover = window.matchMedia("(hover: hover)").matches;
        this.reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    }

    bindEvents() {
        this.btns_planes.forEach(btn => {
            btn.addEventListener("click", (e) => {
                const plan = e.currentTarget.getAttribute("data-plan");
                this.storage("set", "Bawm_Plan_Select", plan);
                // Redirigimos al usuario a la página de registro
                window.location.href = "registro";
            });
        });

        window.addEventListener("scroll", () => this.onScroll(), { passive: true });

        // Tarjetas del equipo: se voltean al tocarlas o con el botón (teclado)
        this.teamCards.forEach(card => {
            card.addEventListener("click", (e) => {
                if (e.target.closest("a")) return;
                // Con mouse ya se voltea por hover; aquí solo atendemos toques y teclado
                if (this.canHover && e.detail !== 0) return;
                const flipped = card.classList.toggle("is-flipped");
                card.querySelector(".team-front .team-flip").setAttribute("aria-expanded", flipped);

                // Si se activó con teclado, el foco pasa a la cara visible
                if (e.detail === 0) {
                    const face = flipped ? ".team-back .team-flip" : ".team-front .team-flip";
                    card.querySelector(face).focus({ preventScroll: true });
                }
            });
        });

        // En móvil, cerramos el menú al elegir una sección
        this.navLinks.forEach(link => {
            link.addEventListener("click", () => {
                if (this.navCollapse.classList.contains("show")) {
                    bootstrap.Collapse.getOrCreateInstance(this.navCollapse).hide();
                }
            });
        });
    }

    storage(action, key, value) {
        try {
            if (action === "set") localStorage.setItem(key, value);
            if (action === "remove") localStorage.removeItem(key);
        } catch (e) {
            // localStorage puede estar bloqueado (modo privado); el registro funciona igual sin plan preseleccionado
        }
    }

    onScroll() {
        this.navbar.classList.toggle("is-scrolled", window.scrollY > 10);
        // El botón flotante aparece al pasar el hero, donde ya hay un CTA de WhatsApp
        this.whatsapp?.classList.toggle("is-visible", window.scrollY > 500);
    }

    initReveal() {
        const items = document.querySelectorAll(".reveal");

        if (this.reduceMotion || !("IntersectionObserver" in window)) {
            items.forEach(el => el.classList.add("is-visible"));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });

        items.forEach(el => observer.observe(el));
    }

    initActiveLink() {
        if (!("IntersectionObserver" in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                this.navLinks.forEach(link => {
                    const active = link.getAttribute("href") === "#" + entry.target.id;
                    link.classList.toggle("active", active);
                    if (active) link.setAttribute("aria-current", "true");
                    else link.removeAttribute("aria-current");
                });
            });
        }, { rootMargin: "-45% 0px -50% 0px" });

        this.navLinks.forEach(link => {
            const section = document.querySelector(link.getAttribute("href"));
            if (section) observer.observe(section);
        });
    }

    respectReducedMotion() {
        // Sin carrusel automático para quien pidió menos movimiento
        if (!this.reduceMotion) return;
        const carousel = document.getElementById("heroCarousel");
        if (carousel) bootstrap.Carousel.getOrCreateInstance(carousel, { ride: false, interval: false }).pause();
    }
}

new WebSite();
