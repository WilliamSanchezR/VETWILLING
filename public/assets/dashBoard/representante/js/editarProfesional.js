class EditarProfesional {
    constructor() {
        this.$$ = (s) => document.querySelectorAll(s);
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        console.log('Editar profesional Init');
        this.cacheDom();
        this.bindEvents();

    }

    cacheDom() {
        this.btnAgregarEsp = document.getElementById('agregarEspecialidadBtn');
        this.listEspecialidades = document.querySelector('.listEspecialidades');
        this.inputEspecialidades = document.getElementById('especialidadesInput');
        this.inputEspecialidadesCargadas = document.getElementById('especialidadesCargadasInput');
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

        if (this.inputEspecialidades.value.length > 0 || this.inputEspecialidadesCargadas.value.length > 0) {
            const listEsp = this.inputEspecialidades.value.length > 0 ? JSON.parse(this.inputEspecialidades.value) : [];
            const listEspCargadas = this.inputEspecialidadesCargadas.value.length > 0 ? JSON.parse(this.inputEspecialidadesCargadas.value) : [];

            const listaCombinada = [...listEspCargadas, ...listEsp];

            if (listaCombinada && listaCombinada.length > 0) {
                this.especialidades.forEach(e => {
                    const espId = e.value;
                    const encontrado = listaCombinada.some(esp => parseInt(esp.id_especialidad) === parseInt(espId));
                    if (encontrado) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                });
            }
        }

        // Agregar lógica para deshabilitar especialidades ya cargadas
        if (this.inputEspecialidadesCargadas.value.length > 0) {
            const listEspCargadas = JSON.parse(this.inputEspecialidadesCargadas.value);
            if (listEspCargadas && listEspCargadas.length > 0) {
                this.especialidades.forEach(e => {
                    const espId = e.value;
                    const encontrado = listEspCargadas.find(esp => esp.id_especialidad === parseInt(espId));
                    if (encontrado) {
                        e.disabled = true;
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
        const especialidadesCargadas = this.inputEspecialidadesCargadas.value;
        if (especialidadesSeleccionadas.length > 0) {
            const listaEspecialidades = JSON.parse(especialidadesSeleccionadas);
            const listaCargadas = especialidadesCargadas.length > 0 ? JSON.parse(especialidadesCargadas) : [];
            this.containerEspecialidades.innerHTML = '';

            const listaCombinada = [...listaCargadas, ...listaEspecialidades.filter(e => !listaCargadas.some(cargada => cargada.id === e.id))];

            listaCombinada.forEach(e => {
                const divEsp = document.createElement('div');
                const titleEsp = document.createElement('span');
                divEsp.classList.add('especialidad-seleccionada');
                titleEsp.textContent = e.name;
                divEsp.appendChild(titleEsp);
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
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
        const especialidadesCargadas = this.inputEspecialidadesCargadas.value;
        const listaEspecialidades = especialidadesSeleccionadas.length > 0 ? JSON.parse(especialidadesSeleccionadas) : [];
        const listaCargadas = especialidadesCargadas.length > 0 ? JSON.parse(especialidadesCargadas) : [];

        if (listaEspecialidades.length > 0 && listaEspecialidades.some(e => e.id === idEsp)) {
            const listaActualizada = listaEspecialidades.filter(e => e.id !== idEsp);
            this.inputEspecialidades.value = JSON.stringify(listaActualizada);
            this.visualizarEspecialidades();
        } else if (listaCargadas.length > 0 && listaCargadas.some(e => e.id === idEsp)) {
            const ruta = window.location.href;
            const idProfesional = ruta.split('id=')[1].split('&')[0];

            const eliminarUrl = `${window.location.origin}/vetwilling/representante/eliminar-esp-profesional?action=eliminarEspProfesional&id_profesional=${idProfesional}&id_especialidad=${idEsp}`;

            window.location.href = eliminarUrl;
        }
    }

}

new EditarProfesional();