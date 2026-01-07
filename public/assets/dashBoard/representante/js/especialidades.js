class especialidades {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.bindEvents();
    }

    cacheDom() { 
        this.especialidadId = document.getElementById("id_especialidad");
        this.nombreEspecialidad = document.getElementById("nombre_especialidad");
    }

    bindEvents() {
         document.addEventListener('click', (e) => {
            if (e.target.classList.contains('bi-pencil')) {
                var btnEdit = e.target.parentElement;
                this.viewModalEdit(btnEdit.dataset);
            }
            if (e.target.classList.contains('btn-editar')) {
                this.viewModalEdit(e.target.dataset);
            }
        });
     }

    viewModalEdit(data) {
        this.especialidadId.value = data.id;
        this.nombreEspecialidad.value = data.name;
    }
}

new especialidades();
