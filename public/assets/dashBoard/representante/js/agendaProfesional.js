const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

class AgendaProfesional {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    init() {
        console.log('Agenda Profesional Init');
        this.cacheDom();
        this.bindEvents();
        this.dataTableAgenda();
    }

    cacheDom() {
        this.tablaAgendaProfesionales;
        this.btnEditarAgenda = document.querySelectorAll('.btn-editar-agenda');
        this.btnEliminarAgenda = document.querySelectorAll('.btn-eliminar-agenda');
        this.segundaFranjaHorarioContainer = document.getElementById('segundaFranjaHorarioContainer');
        this.formAgendaRegister = document.getElementById('formAgenda');
    }

    bindEvents() {
        if (this.btnEliminarAgenda) {
            this.btnEliminarAgenda.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e.target.classList.contains('bi-trash')) {
                        var btnEdit = e.target.parentElement;
                        this.eliminarAgenda(btnEdit.id);
                    }
                    if (e.target.classList.contains('btn-editar')) {
                        this.eliminarAgenda(e.target.dataset.id);

                    }
                })
            });
        }

        if (this.btnEditarAgenda) {
            this.btnEditarAgenda.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e.target.classList.contains('bi-pencil')) {
                        var btnEdit = e.target.parentElement;
                        this.cargarDatosEdicion(btnEdit.dataset.dispo);
                    }
                    if (e.target.classList.contains('btn-editar')) {
                        this.cargarDatosEdicion(e.target.dataset.dispo);

                    }
                })
            });
        }

        if (this.formAgendaRegister) {
            this.formAgendaRegister.addEventListener('submit', (e) => {
                e.preventDefault();

                const formData = new FormData(this.formAgendaRegister);
                const data = Object.fromEntries(formData.entries());
                this.crearAgendaProfesional(data);
            });
        }
    }

    // Funcion para inicializar DataTable para la lista de laboratorios asociados
    dataTableAgenda() {
        try {
            this.tablaAgendaProfesionales = $('#tablaListaAgenda').DataTable({
                // Configuración de idioma en español
                language: {
                    "decimal": "",
                    "emptyTable": "No hay Agendas disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Agendas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Agendas",
                    "infoFiltered": "(filtrado de _MAX_ Agendas totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Agendas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron Agendas",
                    "paginate": {
                        "first": "Primera",
                        "last": "Última",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },

                // Configuración de paginación
                pageLength: 9,
                lengthMenu: [[9, 15, 25, 50, -1], [9, 15, 25, 50, "Todas"]],

                // Configuración de ordenamiento
                order: [[0, 'asc'], [4, 'asc']], // Ordenar por dia

                // Configuración de columnas
                columnDefs: [
                    {
                        targets: -1, // Última columna (Operación)
                        orderable: false,
                        searchable: false
                    }
                ],

                // DOM personalizado
                dom: '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',

            });

            console.log('✅ Tabla inicializada exitosamente');

        } catch (error) {
            console.error('❌ Error al inicializar DataTables:', error);
            alert('Error al inicializar la tabla. Revisa la consola para más detalles.');
            return;
        }
    }

    ordenarProfesionales() {
        const opciones = [
            '👤 Nombre y Apellidos (A-Z)',
            '👤 Nombre y apellidos (Z-A)',
            '# Documento (Ascendente)',
            '# Documento (Descendente)'
        ];

        const mensaje = '⬆️⬇️ Selecciona el ordenamiento:\n\n' +
            opciones.map((o, i) => `${i + 1} - ${o}`).join('\n');

        const opcion = prompt(mensaje);

        switch (opcion) {
            case '1':
                this.tablaProfesionales.order([2, 'asc']).draw();
                console.log('👤 Ordenado por Nombre y Apellidos ascendente');
                break;
            case '2':
                this.tablaProfesionales.order([2, 'desc']).draw();
                console.log('👤 Ordenado por Nombre y Apellidos descendente');
                break;
            case '3':
                this.tablaProfesionales.order([1, 'asc']).draw();
                console.log('# Ordenado por número de documento ascendente');
                break;
            case '4':
                this.tablaProfesionales.order([1, 'desc']).draw();
                console.log('# Ordenado por número de documento descendente');
                break;
            default:
                if (opcion !== null) {
                    alert('❌ Opción no válida');
                }
        }
    }

    eliminarAgenda($id) {
        Swal.fire({
            title: '¿Eliminar agendamiento?',
            text: '¿Estás seguro de que deseas eliminar este agendamiento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((deleteResult) => {
            if (deleteResult.isConfirmed) {
                // Eliminar del servidor
                const redirect = encodeURIComponent(window.location.href);
                const basePath = window.location.pathname.includes('/veterinaria/mi-agenda')
                    ? `${baseUrl}/veterinario/eliminar-disponibilidad-agenda`
                    : `${baseUrl}/representante/eliminar-agenda-usuario`;

                window.location.href = `${basePath}?action=eliminar&id=${$id}&redirect=${redirect}`;
            }
        });
    }

    cargarDatosEdicion($data) {
        const disponibilidad = JSON.parse($data);

        document.getElementById('edit_id_disponibilidad').value = disponibilidad.id_disponibilidad;
        document.getElementById('edit_dia').value = disponibilidad.dia_semana;
        document.getElementById('edit_hora_inicio').value = disponibilidad.hora_inicio;
        document.getElementById('edit_hora_fin').value = disponibilidad.hora_fin;
        document.getElementById('edit_duracion').value = disponibilidad.duracion;
        document.getElementById('edit_especialidad').value = disponibilidad.id_especialidad;

    }

    crearAgendaProfesional(data) {
        // Validamos que los campos obligatorios se hallan registrado
        if (!data.id_usuario || !data.id_veterinaria || !data.dia_semana || !data.duracion_minutos) {
            this.mostrarMensajeError('Campos vacíos', 'Por favor complete todos los campos obligatorios');
            return;
        }

        // Verificar si el primer par de horarios está ingresado (ambos o ninguno)
        const tienePrimerHorario = data.hora_inicio || data.hora_fin;
        const primerHorarioCompleto = data.hora_inicio && data.hora_fin;

        // Verificar si el segundo par de horarios está ingresado (ambos o ninguno)
        const tieneSegundoHorario = data.hora_inicio_seccond || data.hora_fin_seccond;
        const segundoHorarioCompleto = data.hora_inicio_seccond && data.hora_fin_seccond;

        // Validar que al menos uno de los dos grupos de horarios esté completo
        if (!primerHorarioCompleto && !segundoHorarioCompleto) {
            this.mostrarMensajeError('Horario incompleto', 'Debe ingresar al menos un par completo de horarios (inicio y fin)');
            return;
        }

        // Validar que si se ingresó uno del primer par, el otro también debe estar ingresado
        if (tienePrimerHorario && !primerHorarioCompleto) {
            this.mostrarMensajeError('Horario de la mañana incompleto', 'Si ingresa un horario en la mañana, debe completar tanto la hora de inicio como la hora de fin');
            return;
        }

        // Validar que si se ingresó uno del segundo par, el otro también debe estar ingresado
        if (tieneSegundoHorario && !segundoHorarioCompleto) {
            this.mostrarMensajeError('Horario de la tarde incompleto', 'Si ingresa un horario en el horario de la tarde, debe completar tanto la hora de inicio como la hora de fin');
            return;
        }

        // Validamos si se ingreso el primero horario y el segundo horario, el horario de la tarde no puede ser menor al horario de la mañana
        if (primerHorarioCompleto && segundoHorarioCompleto) {
            const horaInicioManana = new Date(`1970-01-01T${data.hora_inicio}:00`);
            const horaFinManana = new Date(`1970-01-01T${data.hora_fin}:00`);
            const horaInicioTarde = new Date(`1970-01-01T${data.hora_inicio_seccond}:00`);
            const horaFinTarde = new Date(`1970-01-01T${data.hora_fin_seccond}:00`);

            if (horaInicioTarde < horaFinManana) {
                this.mostrarMensajeError('Horario de la tarde inválido', 'El horario de la tarde no puede ser menor al horario de la mañana');
                return;
            }

                if (horaFinTarde < horaFinManana) {
                this.mostrarMensajeError('Horario de la tarde inválido', 'El horario de la tarde no puede ser menor al horario de la mañana');
                return;
            }
        }

        // Enviar datos al servidor para registrar la disponibilidad
        fetch(`${baseUrl}/representante/agregar-disponibilidad-agenda`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Disponibilidad registrada',
                    text: 'La disponibilidad ha sido registrada exitosamente.'
                });
                // Recargar la página después de cerrar el mensaje
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.mostrarMensajeError('Error al registrar', 'Hubo un problema al registrar la disponibilidad.');
            }
        })
        .catch(error => {
            this.mostrarMensajeError('Error al registrar', 'Hubo un problema al registrar la disponibilidad.');
        });
    }

    mostrarMensajeError(title, mensaje) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: mensaje
        });
    }
}

new AgendaProfesional();