<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes | Historial Clínico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionClinica.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="historial-shell">
                <div class="encabezado-seccion encabezado-historial mb-0">
                    <div>
                        <h4>Gestión de Pacientes</h4>
                        <p class="subtitulo-historial mb-0">Historial clínico, control de acceso y trazabilidad por atención</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button id="btnExportarPdf" class="btn-secundario" type="button">
                            <i class="bi bi-download me-1"></i> Exportar reporte
                        </button>
                        <button id="btnNuevaAtencion" class="boton-agregar" type="button">
                            <i class="bi bi-plus-circle"></i> Nueva atención
                        </button>
                    </div>
                </div>

                <div class="historial-card mt-3">
                    <div class="bloque-titulo">
                        <h5><i class="bi bi-funnel me-2"></i>Filtros y búsqueda</h5>
                    </div>
                    <div class="filtros-grid">
                        <div>
                            <label class="label-historial">Paciente</label>
                            <input id="filtroPaciente" class="input-filtro" type="text" placeholder="Buscar por nombre del paciente...">
                        </div>
                        <div>
                            <label class="label-historial">Fecha</label>
                            <input id="filtroFecha" class="input-filtro" type="date">
                        </div>
                        <div>
                            <label class="label-historial">Veterinario</label>
                            <select id="filtroVeterinario" class="select-filtro">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-historial">Acceso</label>
                            <select id="filtroAcceso" class="select-filtro">
                                <option value="">Todos</option>
                                <option value="Autorizado">Autorizado</option>
                            </select>
                        </div>
                        <div class="acciones-filtro">
                            <button id="btnFiltrar" class="boton-agregar btn-sm-fit" type="button"><i class="bi bi-search"></i> Filtrar</button>
                            <button id="btnLimpiar" class="btn-secundario btn-sm-fit" type="button"><i class="bi bi-arrow-counterclockwise"></i> Limpiar</button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-xl-7">
                        <div class="historial-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Listado de atenciones</h5>
                                <span class="badge-sync"><i class="bi bi-arrow-repeat"></i> Datos en tiempo real</span>
                            </div>
                            <div class="table-responsive tabla-wrapper">
                                <table class="tabla-historial" id="tablaHistoriales">
                                    <thead>
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Fecha</th>
                                            <th>Veterinario</th>
                                            <th>Motivo</th>
                                            <th>Acceso</th>
                                            <th>Versión</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaHistorialesBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-3">Cargando historiales...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="historial-card h-100">
                            <h5><i class="bi bi-file-medical me-2"></i>Detalle y edición del historial</h5>

                            <div class="panel-seguridad">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="badge-sync"><i class="bi bi-link-45deg"></i> Integrado con consultas</span>
                                    <span class="badge-version" id="detalleVersion">--</span>
                                </div>
                                <div class="small text-muted mt-2">Paciente registrado: <strong id="detallePacienteRegistro">Sí</strong></div>
                                <div class="small text-muted">Acceso de edición: <strong id="detalleAcceso">Autorizado</strong></div>
                                <div class="small text-muted">Última actualización: <strong id="detalleActualizacion">--</strong></div>
                            </div>

                            <form id="formHistorial" class="d-flex flex-column gap-2">
                                <input type="hidden" id="campoIdHistorial">
                                <input type="hidden" id="campoIdPaciente">

                                <div class="campos-grid">
                                    <div>
                                        <label class="label-historial">Nombre del paciente</label>
                                        <input id="campoPaciente" class="input-historial" type="text" readonly>
                                    </div>
                                    <div>
                                        <label class="label-historial">Fecha de atención</label>
                                        <input id="campoFecha" class="input-historial" type="date">
                                    </div>
                                </div>

                                <div class="campos-grid">
                                    <div>
                                        <label class="label-historial">Especie</label>
                                        <input id="campoEspecie" class="input-historial" type="text" readonly>
                                    </div>
                                    <div>
                                        <label class="label-historial">Raza</label>
                                        <input id="campoRaza" class="input-historial" type="text" readonly>
                                    </div>
                                </div>

                                <div>
                                    <label class="label-historial">Veterinario responsable</label>
                                    <input id="campoVeterinario" class="input-historial" type="text" readonly>
                                </div>

                                <div>
                                    <label class="label-historial">Versiones del historial</label>
                                    <select id="selectVersionHistorial" class="select-filtro">
                                        <option value="">Selecciona una atención</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="label-historial">Motivo de la consulta</label>
                                    <textarea id="campoMotivo" class="textarea-historial"></textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Diagnóstico</label>
                                    <textarea id="campoDiagnostico" class="textarea-historial"></textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Tratamientos aplicados</label>
                                    <textarea id="campoTratamiento" class="textarea-historial"></textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Medicación recetada</label>
                                    <textarea id="campoMedicacion" class="textarea-historial"></textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Observaciones adicionales</label>
                                    <textarea id="campoObservaciones" class="textarea-historial"></textarea>
                                </div>

                                <div class="acciones-panel">
                                    <button id="btnGuardar" type="button" class="boton-agregar">
                                        <i class="bi bi-check2-circle"></i> Guardar cambios
                                    </button>
                                </div>
                            </form>

                            <div class="bloque-versionado">
                                <strong class="small d-block mb-2">Trazabilidad y versionado</strong>
                                <div class="item-version mb-0">
                                    <i class="bi bi-shield-check"></i>
                                    <div>Solo puedes editar historiales de pacientes vinculados a tu usuario.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const endpoint = '<?= BASE_URL ?>/veterinaria/pacientes/acciones';
            const endpointDetallesCita = '<?= BASE_URL ?>/calendario/cargar?accion=detalles_completo';

            const tablaBody = document.getElementById('tablaHistorialesBody');
            const tabla = document.getElementById('tablaHistoriales');

            const filtroPaciente = document.getElementById('filtroPaciente');
            const filtroFecha = document.getElementById('filtroFecha');
            const filtroVeterinario = document.getElementById('filtroVeterinario');
            const filtroAcceso = document.getElementById('filtroAcceso');

            const btnFiltrar = document.getElementById('btnFiltrar');
            const btnLimpiar = document.getElementById('btnLimpiar');
            const btnGuardar = document.getElementById('btnGuardar');
            const btnNuevaAtencion = document.getElementById('btnNuevaAtencion');
            const btnExportarPdf = document.getElementById('btnExportarPdf');
            const selectVersionHistorial = document.getElementById('selectVersionHistorial');

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
            const detallePacienteRegistro = document.getElementById('detallePacienteRegistro');

            let registros = [];
            let versionesHistorial = [];
            let pendingSelectId = null;
            let pendingSelectPatientId = null;

            async function postJson(body) {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                });

                const payload = await response.json();
                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || 'Error en la solicitud');
                }

                return payload;
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
                const date = new Date(value.replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);
                return date.toLocaleDateString('es-CO');
            }

            function fechaHoraHumana(value) {
                if (!value) return '--';
                const date = new Date(value.replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) return String(value);
                return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
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
                detallePacienteRegistro.textContent = 'No';
                selectVersionHistorial.innerHTML = '<option value="">Selecciona una atención</option>';
                versionesHistorial = [];
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
                detallePacienteRegistro.textContent = registro.id_paciente ? 'Sí' : 'No';
            }

            function renderVersionesSelector(selectedId) {
                if (!Array.isArray(versionesHistorial) || versionesHistorial.length === 0) {
                    selectVersionHistorial.innerHTML = '<option value="">Sin versiones disponibles</option>';
                    return;
                }

                selectVersionHistorial.innerHTML = versionesHistorial.map(function(item) {
                    const label = 'v' + (item.version_registro || '--') + ' · ' + fechaHumana(item.fecha_atencion);
                    const selected = Number(item.id_historial || 0) === Number(selectedId || 0) ? ' selected' : '';
                    return '<option value="' + escapeHtml(item.id_historial || '') + '"' + selected + '>' + escapeHtml(label) + '</option>';
                }).join('');
            }

            async function cargarVersionesHistorial(idHistorial, selectedId) {
                const id = Number(idHistorial || 0);
                if (id <= 0) {
                    versionesHistorial = [];
                    renderVersionesSelector('');
                    return;
                }

                try {
                    const payload = await postJson({
                        accion: 'listar_versiones_historial',
                        id_historial: id
                    });

                    versionesHistorial = Array.isArray(payload.data) ? payload.data : [];
                    renderVersionesSelector(selectedId || id);
                } catch (error) {
                    versionesHistorial = [];
                    renderVersionesSelector('');
                }
            }

            function prepararNuevaAtencionDesdeCita(contextoCita) {
                if (!contextoCita || Number(contextoCita.id_paciente || 0) <= 0) {
                    return;
                }

                if (Number(campoIdPaciente.value || 0) !== Number(contextoCita.id_paciente || 0)) {
                    return;
                }

                campoIdHistorial.value = '';

                if (contextoCita.fecha_atencion) {
                    campos.fecha.value = toDateInput(contextoCita.fecha_atencion);
                }

                if (contextoCita.motivo_consulta) {
                    campos.motivo.value = contextoCita.motivo_consulta;
                }

                detalleVersion.textContent = 'Nueva';
                detalleActualizacion.textContent = '--';
            }

            async function obtenerContextoDesdeCita() {
                const query = new URLSearchParams(window.location.search);
                const idAgendamiento = Number(query.get('id_agendamiento') || 0);

                if (idAgendamiento <= 0) {
                    return null;
                }

                try {
                    const response = await fetch(endpointDetallesCita + '&id_agendamiento=' + encodeURIComponent(idAgendamiento), {
                        method: 'GET',
                        credentials: 'same-origin'
                    });

                    const payload = await response.json();
                    if (!response.ok || payload.status !== 'success' || !payload.cita) {
                        return null;
                    }

                    const cita = payload.cita;

                    return {
                        id_paciente: Number(cita.id_paciente || 0),
                        fecha_atencion: cita.fecha_hora || '',
                        motivo_consulta: (cita.observaciones || cita.tipo || '').trim(),
                    };
                } catch (error) {
                    return null;
                }
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
                    const idHistorialBase = item.id_historial_base || item.id_historial || '';

                    return '<tr class="' + activo + '" ' +
                        'data-id-historial="' + escapeHtml(item.id_historial || '') + '" ' +
                        'data-id-historial-base="' + escapeHtml(idHistorialBase) + '" ' +
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
                        cargarVersionesHistorial(registro.id_historial, registro.id_historial);
                    });
                });

                let filaInicial = null;
                if (pendingSelectId) {
                    filaInicial = tablaBody.querySelector('tr[data-id-historial="' + pendingSelectId + '"]');
                }
                if (!filaInicial && pendingSelectPatientId) {
                    filaInicial = tablaBody.querySelector('tr[data-id-paciente="' + pendingSelectPatientId + '"]');
                }
                if (!filaInicial) {
                    filaInicial = tablaBody.querySelector('tr[data-id-paciente]');
                }
                pendingSelectId = null;
                pendingSelectPatientId = null;

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
                    alert('Selecciona primero un paciente vinculado en la tabla.');
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

            selectVersionHistorial.addEventListener('change', function() {
                const idVersion = Number(selectVersionHistorial.value || 0);
                if (idVersion <= 0 || !Array.isArray(versionesHistorial) || versionesHistorial.length === 0) {
                    return;
                }

                const versionSeleccionada = versionesHistorial.find(function(item) {
                    return Number(item.id_historial || 0) === idVersion;
                });

                if (!versionSeleccionada) {
                    return;
                }

                cargarDetalle(versionSeleccionada);
            });

            btnExportarPdf.addEventListener('click', function() {
                const filtros = obtenerFiltrosActuales();
                const params = new URLSearchParams();

                if (filtros.paciente) params.append('paciente', filtros.paciente);
                if (filtros.fecha) params.append('fecha', filtros.fecha);
                if (filtros.veterinario) params.append('veterinario', filtros.veterinario);
                if (filtros.acceso) params.append('acceso', filtros.acceso);

                const url = '<?= BASE_URL ?>/veterinaria/gestion-pacientes/pdf' + (params.toString() ? ('?' + params.toString()) : '');
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
                    alert('Debes seleccionar un paciente vinculado.');
                    return;
                }

                if (!payload.fecha_atencion || !payload.motivo_consulta) {
                    alert('La fecha de atención y el motivo de consulta son obligatorios.');
                    return;
                }

                try {
                    const result = await postJson(payload);

                    pendingSelectId = result.data && result.data.id_historial ? Number(result.data.id_historial) : null;
                    await cargarHistoriales(obtenerFiltrosActuales());
                    alert('Historial clínico guardado correctamente.');
                } catch (error) {
                    alert(error.message || 'Error al guardar el historial clínico.');
                }
            });

            const contextoCita = await obtenerContextoDesdeCita();

            if (contextoCita && Number(contextoCita.id_paciente || 0) > 0) {
                pendingSelectPatientId = Number(contextoCita.id_paciente);
            }

            await cargarHistoriales({});

            if (contextoCita && Number(contextoCita.id_paciente || 0) > 0) {
                prepararNuevaAtencionDesdeCita(contextoCita);
            }
        });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
</body>

</html>
