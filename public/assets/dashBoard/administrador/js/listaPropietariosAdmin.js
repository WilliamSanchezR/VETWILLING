const BASE_URL_PROP = window.BASE_URL;

class ListaPropietariosAdmin {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.initDataTable();
        this.bindEvents();
        this.cargarPropietarios();
    }

    cacheDom() {
        this.tabla              = null;
        this.inputBuscar        = document.getElementById('buscarPropietarios');
        this.btnOrdenar         = document.getElementById('btnOrdenar');
        this.btnExport          = document.getElementById('btnExport');
        this.btnVer             = document.getElementById('btnVer');
    }

    bindEvents() {
        if (this.inputBuscar) {
            this.inputBuscar.oninput = () => {
                if (this.tabla) this.tabla.search(this.inputBuscar.value).draw();
            };
        }
        if (this.btnExport) {
            this.btnExport.onclick = () => this.exportarCSV();
        }
        if (this.btnOrdenar) {
            this.btnOrdenar.onclick = () => this.ordenar();
        }
    }

    initDataTable() {
        this.tabla = $('#tablaListaPropietarios').DataTable({
            language: {
                emptyTable:    'No hay propietarios registrados',
                info:          'Mostrando _START_ a _END_ de _TOTAL_ propietarios',
                infoEmpty:     'Mostrando 0 de 0 propietarios',
                infoFiltered:  '(filtrado de _MAX_ propietarios totales)',
                search:        'Buscar:',
                zeroRecords:   'No se encontraron propietarios',
                paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' },
            },
            pageLength: 10,
            order: [[2, 'asc']],
            columnDefs: [{ targets: [0, 6], orderable: false, searchable: false }],
            dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        });
    }

    cargarPropietarios() {
        fetch(`${BASE_URL_PROP}/admin/api/propietarios?accion=listar-admin`)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    this.renderTabla(data.propietarios);
                } else {
                    console.error('Error al cargar propietarios:', data.message);
                }
            })
            .catch(err => console.error('Error de red:', err));
    }

    renderTabla(propietarios) {
        this.tabla.clear();

        propietarios.forEach(p => {
            const foto = p.img_perfil
                ? `<img src="${BASE_URL_PROP}/public/uploads/usuarios/${p.img_perfil}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">`
                : '<i class="bi bi-person-circle fs-4"></i>';

            const estadoBadge = p.estado.toLowerCase() === 'activo'
                ? '<span class="estado-activo">Activo</span>'
                : '<span class="estado-inactivo">Inactivo</span>';

            // Subtarea 3/6: botón con SweetAlert confirm — solo para activos
            const acciones = p.estado.toLowerCase() === 'activo'
                ? `<button class="btn-accion btn-eliminar" title="Inhabilitar"
                        data-id="${p.id_propietario}"
                        data-nombre="${p.nombre_completo.replace(/"/g, '&quot;')}">
                       <i class="bi bi-trash"></i>
                   </button>`
                : '<span class="text-muted">—</span>';

            this.tabla.row.add([foto, p.numero_documento, p.nombre_completo, p.telefono, p.email, estadoBadge, acciones]);
        });

        this.tabla.draw();

        // Actualizar contador
        if (this.btnVer) {
            this.btnVer.innerHTML = `<i class="bi bi-eye"></i> Ver ${propietarios.length}`;
        }

        // Vincular eventos de eliminar
        document.querySelectorAll('.btn-eliminar[data-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id     = btn.getAttribute('data-id');
                const nombre = btn.getAttribute('data-nombre');
                this.confirmarEliminacion(id, nombre);
            });
        });
    }

    // Subtarea 3: solicitar confirmación de eliminación
    confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Inhabilitar propietario?',
            html: `El propietario <strong>${nombre}</strong> perderá acceso al sistema.<br>Esta acción puede revertirse desde la base de datos.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Sí, inhabilitar',
            cancelButtonText:   'Cancelar',
        }).then(result => {
            if (result.isConfirmed) {
                this.inhabilitarPropietario(id);
            }
        });
    }

    // Subtareas 4/5/7: llama al API que inhabilita + registra auditoría
    inhabilitarPropietario(id) {
        fetch(`${BASE_URL_PROP}/admin/api/propietarios?id=${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    // Subtarea 6: confirmación visual
                    Swal.fire('Inhabilitado', 'El propietario ha sido inhabilitado correctamente.', 'success')
                        .then(() => this.cargarPropietarios());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo inhabilitar el propietario.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'No se pudo completar la solicitud. Revisa tu conexión.', 'error'));
    }

    exportarCSV() {
        const data = this.tabla.rows({ search: 'applied' }).data();
        let csv = 'Documento,Nombre,Teléfono,Email,Estado\n';

        data.each(fila => {
            const cols = [1, 2, 3, 4, 5];
            const linea = cols.map(i => {
                const val = fila[i].toString().replace(/<[^>]*>/g, '').trim();
                return `"${val.replace(/"/g, '""')}"`;
            });
            csv += linea.join(',') + '\n';
        });

        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `propietarios_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    }

    ordenar() {
        const opciones = [
            '1 - Nombre (A-Z)', '2 - Nombre (Z-A)',
            '3 - Documento (Asc)', '4 - Documento (Desc)',
        ];
        const opcion = prompt('Selecciona ordenamiento:\n\n' + opciones.join('\n'));
        const map = { '1': [2, 'asc'], '2': [2, 'desc'], '3': [1, 'asc'], '4': [1, 'desc'] };
        if (map[opcion]) this.tabla.order(map[opcion]).draw();
    }
}

new ListaPropietariosAdmin();
