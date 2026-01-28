class ListaSubservicios {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    init() {
        console.log('Lista subservicios Init');
        this.cacheDom();
        this.bindEvents();
        this.dataTableListSubservicios();
    }

    cacheDom() {
        this.tablaSubservicios;
        this.ordebarBtn = document.getElementById('btnOrdenar');
        this.btnExportarCSV = document.getElementById('btnExportSubservicios');
        this.inputBuscarSubservicios = document.getElementById('buscarSubservicio');
        this.limpiarBusqueda = document.querySelector('.campo-buscar i');
        this.btnCrearSubservicio = document.getElementById('btnAgregarNuevo');
        this.btnEliminarServicio = document.querySelectorAll('.btn-eliminar');
    }

    bindEvents() {
        // Aquí puedes agregar los event listeners
        if (this.ordebarBtn) {
            this.ordebarBtn.onclick = () => this.ordenarSubservicios();
        }
        if (this.btnExportarCSV) {
            this.btnExportarCSV.onclick = () => this.exportarCSV();
        }

        if (this.inputBuscarSubservicios) {
            this.inputBuscarSubservicios.oninput = () => this.buscarSubservicios();
        }

        if (this.limpiarBusqueda) {
            this.limpiarBusqueda.onclick = () => {
                this.inputBuscarSubservicios.value = '';
                this.buscarSubservicios();
            }
        };

        if (this.btnCrearSubservicio) {
            this.btnCrearSubservicio.onclick = () => this.crearSubservicio();
        }

        if (this.btnEliminarServicio) {
            this.btnEliminarServicio.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e.target.classList.contains('bi-trash')) {
                        var btnEdit = e.target.parentElement;
                        this.eliminarServicio(btnEdit.dataset.id);
                    }
                    if (e.target.classList.contains('btn-editar')) {
                        this.eliminarServicio(e.target.dataset.id);

                    }
                })
            });
        }
    }

    // Funcion para inicializar DataTable para la lista de subservicios asociados
    dataTableListSubservicios() {
        try {
            this.tablaSubservicios = $('#tablaListaSubservicios').DataTable({
                // Configuración de idioma en español
                language: {
                    "decimal": "",
                    "emptyTable": "No hay Subservicios disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Subservicios",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Subservicios",
                    "infoFiltered": "(filtrado de _MAX_ Subservicios totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Subservicios",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron Subservicios",
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

    ordenarSubservicios() {
        const opciones = [
            'Nombre (A-Z)',
            'Nombre (Z-A)',
            'Costo (Ascendente)',
            'Costo (Descendente)'
        ];

        const mensaje = '⬆️⬇️ Selecciona el ordenamiento:\n\n' +
            opciones.map((o, i) => `${i + 1} - ${o}`).join('\n');

        const opcion = prompt(mensaje);

        switch (opcion) {
            case '1':
                this.tablaSubservicios.order([0, 'asc']).draw();
                console.log('Ordenado por Nombre ascendente');
                break;
            case '2':
                this.tablaSubservicios.order([0, 'desc']).draw();
                console.log('Ordenado por Nombre descendente');
                break;
            case '3':
                this.tablaSubservicios.order([2, 'asc']).draw();
                console.log('Ordenado por Costo ascendente');
                break;
            case '4':
                this.tablaSubservicios.order([2, 'desc']).draw();
                console.log('Ordenado por Costo descendente');
                break;
            default:
                if (opcion !== null) {
                    alert('❌ Opción no válida');
                }
        }
    }

    exportarCSV() {
        try {
            const data = this.tablaSubservicios.rows({ search: 'applied' }).data();
            let csv = 'Nombre,servicio, Costo, Descripción, Estado\n';

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
            link.setAttribute('download', `subservicios_veterinaria_${fecha}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert('✅ Archivo CSV descargado correctamente');
            console.log('✅ CSV exportado:', `subservicios_veterinaria_${fecha}.csv`);
        } catch (error) {
            console.error('❌ Error al exportar CSV:', error);
            alert('Error al exportar CSV. Revisa la consola.');
        }
    }

    buscarSubservicios() {
        const valorBusqueda = this.inputBuscarSubservicios.value;
        console.log('🔍 Buscando:', valorBusqueda);
        this.tablaSubservicios.search(valorBusqueda).draw();
    }

    crearSubservicio() {
        // Lógica para crear un nuevo servicio
        const eliminarUrl = `${window.location.origin}/vetwilling/representante/registro-subservicio`;

        window.location.href = eliminarUrl;
    }

    eliminarServicio($id) {
        Swal.fire({
            title: '¿Eliminar subservicio?',
            text: '¿Estás seguro de que deseas eliminar este subservicio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((deleteResult) => {
            if (deleteResult.isConfirmed) {
                // Eliminar del servidor
                window.location.href = `${window.location.origin}/vetwilling/representante/eliminar-subservicio?action=eliminar&id=${$id}`;
            }
        });
    }
}

new ListaSubservicios();