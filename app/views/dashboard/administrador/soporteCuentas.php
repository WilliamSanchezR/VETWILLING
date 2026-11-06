<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte de cuentas — VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/mediosEnvio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/soporteCuentas.css">
</head>

<body data-api="<?= BASE_URL ?>/admin/api/soporte-cuentas"
      data-csrf="<?= e(csrf_token()) ?>">

    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'; ?>

        <div class="area-contenido">

            <div class="encabezado-pagina mb-3">
                <h1 class="titulo-pagina">
                    <i class="bi bi-person-gear me-2 text-success"></i>Soporte de cuentas
                </h1>
                <p class="subtitulo-pagina mb-0">Revisa si el correo de un usuario funciona y asígnale una clave temporal si no puede entrar</p>
            </div>

            <!-- Búsqueda -->
            <form id="formBuscar" class="card canal-config-card mb-3" role="search">
                <div class="card-body d-flex gap-2 flex-wrap">
                    <label class="visually-hidden" for="inputBuscar">Correo o nombre</label>
                    <input type="search" class="form-control flex-grow-1" id="inputBuscar" name="q" minlength="3"
                           placeholder="Correo o nombre del usuario (mínimo 3 letras)" autocomplete="off" required>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>
                </div>
            </form>

            <div id="resultados" class="list-group mb-3" aria-live="polite"></div>

            <!-- Detalle de la cuenta -->
            <section id="detalleCuenta" class="d-none" aria-live="polite">
                <div class="card canal-config-card mb-3">
                    <div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h2 class="h5 mb-1" id="cuentaNombre"></h2>
                            <div class="text-muted" id="cuentaEmail"></div>
                            <div class="mt-2 d-flex gap-2 flex-wrap" id="cuentaEtiquetas"></div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary" id="btnPrueba">
                                <i class="bi bi-send me-1"></i>Enviar correo de prueba
                            </button>
                            <button type="button" class="btn btn-warning" id="btnClaveTemporal">
                                <i class="bi bi-key me-1"></i>Asignar clave temporal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Clave temporal: se muestra una sola vez -->
                <div class="alert alert-warning d-none" id="cajaClave" role="alert">
                    <h3 class="h6 mb-2"><i class="bi bi-key-fill me-1"></i>Clave temporal (solo se muestra esta vez)</h3>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <code class="clave-temporal" id="claveTemporal"></code>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="btnCopiarClave">
                            <i class="bi bi-clipboard me-1"></i>Copiar
                        </button>
                    </div>
                    <p class="mb-0 mt-2 small">Dásela al usuario por un medio seguro (teléfono o en persona). Al entrar, el sistema le pedirá cambiarla.</p>
                </div>

                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="card canal-config-card h-100">
                            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cloud-check me-2 text-success"></i>Estado en Brevo (30 días)</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefrescar" title="Volver a consultar">
                                    <i class="bi bi-arrow-clockwise"></i><span class="visually-hidden">Volver a consultar</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert mb-3" id="resumenBrevo"></div>
                                <div class="table-responsive">
                                    <table id="tablaBrevo" class="table table-sm align-middle mb-0">
                                        <thead><tr><th>Fecha</th><th>Evento</th><th>Asunto</th></tr></thead>
                                        <tbody id="tbodyBrevo"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card canal-config-card h-100">
                            <div class="card-header fw-semibold">
                                <i class="bi bi-clock-history me-2 text-success"></i>Correos que el sistema le envió
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th>Fecha</th><th>Tipo</th><th>Estado</th></tr></thead>
                                        <tbody id="tbodyHistorial"></tbody>
                                    </table>
                                </div>
                                <p class="form-text mb-0 mt-2">
                                    "Exitoso" significa que el proveedor aceptó el correo, no que llegó a la bandeja. Para saber si llegó, mira el estado en Brevo.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div><!-- /area-contenido -->
    </div><!-- /contenido-principal -->

    <!-- Toast de notificación -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastMensaje" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastBody"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/soporteCuentas.js"></script>
</body>
</html>
