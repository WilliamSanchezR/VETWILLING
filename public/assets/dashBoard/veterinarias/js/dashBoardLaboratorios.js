let tabla = null;
let laboratorioData = [];
let catalogoPruebas = [];
let pacientesDisponibles = [];
let resultadoActual = null;

const APP_BASE = window.location.pathname.split('/').filter(Boolean)[0] || '';
const API_LAB_URL = `/${APP_BASE}/veterinaria/api/laboratorio`;

const buscarPaciente = document.getElementById('buscarPaciente');
const statTotal = document.getElementById('statTotal');
const statCompletados = document.getElementById('statCompletados');
const statPendientes = document.getElementById('statPendientes');
const statCancelados = document.getElementById('statCancelados');
const tablaBody = document.getElementById('tablaLaboratorioBody');
const tablaResultados = document.getElementById('tablaResultados');
const tablaDetalleResultados = document.getElementById('tablaDetalleResultados');
const detalleOrdenPaciente = document.getElementById('detalleOrdenPaciente');
const detalleOrdenMeta = document.getElementById('detalleOrdenMeta');
const resultadoPacienteInfo = document.getElementById('resultadoPacienteInfo');
const resultadoPruebaTitulo = document.getElementById('resultadoPruebaTitulo');
const resultadoOrdenPruebaId = document.getElementById('resultadoOrdenPruebaId');
const estadoPruebaResultado = document.getElementById('estadoPruebaResultado');
const observacionesResultado = document.getElementById('observacionesResultado');
const selectPacienteLaboratorio = document.getElementById('selectPacienteLaboratorio');
const selectPrioridadLaboratorio = document.getElementById('selectPrioridadLaboratorio');
const motivoOrdenLaboratorio = document.getElementById('motivoOrdenLaboratorio');
const observacionesOrdenLaboratorio = document.getElementById('observacionesOrdenLaboratorio');
const listaPruebasCatalogo = document.getElementById('listaPruebasCatalogo');
const labCatalogStatus = document.getElementById('labCatalogStatus');

const modalDetalle = new bootstrap.Modal(document.getElementById('exampleModal'));
const modalResultado = new bootstrap.Modal(document.getElementById('modalResultadoLab'));
const modalNuevaOrden = new bootstrap.Modal(document.getElementById('modalNuevaOrdenLab'));

function apiGet(action, params = {}) {
    const url = new URL(API_LAB_URL, window.location.origin);
    url.searchParams.set('action', action);
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, value);
        }
    });

    return fetch(url.toString()).then(async response => {
        const payload = await response.json();
        if (!response.ok || payload.status !== 'success') {
            throw new Error(payload.message || 'Error de API');
        }
        return payload.data;
    });
}

function apiPost(action, data = {}) {
    return fetch(API_LAB_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, ...data })
    }).then(async response => {
        const payload = await response.json();
        if (!response.ok || payload.status !== 'success') {
            throw new Error(payload.message || 'Error de API');
        }
        return payload.data;
    });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function mostrarEstadoIcono(estado) {
    switch (estado) {
        case '2':
            return '<i class="bi bi-check-circle-fill completado-status" title="Completado"></i>';
        case '3':
            return '<i class="bi bi-x-circle-fill cancelado-status" title="Cancelado"></i>';
        default:
            return '<i class="bi bi-clock-history pendiente-status" title="Pendiente"></i>';
    }
}

function mostrarEstadoTexto(estadoVista) {
    switch (estadoVista) {
        case '2':
            return 'Completado';
        case '3':
            return 'Cancelado';
        default:
            return 'Pendiente';
    }
}

function badgeEstadoPrueba(estado) {
    let clase = 'text-bg-secondary';
    if (estado === 'Pendiente' || estado === 'En proceso') clase = 'text-bg-warning';
    if (estado === 'Procesada' || estado === 'Validada') clase = 'text-bg-success';
    if (estado === 'Cancelada') clase = 'text-bg-danger';
    return `<span class="badge ${clase}">${escapeHtml(estado)}</span>`;
}

function badgeMeta(label, value, className = 'text-bg-light') {
    return `<span class="badge ${className}">${escapeHtml(label)}: ${escapeHtml(value)}</span>`;
}

function pintarStats(stats) {
    if (statTotal) statTotal.textContent = Number(stats.total_examenes || 0);
    if (statCompletados) statCompletados.textContent = Number(stats.completados || 0);
    if (statPendientes) statPendientes.textContent = Number(stats.pendientes || 0);
    if (statCancelados) statCancelados.textContent = Number(stats.cancelados || 0);
}

function renderTablaPacientes(listaPacientes) {
    tablaBody.innerHTML = '';

    listaPacientes.forEach(item => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td class="td-search-icon">
                <button type="button" class="boton-accion-tabla btn-ver-orden" data-id-orden="${item.id_orden_laboratorio}" title="Ver orden">
                    <i class="bi bi-search"></i>
                </button>
            </td>
            <td>${escapeHtml(item.folio)}</td>
            <td>${escapeHtml(item.fecha)}</td>
            <td>${escapeHtml(item.propietario)}</td>
            <td>${escapeHtml(item.nombreMascota)}</td>
            <td>${escapeHtml(item.animal)}</td>
            <td>${escapeHtml(item.raza)}</td>
            <td>${escapeHtml(item.cantLaboratorios)}</td>
            <td class="td-status">${mostrarEstadoIcono(item.estado)}</td>
        `;
        tablaBody.appendChild(fila);
    });

    if ($.fn.DataTable.isDataTable('#tabla-pacientes')) {
        $('#tabla-pacientes').DataTable().destroy();
    }

    tabla = $('#tabla-pacientes').DataTable({
        language: {
            decimal: '',
            emptyTable: 'No hay órdenes de laboratorio disponibles',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ órdenes',
            infoEmpty: 'Mostrando 0 a 0 de 0 órdenes',
            infoFiltered: '(filtrado de _MAX_ órdenes totales)',
            thousands: ',',
            lengthMenu: 'Mostrar _MENU_ órdenes',
            loadingRecords: 'Cargando...',
            processing: 'Procesando...',
            search: 'Buscar:',
            zeroRecords: 'No se encontraron órdenes de laboratorio',
            paginate: {
                first: 'Primera',
                last: 'Última',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        },
        pageLength: 9,
        lengthMenu: [[9, 15, 25, 50, -1], [9, 15, 25, 50, 'Todas']],
        order: [[2, 'desc']],
        columnDefs: [{ targets: [0, -1], orderable: false, searchable: false }],
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
}

function pintarPacienteInfo(container, data) {
    container.innerHTML = `
        <span>Fecha: ${escapeHtml(data.fecha || data.fecha_orden || '—')}</span>
        <span>Propietario: ${escapeHtml(data.propietario || '—')}</span>
        <span>Nombre Paciente: ${escapeHtml(data.nombreMascota || '—')}</span>
        <span>Tipo de Animal: ${escapeHtml(data.animal || '—')}</span>
        <span>Raza: ${escapeHtml(data.raza || '—')}</span>
        <span>Sexo: ${escapeHtml(data.sexo || '—')}</span>
    `;
}

function renderDetalleOrden(detalle) {
    pintarPacienteInfo(detalleOrdenPaciente, {
        fecha: detalle.fecha,
        propietario: detalle.propietario,
        nombreMascota: detalle.nombreMascota,
        animal: detalle.animal,
        raza: detalle.raza,
        sexo: detalle.sexo
    });

    detalleOrdenMeta.innerHTML = [
        badgeMeta('Estado', detalle.estado_orden, 'text-bg-success'),
        badgeMeta('Prioridad', detalle.prioridad, 'text-bg-primary'),
        badgeMeta('Motivo', detalle.motivo || 'Sin motivo', 'text-bg-light')
    ].join('');

    tablaResultados.innerHTML = '';
    detalle.pruebas.forEach(prueba => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>
                <button type="button" class="boton-accion-tabla btn-ver-prueba" data-id-orden-prueba="${prueba.id_orden_prueba}" title="Ver resultado">
                    <i class="bi bi-search"></i>
                </button>
            </td>
            <td>${escapeHtml(prueba.nombre_prueba)}</td>
            <td>${badgeEstadoPrueba(prueba.estado_prueba)}</td>
            <td>${escapeHtml(prueba.fecha || detalle.fecha)}</td>
        `;
        tablaResultados.appendChild(fila);
    });
}

function renderResultadoFila(data) {
    const rango = data.rango_referencia_texto || (
        data.valor_referencia_min !== null && data.valor_referencia_max !== null
            ? `${data.valor_referencia_min} - ${data.valor_referencia_max}`
            : 'Sin referencia'
    );

    let inputResultado = '';
    if (data.tipo_resultado === 'NUMERICO') {
        inputResultado = `<input type="number" step="0.01" id="inputResultadoNumerico" class="input-historial" value="${escapeHtml(data.resultado_numerico ?? '')}">`;
    } else if (data.tipo_resultado === 'BOOLEANO') {
        inputResultado = `
            <select id="inputResultadoBooleano" class="select-filtro">
                <option value="">Seleccione</option>
                <option value="1" ${(String(data.resultado_booleano) === '1') ? 'selected' : ''}>Positivo</option>
                <option value="0" ${(String(data.resultado_booleano) === '0') ? 'selected' : ''}>Negativo</option>
            </select>`;
    } else {
        inputResultado = `<input type="text" id="inputResultadoTexto" class="input-historial" value="${escapeHtml(data.resultado_texto ?? '')}">`;
    }

    tablaDetalleResultados.innerHTML = `
        <tr>
            <td>${escapeHtml(data.nombre_prueba)}</td>
            <td>${inputResultado}</td>
            <td><input type="text" id="inputUnidadResultado" class="input-historial" value="${escapeHtml(data.unidad_resultado || data.unidad_default || '')}"></td>
            <td>${escapeHtml(rango)}</td>
        </tr>
    `;
}

function renderResultadoModal(data) {
    resultadoActual = data;
    resultadoOrdenPruebaId.value = data.id_orden_prueba;
    pintarPacienteInfo(resultadoPacienteInfo, {
        fecha: data.fecha_orden,
        propietario: data.propietario,
        nombreMascota: data.nombreMascota,
        animal: data.animal,
        raza: data.raza,
        sexo: data.sexo
    });
    resultadoPruebaTitulo.textContent = `Laboratorio: ${data.nombre_prueba}`;
    estadoPruebaResultado.value = data.estado_prueba || 'Procesada';
    observacionesResultado.value = data.observaciones || '';
    renderResultadoFila(data);
}

function renderPacientesSelect() {
    selectPacienteLaboratorio.innerHTML = '<option value="">Seleccione un paciente</option>';
    pacientesDisponibles.forEach(paciente => {
        const option = document.createElement('option');
        option.value = paciente.id_paciente;
        option.textContent = `${paciente.nombre_mascota} · ${paciente.propietario} · ${paciente.especie}`;
        selectPacienteLaboratorio.appendChild(option);
    });
}

function renderCatalogoPruebas() {
    listaPruebasCatalogo.innerHTML = '';

    if (catalogoPruebas.length === 0) {
        labCatalogStatus.textContent = 'No hay pruebas cargadas en el catálogo';
        listaPruebasCatalogo.innerHTML = '<div class="text-muted">No hay pruebas disponibles. Agrega registros al catálogo para crear órdenes.</div>';
        return;
    }

    labCatalogStatus.textContent = `${catalogoPruebas.length} pruebas disponibles`;
    catalogoPruebas.forEach(prueba => {
        const item = document.createElement('label');
        item.className = 'lab-check-item';
        item.innerHTML = `
            <input type="checkbox" class="check-prueba-lab" value="${prueba.id_prueba_catalogo}">
            <div>
                <strong>${escapeHtml(prueba.nombre_prueba)}</strong>
                <small>${escapeHtml(prueba.categoria || 'Sin categoría')} · ${escapeHtml(prueba.codigo_prueba)}</small>
                <small>${escapeHtml(prueba.unidad_default || 'Sin unidad')} · ${escapeHtml(prueba.rango_referencia_texto || 'Sin rango')}</small>
            </div>
        `;
        listaPruebasCatalogo.appendChild(item);
    });
}

function obtenerPruebasSeleccionadas() {
    return Array.from(document.querySelectorAll('.check-prueba-lab:checked')).map(input => Number(input.value));
}

function resetModalNuevaOrden() {
    selectPacienteLaboratorio.value = '';
    selectPrioridadLaboratorio.value = 'Normal';
    motivoOrdenLaboratorio.value = '';
    observacionesOrdenLaboratorio.value = '';
    document.querySelectorAll('.check-prueba-lab').forEach(input => {
        input.checked = false;
    });
}

function cargarListado() {
    return apiGet('listar').then(data => {
        laboratorioData = Array.isArray(data) ? data : [];
        renderTablaPacientes(laboratorioData);
    });
}

function cargarStats() {
    return apiGet('estadisticas').then(pintarStats);
}

function cargarCatalogosModal() {
    return Promise.all([
        apiGet('pacientes').then(data => {
            pacientesDisponibles = Array.isArray(data) ? data : [];
            renderPacientesSelect();
        }),
        apiGet('catalogo').then(data => {
            catalogoPruebas = Array.isArray(data) ? data : [];
            renderCatalogoPruebas();
        })
    ]);
}

function abrirDetalleOrden(idOrden) {
    apiGet('detalle', { id_orden_laboratorio: idOrden })
        .then(detalle => {
            renderDetalleOrden(detalle);
            modalDetalle.show();
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'No se pudo cargar la orden');
        });
}

function abrirResultadoPrueba(idOrdenPrueba) {
    apiGet('resultado', { id_orden_prueba: idOrdenPrueba })
        .then(data => {
            renderResultadoModal(data);
            modalResultado.show();
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'No se pudo cargar el resultado');
        });
}

function guardarResultadoActual() {
    if (!resultadoActual) {
        return;
    }

    const payload = {
        id_orden_prueba: Number(resultadoOrdenPruebaId.value),
        estado_prueba: estadoPruebaResultado.value,
        observaciones: observacionesResultado.value.trim(),
    };

    if (resultadoActual.tipo_resultado === 'NUMERICO') {
        payload.resultado_numerico = document.getElementById('inputResultadoNumerico')?.value ?? '';
    } else if (resultadoActual.tipo_resultado === 'BOOLEANO') {
        payload.resultado_booleano = document.getElementById('inputResultadoBooleano')?.value ?? '';
    } else {
        payload.resultado_texto = document.getElementById('inputResultadoTexto')?.value ?? '';
    }

    payload.unidad_resultado = document.getElementById('inputUnidadResultado')?.value ?? '';

    apiPost('guardar-resultado', payload)
        .then(() => Promise.all([cargarListado(), cargarStats(), apiGet('detalle', { id_orden_laboratorio: resultadoActual.id_orden_laboratorio })]))
        .then(([, , detalle]) => {
            renderDetalleOrden(detalle);
            modalResultado.hide();
            alert('Resultado guardado correctamente');
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'No se pudo guardar el resultado');
        });
}

function crearNuevaOrden() {
    const payload = {
        id_paciente: Number(selectPacienteLaboratorio.value),
        prioridad: selectPrioridadLaboratorio.value,
        motivo: motivoOrdenLaboratorio.value.trim(),
        observaciones: observacionesOrdenLaboratorio.value.trim(),
        pruebas: obtenerPruebasSeleccionadas(),
    };

    apiPost('crear-orden', payload)
        .then(() => Promise.all([cargarListado(), cargarStats()]))
        .then(() => {
            resetModalNuevaOrden();
            modalNuevaOrden.hide();
            alert('Orden de laboratorio creada correctamente');
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'No se pudo crear la orden');
        });
}

function exportarACSV() {
    if (!tabla) return;

    try {
        const data = tabla.rows({ search: 'applied' }).data();
        let csv = 'No.,Fecha,Propietario,Nombre Mascota,Animal,Raza,Laboratorios,Estado\n';

        data.each(function (fila) {
            const filaLimpia = [];
            for (let i = 1; i < fila.length; i++) {
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
    } catch (error) {
        console.error(error);
        alert('Error al exportar CSV. Revisa la consola.');
    }
}

$('#btnOrdenar').on('click', function () {
    if (!tabla) return;

    const opciones = [
        '1 - Fecha (más antigua primero)',
        '2 - Fecha (más reciente primero)',
        '3 - Propietario (A-Z)',
        '4 - Propietario (Z-A)',
        '5 - Mascota (A-Z)'
    ];

    const opcion = prompt('Selecciona el ordenamiento:\n\n' + opciones.join('\n'));

    switch (opcion) {
        case '1':
            tabla.order([2, 'asc']).draw();
            break;
        case '2':
            tabla.order([2, 'desc']).draw();
            break;
        case '3':
            tabla.order([3, 'asc']).draw();
            break;
        case '4':
            tabla.order([3, 'desc']).draw();
            break;
        case '5':
            tabla.order([4, 'asc']).draw();
            break;
        default:
            if (opcion !== null) alert('Opción no válida');
            break;
    }
});

$('#btnExport').on('click', exportarACSV);
$('#btnAgregarNuevo').on('click', function () {
    resetModalNuevaOrden();
    modalNuevaOrden.show();
});
$('#btnGuardarOrdenLab').on('click', crearNuevaOrden);
$('#btn-guardar-resultados').on('click', guardarResultadoActual);

$('#buscarPaciente').on('keyup change', function () {
    if (!tabla) return;
    tabla.search(this.value).draw();
});

$('.campo-buscar i').on('click', function () {
    if (!buscarPaciente) return;
    buscarPaciente.value = '';
    $('#buscarPaciente').trigger('keyup');
});

document.addEventListener('click', event => {
    const detailButton = event.target.closest('.btn-ver-orden');
    if (detailButton) {
        abrirDetalleOrden(Number(detailButton.dataset.idOrden));
        return;
    }

    const resultButton = event.target.closest('.btn-ver-prueba');
    if (resultButton) {
        abrirResultadoPrueba(Number(resultButton.dataset.idOrdenPrueba));
    }
});

document.addEventListener('DOMContentLoaded', () => {
    Promise.all([cargarListado(), cargarStats(), cargarCatalogosModal()]).catch(error => {
        console.error(error);
    });
});
