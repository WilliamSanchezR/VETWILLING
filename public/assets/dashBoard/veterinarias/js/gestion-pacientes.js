document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const endpoint = body.dataset.endpoint || '';
    const baseUrl = body.dataset.baseUrl || '';

    if (!endpoint) return;

    const tablaBody = document.getElementById('tablaHistorialesBody');

    const filtroPaciente = document.getElementById('filtroPaciente');
    const filtroFecha = document.getElementById('filtroFecha');
    const filtroVeterinario = document.getElementById('filtroVeterinario');
    const filtroAcceso = document.getElementById('filtroAcceso');

    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnNuevaAtencion = document.getElementById('btnNuevaAtencion');
    const btnExportarPdf = document.getElementById('btnExportarPdf');
    const btnExportarFichaPdf = document.getElementById('btnExportarFichaPdf');

    const campoIdHistorial = document.getElementById('campoIdHistorial');
    const campoIdPaciente = document.getElementById('campoIdPaciente');

    const campos = {
        paciente: document.getElementById('campoPaciente'),
        especie: document.getElementById('campoEspecie'),
        raza: document.getElementById('campoRaza'),
        fecha: document.getElementById('campoFecha'),
        vet: document.getElementById('campoVeterinario'),
        motivo: document.getElementById('campoMotivo'),
        diagnostico: document.getElementById('campoDiagnostico'),
        tratamiento: document.getElementById('campoTratamiento'),
        medicacion: document.getElementById('campoMedicacion'),
        observaciones: document.getElementById('campoObservaciones')
    };

    const detalleAcceso = document.getElementById('detalleAcceso');
    const detalleVersion = document.getElementById('detalleVersion');
    const detalleActualizacion = document.getElementById('detalleActualizacion');
    const selectVersionHistorial = document.getElementById('selectVersionHistorial');

    const vacunaTipo = document.getElementById('vacunaTipo');
    const vacunaDosis = document.getElementById('vacunaDosis');
    const vacunaFecha = document.getElementById('vacunaFecha');
    const vacunaObservaciones = document.getElementById('vacunaObservaciones');
    const btnGuardarVacuna = document.getElementById('btnGuardarVacuna');
    const listaVacunas = document.getElementById('listaVacunas');

    const tratamientoMedicamento = document.getElementById('tratamientoMedicamento');
    const tratamientoDosis = document.getElementById('tratamientoDosis');
    const tratamientoInicio = document.getElementById('tratamientoInicio');
    const tratamientoFin = document.getElementById('tratamientoFin');
    const tratamientoEstado = document.getElementById('tratamientoEstado');
    const tratamientoObservaciones = document.getElementById('tratamientoObservaciones');
    const btnGuardarTratamiento = document.getElementById('btnGuardarTratamiento');
    const listaTratamientos = document.getElementById('listaTratamientos');

    const consultaFecha = document.getElementById('consultaFecha');
    const consultaMotivo = document.getElementById('consultaMotivo');
    const consultaDiagnostico = document.getElementById('consultaDiagnostico');
    const consultaObservaciones = document.getElementById('consultaObservaciones');
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    const listaConsultas = document.getElementById('listaConsultas');

    const notaContenido = document.getElementById('notaContenido');
    const btnGuardarNota = document.getElementById('btnGuardarNota');
    const listaNotas = document.getElementById('listaNotas');

    const buscarVacunas = document.getElementById('buscarVacunas');
    const buscarTratamientos = document.getElementById('buscarTratamientos');
    const buscarConsultas = document.getElementById('buscarConsultas');
    const buscarNotas = document.getElementById('buscarNotas');

    const filtroFechaVacunas = document.getElementById('filtroFechaVacunas');
    const filtroEstadoTratamientos = document.getElementById('filtroEstadoTratamientos');
    const filtroFechaTratamientos = document.getElementById('filtroFechaTratamientos');
    const filtroFechaConsultas = document.getElementById('filtroFechaConsultas');
    const filtroFechaNotas = document.getElementById('filtroFechaNotas');

    const paginacionVacunas = document.getElementById('paginacionVacunas');
    const paginacionTratamientos = document.getElementById('paginacionTratamientos');
    const paginacionConsultas = document.getElementById('paginacionConsultas');
    const paginacionNotas = document.getElementById('paginacionNotas');

    let registros = [];
    let pendingSelectId = null;
    let fichaActual = null;

    const edicionModulo = {
        vacuna: 0,
        tratamiento: 0,
        consulta: 0,
        nota: 0
    };

    const paginacionModulo = {
        vacuna: { pagina: 1, porPagina: 4 },
        tratamiento: { pagina: 1, porPagina: 4 },
        consulta: { pagina: 1, porPagina: 4 },
        nota: { pagina: 1, porPagina: 4 }
    };

    function showToast(message, type) {
        let shell = document.getElementById('toastShell');
        if (!shell) {
            shell = document.createElement('div');
            shell.id = 'toastShell';
            shell.className = 'toast-shell';
            document.body.appendChild(shell);
        }

        const item = document.createElement('div');
        item.className = 'toast-mini ' + (type === 'error' ? 'error' : 'success');
        item.textContent = message;
        shell.appendChild(item);

        window.setTimeout(function() {
            item.remove();
        }, 3000);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toDateInput(value) {
        if (!value) return '';
        return String(value).slice(0, 10);
    }

    function fechaHumana(value) {
        if (!value) return '--';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);
        return date.toLocaleDateString('es-CO');
    }

    function fechaHoraHumana(value) {
        if (!value) return '--';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return String(value);
        return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    async function postJson(payload) {
        const response = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (!response.ok || result.status !== 'success') {
            throw new Error(result.message || 'No fue posible completar la operación');
        }

        return result;
    }

    function renderListadoModulo(contenedor, items, renderItem) {
        if (!Array.isArray(items) || items.length === 0) {
            contenedor.innerHTML = '<div class="modulo-vacio">Sin registros.</div>';
            return;
        }

        contenedor.innerHTML = '<div class="modulo-lista">' + items.map(renderItem).join('') + '</div>';
    }

    function obtenerBusqueda(tipo) {
        if (tipo === 'vacuna') return (buscarVacunas.value || '').trim().toLowerCase();
        if (tipo === 'tratamiento') return (buscarTratamientos.value || '').trim().toLowerCase();
        if (tipo === 'consulta') return (buscarConsultas.value || '').trim().toLowerCase();
        return (buscarNotas.value || '').trim().toLowerCase();
    }

    function coincideFiltroAvanzado(tipo, item) {
        if (tipo === 'vacuna') {
            const fecha = (filtroFechaVacunas.value || '').trim();
            return !fecha || toDateInput(item.fecha_aplicacion) === fecha;
        }

        if (tipo === 'tratamiento') {
            const estado = (filtroEstadoTratamientos.value || '').trim().toLowerCase();
            const fecha = (filtroFechaTratamientos.value || '').trim();
            const coincideEstado = !estado || String(item.estado || '').toLowerCase() === estado;
            const coincideFecha = !fecha || toDateInput(item.fecha_inicio) === fecha;
            return coincideEstado && coincideFecha;
        }

        if (tipo === 'consulta') {
            const fecha = (filtroFechaConsultas.value || '').trim();
            return !fecha || toDateInput(item.fecha_consulta) === fecha;
        }

        const fecha = (filtroFechaNotas.value || '').trim();
        return !fecha || toDateInput(item.created_at) === fecha;
    }

    function renderPaginacion(tipo, totalItems, totalPaginas) {
        const contenedor =
            tipo === 'vacuna' ? paginacionVacunas :
            tipo === 'tratamiento' ? paginacionTratamientos :
            tipo === 'consulta' ? paginacionConsultas : paginacionNotas;

        if (!contenedor) return;

        if (totalItems <= 0 || totalPaginas <= 1) {
            contenedor.innerHTML = '';
            return;
        }

        const actual = paginacionModulo[tipo].pagina;
        let html = '';
        for (let pagina = 1; pagina <= totalPaginas; pagina++) {
            html += '<button type="button" class="btn-pagina ' + (pagina === actual ? 'activa' : '') + '" data-pagina-modulo="' + tipo + '" data-pagina="' + pagina + '">' + pagina + '</button>';
        }
        contenedor.innerHTML = html;
    }

    function filtrarYPaginar(tipo, items) {
        const estado = paginacionModulo[tipo];
        const query = obtenerBusqueda(tipo);

        const filtrados = (items || []).filter(function(item) {
            const texto = JSON.stringify(item || {}).toLowerCase();
            const coincideTexto = !query || texto.indexOf(query) !== -1;
            return coincideTexto && coincideFiltroAvanzado(tipo, item || {});
        });

        const totalPaginas = Math.max(1, Math.ceil(filtrados.length / estado.porPagina));
        if (estado.pagina > totalPaginas) {
            estado.pagina = totalPaginas;
        }

        renderPaginacion(tipo, filtrados.length, totalPaginas);

        const inicio = (estado.pagina - 1) * estado.porPagina;
        return filtrados.slice(inicio, inicio + estado.porPagina);
    }

    function limpiarModulos() {
        fichaActual = null;

        edicionModulo.vacuna = 0;
        edicionModulo.tratamiento = 0;
        edicionModulo.consulta = 0;
        edicionModulo.nota = 0;

        paginacionModulo.vacuna.pagina = 1;
        paginacionModulo.tratamiento.pagina = 1;
        paginacionModulo.consulta.pagina = 1;
        paginacionModulo.nota.pagina = 1;

        btnGuardarVacuna.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar vacuna';
        btnGuardarTratamiento.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar tratamiento';
        btnGuardarConsulta.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar consulta';
        btnGuardarNota.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar nota';

        vacunaTipo.value = '';
        vacunaDosis.value = '';
        vacunaFecha.value = '';
        vacunaObservaciones.value = '';

        tratamientoMedicamento.value = '';
        tratamientoDosis.value = '';
        tratamientoInicio.value = '';
        tratamientoFin.value = '';
        tratamientoEstado.value = 'Activo';
        tratamientoObservaciones.value = '';

        consultaFecha.value = '';
        consultaMotivo.value = '';
        consultaDiagnostico.value = '';
        consultaObservaciones.value = '';

        notaContenido.value = '';

        buscarVacunas.value = '';
        buscarTratamientos.value = '';
        buscarConsultas.value = '';
        buscarNotas.value = '';

        filtroFechaVacunas.value = '';
        filtroEstadoTratamientos.value = '';
        filtroFechaTratamientos.value = '';
        filtroFechaConsultas.value = '';
        filtroFechaNotas.value = '';

        listaVacunas.innerHTML = '<div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>';
        listaTratamientos.innerHTML = '<div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>';
        listaConsultas.innerHTML = '<div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>';
        listaNotas.innerHTML = '<div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>';

        paginacionVacunas.innerHTML = '';
        paginacionTratamientos.innerHTML = '';
        paginacionConsultas.innerHTML = '';
        paginacionNotas.innerHTML = '';
    }

    function editarModulo(tipo, idItem) {
        if (!fichaActual) return;

        if (tipo === 'vacuna') {
            const item = (fichaActual.vacunas || []).find(function(row) {
                return Number(row.id_vacuna) === Number(idItem);
            });
            if (!item) return;
            edicionModulo.vacuna = Number(item.id_vacuna);
            vacunaTipo.value = item.tipo_vacuna || '';
            vacunaDosis.value = item.dosis || '';
            vacunaFecha.value = toDateInput(item.fecha_aplicacion);
            vacunaObservaciones.value = item.observaciones || '';
            btnGuardarVacuna.innerHTML = '<i class="bi bi-pencil-square"></i> Actualizar vacuna';
            return;
        }

        if (tipo === 'tratamiento') {
            const item = (fichaActual.tratamientos || []).find(function(row) {
                return Number(row.id_tratamiento) === Number(idItem);
            });
            if (!item) return;
            edicionModulo.tratamiento = Number(item.id_tratamiento);
            tratamientoMedicamento.value = item.medicamento || '';
            tratamientoDosis.value = item.dosis || '';
            tratamientoInicio.value = toDateInput(item.fecha_inicio);
            tratamientoFin.value = toDateInput(item.fecha_fin);
            tratamientoEstado.value = item.estado || 'Activo';
            tratamientoObservaciones.value = item.observaciones || '';
            btnGuardarTratamiento.innerHTML = '<i class="bi bi-pencil-square"></i> Actualizar tratamiento';
            return;
        }

        if (tipo === 'consulta') {
            const item = (fichaActual.consultas || []).find(function(row) {
                return Number(row.id_consulta) === Number(idItem);
            });
            if (!item) return;
            edicionModulo.consulta = Number(item.id_consulta);
            consultaFecha.value = toDateInput(item.fecha_consulta);
            consultaMotivo.value = item.motivo || '';
            consultaDiagnostico.value = item.diagnostico || '';
            consultaObservaciones.value = item.observaciones || '';
            btnGuardarConsulta.innerHTML = '<i class="bi bi-pencil-square"></i> Actualizar consulta';
            return;
        }

        if (tipo === 'nota') {
            const item = (fichaActual.notas || []).find(function(row) {
                return Number(row.id_nota) === Number(idItem);
            });
            if (!item) return;
            edicionModulo.nota = Number(item.id_nota);
            notaContenido.value = item.nota || '';
            btnGuardarNota.innerHTML = '<i class="bi bi-pencil-square"></i> Actualizar nota';
        }
    }

    async function eliminarModulo(tipo, idItem) {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente antes de eliminar registros.', 'error');
            return;
        }

        let payload = null;
        let mensaje = '¿Confirmas esta eliminación?';

        if (tipo === 'vacuna') {
            payload = { accion: 'eliminar_vacuna', id_paciente: idPaciente, id_vacuna: Number(idItem) };
            mensaje = '¿Eliminar esta vacuna?';
        } else if (tipo === 'tratamiento') {
            payload = { accion: 'eliminar_tratamiento', id_paciente: idPaciente, id_tratamiento: Number(idItem) };
            mensaje = '¿Eliminar este tratamiento?';
        } else if (tipo === 'consulta') {
            payload = { accion: 'eliminar_consulta', id_paciente: idPaciente, id_consulta: Number(idItem) };
            mensaje = '¿Eliminar esta consulta?';
        } else if (tipo === 'nota') {
            payload = { accion: 'eliminar_nota', id_paciente: idPaciente, id_nota: Number(idItem) };
            mensaje = '¿Eliminar esta nota clínica?';
        }

        if (!payload || !window.confirm(mensaje)) return;

        try {
            await postJson(payload);
            await cargarFichaClinica(idPaciente);
            showToast('Registro eliminado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'No se pudo eliminar el registro.', 'error');
        }
    }

    function renderFichaClinica(ficha) {
        fichaActual = ficha || null;

        renderListadoModulo(listaVacunas, filtrarYPaginar('vacuna', ficha && ficha.vacunas ? ficha.vacunas : []), function(item) {
            return '<div class="item-modulo">' +
                '<div class="item-modulo-head"><strong>' + escapeHtml(item.tipo_vacuna || '--') + '</strong><span>' + escapeHtml(fechaHumana(item.fecha_aplicacion)) + '</span></div>' +
                '<div class="item-modulo-body">Dosis: ' + escapeHtml(item.dosis || '--') + '</div>' +
                '<div class="item-modulo-foot">' + escapeHtml(item.observaciones || 'Sin observaciones') + '</div>' +
                '<div class="item-modulo-actions">' +
                '<button type="button" class="btn-mini" data-modulo-action="editar" data-modulo="vacuna" data-id="' + escapeHtml(item.id_vacuna || '') + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
                '<button type="button" class="btn-mini btn-mini-danger" data-modulo-action="eliminar" data-modulo="vacuna" data-id="' + escapeHtml(item.id_vacuna || '') + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '</div>';
        });

        renderListadoModulo(listaTratamientos, filtrarYPaginar('tratamiento', ficha && ficha.tratamientos ? ficha.tratamientos : []), function(item) {
            return '<div class="item-modulo">' +
                '<div class="item-modulo-head"><strong>' + escapeHtml(item.medicamento || '--') + '</strong><span>' + escapeHtml(item.estado || 'Activo') + '</span></div>' +
                '<div class="item-modulo-body">Dosis: ' + escapeHtml(item.dosis || '--') + '</div>' +
                '<div class="item-modulo-foot">Inicio: ' + escapeHtml(fechaHumana(item.fecha_inicio)) + ' · Fin: ' + escapeHtml(fechaHumana(item.fecha_fin) === '--' ? 'N/A' : fechaHumana(item.fecha_fin)) + '</div>' +
                '<div class="item-modulo-actions">' +
                '<button type="button" class="btn-mini" data-modulo-action="editar" data-modulo="tratamiento" data-id="' + escapeHtml(item.id_tratamiento || '') + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
                '<button type="button" class="btn-mini btn-mini-danger" data-modulo-action="eliminar" data-modulo="tratamiento" data-id="' + escapeHtml(item.id_tratamiento || '') + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '</div>';
        });

        renderListadoModulo(listaConsultas, filtrarYPaginar('consulta', ficha && ficha.consultas ? ficha.consultas : []), function(item) {
            return '<div class="item-modulo">' +
                '<div class="item-modulo-head"><strong>' + escapeHtml(item.motivo || '--') + '</strong><span>' + escapeHtml(fechaHoraHumana(item.fecha_consulta)) + '</span></div>' +
                '<div class="item-modulo-body">Diagnóstico: ' + escapeHtml(item.diagnostico || 'Sin diagnóstico') + '</div>' +
                '<div class="item-modulo-foot">' + escapeHtml(item.observaciones || 'Sin observaciones') + '</div>' +
                '<div class="item-modulo-actions">' +
                '<button type="button" class="btn-mini" data-modulo-action="editar" data-modulo="consulta" data-id="' + escapeHtml(item.id_consulta || '') + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
                '<button type="button" class="btn-mini btn-mini-danger" data-modulo-action="eliminar" data-modulo="consulta" data-id="' + escapeHtml(item.id_consulta || '') + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '</div>';
        });

        renderListadoModulo(listaNotas, filtrarYPaginar('nota', ficha && ficha.notas ? ficha.notas : []), function(item) {
            return '<div class="item-modulo">' +
                '<div class="item-modulo-head"><strong>Nota clínica</strong><span>' + escapeHtml(fechaHoraHumana(item.created_at)) + '</span></div>' +
                '<div class="item-modulo-body">' + escapeHtml(item.nota || '--') + '</div>' +
                '<div class="item-modulo-actions">' +
                '<button type="button" class="btn-mini" data-modulo-action="editar" data-modulo="nota" data-id="' + escapeHtml(item.id_nota || '') + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
                '<button type="button" class="btn-mini btn-mini-danger" data-modulo-action="eliminar" data-modulo="nota" data-id="' + escapeHtml(item.id_nota || '') + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '</div>';
        });
    }

    async function cargarFichaClinica(idPaciente) {
        if (!idPaciente) {
            limpiarModulos();
            return;
        }

        try {
            const result = await postJson({
                accion: 'ficha_clinica',
                id_paciente: Number(idPaciente)
            });
            renderFichaClinica(result.data || {});
        } catch (error) {
            limpiarModulos();
            showToast(error.message || 'No se pudo cargar la ficha clínica.', 'error');
        }
    }

    function limpiarFormulario() {
        campoIdHistorial.value = '';
        campoIdPaciente.value = '';
        campos.paciente.value = '';
        campos.especie.value = '';
        campos.raza.value = '';
        campos.fecha.value = '';
        campos.vet.value = '';
        campos.motivo.value = '';
        campos.diagnostico.value = '';
        campos.tratamiento.value = '';
        campos.medicacion.value = '';
        campos.observaciones.value = '';
        detalleAcceso.textContent = 'Autorizado';
        detalleVersion.textContent = '--';
        detalleActualizacion.textContent = '--';
        selectVersionHistorial.innerHTML = '<option value="">Selecciona una atención</option>';
        limpiarModulos();
    }

    async function cargarVersionesHistorial(idHistorial) {
        if (!idHistorial) {
            selectVersionHistorial.innerHTML = '<option value="">Selecciona una atención</option>';
            return;
        }

        try {
            const result = await postJson({
                accion: 'listar_versiones_historial',
                id_historial: idHistorial
            });

            const versiones = result.data || [];
            
            if (versiones.length === 0) {
                selectVersionHistorial.innerHTML = '<option value="">Sin versiones disponibles</option>';
                return;
            }

            selectVersionHistorial.innerHTML = '<option value="">Seleccionar versión...</option>' +
                versiones.map(function(v) {
                    const fecha = fechaHumana(v.fecha_atencion);
                    const version = 'v' + (v.version_registro || '1');
                    const seleccionado = String(v.id_historial) === String(idHistorial) ? ' selected' : '';
                    return '<option value="' + escapeHtml(v.id_historial) + '"' + seleccionado + '>' +
                        version + ' - ' + fecha + ' - ' + escapeHtml(v.veterinario_responsable || 'N/A') +
                        '</option>';
                }).join('');
        } catch (err) {
            console.error('Error al cargar versiones:', err);
            selectVersionHistorial.innerHTML = '<option value="">Error al cargar versiones</option>';
        }
    }

    function cargarDetalle(registro) {
        if (!registro) return;

        campoIdHistorial.value = registro.id_historial || '';
        campoIdPaciente.value = registro.id_paciente || '';

        campos.paciente.value = registro.paciente_nombre || '';
        campos.especie.value = registro.especie || '';
        campos.raza.value = registro.raza || '';
        campos.fecha.value = toDateInput(registro.fecha_atencion) || new Date().toISOString().slice(0, 10);
        campos.vet.value = registro.veterinario_responsable || '';
        campos.motivo.value = registro.motivo_consulta || '';
        campos.diagnostico.value = registro.diagnostico || '';
        campos.tratamiento.value = registro.tratamientos_aplicados || '';
        campos.medicacion.value = registro.medicacion_recetada || '';
        campos.observaciones.value = registro.observaciones_adicionales || '';

        detalleAcceso.textContent = registro.acceso || 'Autorizado';
        detalleVersion.textContent = registro.version_registro ? ('v' + registro.version_registro) : 'Nueva';
        detalleActualizacion.textContent = fechaHoraHumana(registro.updated_at || registro.fecha_atencion);
        
        cargarVersionesHistorial(registro.id_historial || '');
        cargarFichaClinica(registro.id_paciente || '');
    }

    function renderTabla() {
        if (!Array.isArray(registros) || registros.length === 0) {
            tablaBody.innerHTML = '<tr><td colspan="7" class="text-center py-3">No tienes pacientes vinculados o no hay historiales registrados.</td></tr>';
            limpiarFormulario();
            return;
        }

        tablaBody.innerHTML = registros.map(function(item) {
            const activo = pendingSelectId && Number(item.id_historial || 0) === Number(pendingSelectId) ? 'active' : '';
            const version = item.version_registro ? 'v' + item.version_registro : 'Nueva';
            const acceso = item.acceso || 'Autorizado';

            return '<tr class="' + activo + '" ' +
                'data-id-historial="' + escapeHtml(item.id_historial || '') + '" ' +
                'data-id-paciente="' + escapeHtml(item.id_paciente || '') + '" ' +
                'data-paciente="' + escapeHtml(item.paciente_nombre || '') + '" ' +
                'data-especie="' + escapeHtml(item.especie || '') + '" ' +
                'data-raza="' + escapeHtml(item.raza || '') + '" ' +
                'data-fecha="' + escapeHtml(toDateInput(item.fecha_atencion)) + '" ' +
                'data-vet="' + escapeHtml(item.veterinario_responsable || '') + '" ' +
                'data-motivo="' + escapeHtml(item.motivo_consulta || '') + '" ' +
                'data-diagnostico="' + escapeHtml(item.diagnostico || '') + '" ' +
                'data-tratamiento="' + escapeHtml(item.tratamientos_aplicados || '') + '" ' +
                'data-medicacion="' + escapeHtml(item.medicacion_recetada || '') + '" ' +
                'data-observaciones="' + escapeHtml(item.observaciones_adicionales || '') + '" ' +
                'data-acceso="' + escapeHtml(acceso) + '" ' +
                'data-version="' + escapeHtml(item.version_registro || '') + '" ' +
                'data-updated-at="' + escapeHtml(item.updated_at || item.fecha_atencion || '') + '">' +
                '<td class="paciente-cell"><strong>' + escapeHtml(item.paciente_nombre || '--') + '</strong><small>' + escapeHtml((item.especie || '--') + ' · ' + (item.raza || '--')) + '</small></td>' +
                '<td>' + escapeHtml(fechaHumana(item.fecha_atencion)) + '</td>' +
                '<td>' + escapeHtml(item.veterinario_responsable || '--') + '</td>' +
                '<td>' + escapeHtml(item.motivo_consulta || 'Sin registro') + '</td>' +
                '<td><span class="badge-acceso permitido"><i class="bi bi-check-circle"></i> ' + escapeHtml(acceso) + '</span></td>' +
                '<td><span class="badge-version">' + escapeHtml(version) + '</span></td>' +
                '<td><div class="acciones-registro"><button class="btn-mini" title="Seleccionar"><i class="bi bi-eye"></i></button></div></td>' +
                '</tr>';
        }).join('');

        const filas = tablaBody.querySelectorAll('tr[data-id-paciente]');
        filas.forEach(function(fila) {
            fila.addEventListener('click', function() {
                filas.forEach(function(item) {
                    item.classList.remove('active');
                });
                fila.classList.add('active');

                const registro = {
                    id_historial: fila.dataset.idHistorial || '',
                    id_paciente: fila.dataset.idPaciente || '',
                    paciente_nombre: fila.dataset.paciente || '',
                    especie: fila.dataset.especie || '',
                    raza: fila.dataset.raza || '',
                    fecha_atencion: fila.dataset.fecha || '',
                    veterinario_responsable: fila.dataset.vet || '',
                    motivo_consulta: fila.dataset.motivo || '',
                    diagnostico: fila.dataset.diagnostico || '',
                    tratamientos_aplicados: fila.dataset.tratamiento || '',
                    medicacion_recetada: fila.dataset.medicacion || '',
                    observaciones_adicionales: fila.dataset.observaciones || '',
                    acceso: fila.dataset.acceso || 'Autorizado',
                    version_registro: fila.dataset.version || '',
                    updated_at: fila.dataset.updatedAt || ''
                };

                cargarDetalle(registro);
            });
        });

        let filaInicial = null;
        if (pendingSelectId) {
            filaInicial = tablaBody.querySelector('tr[data-id-historial="' + pendingSelectId + '"]');
        }
        if (!filaInicial) {
            filaInicial = tablaBody.querySelector('tr[data-id-paciente]');
        }
        pendingSelectId = null;

        if (filaInicial) {
            filaInicial.click();
        }
    }

    function actualizarFiltroVeterinarios() {
        const actual = filtroVeterinario.value;
        const values = [];

        registros.forEach(function(item) {
            const nombre = (item.veterinario_responsable || '').trim();
            if (nombre && values.indexOf(nombre) === -1) {
                values.push(nombre);
            }
        });

        filtroVeterinario.innerHTML = '<option value="">Todos</option>' + values.map(function(item) {
            return '<option value="' + escapeHtml(item) + '">' + escapeHtml(item) + '</option>';
        }).join('');

        if (actual && values.indexOf(actual) !== -1) {
            filtroVeterinario.value = actual;
        }
    }

    async function cargarHistoriales(filtros) {
        tablaBody.innerHTML = '<tr><td colspan="7" class="text-center py-3">Cargando historiales...</td></tr>';

        try {
            const payload = await postJson(Object.assign({
                accion: 'listar_historiales'
            }, filtros || {}));

            registros = Array.isArray(payload.data) ? payload.data : [];
            actualizarFiltroVeterinarios();
            renderTabla();
        } catch (error) {
            tablaBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">' + escapeHtml(error.message || 'Error de carga') + '</td></tr>';
            limpiarFormulario();
            showToast(error.message || 'No se pudieron cargar los historiales.', 'error');
        }
    }

    function obtenerFiltrosActuales() {
        return {
            paciente: (filtroPaciente.value || '').trim(),
            fecha: filtroFecha.value || '',
            veterinario: filtroVeterinario.value || '',
            acceso: filtroAcceso.value || ''
        };
    }

    btnFiltrar.addEventListener('click', function() {
        cargarHistoriales(obtenerFiltrosActuales());
    });

    btnLimpiar.addEventListener('click', function() {
        filtroPaciente.value = '';
        filtroFecha.value = '';
        filtroVeterinario.value = '';
        filtroAcceso.value = '';
        cargarHistoriales({});
    });

    btnNuevaAtencion.addEventListener('click', function() {
        if (!campoIdPaciente.value) {
            showToast('Selecciona primero un paciente vinculado en la tabla.', 'error');
            return;
        }

        campoIdHistorial.value = '';
        campos.fecha.value = new Date().toISOString().slice(0, 10);
        campos.motivo.value = '';
        campos.diagnostico.value = '';
        campos.tratamiento.value = '';
        campos.medicacion.value = '';
        campos.observaciones.value = '';
        detalleVersion.textContent = 'Nueva';
        detalleActualizacion.textContent = '--';
    });

    btnExportarPdf.addEventListener('click', function() {
        const filtros = obtenerFiltrosActuales();
        const params = new URLSearchParams();

        if (filtros.paciente) params.append('paciente', filtros.paciente);
        if (filtros.fecha) params.append('fecha', filtros.fecha);
        if (filtros.veterinario) params.append('veterinario', filtros.veterinario);
        if (filtros.acceso) params.append('acceso', filtros.acceso);

        const url = baseUrl + '/veterinaria/gestion-pacientes/pdf' + (params.toString() ? ('?' + params.toString()) : '');
        window.open(url, '_blank');
    });

    btnExportarFichaPdf.addEventListener('click', function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente para exportar su ficha clínica.', 'error');
            return;
        }

        const params = new URLSearchParams({
            id_paciente: String(idPaciente)
        });
        const url = baseUrl + '/veterinaria/gestion-pacientes/pdf-mascota?' + params.toString();
        window.open(url, '_blank');
    });

    btnGuardar.addEventListener('click', async function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        const payload = {
            accion: 'guardar_historial',
            id_historial: Number(campoIdHistorial.value || 0),
            id_paciente: idPaciente,
            fecha_atencion: (campos.fecha.value || '').trim(),
            motivo_consulta: (campos.motivo.value || '').trim(),
            diagnostico: (campos.diagnostico.value || '').trim(),
            tratamientos_aplicados: (campos.tratamiento.value || '').trim(),
            medicacion_recetada: (campos.medicacion.value || '').trim(),
            observaciones_adicionales: (campos.observaciones.value || '').trim()
        };

        if (idPaciente <= 0) {
            showToast('Debes seleccionar un paciente vinculado.', 'error');
            return;
        }

        if (!payload.fecha_atencion || !payload.motivo_consulta) {
            showToast('La fecha de atención y el motivo de consulta son obligatorios.', 'error');
            return;
        }

        try {
            const result = await postJson(payload);
            pendingSelectId = result.data && result.data.id_historial ? Number(result.data.id_historial) : null;
            await cargarHistoriales(obtenerFiltrosActuales());
            showToast('Historial clínico guardado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'Error al guardar el historial clínico.', 'error');
        }
    });

    btnGuardarVacuna.addEventListener('click', async function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente antes de registrar vacunas.', 'error');
            return;
        }

        const payload = {
            accion: edicionModulo.vacuna > 0 ? 'actualizar_vacuna' : 'guardar_vacuna',
            id_paciente: idPaciente,
            id_vacuna: edicionModulo.vacuna,
            tipo_vacuna: (vacunaTipo.value || '').trim(),
            dosis: (vacunaDosis.value || '').trim(),
            fecha_aplicacion: (vacunaFecha.value || '').trim(),
            observaciones: (vacunaObservaciones.value || '').trim()
        };

        if (!payload.tipo_vacuna || !payload.dosis || !payload.fecha_aplicacion) {
            showToast('Tipo de vacuna, dosis y fecha son obligatorios.', 'error');
            return;
        }

        try {
            await postJson(payload);
            edicionModulo.vacuna = 0;
            vacunaTipo.value = '';
            vacunaDosis.value = '';
            vacunaFecha.value = '';
            vacunaObservaciones.value = '';
            btnGuardarVacuna.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar vacuna';
            await cargarFichaClinica(idPaciente);
            showToast('Registro de vacuna guardado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'No se pudo guardar la vacuna.', 'error');
        }
    });

    btnGuardarTratamiento.addEventListener('click', async function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente antes de registrar tratamientos.', 'error');
            return;
        }

        const payload = {
            accion: edicionModulo.tratamiento > 0 ? 'actualizar_tratamiento' : 'guardar_tratamiento',
            id_paciente: idPaciente,
            id_tratamiento: edicionModulo.tratamiento,
            medicamento: (tratamientoMedicamento.value || '').trim(),
            dosis: (tratamientoDosis.value || '').trim(),
            fecha_inicio: (tratamientoInicio.value || '').trim(),
            fecha_fin: (tratamientoFin.value || '').trim(),
            estado: (tratamientoEstado.value || 'Activo').trim(),
            observaciones: (tratamientoObservaciones.value || '').trim()
        };

        if (!payload.medicamento || !payload.dosis || !payload.fecha_inicio) {
            showToast('Medicamento, dosis y fecha de inicio son obligatorios.', 'error');
            return;
        }

        try {
            await postJson(payload);
            edicionModulo.tratamiento = 0;
            tratamientoMedicamento.value = '';
            tratamientoDosis.value = '';
            tratamientoInicio.value = '';
            tratamientoFin.value = '';
            tratamientoEstado.value = 'Activo';
            tratamientoObservaciones.value = '';
            btnGuardarTratamiento.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar tratamiento';
            await cargarFichaClinica(idPaciente);
            showToast('Registro de tratamiento guardado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'No se pudo guardar el tratamiento.', 'error');
        }
    });

    btnGuardarConsulta.addEventListener('click', async function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente antes de registrar consultas.', 'error');
            return;
        }

        const payload = {
            accion: edicionModulo.consulta > 0 ? 'actualizar_consulta' : 'guardar_consulta',
            id_paciente: idPaciente,
            id_consulta: edicionModulo.consulta,
            fecha_consulta: (consultaFecha.value || '').trim(),
            motivo: (consultaMotivo.value || '').trim(),
            diagnostico: (consultaDiagnostico.value || '').trim(),
            observaciones: (consultaObservaciones.value || '').trim()
        };

        if (!payload.fecha_consulta || !payload.motivo) {
            showToast('Fecha y motivo de consulta son obligatorios.', 'error');
            return;
        }

        try {
            await postJson(payload);
            edicionModulo.consulta = 0;
            consultaFecha.value = '';
            consultaMotivo.value = '';
            consultaDiagnostico.value = '';
            consultaObservaciones.value = '';
            btnGuardarConsulta.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar consulta';
            await cargarFichaClinica(idPaciente);
            showToast('Registro de consulta guardado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'No se pudo guardar la consulta.', 'error');
        }
    });

    btnGuardarNota.addEventListener('click', async function() {
        const idPaciente = Number(campoIdPaciente.value || 0);
        if (idPaciente <= 0) {
            showToast('Selecciona un paciente antes de registrar notas.', 'error');
            return;
        }

        const payload = {
            accion: edicionModulo.nota > 0 ? 'actualizar_nota' : 'guardar_nota',
            id_paciente: idPaciente,
            id_nota: edicionModulo.nota,
            nota: (notaContenido.value || '').trim()
        };

        if (!payload.nota) {
            showToast('La nota clínica es obligatoria.', 'error');
            return;
        }

        try {
            await postJson(payload);
            edicionModulo.nota = 0;
            notaContenido.value = '';
            btnGuardarNota.innerHTML = '<i class="bi bi-plus-circle"></i> Guardar nota';
            await cargarFichaClinica(idPaciente);
            showToast('Registro de nota guardado correctamente.', 'success');
        } catch (error) {
            showToast(error.message || 'No se pudo guardar la nota clínica.', 'error');
        }
    });

    [listaVacunas, listaTratamientos, listaConsultas, listaNotas].forEach(function(contenedor) {
        contenedor.addEventListener('click', function(event) {
            const boton = event.target.closest('button[data-modulo-action]');
            if (!boton) return;

            const accion = boton.getAttribute('data-modulo-action') || '';
            const modulo = boton.getAttribute('data-modulo') || '';
            const idItem = Number(boton.getAttribute('data-id') || 0);

            if (idItem <= 0 || !modulo) return;

            if (accion === 'editar') {
                editarModulo(modulo, idItem);
            } else if (accion === 'eliminar') {
                eliminarModulo(modulo, idItem);
            }
        });
    });

    [paginacionVacunas, paginacionTratamientos, paginacionConsultas, paginacionNotas].forEach(function(contenedor) {
        contenedor.addEventListener('click', function(event) {
            const boton = event.target.closest('button[data-pagina-modulo]');
            if (!boton || !fichaActual) return;

            const modulo = boton.getAttribute('data-pagina-modulo') || '';
            const pagina = Number(boton.getAttribute('data-pagina') || 1);
            if (!paginacionModulo[modulo] || pagina <= 0) return;

            paginacionModulo[modulo].pagina = pagina;
            renderFichaClinica(fichaActual);
        });
    });

    [buscarVacunas, buscarTratamientos, buscarConsultas, buscarNotas].forEach(function(input, idx) {
        input.addEventListener('input', function() {
            const modulo = ['vacuna', 'tratamiento', 'consulta', 'nota'][idx];
            paginacionModulo[modulo].pagina = 1;
            if (fichaActual) renderFichaClinica(fichaActual);
        });
    });

    [
        { input: filtroFechaVacunas, modulo: 'vacuna' },
        { input: filtroEstadoTratamientos, modulo: 'tratamiento' },
        { input: filtroFechaTratamientos, modulo: 'tratamiento' },
        { input: filtroFechaConsultas, modulo: 'consulta' },
        { input: filtroFechaNotas, modulo: 'nota' }
    ].forEach(function(item) {
        item.input.addEventListener('change', function() {
            paginacionModulo[item.modulo].pagina = 1;
            if (fichaActual) renderFichaClinica(fichaActual);
        });
    });

    selectVersionHistorial.addEventListener('change', async function() {
        const idHistorialSeleccionado = this.value;
        
        if (!idHistorialSeleccionado) {
            return;
        }

        const registroSeleccionado = registros.find(function(r) {
            return String(r.id_historial) === String(idHistorialSeleccionado);
        });

        if (registroSeleccionado) {
            const filaTabla = tablaBody.querySelector('tr[data-id-historial="' + idHistorialSeleccionado + '"]');
            if (filaTabla) {
                filaTabla.click();
            } else {
                cargarDetalle(registroSeleccionado);
            }
        }
    });

    cargarHistoriales({});
});
