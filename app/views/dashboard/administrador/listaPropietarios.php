<?php
// Subtarea 1: autenticación del administrador — antes de cualquier query
require_once BASE_PATH . '/app/helpers/session_administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Propietarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <div class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'; ?>

        <div class="area-contenido">

            <div class="encabezado-modulo">
                <h3>Propietarios Registrados</h3>
            </div>

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

            <div class="contenedor-tabla">
                <table id="tablaListaPropietarios" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cargado dinámicamente por listaPropietariosAdmin.js -->
                    </tbody>
                </table>
            </div>

        </div>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/listaPropietariosAdmin.js"></script>

    </div>

</body>

</html>
