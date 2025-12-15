// Clase con las funiones para ocultar la veterinaria según el rol
class registroUsuario {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init(){
        this.cacheDom();
        this.bindEvents();

        console.log("✅ Registro de usuario cargado correctamente");
    }

    cacheDom() {
        this.rol = document.getElementById("rol");
        this.veterinaria = document.getElementById("input-veterinaria");
    }

    bindEvents() {
        if (this.rol) {
            this.rol.addEventListener("change", () => this.toggleVeterinariaField());
        }
    }

    toggleVeterinariaField() {
        const selectedRol = this.rol.value;
        if (parseInt(selectedRol) === 4) {
            this.veterinaria.style.display = "block";
        } else {
            this.veterinaria.style.display = "none";
        }
    }
}
new registroUsuario();