class RegistroServicio {
    constructor() {

        this.$$ = (s) => document.querySelectorAll(s);
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        console.log('Registro Servicio Init');
        this.cacheDom();
        this.bindEvents();
    }


    cacheDom() {
        this.listDiasSelected = [];
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

    agregarHorario() {
        const selectedDias = [];
        let validacionReptidos = false;
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

        if (this.listDiasSelected.length > 0) {

            this.listDiasSelected.forEach(diaObj => {
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
                    const seCruzaHorario1 = ((this.hora_inicio_1.value < diaObj.horario_1.hora_fin) &&
                        (this.hora_fin_1.value > diaObj.horario_1.hora_inicio));
                    if (seCruzaHorario1) {
                        validacionReptidos = true;
                        return;
                    }

                    // Valifamos si el primer horario no se cruce con el segundo horario ya existente
                    const seCruzaHorario1_2 = ((this.hora_inicio_1.value < diaObj.horario_2?.hora_fin) &&
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

                        const seCruzaHorario2 = (this.hora_inicio_2.value < diaObj.horario_2.hora_fin) &&
                            (this.hora_fin_2.value > diaObj.horario_2.hora_inicio);
                        if (seCruzaHorario2) {
                            validacionReptidos = true;
                            return;
                        }

                        // Valifamos si el segundo horario no se cruce con el primer horario ya existente
                        const seCruzaHorario2_1 = ((this.hora_inicio_2.value < diaObj.horario_1.hora_fin) &&
                            (this.hora_fin_2.value > diaObj.horario_1.hora_inicio));
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

        this.tableListaDiasSeleccionados();
        this.limpiarCampos();
        this.horariosInput.value = JSON.stringify(this.listDiasSelected);
    }

    tableListaDiasSeleccionados() {
        this.tblHorariosBody.innerHTML = '';
        this.listDiasSelected.forEach(diaObj => {
            const row = document.createElement('tr');
            const diaCell = document.createElement('td');
            diaCell.textContent = diaObj.dia === '1' ? 'Lunes' :
                diaObj.dia === '2' ? 'Martes' :
                    diaObj.dia === '3' ? 'Miércoles' :
                        diaObj.dia === '4' ? 'Jueves' :
                            diaObj.dia === '5' ? 'Viernes' :
                                diaObj.dia === '6' ? 'Sábado' :
                                    diaObj.dia === '7' ? 'Domingo' : diaObj.dia;
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
            eliminarBtn.onclick = () => this.eliminarHorario(diaObj.dia);
            const eliminarCell = document.createElement('td');
            eliminarCell.appendChild(eliminarBtn);

            row.appendChild(diaCell);
            row.appendChild(horario1Cell);
            row.appendChild(horario2Cell);
            row.appendChild(eliminarCell);
            this.tblHorariosBody.appendChild(row);
        });
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
        this.listDiasSelected = this.listDiasSelected.filter(diaObj => diaObj.dia !== dia);
        this.horariosInput.value = JSON.stringify(this.listDiasSelected);
        this.tableListaDiasSeleccionados();
    }

}

new RegistroServicio();