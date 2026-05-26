<?php
// require_once BASE_PATH . '/app/helpers/session_paciente.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis notificaciones — Paciente</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/pacientes/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/pacientes/css/navbar-superior.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/pacientes/css/notificaciones-paciente.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <div class="contenido-principal">
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <section class="pac-notif-page">

                <!-- ENCABEZADO -->
                <header class="pac-page-header">
                    <div class="pac-header-meta">
                        <span class="pac-eyebrow">
                            <i class="bi bi-bell"></i>
                            Notificaciones
                        </span>
                        <h1>Mis avisos</h1>
                        <p>Aquí encontrarás tus citas, recordatorios de vacunas y resultados de exámenes.</p>
                    </div>
                    <div class="pac-page-actions">
                        <button type="button" class="pac-btn pac-btn-primary" id="pacBtnMarkAll">
                            <i class="bi bi-check-all"></i>
                            Marcar todas leídas
                        </button>
                        <a href="<?= BASE_URL ?>/paciente/dashboard" class="pac-btn pac-btn-ghost">
                            <i class="bi bi-arrow-left"></i>
                            Volver
                        </a>
                    </div>
                </header>

                <!-- FILTROS -->
                <div class="pac-filters" id="pacFilters">
                    <button class="pac-filter-btn active" data-filter="todas">
                        Todas
                        <span class="pac-count" id="cntTodas">0</span>
                    </button>
                    <button class="pac-filter-btn" data-filter="no-leidas">
                        Sin leer
                        <span class="pac-count" id="cntNoLeidas">0</span>
                    </button>
                    <button class="pac-filter-btn" data-filter="cita">
                        <i class="bi bi-calendar-check"></i> Citas
                        <span class="pac-count" id="cntCitas">0</span>
                    </button>
                    <button class="pac-filter-btn" data-filter="recuerdo">
                        <i class="bi bi-alarm"></i> Recordatorios
                        <span class="pac-count" id="cntRecuerdos">0</span>
                    </button>
                    <button class="pac-filter-btn" data-filter="resultado">
                        <i class="bi bi-clipboard2-pulse"></i> Resultados
                        <span class="pac-count" id="cntResultados">0</span>
                    </button>
                    <button class="pac-filter-btn" data-filter="alerta">
                        <i class="bi bi-exclamation-triangle"></i> Alertas
                        <span class="pac-count" id="cntAlertas">0</span>
                    </button>
                </div>

                <!-- TARJETA PRINCIPAL -->
                <div class="pac-notif-card">

                    <!-- Skeleton mientras carga -->
                    <div class="pac-skeleton" id="pacSkeleton">
                        <div class="pac-skel-item">
                            <div class="pac-skel-icon"></div>
                            <div class="pac-skel-lines">
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                            </div>
                        </div>
                        <div class="pac-skel-item">
                            <div class="pac-skel-icon"></div>
                            <div class="pac-skel-lines">
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                            </div>
                        </div>
                        <div class="pac-skel-item">
                            <div class="pac-skel-icon"></div>
                            <div class="pac-skel-lines">
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                                <div class="pac-skel-line"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista renderizada por JS -->
                    <div id="pacNotifList" class="pac-notif-list is-hidden"></div>

                    <!-- Estado vacío -->
                    <div id="pacEmpty" class="pac-empty is-hidden">
                        <div class="pac-empty-icon">
                            <i class="bi bi-bell-slash"></i>
                        </div>
                        <h2>Sin notificaciones</h2>
                        <p>Cuando tengas citas, recordatorios o resultados nuevos aparecerán aquí.</p>
                    </div>

                </div>

            </section>
        </div>
    </div>

    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/pacientes/js/notificaciones-paciente.js"></script>
</body>

</html>