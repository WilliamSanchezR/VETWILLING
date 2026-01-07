class RegistroProfesionales {
    constructor() {
        this.$$ = (s) => document.querySelectorAll(s);
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        console.log('Registro profesionales Init');
        this.cacheDom();
        this.bindEvents();
    }

    cacheDom() {
        this.formRegistroProf = document.getElementById("registroProfesional");
        this.btnAgregarEsp = document.getElementById('agregarEspecialidadBtn');
        this.listEspecialidades = document.querySelector('.listEspecialidades');
        this.inputEspecialidades = document.getElementById('especialidadesInput');
        this.especialidades = this.$$('.check-especialidades');
        this.containerEspecialidades = document.getElementById('especialidadesContainer');
    }

    bindEvents() {
        if (this.btnAgregarEsp) {
            this.btnAgregarEsp.onclick = () => this.toggleEspecialidades();
        }

        if (this.especialidades) {
            this.especialidades.forEach(e => {
                e.addEventListener('change', (event) => {
                    this.asociarEspecialidades(event);
                });
            });
        }
    }

    toggleEspecialidades() {
        this.listEspecialidades.classList.toggle('vew-list-esp');

        if (this.inputEspecialidades.value.length > 0) {
            const listEsp = JSON.parse(this.inputEspecialidades.value);
            if (listEsp && listEsp.length > 0) {
                this.especialidades.forEach(e => {
                    const espId = e.value;
                    const encontrado = listEsp.find(esp => esp.id === espId);
                    if (encontrado) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                });
            }
        }
    }

    asociarEspecialidades(element) {
        const valueEspecialidad = element.target.value;
        const nameEspecialidad = element.target.dataset.name;
        const valorEspSeleccionadas = this.inputEspecialidades.value;
        const objEspe = { id: valueEspecialidad, name: nameEspecialidad };

        if (valorEspSeleccionadas.length === 0) {
            const arrayEspecialidades = [objEspe];
            this.inputEspecialidades.value = JSON.stringify(arrayEspecialidades);
        } else {
            const listEspDeserializadas = JSON.parse(valorEspSeleccionadas);
            if (element.target.checked) {
                listEspDeserializadas.push(objEspe);
                this.inputEspecialidades.value = JSON.stringify(listEspDeserializadas);
            } else {
                const listaFiltrada = listEspDeserializadas.filter(e => e.id !== valueEspecialidad);
                this.inputEspecialidades.value = JSON.stringify(listaFiltrada);
            }
        }

        this.visualizarEspecialidades();
    }

    visualizarEspecialidades() {
        const especialidadesSeleccionadas = this.inputEspecialidades.value;
        if (especialidadesSeleccionadas.length > 0) {
            const listaEspecialidades = JSON.parse(especialidadesSeleccionadas);
            this.containerEspecialidades.innerHTML = '';
            listaEspecialidades.forEach(e => {
                const divEsp = document.createElement('div');
                const titleEsp = document.createElement('span');
                divEsp.classList.add('especialidad-seleccionada');
                titleEsp.textContent = e.name;
                divEsp.appendChild(titleEsp);
                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '<i class="bi bi-trash3"></i>';
                removeBtn.onclick = () => this.eliminarEspecialidad(e.id);
                divEsp.appendChild(removeBtn);

                this.containerEspecialidades.appendChild(divEsp);
            });
            this.containerEspecialidades.style.display = 'grid';
        } else {
            return;
        }
    }

    eliminarEspecialidad(idEsp) {
        const especialidadesSeleccionadas = this.inputEspecialidades.value;
        if (especialidadesSeleccionadas.length > 0) {
            const listaEspecialidades = JSON.parse(especialidadesSeleccionadas);
            const listaActualizada = listaEspecialidades.filter(e => e.id !== idEsp);
            this.inputEspecialidades.value = JSON.stringify(listaActualizada);
            this.visualizarEspecialidades();
        }
    }

}

new RegistroProfesionales();