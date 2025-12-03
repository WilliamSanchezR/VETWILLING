<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';


// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos

require_once BASE_PATH . '/app/controllers/veterinarioController.php';




// Llamamos la funcion especifica que existe en dicho controlador

$datos = mostrarVeterinarios();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Veterinario - Citas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap"
        rel="stylesheet">

    <!-- Tus CSS -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleTableCitas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">


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
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>


        <!-- ÁREA DE CONTENIDO - MÓDULO GESTIÓN DE CITAS -->
        <div class="area-contenido">

            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>MÓDULO GESTIÓN DE VETERINARIOS</h3>
            </div>

            <!-- <a href="<?= BASE_URL ?>/veterinario/reporte-veterinarios"
                class="btn btn-primary"
                target="_blank">
                Generar Reporte PDF
            </a> -->

            <button id="btnRutaReporte" data-ruta="<?= BASE_URL ?>/veterinario/reporte-veterinarios" hidden></button>



            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarCitas" placeholder="Buscar citas...">
                    </div>
                </div>
                <div class="controles-derecha">



                    <a href="<?= BASE_URL ?>/veterinario/registrar-veterinario">
                        <button class="btn-agregar" id="btnAgregarNuevo">
                            <i class="bi bi-plus-lg"></i> Agregar Nuevo
                        </button>
                    </a>

                </div>
            </div>

            <!-- Tabla de Citas -->
            <div class="contenedor-tabla">
                <table id="tablaCitas" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto de perfi</th>
                            <th>Numero de documeto</th>
                            <th>Nombres & Apellidos</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Rol</th>
                            <th>Veterinaria</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($datos)): ?>
                            <?php foreach ($datos as $veterinario): ?>
                                <tr class="fila-gris">
                                    <td><img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $veterinario['img_perfil'] ?>" alt=""></td>
                                    <td><?= $veterinario['tipo_documento'] ?> - <?= $veterinario['numero_documento'] ?></td>
                                    <td><?= $veterinario['nombres'] ?> <br> <?= $veterinario['apellidos'] ?></td>
                                    <td><?= $veterinario['telefono'] ?></td>
                                    <td><?= $veterinario['email'] ?></td>
                                    <td><?= $veterinario['estado'] ?></td>
                                    <td><?= $veterinario['nombre_rol'] ?></td>
                                    <td><?= $veterinario['nombre_veterinaria'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/veterinario/editar-veterinario?id=<?= $veterinario['id_usuario'] ?>" class="btn-accion btn-editar" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/veterinario/eliminar-veterinario?accion=eliminar&id=<?= $veterinario['id_usuario'] ?>" class="btn-accion btn-eliminar" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>No hay veterinarios registrados</td>
                            </tr>
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

    <!-- DataTables y extensiones -->
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <!-- 4. Script de dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>

    <!-- 5. Tu script de tabla AL FINAL -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/citas.js"></script>

    <!-- Modo dia  y noche -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>