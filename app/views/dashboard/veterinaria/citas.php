<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title>Mis Citas — VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleCitas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido citas-shell">

            <!-- HEADER -->
            <div class="citas-header">
                <div class="citas-header-left">
                    <div class="citas-header-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h2 class="citas-titulo">Mis Citas</h2>
                        <p class="citas-subtitulo">Próximas citas pendientes y confirmadas</p>
                    </div>
                </div>
                <button class="btn-nueva-cita" onclick="nuevaCita()">
                    <i class="bi bi-plus-circle"></i>
                    Nueva Cita
                </button>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="stats-container">
                <div class="stat-card pendientes">
                    <div class="stat-icon pendientes"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-content">
                        <h3 id="statPendientes">—</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
                <div class="stat-card confirmadas">
                    <div class="stat-icon confirmadas"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-content">
                        <h3 id="statConfirmadas">—</h3>
                        <p>Confirmadas</p>
                    </div>
                </div>
                <div class="stat-card completadas">
                    <div class="stat-icon completadas"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="stat-content">
                        <h3 id="statCompletadas">—</h3>
                        <p>Completadas</p>
                    </div>
                </div>
                <div class="stat-card canceladas">
                    <div class="stat-icon canceladas"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-content">
                        <h3 id="statCanceladas">—</h3>
                        <p>Canceladas</p>
                    </div>
                </div>
            </div>

            <!-- BARRA DE BÚSQUEDA Y TABS -->
            <div class="citas-toolbar">
                <div class="campo-busqueda">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Buscar por mascota, propietario o tipo…" id="buscarCita">
                </div>
                <div class="tabs-container">
                    <button class="tab-btn active" data-tab="todas">
                        <i class="bi bi-grid-3x3-gap"></i> Todas
                    </button>
                    <button class="tab-btn" data-tab="pendiente">
                        <i class="bi bi-clock-history"></i> Pendientes
                    </button>
                    <button class="tab-btn" data-tab="confirmada">
                        <i class="bi bi-check-circle"></i> Confirmadas
                    </button>
                    <button class="tab-btn" data-tab="completada">
                        <i class="bi bi-check-circle-fill"></i> Completadas
                    </button>
                </div>
            </div>

            <!-- LISTA DE CITAS -->
            <div class="citas-lista" id="citasLista">
                <div class="citas-skeleton">
                    <i class="bi bi-hourglass-split"></i>
                    Cargando citas…
                </div>
            </div>

        </div><!-- /.area-contenido -->
    </div><!-- /.contenido-principal -->

    <!-- ===== MODAL ATENDER CITA ===== -->
    <div class="modal fade" id="modalAtenderCita" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content hist-modal">

                <div class="hist-modal-header">
                    <div class="hist-modal-header-info">
                        <div class="hist-modal-icon">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">Registrar Atención</h5>
                            <small>Completa los datos clínicos de la consulta</small>
                        </div>
                    </div>
                    <button type="button" class="hist-modal-close" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="hist-modal-body">
                    <!-- Resumen del paciente -->
                    <div class="resumen-paciente mb-4">
                        <div class="resumen-paciente-head">
                            <i class="bi bi-heart-pulse"></i>
                            <div>
                                <strong id="atenderPacienteNombre">--</strong>
                                <small id="atenderPacienteMeta">--</small>
                            </div>
                        </div>
                        <span class="badge-version">Atención</span>
                    </div>

                    <form id="formAtenderCita" class="d-flex flex-column gap-3">
                        <input type="hidden" id="atenderIdPaciente">
                        <input type="hidden" id="atenderIdCita">

                        <div class="campos-grid">
                            <div>
                                <label for="atenderFecha" class="label-historial">
                                    Fecha de atención <span class="text-danger">*</span>
                                </label>
                                <input id="atenderFecha" class="input-historial" type="date">
                            </div>
                            <div>
                                <label for="atenderMotivo" class="label-historial">
                                    Motivo de consulta <span class="text-danger">*</span>
                                </label>
                                <input id="atenderMotivo" class="input-historial" type="text" placeholder="Describe el motivo...">
                            </div>
                        </div>
                        <div>
                            <label for="atenderDiagnostico" class="label-historial">Diagnóstico</label>
                            <textarea id="atenderDiagnostico" class="textarea-historial" rows="2"></textarea>
                        </div>
                        <div>
                            <label for="atenderTratamiento" class="label-historial">Tratamientos aplicados</label>
                            <textarea id="atenderTratamiento" class="textarea-historial" rows="2"></textarea>
                        </div>
                        <div>
                            <label for="atenderMedicacion" class="label-historial">Medicación recetada</label>
                            <textarea id="atenderMedicacion" class="textarea-historial" rows="2"></textarea>
                        </div>
                        <div>
                            <label for="atenderObservaciones" class="label-historial">Observaciones adicionales</label>
                            <textarea id="atenderObservaciones" class="textarea-historial" rows="2"></textarea>
                        </div>
                    </form>
                </div>

                <div class="hist-modal-footer">
                    <button type="button" class="btn-secundario" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="button" class="boton-agregar" id="btnGuardarAtencionCita">
                        <i class="bi bi-check2-circle me-1"></i> Guardar atención
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/citas.js"></script>
</body>
</html>
