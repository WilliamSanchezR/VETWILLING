<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medios de Envío — VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/mediosEnvio.css">
</head>

<body data-api="<?= BASE_URL ?>/admin/api/medios-envio">

    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'; ?>

        <div class="area-contenido" style="padding:1.25rem;">

            <!-- Encabezado -->
            <div class="encabezado-pagina mb-3">
                <h1 class="titulo-pagina">
                    <i class="bi bi-send-check-fill me-2 text-success"></i>Medios de Envío
                </h1>
                <p class="subtitulo-pagina mb-0">Configuración de canales de notificación del sistema</p>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="tabsMedios" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-canales" type="button">
                        <i class="bi bi-toggles2 me-1"></i>Canales
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parametros" type="button">
                        <i class="bi bi-sliders me-1"></i>Parámetros Técnicos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-plantillas" type="button">
                        <i class="bi bi-file-earmark-text me-1"></i>Mensajes por Canal
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-monitor" type="button">
                        <i class="bi bi-exclamation-triangle me-1"></i>Monitor de Fallos
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="tabsContent">

                <!-- ══════════════════ TAB 1: CANALES ══════════════════ -->
                <div class="tab-pane fade show active" id="tab-canales" role="tabpanel">

                    <div class="row g-3" id="listaCanales">
                        <!-- Tarjetas generadas por JS -->
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm"></div> Cargando canales…
                        </div>
                    </div>

                </div>

                <!-- ══════════════════ TAB 2: PARÁMETROS TÉCNICOS ══════════════════ -->
                <div class="tab-pane fade" id="tab-parametros" role="tabpanel">

                    <!-- Selector de canal -->
                    <div class="mb-3 d-flex gap-2 align-items-center">
                        <label class="fw-semibold me-2">Canal:</label>
                        <div id="btnsCanalConfig" class="d-flex gap-2 flex-wrap"></div>
                    </div>

                    <!-- Formulario SMTP (email) -->
                    <div id="formSmtpWrap" class="card canal-config-card d-none">
                        <div class="card-header fw-semibold">
                            <i class="bi bi-envelope-at me-2 text-success"></i>Configuración SMTP — Correo Electrónico
                        </div>
                        <div class="card-body">
                            <form id="formSmtp" class="row g-3">
                                <input type="hidden" name="id_canal" id="smtpIdCanal">
                                <div class="col-md-6">
                                    <label class="form-label">Servidor SMTP</label>
                                    <input type="text" class="form-control" name="smtp_host" placeholder="smtp.gmail.com">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Puerto</label>
                                    <input type="number" class="form-control" name="smtp_port" placeholder="465">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Encriptación</label>
                                    <select class="form-select" name="smtp_encriptacion">
                                        <option value="smtps">SMTPS (SSL/465)</option>
                                        <option value="tls">STARTTLS (587)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Usuario SMTP</label>
                                    <input type="email" class="form-control" name="smtp_usuario">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contraseña / App Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="smtp_password" id="inputSmtpPass" autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('inputSmtpPass')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Dejar vacío para mantener la contraseña actual.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email remitente</label>
                                    <input type="email" class="form-control" name="smtp_remitente">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre remitente</label>
                                    <input type="text" class="form-control" name="smtp_nombre_remitente">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Máx. reintentos</label>
                                    <input type="number" class="form-control" name="max_reintentos" min="1" max="10">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Intervalo (seg)</label>
                                    <input type="number" class="form-control" name="intervalo_reintento_seg" min="0">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-floppy me-1"></i>Guardar configuración
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="probarSmtp()">
                                        <i class="bi bi-send me-1"></i>Probar conexión
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Formulario Push (push) -->
                    <div id="formPushWrap" class="card canal-config-card d-none">
                        <div class="card-header fw-semibold">
                            <i class="bi bi-phone me-2 text-success"></i>Configuración Push — Notificaciones Móviles
                        </div>
                        <div class="card-body">
                            <form id="formPush" class="row g-3">
                                <input type="hidden" name="id_canal" id="pushIdCanal">
                                <div class="col-12">
                                    <label class="form-label">FCM Server Key (API Key)</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="push_api_key" id="inputPushKey" autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('inputPushKey')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Clave del servidor de Firebase Cloud Messaging.</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Endpoint FCM</label>
                                    <input type="text" class="form-control" name="push_endpoint"
                                           placeholder="https://fcm.googleapis.com/fcm/send">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Máx. reintentos</label>
                                    <input type="number" class="form-control" name="max_reintentos" min="1" max="10">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Intervalo (seg)</label>
                                    <input type="number" class="form-control" name="intervalo_reintento_seg" min="0">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-floppy me-1"></i>Guardar configuración
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- In-app: sin parámetros externos -->
                    <div id="formInAppWrap" class="card canal-config-card d-none">
                        <div class="card-header fw-semibold">
                            <i class="bi bi-bell me-2 text-success"></i>Notificaciones en la Aplicación
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Este canal no requiere parámetros externos. Las notificaciones se almacenan
                                directamente en la base de datos y son visibles en el panel de cada usuario.
                            </div>
                            <form id="formInApp" class="row g-3">
                                <input type="hidden" name="id_canal" id="inappIdCanal">
                                <div class="col-md-3">
                                    <label class="form-label">Máx. reintentos</label>
                                    <input type="number" class="form-control" name="max_reintentos" min="1" max="5">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-floppy me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="seleccionaCanalMsg" class="text-muted mt-3">
                        <i class="bi bi-arrow-up me-1"></i>Selecciona un canal para ver su configuración.
                    </div>

                </div>

                <!-- ══════════════════ TAB 3: PLANTILLAS ══════════════════ -->
                <div class="tab-pane fade" id="tab-plantillas" role="tabpanel">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Canal</label>
                            <select class="form-select" id="selectCanalPlantilla">
                                <option value="">-- Seleccionar --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo de notificación</label>
                            <select class="form-select" id="selectTipoPlantilla" disabled>
                                <option value="">-- Seleccionar --</option>
                            </select>
                        </div>
                    </div>

                    <div id="editorPlantilla" class="d-none">
                        <form id="formPlantilla" class="card">
                            <div class="card-header fw-semibold">
                                <i class="bi bi-pencil-square me-2"></i>Editor de plantilla
                            </div>
                            <div class="card-body row g-3">
                                <input type="hidden" name="id_canal" id="plantIdCanal">
                                <input type="hidden" name="tipo_notificacion" id="plantTipo">

                                <div id="wrapAsunto" class="col-12 d-none">
                                    <label class="form-label">Asunto del correo</label>
                                    <input type="text" class="form-control" name="asunto" id="plantAsunto">
                                </div>
                                <div id="wrapHtml" class="col-12 d-none">
                                    <label class="form-label">Cuerpo HTML (email)</label>
                                    <textarea class="form-control font-monospace" name="cuerpo_html" id="plantHtml" rows="10"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Cuerpo en texto plano
                                        <span class="text-muted small">(push / in-app)</span>
                                    </label>
                                    <textarea class="form-control" name="cuerpo_texto" id="plantTexto" rows="4"></textarea>
                                </div>
                                <div class="col-12">
                                    <div id="varsDisponibles" class="vars-badge-list"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-floppy me-1"></i>Guardar plantilla
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- ══════════════════ TAB 4: MONITOR DE FALLOS ══════════════════ -->
                <div class="tab-pane fade" id="tab-monitor" role="tabpanel">

                    <!-- Resumen estadístico -->
                    <div id="resumenMonitoreo" class="row g-3 mb-4"></div>

                    <!-- Controles -->
                    <div class="d-flex gap-2 flex-wrap align-items-center mb-3">
                        <select class="form-select w-auto" id="selectHorasMonitor">
                            <option value="6">Últimas 6 h</option>
                            <option value="24" selected>Últimas 24 h</option>
                            <option value="72">Últimas 72 h</option>
                            <option value="168">Última semana</option>
                        </select>
                        <button class="btn btn-outline-secondary btn-sm" onclick="cargarFallos()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                        </button>
                        <button class="btn btn-warning btn-sm" id="btnReintentar" onclick="abrirModalReintentar()">
                            <i class="bi bi-arrow-repeat me-1"></i>Reintentar fallidos
                        </button>
                    </div>

                    <!-- Tabla de fallos -->
                    <div class="table-responsive">
                        <table class="tabla-admin" id="tablaFallos">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Canal</th>
                                    <th>Destinatario</th>
                                    <th>Propietario</th>
                                    <th>Fecha envío</th>
                                    <th>Intentos</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyFallos">
                                <tr><td colspan="7" class="text-center text-muted py-3">Cargando…</td></tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div><!-- /tab-content -->

        </div><!-- /area-contenido -->
    </div><!-- /contenido-principal -->

    <!-- Modal: reintentar -->
    <div class="modal fade" id="modalReintentar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Reintentar envíos fallidos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Canal</label>
                    <select class="form-select" id="selectReintentarCanal">
                        <option value="email">Correo Electrónico</option>
                        <option value="in_app">Notificaciones en App</option>
                        <option value="push">Push Móvil</option>
                    </select>
                    <div class="form-text mt-2">
                        Marca como "pendiente" los envíos fallidos con menos de 3 intentos
                        para que el cron los procese nuevamente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="ejecutarReintento()">
                        <i class="bi bi-arrow-repeat me-1"></i>Reintentar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast de notificación -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastMensaje" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastBody"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/mediosEnvio.js"></script>
</body>
</html>
