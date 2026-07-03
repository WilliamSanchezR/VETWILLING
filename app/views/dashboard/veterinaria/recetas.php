<?php
require_once BASE_PATH . "/app/helpers/session_veterinario.php"; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recetas Médicas | Dashboard Veterinario</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <!-- CSS compartido del dashboard -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">

    <!-- CSS específico de recetas -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleRecetas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <!-- BARRA LATERAL -->
    <?php include_once __DIR__ . "/../../layouts/sidebar_veterinario.php"; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ .
            "/../../layouts/panel_superior_veterinario.php"; ?>

        <div class="area-contenido">
            <section class="historial-shell">

                <!-- ── ENCABEZADO ── -->
                <div class="encabezado-seccion mb-0">
                    <div>
                        <h4><i class="bi bi-file-earmark-medical me-2"></i>Recetas Médicas</h4>
                        <p class="subtitulo-historial mb-0">Emite, previsualiza e imprime recetas clínicas para tus pacientes</p>
                    </div>
                    <div class="receta-steps d-none d-lg-flex align-items-center">
                        <span class="receta-step active" id="step1">
                            <span class="step-num">1</span>
                            <span class="step-label">Buscar paciente</span>
                        </span>
                        <span class="receta-step-arrow"><i class="bi bi-chevron-right"></i></span>
                        <span class="receta-step" id="step2">
                            <span class="step-num">2</span>
                            <span class="step-label">Redactar receta</span>
                        </span>
                        <span class="receta-step-arrow"><i class="bi bi-chevron-right"></i></span>
                        <span class="receta-step" id="step3">
                            <span class="step-num">3</span>
                            <span class="step-label">Imprimir</span>
                        </span>
                    </div>
                </div>

                <!-- ── ESTADÍSTICAS ── -->
                <div class="row g-3 mt-0">
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background:rgba(10,147,44,.1);color:#0a932c;">
                                <i class="bi bi-file-earmark-medical-fill"></i>
                            </div>
                            <div>
                                <strong id="statRecetas">0</strong>
                                <small>Recetas Emitidas</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <strong id="statPacientes">0</strong>
                                <small>Pacientes Atendidos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div>
                                <strong id="statHoy">0</strong>
                                <small>Emitidas Hoy</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">
                                <i class="bi bi-printer-fill"></i>
                            </div>
                            <div>
                                <strong id="statImpresiones">0</strong>
                                <small>Impresiones del Día</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── LAYOUT PRINCIPAL ── -->
                <div class="row g-3 recetas-main-layout">

                    <!-- COLUMNA IZQUIERDA: Buscador + Info del paciente -->
                    <div class="col-lg-4 d-flex flex-column gap-3">

                        <!-- Panel de búsqueda -->
                        <div class="historial-card receta-search-card">
                            <div class="bloque-titulo mb-3">
                                <h5><i class="bi bi-search me-2"></i>Buscar Paciente</h5>
                            </div>
                            <div class="receta-search-wrapper">
                                <i class="bi bi-search receta-search-icon"></i>
                                <input type="text" id="buscarPacientes" class="input-historial receta-search-input"
                                    placeholder="N.º documento del propietario…" autocomplete="off">
                                <ul class="lista-sugerencias" id="listaSugerencias"></ul>
                            </div>
                            <p class="receta-search-hint mt-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Ingresa el documento del propietario para buscar a su mascota.
                            </p>
                        </div>

                        <!-- Tarjeta info paciente -->
                        <div class="historial-card receta-info-card grow">
                            <div class="receta-info-empty" id="emptyState">
                                <div class="receta-empty-icon">
                                    <i class="bi bi-paw"></i>
                                </div>
                                <p>Selecciona un paciente<br>para ver su información</p>
                            </div>
                            <div id="plaque-paciente" class="receta-info-filled" style="display:none;"></div>
                        </div>

                    </div>

                    <!-- COLUMNA DERECHA: Editor de receta -->
                    <div class="col-lg-8">
                        <div class="historial-card receta-pad-wrapper h-100">

                            <!-- Cabecera del taco de recetas -->
                            <div class="receta-pad-header">
                                <div class="receta-pad-logo">
                                    <i class="bi bi-heart-pulse-fill"></i>
                                    <span>VetWilling</span>
                                </div>
                                <div class="receta-pad-meta">
                                    <span class="receta-pad-fecha" id="receta-pad-fecha"></span>
                                    <span class="receta-pad-badge">
                                        <i class="bi bi-shield-check me-1"></i>Receta Veterinaria
                                    </span>
                                </div>
                            </div>

                            <!-- Indicador de paciente seleccionado (mini inline) -->
                            <div class="receta-pad-patient-bar" id="receta-pad-patient-bar" style="display:none;">
                                <i class="bi bi-paw me-2"></i>
                                <span id="receta-pad-patient-name">—</span>
                                <button class="receta-pad-patient-clear" id="btn-deselect-patient" title="Cambiar paciente">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>

                            <!-- Cuerpo del editor -->
                            <div class="receta-pad-body">
                                <div class="receta-rp-label">
                                    <i class="bi bi-capsule me-2"></i>RP /
                                </div>
                                <textarea id="descripcion-receta" class="receta-textarea"
                                    placeholder="Escribe el nombre del medicamento, concentración, dosis, frecuencia y duración del tratamiento…"></textarea>
                            </div>

                            <!-- Barra de acciones -->
                            <div class="receta-pad-footer">
                                <span class="receta-char-counter">
                                    <i class="bi bi-text-paragraph me-1"></i>
                                    <span id="charCount">0</span> caracteres
                                </span>
                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn-secundario" id="btn-limpiar-receta" type="button">
                                        <i class="bi bi-trash me-1"></i> Limpiar
                                    </button>
                                    <button id="btn-guardar-receta" type="button" class="boton-agregar" disabled>
                                        <i class="bi bi-printer me-1"></i> Vista Previa e Imprimir
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>


    <!-- ═══════════ MODAL VISTA PREVIA ═══════════ -->
    <div class="modal modal-xl fade" id="vistaImprimir" role="dialog" tabindex="-1"
        aria-labelledby="vistaImprimirLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content receta-modal-content">

                <div class="modal-header receta-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="receta-modal-icon">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="vistaImprimirLabel">Vista Previa — Receta Médica</h5>
                            <small class="opacity-75">Revisa el documento antes de imprimir</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="cont-imprimir">
                        <div class="modal-fondo">
                            <img src="<?= BASE_URL ?>/public/assets/auth/img/fondo-marca.png" aria-hidden="true" />
                        </div>

                        <div class="cont-header">
                            <div class="logo">
                                <img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="VetWilling">
                            </div>
                            <div class="info-medico">
                                <span>DR. Chapatin Pepito Perez</span>
                                <span>Médico Veterinario</span>
                                <span>Rut: 111100111</span>
                            </div>
                        </div>

                        <div>
                            <div id="plaque-paciente-modal" class="info-paciente"></div>
                            <div class="cont-receta-preview">
                                <p id="receta-paciente"></p>
                            </div>
                        </div>

                        <div class="footer_preview">
                            <div class="footer-fecha">
                                <span id="fecha_report"></span>
                                <div class="pie-firma"><span>Fecha Entrega</span></div>
                            </div>
                            <div></div>
                            <div class="firma">
                                <img src="" alt="">
                                <div class="pie-firma"><span>Firma y sello</span></div>
                            </div>
                        </div>
                    </div>


                </div><!-- /.modal-body -->

                <div class="modal-footer receta-modal-footer">
                    <button type="button" class="btn-secundario" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button id="btn-guardar-resultados" type="button" class="btn" style="background:#0a932c;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:600;cursor:pointer;">
                        <i class="bi bi-printer me-1"></i> Imprimir Receta
                    </button>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /#vistaImprimir -->


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardRecetas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/tableCitas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>
