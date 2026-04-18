const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

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
        this.btnAgregarServ = document.getElementById('agregarServicioBtn');
        this.listEspecialidades = document.querySelector('.listEspecialidades');
        this.listServicios = document.querySelector('.listServicios');
        this.inputEspecialidades = document.getElementById('especialidadesInput');
        this.inputServicios = document.getElementById('serviciosInput');
        this.especialidades = this.$$('.check-especialidades');
        this.servicios = this.$$('.check-servicios');
        this.containerEspecialidades = document.getElementById('especialidadesContainer');
        this.containerServicios = document.getElementById('serviciosContainer');
    }

    bindEvents() {
        if (this.btnAgregarEsp) {
            this.btnAgregarEsp.onclick = () => this.toggleEspecialidades();
        }

        if (this.btnAgregarServ) {
            this.btnAgregarServ.onclick = () => this.toggleServicios();
        }

        if (this.especialidades) {
            this.especialidades.forEach(e => {
                e.addEventListener('change', (event) => {
                    this.asociarEspecialidades(event);
                });
            });
        }


        if (this.servicios) {
            this.servicios.forEach(e => {
                e.addEventListener('change', (event) => {
                    this.asociarServicios(event);
                    // Cargar especialidades según servicios seleccionados
                    this.cargarEspecialidadesPorServicios();
                });
            });
        }

        // Vaidar si le doy por fuera del contenedor this.listServicios se cierre
        if (this.listServicios) {
            document.addEventListener('click', (event) => {
                const isClickInside = this.listServicios.contains(event.target) || this.btnAgregarServ.contains(event.target);
                if (!isClickInside) {
                    this.listServicios.classList.remove('vew-list-serv');
                }
            });
        }

        if (this.listEspecialidades) {
            document.addEventListener('click', (event) => {
                const isClickInside = this.listEspecialidades.contains(event.target) || this.btnAgregarEsp.contains(event.target);
                if (!isClickInside) {
                    this.listEspecialidades.classList.remove('vew-list-esp');
                }
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
                    const encontrado = listEsp.find(esp => parseInt(esp.id) === parseInt(espId));
                    if (encontrado) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                });
            }
        } else {
             this.cargarEspecialidadesPorServicios();
        }
    }

    toggleServicios() {
        this.listServicios.classList.toggle('vew-list-serv');

        if (this.inputServicios.value.length > 0) {
            const listServ = JSON.parse(this.inputServicios.value);
            if (listServ && listServ.length > 0) {
                this.servicios.forEach(e => {
                    const servId = e.value;
                    const encontrado = listServ.find(serv => parseInt(serv.id) === parseInt(servId));
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

    asociarServicios(element) {
        const valueServicio = element.target.value;
        const nameServicio = element.target.dataset.name;
        const valorServSeleccionadas = this.inputServicios.value;
        const objServ = { id: valueServicio, name: nameServicio };
        if (valorServSeleccionadas.length === 0) {
            const arrayServicios = [objServ];
            this.inputServicios.value = JSON.stringify(arrayServicios);
        } else {
            const listServDeserializadas = JSON.parse(valorServSeleccionadas);
            if (element.target.checked) {
                listServDeserializadas.push(objServ);
                this.inputServicios.value = JSON.stringify(listServDeserializadas);
            } else {
                const listaFiltrada = listServDeserializadas.filter(e => e.id !== valueServicio);
                this.inputServicios.value = JSON.stringify(listaFiltrada);
            }
        }

        this.visualizarServicios();
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
            this.containerEspecialidades.style.display = 'flex';
        } else {
            return;
        }
    }

    visualizarServicios() {
        const serviciosSeleccionados = this.inputServicios.value;
        if (serviciosSeleccionados.length > 0) {
            const listaServicios = JSON.parse(serviciosSeleccionados);
            this.containerServicios.innerHTML = '';
            listaServicios.forEach(e => {
                const divServ = document.createElement('div');
                const titleServ = document.createElement('span');
                divServ.classList.add('servicio-seleccionado');
                titleServ.textContent = e.name;
                divServ.appendChild(titleServ);
                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = '<i class="bi bi-trash3"></i>';
                removeBtn.onclick = () => this.eliminarServicio(e.id);
                divServ.appendChild(removeBtn);
                this.containerServicios.appendChild(divServ);
            });
            this.containerServicios.style.display = 'flex';
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

    eliminarServicio(idServ) {
        const serviciosSeleccionados = this.inputServicios.value;
        if (serviciosSeleccionados.length > 0) {
            const listaServicios = JSON.parse(serviciosSeleccionados);
            const listaActualizada = listaServicios.filter(e => e.id !== idServ);
            this.inputServicios.value = JSON.stringify(listaActualizada);
            this.visualizarServicios();
            // Recargar especialidades al eliminar un servicio
            this.cargarEspecialidadesPorServicios();
        }
    }

    // Método para cargar especialidades según servicios seleccionados
    cargarEspecialidadesPorServicios() {
        const serviciosSeleccionados = this.inputServicios.value;

        // Si no hay servicios seleccionados, limpiar especialidades
        if (!serviciosSeleccionados || serviciosSeleccionados.length === 0) {
            this.actualizarListaEspecialidades([]);
            return;
        }

        // Obtener los IDs de servicios seleccionados
        const listaServicios = JSON.parse(serviciosSeleccionados);
        const idsServicios = listaServicios.map(s => s.id).join(',');

        // Realizar petición AJAX a la API
        fetch(`${baseUrl}/representante/api/especialidades?action=lista&servicio=${idsServicios}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.actualizarListaEspecialidades(data.especialidades);
                } else {
                    console.error('Error al cargar especialidades:', data);
                    this.actualizarListaEspecialidades([]);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                this.actualizarListaEspecialidades([]);
            });
    }

    // Método para actualizar la lista de especialidades en el DOM
    actualizarListaEspecialidades(especialidades) {
        const ulEspecialidades = this.listEspecialidades.querySelector('ul');

        if (!ulEspecialidades) return;

        // Limpiar la lista actual
        ulEspecialidades.innerHTML = '';

        // Si no hay especialidades, mostrar mensaje
        if (especialidades.length === 0) {
            ulEspecialidades.innerHTML = '<li style="padding: 10px; text-align: center; color: #999;">Seleccione un servicio para ver las especialidades disponibles</li>';
            // Limpiar especialidades seleccionadas
            this.inputEspecialidades.value = '';
            this.containerEspecialidades.innerHTML = '';
            this.containerEspecialidades.style.display = 'none';
            return;
        }

        // Obtener las especialidades actualmente seleccionadas
        const especialidadesSeleccionadas = this.inputEspecialidades.value ?
            JSON.parse(this.inputEspecialidades.value) : [];

        // Crear los nuevos items de la lista
        especialidades.forEach(esp => {
            const li = document.createElement('li');
            const checkbox = document.createElement('input');
            checkbox.className = 'form-check-input check-especialidades';
            checkbox.type = 'checkbox';
            checkbox.value = esp.id_especialidad;
            checkbox.id = `idCheck${esp.id_especialidad}`;
            checkbox.dataset.name = esp.nombre;

            // Marcar como checked si ya estaba seleccionada
            const yaSeleccionada = especialidadesSeleccionadas.find(
                e => parseInt(e.id) === parseInt(esp.id_especialidad)
            );
            if (yaSeleccionada) {
                checkbox.checked = true;
            }

            // Agregar evento change
            checkbox.addEventListener('change', (event) => {
                this.asociarEspecialidades(event);
            });

            const label = document.createElement('label');
            label.htmlFor = `idCheck${esp.id_especialidad}`;
            label.textContent = esp.nombre;

            li.appendChild(checkbox);
            li.appendChild(label);
            ulEspecialidades.appendChild(li);
        });

        // Actualizar la referencia de los checkboxes
        this.especialidades = this.$$('.check-especialidades');

        // Filtrar especialidades seleccionadas que ya no están disponibles
        this.filtrarEspecialidadesNoDisponibles(especialidades);
    }

    // Método para eliminar de las seleccionadas las que ya no están disponibles
    filtrarEspecialidadesNoDisponibles(especialidadesDisponibles) {
        const especialidadesSeleccionadas = this.inputEspecialidades.value ?
            JSON.parse(this.inputEspecialidades.value) : [];

        if (especialidadesSeleccionadas.length === 0) return;

        const idsDisponibles = especialidadesDisponibles.map(e => e.id_especialidad.toString());
        const especialidadesFiltradas = especialidadesSeleccionadas.filter(
            esp => idsDisponibles.includes(esp.id.toString())
        );

        this.inputEspecialidades.value = JSON.stringify(especialidadesFiltradas);
        this.visualizarEspecialidades();
    }

}

new RegistroProfesionales();