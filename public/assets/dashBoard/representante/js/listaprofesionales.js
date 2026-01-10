class ListaPorfesionales {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }
    init() {
        console.log('Lista profesionales Init');
        this.cacheDom();
        this.bindEvents();
        this.dataTableListProfesionales();
    }

    cacheDom() {
        this.tablaProfesionales;
        this.ordebarBtn = document.getElementById('btnOrdenar');
        this.btnExportarCSV = document.getElementById('btnExport');
        this.inputBuscarProfesionales = document.getElementById('buscarProfesionales');
        this.limpiarBusqueda = document.querySelector('.campo-buscar i');
    }

    bindEvents() {
        // Aquí puedes agregar los event listeners
        if (this.ordebarBtn) {
            this.ordebarBtn.onclick = () => this.ordenarProfesionales();
        }
        if (this.btnExportarCSV) {
            this.btnExportarCSV.onclick = () => this.exportarCSV();
        }

        if (this.inputBuscarProfesionales) {
            this.inputBuscarProfesionales.oninput = () => this.buscarProfesionales();
        }

        if (this.limpiarBusqueda) {
            this.limpiarBusqueda.onclick = () => {
                this.inputBuscarProfesionales.value = '';
                this.buscarProfesionales();
            }
        };
    }

    // Funcion para inicializar DataTable para la lista de laboratorios asociados
    dataTableListProfesionales() {
        try {
            this.tablaProfesionales = $('#tablaListaProfesionales').DataTable({
                // Configuración de idioma en español
                language: {
                    "decimal": "",
                    "emptyTable": "No hay Profesionales disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Profesionales",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Profesionales",
                    "infoFiltered": "(filtrado de _MAX_ Profesionales totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Profesionales",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron Profesionales",
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

    exportarCSV() {
        try {
            const data = this.tablaProfesionales.rows({ search: 'applied' }).data();
            let csv = 'No.,Fecha,Propietario,Nombre Mascota,Animal,Raza,Laboratorios,Estado\n';

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
            link.setAttribute('download', `laboratorios_veterinaria_${fecha}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert('✅ Archivo CSV descargado correctamente');
            console.log('✅ CSV exportado:', `citas_veterinaria_${fecha}.csv`);
        } catch (error) {
            console.error('❌ Error al exportar CSV:', error);
            alert('Error al exportar CSV. Revisa la consola.');
        }
    }

    buscarProfesionales() {
        const valorBusqueda = this.inputBuscarProfesionales.value;
        console.log('🔍 Buscando:', valorBusqueda);
        this.tablaProfesionales.search(valorBusqueda).draw();
    }
}

new ListaPorfesionales();