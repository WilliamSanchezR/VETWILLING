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

<body data-endpoint="<?= BASE_URL ?>/veterinaria/pacientes/acciones" data-base-url="<?= BASE_URL ?>">
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="historial-shell" aria-labelledby="tituloGestionPacientes">

                <!-- ============ ENCABEZADO ============ -->
                <header class="encabezado-seccion encabezado-historial d-flex justify-content-between align-items-end flex-wrap gap-3 mb-0">
                    <div>
                        <h4 id="tituloGestionPacientes">
                            <i class="bi bi-clipboard2-pulse me-2"></i>Gestión de Pacientes
                        </h4>
                        <p class="subtitulo-historial mb-0">
                            Historial clínico, control de acceso y trazabilidad por atención.
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" role="toolbar" aria-label="Acciones principales">
                        <button id="btnExportarPdf" class="btn-secundario" type="button">
                            <i class="bi bi-download"></i> Exportar reporte
                        </button>
                        <button id="btnExportarFichaPdf" class="btn-secundario" type="button">
                            <i class="bi bi-file-earmark-medical"></i> Ficha del paciente
                        </button>
                        <button id="btnNuevaAtencion" class="boton-agregar" type="button">
                            <i class="bi bi-plus-circle"></i> Nueva atención
                        </button>
                    </div>
                </header>

                <!-- ============ MINI STATS ============ -->
                <div class="row g-3" role="list" aria-label="Resumen operativo">
                    <div class="col-6 col-md-3" role="listitem">
                        <div class="mini-stat">
                            <span class="mini-stat-icon" style="background: rgba(10, 147, 44, 0.12); color: #0a932c;">
                                <i class="bi bi-journal-text"></i>
                            </span>
                            <div>
                                <strong id="statTotalAtenciones">0</strong>
                                <small>Atenciones</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" role="listitem">
                        <div class="mini-stat">
                            <span class="mini-stat-icon" style="background: rgba(37, 99, 235, 0.12); color: #2563eb;">
                                <i class="bi bi-people"></i>
                            </span>
                            <div>
                                <strong id="statPacientesUnicos">0</strong>
                                <small>Pacientes únicos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" role="listitem">
                        <div class="mini-stat">
                            <span class="mini-stat-icon" style="background: rgba(245, 158, 11, 0.14); color: #b45309;">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <div>
                                <strong id="statVeterinariosActivos">0</strong>
                                <small>Veterinarios</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3" role="listitem">
                        <div class="mini-stat">
                            <span class="mini-stat-icon" style="background: rgba(20, 184, 166, 0.14); color: #0f766e;">
                                <i class="bi bi-clock-history"></i>
                            </span>
                            <div>
                                <strong id="statUltimaAtencion">--</strong>
                                <small>Última atención</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ FLUJO RECOMENDADO ============ -->
                <div class="historial-card atajos-card">
                    <div class="bloque-titulo d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5><i class="bi bi-signpost-split me-2"></i>Flujo recomendado</h5>
                        <small class="text-muted">Buenas prácticas clínicas</small>
                    </div>
                    <div class="atajos-grid">
                        <div class="atajo-item">
                            <span class="atajo-num">1</span>
                            <div>
                                <strong>Filtra o busca el paciente</strong>
                                <small>Localiza el historial en segundos por nombre, fecha o veterinario.</small>
                            </div>
                        </div>
                        <div class="atajo-item">
                            <span class="atajo-num">2</span>
                            <div>
                                <strong>Selecciona la atención</strong>
                                <small>El panel derecho carga versiones, ficha y trazabilidad automáticamente.</small>
                            </div>
                        </div>
                        <div class="atajo-item">
                            <span class="atajo-num">3</span>
                            <div>
                                <strong>Edita, valida y guarda</strong>
                                <small>Registra cambios en vacunas, tratamientos, consultas y notas clínicas.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ FILTROS ============ -->
                <div class="historial-card">
                    <div class="bloque-titulo">
                        <h5><i class="bi bi-funnel me-2"></i>Filtros y búsqueda</h5>
                    </div>
                    <div class="filtros-grid">
                        <div>
                            <label for="filtroPaciente" class="label-historial">Paciente</label>
                            <input id="filtroPaciente" class="input-filtro" type="text"
                                placeholder="Buscar por nombre del paciente...">
                        </div>
                        <div>
                            <label for="filtroFecha" class="label-historial">Fecha</label>
                            <input id="filtroFecha" class="input-filtro" type="date">
                        </div>
                        <div>
                            <label for="filtroVeterinario" class="label-historial">Veterinario</label>
                            <select id="filtroVeterinario" class="select-filtro">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div>
                            <label for="filtroAcceso" class="label-historial">Acceso</label>
                            <select id="filtroAcceso" class="select-filtro">
                                <option value="">Todos</option>
                                <option value="Autorizado">Autorizado</option>
                            </select>
                        </div>
                        <div class="acciones-filtro">
                            <button id="btnFiltrar" class="boton-agregar btn-sm-fit" type="button">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <button id="btnLimpiar" class="btn-secundario btn-sm-fit" type="button">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============ WORKSPACE TABS ============ -->
                <div class="workspace-tabs" role="tablist" aria-label="Ventanas de gestión clínica">
                    <button type="button" class="workspace-tab active" data-workspace-tab="listado" aria-selected="true">
                        <i class="bi bi-table"></i> Listado de atenciones
                    </button>
                    <button type="button" class="workspace-tab" data-workspace-tab="detalle" aria-selected="false">
                        <i class="bi bi-file-earmark-medical"></i> Detalle y edición
                    </button>
                    <button type="button" class="workspace-tab" data-workspace-tab="modulos" aria-selected="false">
                        <i class="bi bi-journal-medical"></i> Módulos clínicos
                    </button>
                </div>

                <!-- ============ LISTADO + DETALLE ============ -->
                <div class="row g-3">
                    <!-- LISTADO -->
                    <div class="col-xl-7" id="panelListadoAtenciones">
                        <div class="historial-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
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
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaHistorialesBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="bi bi-hourglass-split me-1"></i> Cargando historiales...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLE -->
                    <div class="col-xl-5 ventana-oculta" id="panelEdicionClinica">
                        <div class="historial-card h-100">
                            <div id="detalleContenidoHistorial">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h5 class="mb-0"><i class="bi bi-file-medical me-2"></i>Detalle y edición</h5>
                                    <span class="badge-version" id="detalleVersion">--</span>
                                </div>

                                <!-- Resumen del paciente -->
                                <div class="resumen-paciente mb-3">
                                    <div class="resumen-paciente-head">
                                        <i class="bi bi-heart-pulse"></i>
                                        <div>
                                            <strong id="detallePacienteTitulo">Sin paciente seleccionado</strong>
                                            <small id="detallePacienteMeta">Selecciona un registro de la tabla para iniciar la edición.</small>
                                        </div>
                                    </div>
                                    <span class="badge-acceso permitido" id="detalleEstadoEdicion">
                                        <i class="bi bi-check-circle"></i> Listo para editar
                                    </span>
                                </div>

                                <!-- Trazabilidad -->
                                <div class="panel-seguridad">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <span class="badge-sync"><i class="bi bi-link-45deg"></i> Integrado con consultas</span>
                                    </div>
                                    <div class="small">Paciente registrado: <strong id="detallePacienteRegistro">Sí</strong></div>
                                    <div class="small">Acceso de edición: <strong id="detalleAcceso">Autorizado</strong></div>
                                    <div class="small">Última actualización: <strong id="detalleActualizacion">--</strong></div>
                                </div>

                                <!-- Formulario -->
                                <form id="formHistorial" class="d-flex flex-column gap-3 mt-3">
                                    <input type="hidden" id="campoIdHistorial">
                                    <input type="hidden" id="campoIdPaciente">

                                    <div class="campos-grid">
                                        <div>
                                            <label for="campoPaciente" class="label-historial">Nombre del paciente</label>
                                            <input id="campoPaciente" class="input-historial" type="text" readonly>
                                        </div>
                                        <div>
                                            <label for="campoFecha" class="label-historial">Fecha de atención</label>
                                            <input id="campoFecha" class="input-historial" type="date">
                                        </div>
                                    </div>

                                    <div class="campos-grid">
                                        <div>
                                            <label for="campoEspecie" class="label-historial">Especie</label>
                                            <input id="campoEspecie" class="input-historial" type="text" readonly>
                                        </div>
                                        <div>
                                            <label for="campoRaza" class="label-historial">Raza</label>
                                            <input id="campoRaza" class="input-historial" type="text" readonly>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="campoVeterinario" class="label-historial">Veterinario responsable</label>
                                        <input id="campoVeterinario" class="input-historial" type="text" readonly>
                                    </div>

                                    <div>
                                        <label for="selectVersionHistorial" class="label-historial">Versiones del historial</label>
                                        <select id="selectVersionHistorial" class="select-filtro">
                                            <option value="">Selecciona una atención</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="campoMotivo" class="label-historial">Motivo de la consulta</label>
                                        <textarea id="campoMotivo" class="textarea-historial"></textarea>
                                    </div>

                                    <div>
                                        <label for="campoDiagnostico" class="label-historial">Diagnóstico</label>
                                        <textarea id="campoDiagnostico" class="textarea-historial"></textarea>
                                    </div>

                                    <div>
                                        <label for="campoTratamiento" class="label-historial">Tratamientos aplicados</label>
                                        <textarea id="campoTratamiento" class="textarea-historial"></textarea>
                                    </div>

                                    <div>
                                        <label for="campoMedicacion" class="label-historial">Medicación recetada</label>
                                        <textarea id="campoMedicacion" class="textarea-historial"></textarea>
                                    </div>

                                    <div>
                                        <label for="campoObservaciones" class="label-historial">Observaciones adicionales</label>
                                        <textarea id="campoObservaciones" class="textarea-historial"></textarea>
                                    </div>

                                    <div class="acciones-panel">
                                        <button id="btnGuardar" type="button" class="boton-agregar">
                                            <i class="bi bi-check2-circle"></i> Guardar cambios
                                        </button>
                                    </div>
                                </form>

                                <!-- Trazabilidad final -->
                                <div class="bloque-versionado">
                                    <strong class="small d-block mb-2">Trazabilidad y versionado</strong>
                                    <div class="item-version mb-0">
                                        <i class="bi bi-shield-check"></i>
                                        <div>Solo puedes editar historiales de pacientes vinculados a tu usuario.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============ MÓDULOS CLÍNICOS ============ -->
                            <div class="historial-card modulo-fase2 mt-3 p-3 ventana-oculta" id="panelModulosClinicos">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h5 class="mb-0"><i class="bi bi-journal-medical me-2"></i>Módulos clínicos</h5>
                                    <span class="badge-sync"><i class="bi bi-heart-pulse"></i> Ficha integral</span>
                                </div>

                                <ul class="nav nav-tabs tabs-ficha" id="tabsFichaClinica" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-vacunas" data-bs-toggle="tab"
                                            data-bs-target="#panel-vacunas" type="button" role="tab">Vacunas</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-tratamientos" data-bs-toggle="tab"
                                            data-bs-target="#panel-tratamientos" type="button" role="tab">Tratamientos</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-consultas" data-bs-toggle="tab"
                                            data-bs-target="#panel-consultas" type="button" role="tab">Consultas</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-notas" data-bs-toggle="tab"
                                            data-bs-target="#panel-notas" type="button" role="tab">Notas</button>
                                    </li>
                                </ul>

                                <div class="tab-content pt-3" id="tabContentFichaClinica">

                                    <!-- VACUNAS -->
                                    <div class="tab-pane fade show active" id="panel-vacunas" role="tabpanel">
                                        <div class="modulo-toolbar">
                                            <input id="buscarVacunas" class="input-filtro" type="text" placeholder="Buscar vacuna...">
                                            <input id="filtroFechaVacunas" class="input-filtro" type="date" title="Filtrar por fecha de aplicación">
                                        </div>
                                        <form id="formVacuna" class="d-flex flex-column gap-2">
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="vacunaTipo" class="label-historial">Tipo de vacuna</label>
                                                    <input id="vacunaTipo" class="input-historial" type="text" placeholder="Ej. Rabia">
                                                </div>
                                                <div>
                                                    <label for="vacunaDosis" class="label-historial">Dosis</label>
                                                    <input id="vacunaDosis" class="input-historial" type="text" placeholder="Ej. 1 ml">
                                                </div>
                                            </div>
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="vacunaFecha" class="label-historial">Fecha de aplicación</label>
                                                    <input id="vacunaFecha" class="input-historial" type="date">
                                                </div>
                                                <div>
                                                    <label for="vacunaObservaciones" class="label-historial">Observaciones</label>
                                                    <input id="vacunaObservaciones" class="input-historial" type="text" placeholder="Notas...">
                                                </div>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarVacuna" type="button" class="boton-agregar">
                                                    <i class="bi bi-plus-circle"></i> Guardar vacuna
                                                </button>
                                            </div>
                                        </form>
                                        <div id="listaVacunas" class="listado-modulo mt-3">
                                            <div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>
                                        </div>
                                        <div id="paginacionVacunas" class="paginacion-modulo"></div>
                                    </div>

                                    <!-- TRATAMIENTOS -->
                                    <div class="tab-pane fade" id="panel-tratamientos" role="tabpanel">
                                        <div class="modulo-toolbar">
                                            <input id="buscarTratamientos" class="input-filtro" type="text" placeholder="Buscar tratamiento...">
                                            <select id="filtroEstadoTratamientos" class="select-filtro">
                                                <option value="">Todos los estados</option>
                                                <option value="Activo">Activo</option>
                                                <option value="Finalizado">Finalizado</option>
                                                <option value="Suspendido">Suspendido</option>
                                            </select>
                                            <input id="filtroFechaTratamientos" class="input-filtro" type="date" title="Filtrar por fecha de inicio">
                                        </div>
                                        <form id="formTratamiento" class="d-flex flex-column gap-2">
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="tratamientoMedicamento" class="label-historial">Medicamento</label>
                                                    <input id="tratamientoMedicamento" class="input-historial" type="text">
                                                </div>
                                                <div>
                                                    <label for="tratamientoDosis" class="label-historial">Dosis</label>
                                                    <input id="tratamientoDosis" class="input-historial" type="text">
                                                </div>
                                            </div>
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="tratamientoInicio" class="label-historial">Inicio</label>
                                                    <input id="tratamientoInicio" class="input-historial" type="date">
                                                </div>
                                                <div>
                                                    <label for="tratamientoFin" class="label-historial">Fin</label>
                                                    <input id="tratamientoFin" class="input-historial" type="date">
                                                </div>
                                            </div>
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="tratamientoEstado" class="label-historial">Estado</label>
                                                    <select id="tratamientoEstado" class="select-filtro">
                                                        <option value="Activo">Activo</option>
                                                        <option value="Finalizado">Finalizado</option>
                                                        <option value="Suspendido">Suspendido</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="tratamientoObservaciones" class="label-historial">Observaciones</label>
                                                    <input id="tratamientoObservaciones" class="input-historial" type="text">
                                                </div>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarTratamiento" type="button" class="boton-agregar">
                                                    <i class="bi bi-plus-circle"></i> Guardar tratamiento
                                                </button>
                                            </div>
                                        </form>
                                        <div id="listaTratamientos" class="listado-modulo mt-3">
                                            <div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>
                                        </div>
                                        <div id="paginacionTratamientos" class="paginacion-modulo"></div>
                                    </div>

                                    <!-- CONSULTAS -->
                                    <div class="tab-pane fade" id="panel-consultas" role="tabpanel">
                                        <div class="modulo-toolbar">
                                            <input id="buscarConsultas" class="input-filtro" type="text" placeholder="Buscar consulta...">
                                            <input id="filtroFechaConsultas" class="input-filtro" type="date">
                                        </div>
                                        <form id="formConsulta" class="d-flex flex-column gap-2">
                                            <div class="campos-grid">
                                                <div>
                                                    <label for="consultaFecha" class="label-historial">Fecha</label>
                                                    <input id="consultaFecha" class="input-historial" type="date">
                                                </div>
                                                <div>
                                                    <label for="consultaMotivo" class="label-historial">Motivo</label>
                                                    <input id="consultaMotivo" class="input-historial" type="text">
                                                </div>
                                            </div>
                                            <div>
                                                <label for="consultaDiagnostico" class="label-historial">Diagnóstico</label>
                                                <textarea id="consultaDiagnostico" class="textarea-historial"></textarea>
                                            </div>
                                            <div>
                                                <label for="consultaObservaciones" class="label-historial">Observaciones</label>
                                                <textarea id="consultaObservaciones" class="textarea-historial"></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarConsulta" type="button" class="boton-agregar">
                                                    <i class="bi bi-plus-circle"></i> Guardar consulta
                                                </button>
                                            </div>
                                        </form>
                                        <div id="listaConsultas" class="listado-modulo mt-3">
                                            <div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>
                                        </div>
                                        <div id="paginacionConsultas" class="paginacion-modulo"></div>
                                    </div>

                                    <!-- NOTAS -->
                                    <div class="tab-pane fade" id="panel-notas" role="tabpanel">
                                        <div class="modulo-toolbar">
                                            <input id="buscarNotas" class="input-filtro" type="text" placeholder="Buscar nota...">
                                            <input id="filtroFechaNotas" class="input-filtro" type="date">
                                        </div>
                                        <form id="formNota" class="d-flex flex-column gap-2">
                                            <div>
                                                <label for="notaContenido" class="label-historial">Nota clínica</label>
                                                <textarea id="notaContenido" class="textarea-historial" placeholder="Escribe una nota..."></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarNota" type="button" class="boton-agregar">
                                                    <i class="bi bi-plus-circle"></i> Guardar nota
                                                </button>
                                            </div>
                                        </form>
                                        <div id="listaNotas" class="listado-modulo mt-3">
                                            <div class="modulo-vacio">Selecciona un paciente para ver su ficha clínica.</div>
                                        </div>
                                        <div id="paginacionNotas" class="paginacion-modulo"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- ===== MODAL NUEVA ATENCIÓN ===== -->
    <div class="modal fade" id="modalNuevaAtencion" tabindex="-1" aria-labelledby="modalNuevaAtencionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content hist-modal">

                <div class="hist-modal-header">
                    <div class="hist-modal-header-info">
                        <div class="hist-modal-icon">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="modalNuevaAtencionLabel">
                                <span id="modalTitulo">Nueva Atención</span>
                            </h5>
                            <small>Registro clínico del paciente</small>
                        </div>
                    </div>
                    <button type="button" class="hist-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="hist-modal-body">

                    <!-- PASO 1: Lista de mascotas asignadas -->
                    <div id="modalSeccionCitas">
                        <p class="hist-modal-hint">
                            <i class="bi bi-heart-pulse me-1"></i>
                            Selecciona la mascota para registrar una nueva atención clínica.
                        </p>
                        <div id="modalListaCitas">
                            <div class="hist-modal-loading">
                                <i class="bi bi-hourglass-split"></i> Cargando pacientes...
                            </div>
                        </div>
                    </div>

                    <!-- PASO 2: Formulario de atención -->
                    <div id="modalSeccionFormulario" class="d-none">

                        <div class="resumen-paciente mb-4">
                            <div class="resumen-paciente-head">
                                <i class="bi bi-heart-pulse"></i>
                                <div>
                                    <strong id="modalPacienteNombre">--</strong>
                                    <small id="modalPacienteMeta">--</small>
                                </div>
                            </div>
                            <span class="badge-version" id="modalModoLabel">Nueva</span>
                        </div>

                        <form id="formModalAtencion" class="d-flex flex-column gap-3">
                            <input type="hidden" id="modalIdPaciente">
                            <input type="hidden" id="modalIdHistorial">
                            <input type="hidden" id="modalIdCita">

                            <div class="campos-grid">
                                <div>
                                    <label for="modalFechaAtencion" class="label-historial">
                                        Fecha de atención <span class="text-danger">*</span>
                                    </label>
                                    <input id="modalFechaAtencion" class="input-historial" type="date">
                                </div>
                                <div>
                                    <label for="modalMotivo" class="label-historial">
                                        Motivo de consulta <span class="text-danger">*</span>
                                    </label>
                                    <input id="modalMotivo" class="input-historial" type="text" placeholder="Describe el motivo...">
                                </div>
                            </div>

                            <div>
                                <label for="modalDiagnostico" class="label-historial">Diagnóstico</label>
                                <textarea id="modalDiagnostico" class="textarea-historial" rows="2"></textarea>
                            </div>
                            <div>
                                <label for="modalTratamiento" class="label-historial">Tratamientos aplicados</label>
                                <textarea id="modalTratamiento" class="textarea-historial" rows="2"></textarea>
                            </div>
                            <div>
                                <label for="modalMedicacion" class="label-historial">Medicación recetada</label>
                                <textarea id="modalMedicacion" class="textarea-historial" rows="2"></textarea>
                            </div>
                            <div>
                                <label for="modalObservaciones" class="label-historial">Observaciones adicionales</label>
                                <textarea id="modalObservaciones" class="textarea-historial" rows="2"></textarea>
                            </div>
                        </form>
                    </div>

                </div><!-- /.hist-modal-body -->

                <div class="hist-modal-footer">
                    <button type="button" class="btn-secundario d-none" id="modalBtnVolver">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </button>
                    <button type="button" class="btn-secundario" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="button" class="boton-agregar d-none" id="modalBtnGuardar">
                        <i class="bi bi-check2-circle me-1"></i> Guardar atención
                    </button>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /#modalNuevaAtencion -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/gestion-pacientes.js"></script>
</body>

</html>
