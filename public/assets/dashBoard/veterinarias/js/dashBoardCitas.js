// ========================================
// TABLA DE CITAS - DATATABLES OPTIMIZADO
// Archivo: tableCitas.js
// ========================================

let tabla;

// ESPERAR A QUE TODO ESTÉ CARGADO
$(document).ready(function() {
    
    console.log('🔄 Inicializando módulo de citas...');
    
    // Verificar que DataTables esté disponible
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('❌ DataTables no está cargado');
        alert('Error: DataTables no está disponible. Verifica las librerías.');
        return;
    }

    // ========================================
    // FUNCIONES AUXILIARES (DECLARAR PRIMERO)
    // ========================================
    
    function actualizarColoresFilas() {
        $('#tablaCitas tbody tr').each(function(index) {
            $(this).removeClass('fila-gris fila-blanca');
            
            const patron = index % 2;
            if (patron === 0) {
                $(this).addClass('fila-gris');
            } 

        });
    }

    function actualizarContadorVer() {
        if (tabla && tabla.page) {
            const info = tabla.page.info();
            $('#btnVer').html(`<i class="bi bi-eye"></i> Ver ${info.recordsDisplay}/${info.recordsTotal}`);
        }
    }

    // ========================================
    // INICIALIZACIÓN DE DATATABLES
    // ========================================
    
    try {
        tabla = $('#tablaCitas').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay citas disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ citas",
                "infoEmpty": "Mostrando 0 a 0 de 0 citas",
                "infoFiltered": "(filtrado de _MAX_ citas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ citas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron citas",
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
            order: [[1, 'asc']], // Ordenar por fecha por defecto
            
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
            
            // Callback después de dibujar
            drawCallback: function() {
                actualizarColoresFilas();
                actualizarContadorVer();
            },
            
            // Callback después de inicializar
            initComplete: function() {
                console.log('✅ DataTables inicializado correctamente');
                actualizarColoresFilas();
                actualizarContadorVer();
            }
        });

        console.log('✅ Tabla inicializada exitosamente');

    } catch (error) {
        console.error('❌ Error al inicializar DataTables:', error);
        alert('Error al inicializar la tabla. Revisa la consola para más detalles.');
        return;
    }

    // ========================================
    // BÚSQUEDA PERSONALIZADA
    // ========================================
    $('#buscarCitas').on('keyup change', function() {
        const valorBusqueda = this.value;
        console.log('🔍 Buscando:', valorBusqueda);
        tabla.search(valorBusqueda).draw();
    });

    // Limpiar búsqueda al hacer clic en el icono
    $('.campo-buscar i').on('click', function() {
        $('#buscarCitas').val('').trigger('keyup');
    });

    // ========================================
    // BOTÓN "VER" - MOSTRAR INFO
    // ========================================
    $('#btnVer').on('click', function() {
        const info = tabla.page.info();
        const mensaje = `📊 Información de Citas:\n\n` +
                       `Total de registros: ${info.recordsTotal}\n` +
                       `Registros visibles: ${info.recordsDisplay}\n` +
                       `Mostrando: ${info.start + 1} a ${info.end}\n` +
                       `Página actual: ${info.page + 1} de ${info.pages}`;
        alert(mensaje);
    });

    // ========================================
    // BOTÓN "FILTRAR"
    // ========================================
    $('#btnFiltrar').on('click', function() {
        const servicios = [
            'Vacunación',
            'Atención Medica',
            'VC, MDC, NC',
            'Todas'
        ];
        
        const mensaje = '🔽 Filtrar por Servicio:\n\n' + 
                       servicios.map((s, i) => `${i + 1} - ${s}`).join('\n');
        
        const opcion = prompt(mensaje);
        
        if (opcion !== null) {
            const indice = parseInt(opcion) - 1;
            if (indice >= 0 && indice < servicios.length - 1) {
                tabla.column(6).search(servicios[indice]).draw();
                console.log('🔽 Filtrado por:', servicios[indice]);
            } else if (indice === servicios.length - 1) {
                tabla.column(6).search('').draw();
                console.log('🔽 Filtro eliminado');
            }
        }
    });

    // ========================================
    // BOTÓN "ORDENAR"
    // ========================================
    $('#btnOrdenar').on('click', function() {
        const opciones = [
            '📅 Fecha (más antigua primero)',
            '📅 Fecha (más reciente primero)',
            '👤 Propietario (A-Z)',
            '👤 Propietario (Z-A)',
            '🏥 Servicio (A-Z)',
            '🐾 Mascota (A-Z)'
        ];
        
        const mensaje = '⬆️⬇️ Selecciona el ordenamiento:\n\n' + 
                       opciones.map((o, i) => `${i + 1} - ${o}`).join('\n');
        
        const opcion = prompt(mensaje);
        
        switch(opcion) {
            case '1':
                tabla.order([1, 'asc']).draw();
                console.log('📅 Ordenado por fecha ascendente');
                break;
            case '2':
                tabla.order([1, 'desc']).draw();
                console.log('📅 Ordenado por fecha descendente');
                break;
            case '3':
                tabla.order([2, 'asc']).draw();
                console.log('👤 Ordenado por propietario A-Z');
                break;
            case '4':
                tabla.order([2, 'desc']).draw();
                console.log('👤 Ordenado por propietario Z-A');
                break;
            case '5':
                tabla.order([6, 'asc']).draw();
                console.log('🏥 Ordenado por servicio');
                break;
            case '6':
                tabla.order([3, 'asc']).draw();
                console.log('🐾 Ordenado por mascota');
                break;
            default:
                if (opcion !== null) {
                    alert('❌ Opción no válida');
                }
        }
    });

    // ========================================
    // BOTÓN "EXPORT" - EXPORTAR A CSV
    // ========================================
    $('#btnExport').on('click', function() {
        console.log('📥 Exportando a CSV...');
        exportarACSV();
    });

    function exportarACSV() {
        try {
            const data = tabla.rows({ search: 'applied' }).data();
            let csv = 'ID Perfil,Fecha,Propietario,Mascota,Teléfono,Dirección,Servicio,Nota\n';
            
            data.each(function(fila) {
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
            link.setAttribute('download', `citas_veterinaria_${fecha}.csv`);
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

    // ========================================
    // BOTÓN "AGREGAR NUEVO"
    // ========================================
    $('#btnAgregarNuevo').on('click', function() {
        console.log('➕ Agregar nueva cita');
        
        const confirmacion = confirm('➕ Nueva Cita\n\n¿Deseas agregar una nueva cita?\n\n(Esta es una función de demostración)');
        
        if (confirmacion) {
            try {
                const ahora = new Date();
                const diasSemana = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                const meses = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const fecha = `${diasSemana[ahora.getDay()]},${ahora.getDate()} ${meses[ahora.getMonth()]}`;
                
                tabla.row.add([
                    'NEW' + Math.floor(Math.random() * 1000),
                    fecha,
                    'Nuevo Cliente',
                    'Perro',
                    '300 000 0000',
                    'Nueva Dirección',
                    'Consulta General',
                    'Nueva cita',
                    '<a href="vetwilling/veterinario/editar-veterinario" class="btn-accion btn-editar" title="Editar"><i class="bi bi-pencil"></i></a>' +
                    '<a href="vetwilling/veterinario/eliminar-veterinario class="btn-accion btn-eliminar" title="Eliminar"><i class="bi bi-trash"></i></a>'
                ]).draw();
                
                alert('✅ Nueva cita agregada correctamente');
                console.log('✅ Cita agregada');
            } catch (error) {
                console.error('❌ Error al agregar cita:', error);
                alert('Error al agregar cita. Revisa la consola.');
            }
        }
    });

    // ========================================
    // NAVEGACIÓN DE PÁGINAS
    // ========================================
    tabla.on('page.dt', function() {
        const info = tabla.page.info();
        console.log(`📄 Página ${info.page + 1} de ${info.pages}`);
    });

    // ========================================
    // SELECCIÓN DE FILA (OPCIONAL)
    // ========================================
    $('#tablaCitas tbody').on('click', 'tr', function() {
        if (!$(this).hasClass('selected')) {
            $(this).addClass('selected').siblings().removeClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });

    // ========================================
    // ATAJOS DE TECLADO
    // ========================================
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + F para enfocar búsqueda
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            $('#buscarCitas').focus().select();
        }
        
        // Esc para limpiar búsqueda
        if (e.key === 'Escape' && $('#buscarCitas').is(':focus')) {
            $('#buscarCitas').val('').trigger('keyup').blur();
        }
    });

    // ========================================
    // MENSAJE FINAL
    // ========================================
    console.log('✅ Módulo de Gestión de Citas inicializado correctamente');
    console.log('💡 Tip: Usa Ctrl+F para buscar, Esc para limpiar');
    console.log('💡 Debug: Escribe debugTabla() en la consola para ver el estado');
});

// ========================================
// FUNCIONES AUXILIARES GLOBALES
// ========================================

// Debug tabla
window.debugTabla = function() {
    if (typeof tabla !== 'undefined' && tabla) {
        console.log('📊 Estado de la tabla:');
        console.log('Total de filas:', tabla.rows().count());
        console.log('Filas filtradas:', tabla.rows({ search: 'applied' }).count());
        console.log('Info de página:', tabla.page.info());
        console.log('Búsqueda actual:', tabla.search());
    } else {
        console.log('⚠️ La tabla no está inicializada');
    }
};

// Imprimir tabla
function imprimirTabla() {
    window.print();
}

// Limpiar filtros
function limpiarFiltros() {
    if (typeof tabla !== 'undefined' && tabla) {
        $('#buscarCitas').val('');
        tabla.search('').columns().search('').draw();
        alert('🧹 Filtros limpiados');
        console.log('🧹 Todos los filtros eliminados');
    }
}

// Recargar datos
function recargarDatos() {
    if (typeof tabla !== 'undefined' && tabla) {
        if (tabla.ajax) {
            tabla.ajax.reload();
            console.log('🔄 Datos recargados desde API');
        } else {
            tabla.draw();
            console.log('🔄 Tabla redibujada');
        }
    }
}

// Obtener datos
function obtenerDatosTabla() {
    if (typeof tabla !== 'undefined' && tabla) {
        const datos = tabla.rows().data().toArray();
        console.log('📋 Datos de la tabla:', datos);
        return datos;
    }
    return [];
}

// Exportar JSON
function exportarJSON() {
    if (typeof tabla !== 'undefined' && tabla) {
        try {
            const datos = tabla.rows({ search: 'applied' }).data().toArray();
            const json = JSON.stringify(datos, null, 2);
            
            const blob = new Blob([json], { type: 'application/json' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            const fecha = new Date().toISOString().split('T')[0];
            
            link.href = url;
            link.download = `citas_veterinaria_${fecha}.json`;
            link.click();
            
            console.log('✅ JSON exportado');
            alert('✅ Archivo JSON descargado');
        } catch (error) {
            console.error('❌ Error al exportar JSON:', error);
            alert('Error al exportar JSON');
        }
    }
}