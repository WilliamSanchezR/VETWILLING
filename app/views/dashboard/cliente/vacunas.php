<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$id_mascota = $_GET['id'];
$mascota = consultarMascotaId($id_mascota);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/vacunas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="vacunas-container">
                <div class="vacunas-header">
                    <h2>Historial de Vacunas</h2>
                    <div class="vacunas-resumen">
                        <span class="resumen-item">
                            <strong id="totalVacunas">0</strong> vacunas aplicadas
                        </span>
                    </div>
                </div>

                <!-- Timeline de vacunas -->
                <div class="vacunas-timeline" id="vacunasTimeline">
                    <!-- Las vacunas se cargarán dinámicamente aquí -->
                </div>

                <!-- Mensaje cuando no hay vacunas -->
                <div class="vacunas-vacio" id="vacunasVacio" style="display: none;">
                    <div class="vacio-icon">💉</div>
                    <p>No hay vacunas registradas aún</p>
                    <span>El veterinario agregará las vacunas de tu mascota</span>
                </div>

                <!-- Próximas vacunas -->
                <div class="proximas-vacunas" id="proximasVacunas" style="display: none;">
                    <h3>📅 Próximas vacunas programadas</h3>
                    <div class="proximas-lista" id="proximasLista"></div>
                </div>
            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/vacunas.js"></script>

</body>

</html>