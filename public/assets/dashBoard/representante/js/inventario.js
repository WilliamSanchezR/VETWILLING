/* ============================================================
   inventario.js
   Lógica del módulo de Gestión de Inventario para el Representante.

   Patrón del proyecto: clase ES6 con constructor + init().
   Sigue la misma estructura que ListaProfesionales (listaprofesionales.js).
   ============================================================ */

class GestionInventario {

    constructor() {
        // Esperamos a que el DOM esté completamente cargado antes de inicializar
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    /* ----------------------------------------------------------
       init()
       Punto de entrada. Llama a los tres pasos en orden:
       1. cacheDom  → guarda referencias a los elementos HTML
       2. bindEvents → conecta los listeners de eventos
       3. initDataTable → arranca la tabla con DataTables
    ---------------------------------------------------------- */
    init() {
        console.log('[Inventario] Módulo inicializado');
        this.cacheDom();
        this.bindEvents();
        this.initDataTable();
    }

    /* ----------------------------------------------------------
       cacheDom()
       Guardamos en propiedades de la clase los elementos del DOM
       que vamos a usar, para no buscarlo cada vez con querySelector.
    ---------------------------------------------------------- */
    cacheDom() {
        // Tabla principal de inventario
        this.tablaInventario = document.getElementById('tablaInventario');

        // Input de búsqueda personalizado (el que está en .campo-buscar)
        this.inputBuscar = document.getElementById('buscarInventario');

        // El icono de lupa del campo buscar (clic para limpiar búsqueda)
        this.iconoBuscar = document.querySelector('.campo-buscar i');
    }

    /* ----------------------------------------------------------
       bindEvents()
       Conectamos los eventos de los elementos del DOM.
    ---------------------------------------------------------- */
    bindEvents() {

        // ---- Búsqueda en tiempo real ----
        // Cada vez que el usuario escribe en el input, filtramos la tabla
        if (this.inputBuscar) {
            this.inputBuscar.addEventListener('input', () => {
                this.buscarEnTabla();
            });
        }

        // ---- Clic en el ícono de búsqueda: limpiar el filtro ----
        if (this.iconoBuscar) {
            this.iconoBuscar.addEventListener('click', () => {
                this.inputBuscar.value = '';
                this.buscarEnTabla(); // Actualizamos la tabla con búsqueda vacía
            });
        }

        // ---- Confirmación antes de eliminar ----
        // Usamos delegación de eventos: escuchamos en el documento
        // para capturar el submit de cualquier .form-eliminar-inv
        document.addEventListener('submit', (e) => {
            // Verificamos que el formulario enviado sea uno de eliminar
            if (e.target.classList.contains('form-eliminar-inv')) {
                e.preventDefault(); // Detenemos el envío automático
                this.confirmarEliminacion(e.target); // Pedimos confirmación
            }
        });
    }

    /* ----------------------------------------------------------
       initDataTable()
       Inicializa la tabla de inventario con DataTables.
       Usamos el mismo patrón de configuración que listaprofesionales.js
    ---------------------------------------------------------- */
    initDataTable() {
        // Solo inicializamos si la tabla existe en esta página
        if (!this.tablaInventario) return;

        this.dtInventario = $('#tablaInventario').DataTable({

            // ---- Mensajes en español ----
            language: {
                decimal:       '',
                emptyTable:    'No hay productos registrados en el inventario',
                info:          'Mostrando _START_ a _END_ de _TOTAL_ productos',
                infoEmpty:     'Mostrando 0 a 0 de 0 productos',
                infoFiltered:  '(filtrado de _MAX_ productos en total)',
                lengthMenu:    'Mostrar _MENU_ productos',
                loadingRecords:'Cargando...',
                processing:    'Procesando...',
                search:        'Buscar:',
                zeroRecords:   'No se encontraron productos con ese criterio',
                paginate: {
                    first:    'Primero',
                    last:     'Último',
                    next:     'Siguiente',
                    previous: 'Anterior'
                },
                aria: {
                    sortAscending:  ': activar para ordenar ascendente',
                    sortDescending: ': activar para ordenar descendente'
                }
            },

            // ---- Configuración de la tabla ----
            pageLength:  10,            // 10 filas por página por defecto
            order:       [[0, 'desc']], // Ordenar por ID descendente (más reciente primero)
            responsive:  true,          // Se adapta a pantallas pequeñas

            // Ocultamos la columna de búsqueda nativa de DataTables
            // porque usamos nuestro propio input .campo-buscar
            dom: 'lrtip'
        });
    }

    /* ----------------------------------------------------------
       buscarEnTabla()
       Conecta el input personalizado (.campo-buscar) con el filtro
       interno de DataTables.
    ---------------------------------------------------------- */
    buscarEnTabla() {
        if (this.dtInventario) {
            // .search() de DataTables filtra todas las columnas
            this.dtInventario.search(this.inputBuscar.value).draw();
        }
    }

    /* ----------------------------------------------------------
       confirmarEliminacion(form)
       Muestra un diálogo de SweetAlert2 antes de enviar el
       formulario de eliminación. Solo envía si el usuario confirma.

       @param {HTMLFormElement} form - El formulario que se intentó enviar
    ---------------------------------------------------------- */
    confirmarEliminacion(form) {
        Swal.fire({
            title:              '¿Eliminar producto?',
            text:               'El lote será desactivado del inventario.',
            icon:               'warning',
            showCancelButton:   true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Sí, eliminar',
            cancelButtonText:   'Cancelar'
        }).then((result) => {
            // Solo enviamos el formulario si el representante confirmó
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

}

/* ============================================================
   Instanciar la clase al cargar el script.
   El constructor ya espera el evento DOMContentLoaded internamente.
   ============================================================ */
new GestionInventario();
