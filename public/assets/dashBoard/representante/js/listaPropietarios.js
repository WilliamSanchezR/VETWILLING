class ListaPropietarios {
    constructor() {

        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    init() {
        console.log('Lista propietarios Init');
        this.cacheDom();
        this.bindEvents();
        this.dataTableListPropietarios();
        this.listaPropietariosVeterinaria();
    }

    cacheDom() {
        this.tablaPropietarios;
        this.ordebarBtn = document.getElementById('btnOrdenar');
        this.btnExportarCSV = document.getElementById('btnExport');
        this.inputBuscarPropietarios = document.getElementById('buscarPropietarios');
        this.limpiarBusqueda = document.querySelector('.campo-buscar i');
        this.idVeterinaria = document.getElementById('id_veterinaria').value;
        this.tablePropietarios = document.getElementById('tablaListaPropietarios');
    }

    bindEvents() {
        // Aquí puedes agregar los event listeners
        if (this.ordebarBtn) {
            this.ordebarBtn.onclick = () => this.ordenarPropietarios();
        }
        if (this.btnExportarCSV) {
            this.btnExportarCSV.onclick = () => this.exportarCSV();
        }

        if (this.inputBuscarPropietarios) {
            this.inputBuscarPropietarios.oninput = () => this.buscarPropietarios();
        }

        if (this.limpiarBusqueda) {
            this.limpiarBusqueda.onclick = () => {
                this.inputBuscarPropietarios.value = '';
                this.buscarPropietarios();
            }
        };

        if (this.btnEliminarPropietario) {
            this.btnEliminarPropietario.forEach(btn => {
                btn.onclick = (event) => {
                    const id = event.currentTarget.getAttribute('data-id');
                    this.eliminarPropietario(id);
                }
            });
        }
    }

    // Funcion para inicializar DataTable para la lista de laboratorios asociados
    dataTableListPropietarios() {
        try {
            this.tablaPropietarios = $('#tablaListaPropietarios').DataTable({
                // Configuración de idioma en español
                language: {
                    "decimal": "",
                    "emptyTable": "No hay Propietarios disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Propietarios",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Propietarios",
                    "infoFiltered": "(filtrado de _MAX_ Propietarios totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Propietarios",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron Propietarios",
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

    ordenarPropietarios() {
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
                this.tablaPropietarios.order([1, 'asc']).draw();
                console.log('👤 Ordenado por Nombre y Apellidos ascendente');
                break;
            case '2':
                this.tablaPropietarios.order([1, 'desc']).draw();
                console.log('👤 Ordenado por Nombre y Apellidos descendente');
                break;
            case '3':
                this.tablaPropietarios.order([0, 'asc']).draw();
                console.log('# Ordenado por número de documento ascendente');
                break;
            case '4':
                this.tablaPropietarios.order([0, 'desc']).draw();
                console.log('# Ordenado por número de documento descendente');
                break;
            default:
                if (opcion !== null) {
                    alert('❌ Opción no válida');
                }
        }
    }

    exportarCSV() {
        try {
            const data = this.tablaPropietarios.rows({ search: 'applied' }).data();
            let csv = 'foto Documento, Nombres y Apellidos, Teléfono, Email, Estado\n';

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
            link.setAttribute('download', `propietarios_veterinaria_${fecha}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert('✅ Archivo CSV descargado correctamente');
            console.log('✅ CSV exportado:', `propietarios_veterinaria_${fecha}.csv`);
        } catch (error) {
            console.error('❌ Error al exportar CSV:', error);
            alert('Error al exportar CSV. Revisa la consola.');
        }
    }

    buscarPropietarios() {
        const valorBusqueda = this.inputBuscarPropietarios.value;
        console.log('🔍 Buscando:', valorBusqueda);
        this.tablaPropietarios.search(valorBusqueda).draw();
    }

    listaPropietariosVeterinaria() {
        fetch(`${window.location.origin}/vetwilling/representante/propietarios/api/listar?accion=listar-veterinaria&id=${this.idVeterinaria}`)
            .then(response => response.json())
            .then(data => {
                // Aquí puedes actualizar la tabla con los datos recibidos
                if (data.propietarios) {
                    this.tablaPropietarios.clear();
                    data.propietarios.forEach(propietario => {
                        this.tablaPropietarios.row.add([
                            propietario.numero_documento,
                            `${propietario.nombre}`,
                            propietario.telefono,
                            propietario.email,
                            propietario.estado === 'activo' ? '<span class="estado-activo">Activo</span>' : '<span class="estado-inactivo">Inactivo</span>',
                            propietario.estado === 'activo' ? `<button class="btn-accion btn-eliminar" title="Eliminar" data-id="${propietario.id_propietario}">
                                    <i class="bi bi-trash"></i>
                            </button>` : ''
                        ]).draw();
                    });

                    // Reasignar eventos a los nuevos botones de eliminar
                    this.btnEliminarPropietario = document.querySelectorAll('.btn-eliminar');
                    if (this.btnEliminarPropietario) {
                        this.btnEliminarPropietario.forEach(btn => {
                            btn.onclick = (event) => {
                                const id = event.currentTarget.getAttribute('data-id');
                                this.eliminarPropietario(id);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('❌ Error al obtener los propietarios:', error);
            });
    }

    eliminarPropietario(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.desactivarPropietario(id);
            }
        });
    }

    desactivarPropietario(id) {
        fetch(`${window.location.origin}/vetwilling/representante/api/propietarios?id=${id}`, {
            method: 'DELETE',
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire(
                        'Propietario inhabilitado',
                        'El propietario ha sido inhabilitado correctamente',
                        'success',
                    );
                    this.listaPropietariosVeterinaria();
                } else {
                    Swal.fire(
                        'Error',
                        'No se pudo eliminar',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('❌ Error al inhabilitar propietario:', error);
                Swal.fire(
                    'Error',
                    'No se pudo inhabilitar el propietario. Revisa la consola.',
                    'error'
                );
            });
    }
}

new ListaPropietarios();