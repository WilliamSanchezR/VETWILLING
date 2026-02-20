<?php
// Enlazamos la ruta para tomar la session del administrador
require_once BASE_PATH . '/app/helpers/session_representante.php'; // Enlazamos el controlador de profesional para listar los profesionales
$id_veterinaria = $_SESSION['user']['id_veterinaria']; // Obtenemos el ID de la veterinaria desde la sesión

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Propietarios</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap"
        rel="stylesheet">

    <!-- Tus CSS -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Include de la barra lateral izquierda -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_representante.php'
    ?>


    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_representante.php'
        ?>


        <!-- ÁREA DE CONTENIDO - MÓDULO GESTIÓN DE profesionales -->

        <div class="area-contenido" data-id-veterinaria="<?= $_SESSION['user']['id_veterinaria'] ?>">
            <input type="hidden" name="id_veterinaria" id="id_veterinaria" value="<?= $_SESSION['user']['id_veterinaria'] ?>">
            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>Lista Propietarios Registrados</h3>
            </div>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarPropietarios" placeholder="Buscar propietarios...">
                    </div>
                </div>
                <div class="controles-derecha">
                    <button class="btn-control" id="btnVer">
                        <i class="bi bi-eye"></i> Ver 0/0
                    </button>
                    <button class="btn-control" id="btnFiltrar">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <button class="btn-control" id="btnOrdenar">
                        <i class="bi bi-sort-down"></i> Ordenar
                    </button>
                    <button class="btn-control" id="btnExport">
                        <i class="bi bi-download"></i> Export
                    </button>

                </div>
            </div>

            <!-- Tabla de propietarios -->
            <div class="contenedor-tabla">
                <table id="tablaListaPropietarios" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Nombres y Apellidos</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargan dinámicamente desde renderPropietarios.js -->
                    </tbody>
                </table>
            </div>

        </div>

        <!-- SCRIPTS -->
        <!-- 1. jQuery PRIMERO -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        <!-- 2. Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

        <!-- 3. DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



        <!-- 5. Tu script de tabla AL FINAL -->

        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/listaPropietarios.js"></script>

</body>

</html>