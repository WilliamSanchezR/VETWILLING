<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario - Recetas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">


    <!-- Tus CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleRecetas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
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

        <!-- CONTENIDO DASHBOARD RECETAS -->
        <div class="cont-receta">
            <div>
                <h2>Recetas</h2>
            </div>
            <div class="campo-buscar">
                <i class="bi bi-search"></i>
                <input type="text" id="buscarPacientes" placeholder="Buscar paciente por documento" autocomplete="off">
                <ul class="lista-sugerencias autocomplete-items" id="listaSugerencias"></ul>
            </div>
            <div class="cont-form">
                <div id="plaque-paciente" class="info-paciente">

                </div>

                <textarea name="" id="descripcion-receta">RP/ </textarea>

                <div class="cont-btn-guardar">
                    <button id="btn-guardar-receta" type="button" class="btn btn-primary btn-guardar">Guardar</button>
                </div>

            </div>
        </div>

    </div>


    <!-- Modal Vista Previa -->
    <div class="modal modal-xl" id="vistaImprimir" role="dialog" tabindex="-1" aria-labelledby="vistaImprimirLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="vistaImprimirLabel">Receta - Vitas previa Imprimir</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="cont-imprimir">
                        <div class="modal-fondo">
                            <img src="<?= BASE_URL ?>/public/assets/auth/img/fondo-marca.png" />
                        </div>

                        <div class="cont-header">
                            <div class="logo">
                                <img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="">
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


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="btn-guardar-resultados" type="button" class="btn btn-primary btn-guardar"><i
                                class="bi bi-printer"></i> Imprimir</button>
                    </div>
                </div>


            </div>
        </div>
    </div>


    <!-- 1. jQuery PRIMERO (obligatorio) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2. Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Script personalizado -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardRecetas.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>

    <!-- 5. Tu script de tabla AL FINAL -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/tableCitas.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>