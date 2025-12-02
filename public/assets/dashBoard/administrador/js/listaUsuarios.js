let tablaUsuarios;

// Botón "ordenar" - ordenar la tabla
$('#btnOrdenar').on('click', function () {
    const opciones = [
        '📅 Fecha (más antigua primero)',
        '📅 Fecha (más reciente primero)',
        '👤 Propietario (A-Z)',
        '👤 Propietario (Z-A)',
        '🐾 Mascota (A-Z)'
    ];

    const mensaje = '⬆️⬇️ Selecciona el ordenamiento:\n\n' +
        opciones.map((o, i) => `${i + 1} - ${o}`).join('\n');

    const opcion = prompt(mensaje);

    switch (opcion) {
        case '1':
            tablaUsuarios.order([2, 'asc']).draw();
            console.log('📅 Ordenado por fecha ascendente');
            break;
        case '2':
            tablaUsuarios.order([2, 'desc']).draw();
            console.log('📅 Ordenado por fecha descendente');
            break;
        case '3':
            tablaUsuarios.order([3, 'asc']).draw();
            console.log('👤 Ordenado por propietario A-Z');
            break;
        case '4':
            tablaUsuarios.order([3, 'desc']).draw();
            console.log('👤 Ordenado por propietario Z-A');
            break;
        case '5':
            tablaUsuarios.order([4, 'asc']).draw();
            console.log('🐾 Ordenado por mascota A-Z');
            break;
        default:
            if (opcion !== null) {
                alert('❌ Opción no válida');
            }
    }
});


// Botón "exportar" - exportar a CSV la tabla 
$('#btnExport').on('click', function () {
    console.log('📥 Exportando a CSV...');
    exportarACSV();
});


// Función para exportar la tabla a CSV
function exportarACSV() {
    try {
        const data = tablaUsuarios.rows({ search: 'applied' }).data();
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


// Búsqueda en la tabla
$('#buscarPaciente').on('keyup change', function () {
    const valorBusqueda = this.value;
    console.log('🔍 Buscando:', valorBusqueda);
    tablaUsuarios.search(valorBusqueda).draw();
});

// Limpiar búsqueda al hacer clic en el icono de búsqueda
$('.campo-buscar i').on('click', function () {
    $('#buscarPaciente').val('').trigger('keyup');
});


// Funcion para inicializar DataTable para la lista de laboratorios asociados
function dataTableListUsuarios() {
    try {
        tablaUsuarios = $('#tablaListaUsuarios').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay Usuarios disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Usuarios",
                "infoFiltered": "(filtrado de _MAX_ Usuarios totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Usuarios",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron Usuarios",
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

document.addEventListener("DOMContentLoaded", () => {
    dataTableListUsuarios();
})