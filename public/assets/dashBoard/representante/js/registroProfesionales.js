class RegistroProfesionales {

    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        console.log('RegistroProfesionales iniciado');
        this.cacheDom();
        this.bindEvents();
    }

    // =============================
    // CACHEO DE ELEMENTOS
    // =============================
    cacheDom() {
        this.form = document.getElementById("registroProfesional");

        this.btnAgregarEsp = document.getElementById('agregarEspecialidadBtn');
        this.btnAgregarServ = document.getElementById('agregarServicioBtn');

        this.listEspecialidades = document.querySelector('.listEspecialidades');
        this.listServicios = document.querySelector('.listServicios');

        this.inputEspecialidades = document.getElementById('especialidadesInput');
        this.inputServicios = document.getElementById('serviciosInput');

        this.containerEspecialidades = document.getElementById('especialidadesContainer');
        this.containerServicios = document.getElementById('serviciosContainer');
    }

    // =============================
    // EVENTOS
    // =============================
    bindEvents() {

        if (this.btnAgregarEsp && this.listEspecialidades) {
            this.btnAgregarEsp.addEventListener('click', () => {
                this.listEspecialidades.classList.toggle('vew-list-esp');
            });
        }

        if (this.btnAgregarServ && this.listServicios) {
            this.btnAgregarServ.addEventListener('click', () => {
                this.listServicios.classList.toggle('vew-list-serv');
            });
        }

        // Click fuera para cerrar
        document.addEventListener('click', (e) => {

            if (this.listServicios && this.btnAgregarServ) {
                const insideServ =
                    this.listServicios.contains(e.target) ||
                    this.btnAgregarServ.contains(e.target);

                if (!insideServ) {
                    this.listServicios.classList.remove('vew-list-serv');
                }
            }

            if (this.listEspecialidades && this.btnAgregarEsp) {
                const insideEsp =
                    this.listEspecialidades.contains(e.target) ||
                    this.btnAgregarEsp.contains(e.target);

                if (!insideEsp) {
                    this.listEspecialidades.classList.remove('vew-list-esp');
                }
            }
        });

        this.delegateCheckboxEvents();
    }

    // =============================
    // DELEGACIÓN DE EVENTOS
    // =============================
    delegateCheckboxEvents() {

        document.addEventListener('change', (e) => {

            if (e.target.classList.contains('check-servicios')) {
                this.handleServicioChange(e.target);
            }

            if (e.target.classList.contains('check-especialidades')) {
                this.handleEspecialidadChange(e.target);
            }

        });
    }

    // =============================
    // SERVICIOS
    // =============================
    handleServicioChange(checkbox) {

        if (!this.inputServicios) return;

        const servicio = {
            id: checkbox.value,
            name: checkbox.dataset.name
        };

        let servicios = this.getJsonValue(this.inputServicios);

        if (checkbox.checked) {
            servicios.push(servicio);
        } else {
            servicios = servicios.filter(s => s.id !== checkbox.value);
        }

        this.inputServicios.value = JSON.stringify(servicios);
        this.renderServicios();
        this.cargarEspecialidadesPorServicios();
    }

    renderServicios() {

        if (!this.containerServicios || !this.inputServicios) return;

        const servicios = this.getJsonValue(this.inputServicios);
        this.containerServicios.innerHTML = '';

        if (servicios.length === 0) {
            this.containerServicios.style.display = 'none';
            return;
        }

        servicios.forEach(s => {
            const div = document.createElement('div');
            div.className = 'servicio-seleccionado';

            div.innerHTML = `
                <span>${s.name}</span>
                <button type="button" data-id="${s.id}">
                    <i class="bi bi-trash3"></i>
                </button>
            `;

            div.querySelector('button').addEventListener('click', () => {
                this.removeServicio(s.id);
            });

            this.containerServicios.appendChild(div);
        });

        this.containerServicios.style.display = 'grid';
    }

    removeServicio(id) {

        let servicios = this.getJsonValue(this.inputServicios);
        servicios = servicios.filter(s => s.id !== id);

        this.inputServicios.value = JSON.stringify(servicios);
        this.renderServicios();
        this.cargarEspecialidadesPorServicios();
    }

    // =============================
    // ESPECIALIDADES
    // =============================
    handleEspecialidadChange(checkbox) {

        if (!this.inputEspecialidades) return;

        const especialidad = {
            id: checkbox.value,
            name: checkbox.dataset.name
        };

        let especialidades = this.getJsonValue(this.inputEspecialidades);

        if (checkbox.checked) {
            especialidades.push(especialidad);
        } else {
            especialidades = especialidades.filter(e => e.id !== checkbox.value);
        }

        this.inputEspecialidades.value = JSON.stringify(especialidades);
        this.renderEspecialidades();
    }

    renderEspecialidades() {

        if (!this.containerEspecialidades || !this.inputEspecialidades) return;

        const especialidades = this.getJsonValue(this.inputEspecialidades);
        this.containerEspecialidades.innerHTML = '';

        if (especialidades.length === 0) {
            this.containerEspecialidades.style.display = 'none';
            return;
        }

        especialidades.forEach(e => {

            const div = document.createElement('div');
            div.className = 'especialidad-seleccionada';

            div.innerHTML = `
                <span>${e.name}</span>
                <button type="button">
                    <i class="bi bi-trash3"></i>
                </button>
            `;

            div.querySelector('button').addEventListener('click', () => {
                this.removeEspecialidad(e.id);
            });

            this.containerEspecialidades.appendChild(div);
        });

        this.containerEspecialidades.style.display = 'grid';
    }

    removeEspecialidad(id) {

        let especialidades = this.getJsonValue(this.inputEspecialidades);
        especialidades = especialidades.filter(e => e.id !== id);

        this.inputEspecialidades.value = JSON.stringify(especialidades);
        this.renderEspecialidades();
    }

    // =============================
    // FETCH ESPECIALIDADES
    // =============================
    async cargarEspecialidadesPorServicios() {

        if (!this.inputServicios) return;

        const servicios = this.getJsonValue(this.inputServicios);

        if (servicios.length === 0) {
            this.actualizarListaEspecialidades([]);
            return;
        }

        const ids = servicios.map(s => s.id).join(',');

        try {
            const response = await fetch(`/vetwilling/representante/api/especialidades?action=lista&servicio=${ids}`);
            const data = await response.json();

            if (data.status === 'success') {
                this.actualizarListaEspecialidades(data.especialidades);
            } else {
                this.actualizarListaEspecialidades([]);
            }

        } catch (error) {
            console.error('Error cargando especialidades:', error);
            this.actualizarListaEspecialidades([]);
        }
    }

    actualizarListaEspecialidades(lista) {

        if (!this.listEspecialidades) return;

        const ul = this.listEspecialidades.querySelector('ul');
        if (!ul) return;

        ul.innerHTML = '';

        if (lista.length === 0) {
            ul.innerHTML = `<li style="padding:10px;text-align:center;color:#999;">
                Seleccione un servicio
            </li>`;
            return;
        }

        lista.forEach(esp => {

            const li = document.createElement('li');

            li.innerHTML = `
                <input type="checkbox"
                    class="form-check-input check-especialidades"
                    value="${esp.id_especialidad}"
                    data-name="${esp.nombre}">
                <label>${esp.nombre}</label>
            `;

            ul.appendChild(li);
        });
    }

    // =============================
    // UTILIDAD
    // =============================
    getJsonValue(input) {
        try {
            return input.value ? JSON.parse(input.value) : [];
        } catch {
            return [];
        }
    }
}

new RegistroProfesionales();