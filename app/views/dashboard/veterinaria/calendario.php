<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/models/Veterinario.php';

$idUsuario = $_SESSION['user']['id_usuario'] ?? null;
$fechaHoy = date('Y-m-d');
$statsVeterinario = new Veterinario();

$totalCitasHoy = $idUsuario ? $statsVeterinario->contarCitasHoyPorVeterinario($idUsuario, $fechaHoy) : 0;
$totalPendientesHoy = $idUsuario ? $statsVeterinario->contarCitasPendientesHoyPorVeterinario($idUsuario, $fechaHoy) : 0;
$totalPacientesHoy = $idUsuario ? $statsVeterinario->contarPacientesHoyPorVeterinario($idUsuario, $fechaHoy) : 0;
$totalCitasSemana = $idUsuario ? $statsVeterinario->contarCitasSemanaPorVeterinario($idUsuario, $fechaHoy) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- FullCalendar -->

    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleCalendario.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css"> -->

</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Aqui va el include -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'
    ?>


    <!-- PANEL DERECHO -->
    <!-- aqui va el inclunde notifi -->
    <?php
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- TARJETAS DE RESUMEN -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-success-soft">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars((string)$totalCitasHoy) ?></h3>
                            <p>Citas Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-warning-soft">
                            <i class="bi bi-clock-fill text-warning"></i>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars((string)$totalPendientesHoy) ?></h3>
                            <p>Pendientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-info-soft">
                            <i class="bi bi-people-fill text-info"></i>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars((string)$totalPacientesHoy) ?></h3>
                            <p>Pacientes Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-success-soft">
                            <i class="bi bi-calendar-week-fill text-success"></i>
                        </div>
                        <div>
                            <h3><?= htmlspecialchars((string)$totalCitasSemana) ?></h3>
                            <p>Esta Semana</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BARRA DE ACCIONES -->
            <div class="barra-acciones-citas">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="boton-vista active" data-vista="calendario">
                            <i class="bi bi-calendar3"></i> Calendario
                        </button>
                        <button class="boton-vista" data-vista="lista">
                            <i class="bi bi-list-ul"></i> Lista
                        </button>
                        <button class="boton-vista" data-vista="dia">
                            <i class="bi bi-clock"></i> Día
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <select class="select-veterinario">
                            <option value="">Cargando veterinarios...</option>
                        </select>
                        <button class="boton-nueva-cita" onclick="abrirModalNuevaCita()">
                            <i class="bi bi-plus-circle"></i> Nueva Cita
                        </button>
                    </div>
                </div>
            </div>

            <!-- VISTA CALENDARIO -->
            <div id='calendar'>

            </div>

            <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- FullCalendar -->

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/mainCalendar.js"></script>

    <!-- Propio -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>