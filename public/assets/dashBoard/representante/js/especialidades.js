class especialidades {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.bindEvents();
    }

    cacheDom() { 
        this.tablaEspecilidades = null;
        this.inputBuscarEspecialidades = document.getElementById("buscarEspecialidad");
        this.dataTableListEspecialidades();
        this.ordebarBtn = document.getElementById('btnOrdenar');
        this.btnExportarCSV = document.getElementById('btnExport');
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

        if (this.inputBuscarEspecialidades) {
            this.inputBuscarEspecialidades.oninput = () => this.buscarEspecialidades();
        }

        if (this.ordebarBtn) {
            this.ordebarBtn.onclick = () => this.ordenarEspecialidades();
        }

        if (this.btnExportarCSV) {
            this.btnExportarCSV.onclick = () => this.exportarCSV();
        }
     }

    viewModalEdit(data) {
        this.especialidadId.value = data.id;
        this.nombreEspecialidad.value = data.name;
    }

        // Funcion para inicializar DataTable para la lista de especialidades asociados
    dataTableListEspecialidades() {
        try {
            this.tablaEspecialidades = $('#tablaListaEspecialidades').DataTable({
                // Configuración de idioma en español
                language: {
                    "decimal": "",
                    "emptyTable": "No hay Especialidades disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Especialidades",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Especialidades",
                    "infoFiltered": "(filtrado de _MAX_ Especialidades totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Especialidades",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron Especialidades",
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
                order: [[2, 'desc']], // Ordenar por fecha por defecto

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

    ordenarEspecialidades() {
        const opciones = [
            'Nombre (A-Z)',
            'Nombre (Z-A)',  
        ];

        const mensaje = '⬆️⬇️ Selecciona el ordenamiento:\n\n' +
            opciones.map((o, i) => `${i + 1} - ${o}`).join('\n');

        const opcion = prompt(mensaje);

        switch (opcion) {
            case '1':
                this.tablaEspecialidades.order([0, 'asc']).draw();
                console.log('Ordenado por Nombres ascendente');
                break;
            case '2':
                this.tablaEspecialidades.order([0, 'desc']).draw();
                console.log('Ordenado por Nombres descendente');
                break;
            default:
                if (opcion !== null) {
                    alert('❌ Opción no válida');
                }
        }
    }

    exportarCSV() {
        try {
            const data = this.tablaEspecialidades.rows({ search: 'applied' }).data();
            let csv = 'Nombre, Estado\n';

            data.each(function (fila) {
                const filaLimpia = [];
                for (let i = 0; i < fila.length - 1; i++) {
                    let valor = fila[i].toString().replace(/<[^>]*>/g, '');
                    valor = valor.replace(/"/g, '""');
                    filaLimpia.push(`"${valor}"`);
                }
                csv += filaLimpia.join(',') + '\n';
            });

            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            const fecha = new Date().toISOString().split('T')[0];

            link.setAttribute('href', url);
            link.setAttribute('download', `especialidades_veterinaria_${fecha}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert('✅ Archivo CSV descargado correctamente');
            console.log('✅ CSV exportado:', `especialidades_veterinaria_${fecha}.csv`);
        } catch (error) {
            console.error('❌ Error al exportar CSV:', error);
            alert('Error al exportar CSV. Revisa la consola.');
        }
    }

    buscarEspecialidades() {
        const valorBusqueda = this.inputBuscarEspecialidades.value;
        console.log('🔍 Buscando:', valorBusqueda);
        this.tablaEspecialidades.search(valorBusqueda).draw();
    }
}

new especialidades();
