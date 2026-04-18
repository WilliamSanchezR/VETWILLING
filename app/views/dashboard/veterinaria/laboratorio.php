<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio Clínico | Dashboard Veterinario</title>



    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardLaboratorio.css">
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="historial-shell laboratorio-shell">
                <div class="encabezado-seccion mb-0">
                    <div>
                        <h4><i class="bi bi-eyedropper me-2"></i>Laboratorio Clínico</h4>
                        <p class="subtitulo-historial mb-0">Gestiona resultados, seguimiento y estado de exámenes por paciente</p>
                    </div>
                    <span class="badge-sync"><i class="bi bi-activity"></i> Panel de Laboratorio</span>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(10, 147, 44, 0.1); color: #0a932c;">
                                <i class="bi bi-clipboard2-pulse"></i>
                            </div>
                            <div>
                                <strong id="statTotal">0</strong>
                                <small>Total Exámenes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <strong id="statCompletados">0</strong>
                                <small>Completados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <strong id="statPendientes">0</strong>
                                <small>Pendientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <div>
                                <strong id="statCancelados">0</strong>
                                <small>Cancelados</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="historial-card mt-3">
                    <div class="bloque-titulo mb-3">
                        <h5><i class="bi bi-funnel me-2"></i>Controles de Búsqueda</h5>
                    </div>

                    <div class="controles-tabla">
                        <div class="controles-izquierda">
                            <div class="campo-buscar">
                                <i class="bi bi-search"></i>
                                <input type="text" id="buscarPaciente" placeholder="Buscar por propietario, mascota, raza o folio...">
                            </div>
                        </div>
                        <div class="controles-derecha">
                            <button class="btn-control" id="btnOrdenar">
                                <i class="bi bi-sort-down"></i> Ordenar
                            </button>
                            <button class="btn-control" id="btnExport">
                                <i class="bi bi-download"></i> Exportar CSV
                            </button>
                            <button class="boton-agregar" id="btnAgregarNuevo" type="button">
                                <i class="bi bi-plus-circle"></i> Nuevo Registro
                            </button>
                        </div>
                    </div>
                </div>

                <div class="historial-card mt-3 p-0 overflow-hidden">
                    <div class="tabla-header-strip">
                        <h5 class="mb-0"><i class="bi bi-bezier2 me-2"></i>Listado de Pacientes de Laboratorio</h5>
                    </div>
                    <div class="contenedor-tabla-laboratorio">
                        <table class="tabla-laboratorio" id="tabla-pacientes">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No.</th>
                                    <th>Fecha</th>
                                    <th>Propietario</th>
                                    <th>Nombre Mascota</th>
                                    <th>Animal</th>
                                    <th>Raza</th>
                                    <th>Laboratorios</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody id="tablaLaboratorioBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Modal para ver laboratorio  -->
    <div class="modal modal-lg" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Laboratorios Clínicos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="info-paciente" id="detalleOrdenPaciente">
                        <span>Fecha: —</span>
                        <span>Propietario: —</span>
                        <span>Nombre Paciente: —</span>
                        <span>Tipo de Animal: —</span>
                        <span>Raza: —</span>
                        <span>Sexo: —</span>
                    </div>

                    <div class="lab-order-meta mb-3" id="detalleOrdenMeta"></div>

                    <div>
                        <table class="tabla-laboratorio" id="list-laboratorios-asociados">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tipo de Laboratorio</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>

                            <tbody id="tablaResultados"></tbody>
                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>


    <!-- Modal para cargar Resultados laboratorio -->
    <div class="modal modal-xl" id="modalResultadoLab" tabindex="-1" aria-labelledby="modalResultadoLabLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalResultadoLabLabel">Resultado de Laboratorios Clínicos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="info-paciente" id="resultadoPacienteInfo">
                        <span>Fecha: —</span>
                        <span>Propietario: —</span>
                        <span>Nombre Paciente: —</span>
                        <span>Tipo de Animal: —</span>
                        <span>Raza: —</span>
                        <span>Sexo: —</span>
                    </div>

                    <div class="list-examenes">
                        <input type="hidden" id="resultadoOrdenPruebaId">
                        <span id="resultadoPruebaTitulo">Laboratorio: </span>
                        <table class="tabla-laboratorio" id="lista-resultados">
                            <thead>
                                <tr>
                                    <th>Tipo de Prueba</th>
                                    <th>Resultado</th>
                                    <th>Unidades</th>
                                    <th>Rango de Referencia</th>
                                </tr>
                            </thead>

                            <tbody id="tablaDetalleResultados"></tbody>


                        </table>

                        <div class="resultado-toolbar">
                            <select id="estadoPruebaResultado" class="select-filtro">
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Procesada">Procesada</option>
                                <option value="Validada">Validada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                            <textarea id="observacionesResultado" class="textarea-historial" rows="3" placeholder="Observaciones del resultado o de la validación..."></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button id="btn-guardar-resultados" type="button"
                                class="btn btn-primary btn-guardar">Guardar</button>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>

    <div class="modal modal-lg" id="modalNuevaOrdenLab" tabindex="-1" aria-labelledby="modalNuevaOrdenLabLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalNuevaOrdenLabLabel">Nueva Orden de Laboratorio</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="lab-form-grid">
                        <div>
                            <label for="selectPacienteLaboratorio" class="form-label">Paciente</label>
                            <select id="selectPacienteLaboratorio" class="input-historial"></select>
                        </div>
                        <div>
                            <label for="selectPrioridadLaboratorio" class="form-label">Prioridad</label>
                            <select id="selectPrioridadLaboratorio" class="select-filtro">
                                <option value="Baja">Baja</option>
                                <option value="Normal" selected>Normal</option>
                                <option value="Alta">Alta</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="lab-form-span-2">
                            <label for="motivoOrdenLaboratorio" class="form-label">Motivo</label>
                            <input id="motivoOrdenLaboratorio" class="input-historial" type="text"
                                placeholder="Motivo de la orden o sospecha clínica">
                        </div>
                        <div class="lab-form-span-2">
                            <label for="observacionesOrdenLaboratorio" class="form-label">Observaciones</label>
                            <textarea id="observacionesOrdenLaboratorio" class="textarea-historial" rows="3"
                                placeholder="Observaciones adicionales para el laboratorio..."></textarea>
                        </div>
                    </div>

                    <div class="lab-checklist-wrapper mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Pruebas disponibles</h6>
                            <small id="labCatalogStatus" class="text-muted"></small>
                        </div>
                        <div id="listaPruebasCatalogo" class="lab-checklist"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnGuardarOrdenLab" type="button" class="btn btn-primary btn-guardar">Crear Orden</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->
    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardLaboratorios.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>