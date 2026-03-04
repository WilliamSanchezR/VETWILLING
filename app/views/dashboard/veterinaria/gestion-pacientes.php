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
                        <button id="btnExportarFichaPdf" class="btn-secundario" type="button">
                            <i class="bi bi-file-earmark-medical me-1"></i> Exportar ficha paciente
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

                            <div class="historial-card modulo-fase2 mt-3 p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <h5 class="mb-0"><i class="bi bi-journal-medical me-2"></i>Módulos clínicos (Fase 2)</h5>
                                    <span class="badge-sync"><i class="bi bi-heart-pulse"></i> Ficha integral</span>
                                </div>

                                <ul class="nav nav-tabs tabs-ficha" id="tabsFichaClinica" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-vacunas" data-bs-toggle="tab" data-bs-target="#panel-vacunas" type="button" role="tab">Vacunas</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-tratamientos" data-bs-toggle="tab" data-bs-target="#panel-tratamientos" type="button" role="tab">Tratamientos</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-consultas" data-bs-toggle="tab" data-bs-target="#panel-consultas" type="button" role="tab">Consultas</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-notas" data-bs-toggle="tab" data-bs-target="#panel-notas" type="button" role="tab">Notas</button>
                                    </li>
                                </ul>

                                <div class="tab-content pt-3" id="tabContentFichaClinica">
                                    <div class="tab-pane fade show active" id="panel-vacunas" role="tabpanel">
                                        <div class="modulo-toolbar mb-2">
                                            <input id="buscarVacunas" class="input-filtro" type="text" placeholder="Buscar vacuna...">
                                            <input id="filtroFechaVacunas" class="input-filtro" type="date" title="Filtrar por fecha de aplicación">
                                        </div>
                                        <form id="formVacuna" class="d-flex flex-column gap-2">
                                            <div class="campos-grid">
                                                <div>
                                                    <label class="label-historial">Tipo de vacuna</label>
                                                    <input id="vacunaTipo" class="input-historial" type="text" placeholder="Ej. Rabia">
                                                </div>
                                                <div>
                                                    <label class="label-historial">Dosis</label>
                                                    <input id="vacunaDosis" class="input-historial" type="text" placeholder="Ej. 1 ml">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="label-historial">Fecha de aplicación</label>
                                                <input id="vacunaFecha" class="input-historial" type="date">
                                            </div>
                                            <div>
                                                <label class="label-historial">Observaciones</label>
                                                <textarea id="vacunaObservaciones" class="textarea-historial" placeholder="Observaciones de la vacuna"></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarVacuna" type="button" class="boton-agregar"><i class="bi bi-plus-circle"></i> Guardar vacuna</button>
                                            </div>
                                        </form>
                                        <div id="listaVacunas" class="listado-modulo mt-2"></div>
                                        <div id="paginacionVacunas" class="paginacion-modulo"></div>
                                    </div>

                                    <div class="tab-pane fade" id="panel-tratamientos" role="tabpanel">
                                        <div class="modulo-toolbar mb-2">
                                            <input id="buscarTratamientos" class="input-filtro" type="text" placeholder="Buscar tratamiento...">
                                            <select id="filtroEstadoTratamientos" class="select-filtro" title="Filtrar por estado">
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
                                                    <label class="label-historial">Medicamento</label>
                                                    <input id="tratamientoMedicamento" class="input-historial" type="text" placeholder="Nombre del medicamento">
                                                </div>
                                                <div>
                                                    <label class="label-historial">Dosis</label>
                                                    <input id="tratamientoDosis" class="input-historial" type="text" placeholder="Ej. 5 mg cada 12h">
                                                </div>
                                            </div>
                                            <div class="campos-grid">
                                                <div>
                                                    <label class="label-historial">Fecha inicio</label>
                                                    <input id="tratamientoInicio" class="input-historial" type="date">
                                                </div>
                                                <div>
                                                    <label class="label-historial">Fecha fin</label>
                                                    <input id="tratamientoFin" class="input-historial" type="date">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="label-historial">Estado</label>
                                                <select id="tratamientoEstado" class="select-filtro">
                                                    <option value="Activo">Activo</option>
                                                    <option value="Finalizado">Finalizado</option>
                                                    <option value="Suspendido">Suspendido</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="label-historial">Observaciones</label>
                                                <textarea id="tratamientoObservaciones" class="textarea-historial" placeholder="Notas del tratamiento"></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarTratamiento" type="button" class="boton-agregar"><i class="bi bi-plus-circle"></i> Guardar tratamiento</button>
                                            </div>
                                        </form>
                                        <div id="listaTratamientos" class="listado-modulo mt-2"></div>
                                        <div id="paginacionTratamientos" class="paginacion-modulo"></div>
                                    </div>

                                    <div class="tab-pane fade" id="panel-consultas" role="tabpanel">
                                        <div class="modulo-toolbar mb-2">
                                            <input id="buscarConsultas" class="input-filtro" type="text" placeholder="Buscar consulta...">
                                            <input id="filtroFechaConsultas" class="input-filtro" type="date" title="Filtrar por fecha de consulta">
                                        </div>
                                        <form id="formConsulta" class="d-flex flex-column gap-2">
                                            <div>
                                                <label class="label-historial">Fecha de consulta</label>
                                                <input id="consultaFecha" class="input-historial" type="date">
                                            </div>
                                            <div>
                                                <label class="label-historial">Motivo</label>
                                                <textarea id="consultaMotivo" class="textarea-historial" placeholder="Motivo de consulta"></textarea>
                                            </div>
                                            <div>
                                                <label class="label-historial">Diagnóstico</label>
                                                <textarea id="consultaDiagnostico" class="textarea-historial" placeholder="Diagnóstico de la consulta"></textarea>
                                            </div>
                                            <div>
                                                <label class="label-historial">Observaciones</label>
                                                <textarea id="consultaObservaciones" class="textarea-historial" placeholder="Observaciones adicionales"></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarConsulta" type="button" class="boton-agregar"><i class="bi bi-plus-circle"></i> Guardar consulta</button>
                                            </div>
                                        </form>
                                        <div id="listaConsultas" class="listado-modulo mt-2"></div>
                                        <div id="paginacionConsultas" class="paginacion-modulo"></div>
                                    </div>

                                    <div class="tab-pane fade" id="panel-notas" role="tabpanel">
                                        <div class="modulo-toolbar mb-2">
                                            <input id="buscarNotas" class="input-filtro" type="text" placeholder="Buscar nota...">
                                            <input id="filtroFechaNotas" class="input-filtro" type="date" title="Filtrar por fecha de creación">
                                        </div>
                                        <form id="formNota" class="d-flex flex-column gap-2">
                                            <div>
                                                <label class="label-historial">Nota clínica</label>
                                                <textarea id="notaContenido" class="textarea-historial" placeholder="Escribe una nota breve para el seguimiento"></textarea>
                                            </div>
                                            <div class="acciones-panel">
                                                <button id="btnGuardarNota" type="button" class="boton-agregar"><i class="bi bi-plus-circle"></i> Guardar nota</button>
                                            </div>
                                        </form>
                                        <div id="listaNotas" class="listado-modulo mt-2"></div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/gestion-pacientes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
</body>

</html>
