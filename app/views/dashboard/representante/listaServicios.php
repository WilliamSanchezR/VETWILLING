<?php
// Enlazamos la ruta para tomar la session del representante
require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/controllers/servicioController.php';

// Obtenemos los servicios de la veterinaria del representante logueado
$datos = listaServiciosPorVeterinaria($_SESSION['user']['id_veterinaria']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Servicios</title>

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

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <!-- Tus CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/listaEspecialidad.styles.css">

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body><!-- BARRA LATERAL IZQUIERDA -->
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


        <!-- ÁREA DE CONTENIDO - MÓDULO GESTIÓN DE VETERINARIAS -->

        <div class="area-contenido">

            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>Lista Servicios Registrados</h3>
            </div>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarServicio" placeholder="Buscar Servicio...">
                    </div>
                </div>
                <div class="controles-derecha">
                    <button class="btn-control" id="btnVer">
                        <i class="bi bi-eye"></i> Ver 0/0
                    </button>

                    <button class="btn-control" id="btnOrdenar">
                        <i class="bi bi-sort-down"></i> Ordenar
                    </button>

                    <button class="btn-control" id="btnExportService">
                        <i class="bi bi-download"></i> Export
                    </button>

                    <!-- <a href="<?= BASE_URL ?>/admin/pdf-veterinarias?action=reporteVeterinariasPDF" target="_blank">
                        <button class="btn-control" id="btnGenerarPdf">
                            <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                        </button>
                    </a> -->


                    <button class="btn-agregar" id="btnAgregarNuevo" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="bi bi-plus-lg"></i> Crear Nuevo Servicio
                    </button>

                </div>
            </div>

            <!-- Tabla de Especialidades -->
            <div class="contenedor-tabla">
                <table id="tablaListaServicios" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Horarios</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($datos)) : ?>
                            <?php foreach ($datos as $servicio):  ?>
                                <tr class="fila-blanca">

                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['horarios']) ?></td>
                                    <td><?= htmlspecialchars($servicio['estado']) ?></td>

                                    <td>
                                        <button class="btn-accion btn-editar" title="Editar" onclick="window.location.href='<?= BASE_URL ?>/representante/editar-servicio?id=<?= $servicio['id_servicio'] ?>'">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button class="btn-accion btn-eliminar" title="Eliminar" data-id="<?= $servicio['id_servicio'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>



    <!-- SCRIPTS -->
    <!-- 1. jQuery PRIMERO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2. Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 3. DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <!-- 5. Script de lista de veterinarias -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/listaVeterinarias.js"></script>

    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <!-- Modo dia  y noche -->
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script> -->

    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/listaServicios.js"></script>




</body>

</html>