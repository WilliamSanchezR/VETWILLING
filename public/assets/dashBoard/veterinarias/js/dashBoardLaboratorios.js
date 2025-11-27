let tabla;
let tablaLaboratoriosCargados;
let tableResultadosLab;

//Se crea la funcion para crear la tabla de pacientes con laboratorio.
function CrearTablaPacientes(listaPacientes) {
    // Se selecciona el cuerpo de la tabla y se limpia su contenido.
    const tablaBody = document.getElementById("tablaLaboratorioBody");
    tablaBody.innerHTML = "";
    // Se recorre la lista de pacientes y se crea las filas en la tabla.
    listaPacientes.forEach(paciente => {
        const fila = document.createElement("tr");


        fila.innerHTML = `
            <td class="td-search-icon" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo"><i class="bi bi-search"></i></td>
            <td>${paciente.folio}</td>
            <td>${paciente.fecha}</td>
            <td>${paciente.propietario}</td>
            <td>${paciente.nombreMascota}</td>
            <td>${paciente.animal}</td>
            <td>${paciente.raza}</td>
            <td>${paciente.cantLaboratorios}</td>
            <td class="td-status">${MostrarEstado(paciente.estado)}</td>
        `;
        // Se agrega la fila al cuerpo de la tabla.
        tablaBody.appendChild(fila);
    });

    try {
        tabla = $('#tabla-pacientes').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay Laboratorios disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Laboratorios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Laboratorios",
                "infoFiltered": "(filtrado de _MAX_ Laboratorios totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Laboratorios",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron Laboratorios",
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

// Función para mostrar el estado con íconos
function MostrarEstado(estado) {
    switch (estado) {
        case "1":
            return `<i class="bi bi-clock-history pendiente-status" title="Pendiente"></i>`;
        case "2":
            return `<i class="bi bi-check-circle-fill completado-status" title="Completado"></i>`;
        default:
            return `<i class="bi bi-x-circle-fill cancelado-status" title="Cancelado"></i>`;
    }
}


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
            tabla.order([2, 'asc']).draw();
            console.log('📅 Ordenado por fecha ascendente');
            break;
        case '2':
            tabla.order([2, 'desc']).draw();
            console.log('📅 Ordenado por fecha descendente');
            break;
        case '3':
            tabla.order([3, 'asc']).draw();
            console.log('👤 Ordenado por propietario A-Z');
            break;
        case '4':
            tabla.order([3, 'desc']).draw();
            console.log('👤 Ordenado por propietario Z-A');
            break;
        case '5':
            tabla.order([4, 'asc']).draw();
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
        const data = tabla.rows({ search: 'applied' }).data();
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
    tabla.search(valorBusqueda).draw();
});


// Limpiar búsqueda al hacer clic en el icono de búsqueda
$('.campo-buscar i').on('click', function () {
    $('#buscarPaciente').val('').trigger('keyup');
});


$('#btnAgregarNuevo').on('click', function () {
    document.location.href = "../../../../dashBoard/veterinarias/registroPacientesLaboratorio.html";
});

// Cerrar modal de resultados de laboratorio al guardar
$('#btn-guardar-resultados').on('click', function () {
    $('#modalResultadoLab').modal('hide');
});


// Funcion para inicializar DataTable para la lista de laboratorios asociados
function dataTableListLaboratorios() {
    try {
        tablaUsuarios = $('#list-laboratorios-asociados').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay Laboratorios disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Laboratorios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Laboratorios",
                "infoFiltered": "(filtrado de _MAX_ Laboratorios totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Laboratorios",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron Laboratorios",
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

// Funcion para inicializar DataTable para la lista de resultados de laboratorio
function dataTableResultadosLaboratorio() {
    try {
        tableResultadosLab = $('#lista-resultados').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay Laboratorios disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Laboratorios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Laboratorios",
                "infoFiltered": "(filtrado de _MAX_ Laboratorios totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Laboratorios",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron Laboratorios",
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
    fetch("../../assets/data/laboratorioPacientes.json")
        .then(response => response.json())
        .then(data => {
            CrearTablaPacientes(data);
        })
        .catch(error => {
            console.error("Error al cargar el JSON:", error);

        });

    dataTableListLaboratorios();
    dataTableResultadosLaboratorio();
})