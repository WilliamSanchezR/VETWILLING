class EditarServicio {
    constructor() {

        this.$$ = (s) => document.querySelectorAll(s);
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        console.log('Registro Servicio Init');
        this.cacheDom();
        this.bindEvents();
        this.consultarHorariosExistentes();
    }


    cacheDom() {
        this.listDiasSelected = [];
        this.listDBDiasSelected = [];
        this.listDiasCargadosDB = [];
        this.listDias = this.$$('.btn-check');
        this.horariosInput = document.getElementById('horariosInput');
        this.btnAgregarHorario = document.querySelector('.btn-agregar-horario');
        this.hora_inicio_1 = document.getElementById('hora_inicio_1');
        this.hora_fin_1 = document.getElementById('hora_fin_1');
        this.hora_inicio_2 = document.getElementById('hora_inicio_2');
        this.hora_fin_2 = document.getElementById('hora_fin_2');
        this.tblHorariosBody = document.getElementById('horariosBody');
    }

    bindEvents() {
        if (this.btnAgregarHorario) {
            this.btnAgregarHorario.onclick = () => this.agregarHorario();
        }
    }

    async consultarHorariosExistentes() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const idServicio = urlParams.get('id');
            const response = await fetch(`/vetwilling/representante/obtener-horarios-servicio?action=obtener_horarios&id=${idServicio}`);
            const result = await response.json();

            if (result.status === 'success') {
                if (Array.isArray(result.horarios)) {
                    let listDiasCargados = [];
                    result.horarios.forEach(horario => {
                        if (!listDiasCargados.includes(horario.dia_semana.toString()) && listDiasCargados.some(d => d.dia === horario.dia_semana.toString() && !d.horario_2)) {
                            const diaObj = listDiasCargados.find(d => d.dia === horario.dia_semana.toString());
                            diaObj.id.push(horario.id_horario_servicio);
                            diaObj.horario_2 = {
                                hora_inicio: horario.hora_inicio,
                                hora_fin: horario.hora_fin
                            };

                            listDiasCargados = listDiasCargados.map(d => d.dia === horario.dia_semana.toString() ? diaObj : d);
                        } else {
                            const diaObj = {
                                id: [horario.id_horario_servicio],
                                dia: horario.dia_semana.toString(),
                                horario_1: {
                                    hora_inicio: horario.hora_inicio,
                                    hora_fin: horario.hora_fin
                                },
                                horario_2: null
                            };

                            listDiasCargados.push(diaObj);
                        }
                    });

                    this.listDiasCargadosDB = result.horarios;
                    this.listDBDiasSelected = listDiasCargados;
                    this.actualizarTablaHorarios();
                }
            }
        } catch (error) {
            console.error('Error al cargar horarios:', error);
            return [];
        }
    }

    actualizarTablaHorarios() {
        this.tblHorariosBody.innerHTML = '';
        const combinedList = [...this.listDBDiasSelected, ...this.listDiasSelected];
        if (combinedList.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'text-center';
            cell.textContent = 'No hay horarios agregados.';
            row.appendChild(cell);
            this.tblHorariosBody.appendChild(row);
            return;
        }
        combinedList.forEach(diaObj => {
            const row = document.createElement('tr');
            const diaCell = document.createElement('td');
            diaCell.textContent = (diaObj.dia) === '1' ? 'Lunes' :
                (diaObj.dia) === '2' ? 'Martes' :
                    (diaObj.dia) === '3' ? 'Miércoles' :
                        (diaObj.dia) === '4' ? 'Jueves' :
                            (diaObj.dia) === '5' ? 'Viernes' :
                                (diaObj.dia) === '6' ? 'Sábado' :
                                    (diaObj.dia) === '7' ? 'Domingo' : diaObj.dia;

            const horario1Cell = document.createElement('td');
            const horario2Cell = document.createElement('td');
            // Formateamos las horas en formato 12 horas con AM/PM

            const formatTime = (time) => {
                const [hour, minute] = time.split(':');
                const date = new Date();
                date.setHours(parseInt(hour), parseInt(minute));
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            };

            const horaInicio = formatTime(diaObj.horario_1.hora_inicio);
            const horaFin = formatTime(diaObj.horario_1.hora_fin);
            const inicioHour = parseInt(diaObj.horario_1.hora_inicio.split(':')[0]);
            const esAnteDeMedioDia = inicioHour < 12;

            if (esAnteDeMedioDia) {
                horario1Cell.textContent = `${horaInicio} - ${horaFin}`;
            } else if (!diaObj.horario_2 && !esAnteDeMedioDia) {
                horario1Cell.textContent = 'N/A';
                horario2Cell.textContent = `${horaInicio} - ${horaFin}`;
            } else {
                horario1Cell.textContent = `${horaInicio} - ${horaFin}`;
            }

            if (diaObj.horario_2) {
                horario2Cell.textContent = diaObj.horario_2?.hora_inicio && diaObj.horario_2?.hora_fin ?
                    `${formatTime(diaObj.horario_2.hora_inicio)} - ${formatTime(diaObj.horario_2.hora_fin)}` : 'N/A';
            } else if (horario2Cell.textContent === '') {
                horario2Cell.textContent = 'N/A';
            }

            const eliminarBtn = document.createElement('button');
            eliminarBtn.innerHTML = '<i class="bi bi-trash"></i>';
            eliminarBtn.className = 'btn btn-danger btn-sm';
            eliminarBtn.type = 'button';
            eliminarBtn.setAttribute('aria-label', `Eliminar horario de ${diaObj.dia}`);
            eliminarBtn.onclick = () => this.eliminarHorario(diaObj);
            const eliminarCell = document.createElement('td');
            eliminarCell.appendChild(eliminarBtn);

            row.appendChild(diaCell);
            row.appendChild(horario1Cell);
            row.appendChild(horario2Cell);
            row.appendChild(eliminarCell);
            this.tblHorariosBody.appendChild(row);
        });
    }

    agregarHorario() {
        const selectedDias = [];
        let validacionReptidos = false;
        const combinedList = [...this.listDBDiasSelected, ...this.listDiasSelected];
        this.listDias.forEach(dia => {
            if (dia.checked) {
                selectedDias.push(dia.value);
            }
        });

        if (selectedDias.length === 0) {
            Swal.fire({
                title: 'Atención',
                text: 'Por favor, seleccione al menos un día para agregar el horario.',
                icon: 'warning',
                showCancelButton: false,
                cancelButtonText: 'Cancelar'
            });
            return;
        }

        if (!this.hora_inicio_1.value || !this.hora_fin_1.value) {
            Swal.fire({
                title: 'Atención',
                text: 'Por favor, ingrese la hora de inicio y fin para el primer horario.',
                icon: 'warning',
                showCancelButton: false,
                cancelButtonText: 'Cancelar'
            });
            return;
        }

        if (this.hora_inicio_2.value && !this.hora_fin_2.value) {
            Swal.fire({
                title: 'Atención',
                text: 'Por favor, ingrese la hora de inicio y fin para el segundo horario.',
                icon: 'warning',
                showCancelButton: false,
                cancelButtonText: 'Cancelar'
            });
            return;
        }

        if (combinedList.length > 0) {

            combinedList.forEach(diaObj => {
                // validamos si el dia ya fue agregado
                if (selectedDias.includes(diaObj.dia)) {
                    // validamos si los horarios coinciden
                    const mismoHorario1 = diaObj.horario_1.hora_inicio === this.hora_inicio_1.value &&
                        diaObj.horario_1.hora_fin === this.hora_fin_1.value;

                    if (mismoHorario1) {
                        validacionReptidos = true;
                        return;
                    }
                    // Validamos si el horario se cruza con el ya existente 
                    const seCruzaHorario1 = ((this.hora_inicio_1?.value < diaObj.horario_1?.hora_fin) &&
                        (this.hora_fin_1.value > diaObj.horario_1?.hora_inicio));
                    if (seCruzaHorario1) {
                        validacionReptidos = true;
                        return;
                    }

                    // Valifamos si el primer horario no se cruce con el segundo horario ya existente
                    const seCruzaHorario1_2 = ((this.hora_inicio_1?.value < diaObj.horario_2?.hora_fin) &&
                        (this.hora_fin_1.value > diaObj.horario_2?.hora_inicio));

                    if (seCruzaHorario1_2) {
                        validacionReptidos = true;
                        return;
                    }

                    // Validamos el segundo horario si existe
                    if (this.hora_inicio_2?.value && this.hora_fin_2?.value) {
                        const mismoHorario2 = diaObj.horario_2.hora_inicio === this.hora_inicio_2.value &&
                            diaObj.horario_2.hora_fin === this.hora_fin_2.value;
                        if (mismoHorario2) {
                            validacionReptidos = true;
                            return;
                        }

                        const seCruzaHorario2 = (this.hora_inicio_2?.value < diaObj.horario_2?.hora_fin) &&
                            (this.hora_fin_2?.value > diaObj.horario_2?.hora_inicio);
                        if (seCruzaHorario2) {
                            validacionReptidos = true;
                            return;
                        }

                        // Valifamos si el segundo horario no se cruce con el primer horario ya existente
                        const seCruzaHorario2_1 = ((this.hora_inicio_2?.value < diaObj.horario_1?.hora_fin) &&
                            (this.hora_fin_2?.value > diaObj.horario_1?.hora_inicio));
                        if (seCruzaHorario2_1) {
                            validacionReptidos = true;
                            return;
                        }
                    }
                }

            });

        }

        if (validacionReptidos) {
            Swal.fire({
                title: 'Atención',
                text: `El horario que intenta agregar se cruza con un horario ya existente para uno de los días seleccionados.`,
                icon: 'warning',
                showCancelButton: false,
                cancelButtonText: 'Cancelar'
            });
            return;
        }


        const horarioData = selectedDias.map(dia => {
            const horario = {
                dia: dia,
                horario_1: {
                    hora_inicio: this.hora_inicio_1.value,
                    hora_fin: this.hora_fin_1.value
                },
                horario_2: this.hora_inicio_2.value ? {
                    hora_inicio: this.hora_inicio_2.value,
                    hora_fin: this.hora_fin_2.value
                } : null
            };
            return horario;
        });

        this.listDiasSelected.push(...horarioData);

        this.actualizarTablaHorarios();
        this.limpiarCampos();
        this.horariosInput.value = JSON.stringify(this.listDiasSelected);
    }

    limpiarCampos() {
        this.listDias.forEach(dia => {
            dia.checked = false;
        });
        this.hora_inicio_1.value = '';
        this.hora_fin_1.value = '';
        this.hora_inicio_2.value = '';
        this.hora_fin_2.value = '';
    }

    eliminarHorario(dia) {
        if (dia.id && dia.id.length > 0) {
            // Si el día tiene IDs asociados, significa que está en la base de datos
            Swal.fire({
                title: 'Confirmar eliminación',
                text: `¿Está seguro de que desea eliminar el horario para el día seleccionado? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {

                if (result.isConfirmed) {
                    this.eliminarHorarioServidor(dia);
                }
            });
        } else {
            this.listDiasSelected = this.listDiasSelected.filter(diaObj => diaObj.dia.toString() !== dia.dia.toString());
            this.horariosInput.value = JSON.stringify(this.listDiasSelected);
            this.actualizarTablaHorarios();
        }
    }

    async eliminarHorarioServidor(dia) {
        try {
            for (const id_horario of dia.id) {
                const response = await fetch(`/vetwilling/representante/actualizar-servicio?action=eliminar_horarios&id=${id_horario}`, {
                    method: 'GET',
                });
                const result = await response.json();
                if (result.status !== 'success') {
                    throw new Error('Error al eliminar el horario en el servidor');
                } else {
                    this.listDBDiasSelected = this.listDBDiasSelected.filter(diaObj => diaObj.dia.toString() !== dia.dia.toString());
                    this.horariosInput.value = JSON.stringify(this.listDiasSelected);
                    this.actualizarTablaHorarios();
                }
            }

        } catch (error) {
            console.error('Error al eliminar el horario:', error);
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al eliminar el horario. Por favor, intente nuevamente.',
                icon: 'error',
                showCancelButton: false,
                cancelButtonText: 'Cancelar'
            });
        }
    }

}

new EditarServicio();
