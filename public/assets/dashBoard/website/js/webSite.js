// Archivo JavaScript para la página principal del sitio web de VetWilling. Este archivo se encarga de manejar la interacción del usuario con los botones de solicitud de prueba, almacenando el plan seleccionado en el localStorage y redirigiendo al usuario a la página de registro.
class WebSite {

    constructor() {
        this.init();
        this.bindEvents();
    }

    init() {
        console.log("WebSite JS loaded");
        this.cacheDom();
        // Limpiamos el localStorage para evitar conflictos con datos anteriores
        localStorage.clear();
    }

    cacheDom() {
        this.btns_planes = document.querySelectorAll(".btn_planes");
    }

    bindEvents(){
        this.btns_planes.forEach(btn => {
            btn.addEventListener("click", (e) => {
                const plan = e.currentTarget.getAttribute("data-plan");
                localStorage.setItem("Bawm_Plan_Select", plan);
                // Redirigimos al usuario a la página de registro
                window.location.href = "registro";
            });
        });
    }

    
}

new WebSite();