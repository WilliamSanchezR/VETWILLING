// Archivo JavaScript de la página de inicio de sesión de VetWilling. Maneja: la mascota que reacciona al formulario, mostrar/ocultar contraseña, aviso de Bloq Mayús, validación de campos antes de enviar, estado de carga del botón y el carrusel de imágenes del panel derecho.
class LoginPage {

    constructor() {
        this.cacheDom();
        this.bindEvents();
        this.initCarousel();
        this.initParallax();
        this.reaccionarAErrorDelServidor();
    }

    cacheDom() {
        this.main = document.querySelector(".auth");
        this.form = document.querySelector(".login");
        this.email = document.getElementById("correo");
        this.emailWrap = this.email.closest(".input-wrap");
        this.clave = document.getElementById("clave");
        this.toggle = document.getElementById("toggleClave");
        this.capsAviso = document.getElementById("caps-aviso");
        this.btn = document.getElementById("btn_ingresar");
        this.btnText = this.btn.querySelector(".btn-text");
        this.mascota = document.getElementById("mascota");
        this.visual = document.querySelector(".auth-visual");
        this.slides = document.querySelectorAll(".bg-slide");
        this.dots = document.querySelectorAll(".carousel-dots button");
        this.reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        this.punteroFino = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    }

    bindEvents() {
        this.toggle.addEventListener("click", () => this.toggleClave());

        // Aviso de Bloq Mayús mientras se escribe la contraseña
        ["keydown", "keyup"].forEach(evento => {
            this.clave.addEventListener(evento, (e) => {
                if (typeof e.getModifierState === "function") {
                    this.capsAviso.hidden = !e.getModifierState("CapsLock");
                }
            });
        });

        // Al corregir un campo, se limpia su error y la mascota se anima de nuevo
        [this.email, this.clave].forEach(input => {
            input.addEventListener("input", () => {
                this.limpiarError(input);
                this.dejarDeEstarTriste();
            });
        });

        // Correo: la mascota sigue con la mirada lo que se escribe y aparece el check si es válido
        ["focus", "input", "click", "keyup"].forEach(evento => {
            this.email.addEventListener(evento, () => this.mirarCorreo());
        });
        this.email.addEventListener("input", () => this.validarCorreoEnVivo());
        this.email.addEventListener("blur", () => this.estadoMascota());
        this.validarCorreoEnVivo();

        // Contraseña: la mascota se tapa los ojos
        this.clave.addEventListener("focus", () => this.estadoMascota());
        this.clave.addEventListener("blur", (e) => {
            this.capsAviso.hidden = true;
            // Si el foco pasa al botón del ojo, la mascota sigue tapándose
            if (e.relatedTarget !== this.toggle) this.estadoMascota();
        });

        // Cuando no hay campo activo, la mascota sigue el puntero
        if (this.punteroFino) {
            document.addEventListener("mousemove", (e) => this.seguirPuntero(e));
        }

        this.form.addEventListener("submit", (e) => this.onSubmit(e));
        this.btn.addEventListener("pointerdown", (e) => this.ripple(e));

        // Si el usuario vuelve con el botón "atrás", el botón no debe quedar cargando
        window.addEventListener("pageshow", () => {
            this.setCargando(false);
            this.estadoMascota();
        });
    }

    /* ---------- Mascota ---------- */

    setEstado(estado) {
        this.mascota.classList.remove("mira", "tapa", "espia", "triste", "feliz");
        if (estado) this.mascota.classList.add(estado);
    }

    mirar(x, y) {
        this.mascota.style.setProperty("--px", x.toFixed(1) + "px");
        this.mascota.style.setProperty("--py", y.toFixed(1) + "px");
    }

    // Decide el estado según dónde está el foco
    estadoMascota() {
        if (this.triste) return;
        if (this.btn.classList.contains("is-loading")) {
            this.setEstado("feliz");
            return;
        }
        const foco = document.activeElement;
        if (foco === this.clave || foco === this.toggle) {
            this.setEstado(this.clave.type === "text" ? "espia" : "tapa");
            this.mirar(0, 3);
            return;
        }
        if (foco === this.email) {
            this.mirarCorreo();
            return;
        }
        this.setEstado(null);
        this.mirar(0, 0);
    }

    // La mirada recorre el campo según la posición del cursor de texto
    mirarCorreo() {
        if (this.triste || document.activeElement !== this.email) return;
        this.setEstado("mira");
        const largo = Math.max(this.email.value.length, 1);
        const posicion = this.email.selectionStart ?? this.email.value.length;
        const avance = Math.min(posicion / Math.max(largo, 28), 1);
        this.mirar(-4.5 + avance * 9, 4);
    }

    seguirPuntero(e) {
        const foco = document.activeElement;
        if (foco === this.email || foco === this.clave || foco === this.toggle) return;
        if (this.triste || this.mascota.classList.contains("feliz")) return;

        const r = this.mascota.getBoundingClientRect();
        const dx = e.clientX - (r.left + r.width / 2);
        const dy = e.clientY - (r.top + r.height / 2);
        const distancia = Math.hypot(dx, dy) || 1;
        const alcance = Math.min(distancia / 120, 1) * 4.5;
        this.mirar((dx / distancia) * alcance, (dy / distancia) * alcance);
    }

    ponerTriste() {
        this.triste = true;
        this.setEstado("triste");
        this.mirar(0, 4);
        clearTimeout(this.timerTriste);
        this.timerTriste = setTimeout(() => this.dejarDeEstarTriste(), 2200);
    }

    dejarDeEstarTriste() {
        if (!this.triste) return;
        clearTimeout(this.timerTriste);
        this.triste = false;
        this.estadoMascota();
    }

    reaccionarAErrorDelServidor() {
        if (this.main.dataset.error) this.ponerTriste();
    }

    /* ---------- Formulario ---------- */

    toggleClave() {
        const mostrar = this.clave.type === "password";
        this.clave.type = mostrar ? "text" : "password";
        this.toggle.setAttribute("aria-pressed", String(mostrar));
        this.toggle.setAttribute("aria-label", mostrar ? "Ocultar contraseña" : "Mostrar contraseña");
        this.toggle.querySelector("i").className = mostrar ? "bi bi-eye-slash" : "bi bi-eye";
        this.clave.focus();
        this.estadoMascota();
    }

    validarCorreoEnVivo() {
        const valido = this.email.value.trim() !== "" && this.email.checkValidity();
        this.emailWrap.classList.toggle("is-valid", valido);
    }

    onSubmit(e) {
        const errores = [];
        const correo = this.email.value.trim();

        if (correo === "") {
            errores.push([this.email, "Ingresa tu correo electrónico."]);
        } else if (!this.email.checkValidity()) {
            errores.push([this.email, "Ingresa un correo válido, por ejemplo nombre@correo.com."]);
        }

        if (this.clave.value === "") {
            errores.push([this.clave, "Ingresa tu contraseña."]);
        }

        if (errores.length > 0) {
            e.preventDefault();
            errores.forEach(([input, mensaje]) => this.mostrarError(input, mensaje));
            errores[0][0].focus();
            this.ponerTriste();
            return;
        }

        this.setCargando(true);
        this.estadoMascota();
    }

    mostrarError(input, mensaje) {
        const error = document.getElementById(input.id + "-error");
        error.innerHTML = '<i class="bi bi-exclamation-circle" aria-hidden="true"></i>' + mensaje;
        error.hidden = false;
        input.setAttribute("aria-invalid", "true");
    }

    limpiarError(input) {
        const error = document.getElementById(input.id + "-error");
        error.hidden = true;
        input.removeAttribute("aria-invalid");
    }

    setCargando(cargando) {
        this.btn.classList.toggle("is-loading", cargando);
        this.btn.setAttribute("aria-busy", String(cargando));
        this.btnText.textContent = cargando ? "Ingresando…" : "Ingresar";
    }

    // Onda que sale desde el punto donde se presionó el botón
    ripple(e) {
        if (this.reduceMotion) return;
        const r = this.btn.getBoundingClientRect();
        const size = Math.max(r.width, r.height) * 2;
        const onda = document.createElement("span");
        onda.className = "ripple";
        onda.style.width = onda.style.height = size + "px";
        onda.style.left = (e.clientX - r.left - size / 2) + "px";
        onda.style.top = (e.clientY - r.top - size / 2) + "px";
        this.btn.appendChild(onda);
        onda.addEventListener("animationend", () => onda.remove());
    }

    /* ---------- Panel visual ---------- */

    initCarousel() {
        if (this.slides.length < 2) return;
        this.slideActual = 0;

        this.dots.forEach((dot, i) => {
            dot.addEventListener("click", () => {
                this.irASlide(i);
                this.reiniciarCarrusel();
            });
        });

        if (this.reduceMotion) return;
        this.reiniciarCarrusel();

        // Se pausa mientras el mouse está sobre las fotos
        this.visual.addEventListener("mouseenter", () => clearInterval(this.timerCarrusel));
        this.visual.addEventListener("mouseleave", () => this.reiniciarCarrusel());
    }

    irASlide(i) {
        this.slides[this.slideActual].classList.remove("active");
        this.dots[this.slideActual]?.classList.remove("active");
        this.slideActual = i;
        this.slides[i].classList.add("active");
        this.dots[i]?.classList.add("active");
    }

    reiniciarCarrusel() {
        if (this.reduceMotion) return;
        clearInterval(this.timerCarrusel);
        this.timerCarrusel = setInterval(() => {
            this.irASlide((this.slideActual + 1) % this.slides.length);
        }, 5500); // Cambia cada 5.5 segundos
    }

    // Efecto de profundidad: la foto y el recuadro se mueven en sentidos opuestos
    initParallax() {
        if (this.reduceMotion || !this.punteroFino) return;

        this.visual.addEventListener("mousemove", (e) => {
            const r = this.visual.getBoundingClientRect();
            const mx = ((e.clientX - r.left) / r.width) * 2 - 1;
            const my = ((e.clientY - r.top) / r.height) * 2 - 1;
            this.visual.style.setProperty("--mx", mx.toFixed(3));
            this.visual.style.setProperty("--my", my.toFixed(3));
        });

        this.visual.addEventListener("mouseleave", () => {
            this.visual.style.setProperty("--mx", 0);
            this.visual.style.setProperty("--my", 0);
        });
    }
}

new LoginPage();
