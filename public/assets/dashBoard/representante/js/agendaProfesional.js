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
                    ? `${window.location.origin}/vetwilling/veterinario/eliminar-disponibilidad-agenda`
                    : `${window.location.origin}/vetwilling/representante/eliminar-agenda-usuario`;

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
}

new AgendaProfesional();