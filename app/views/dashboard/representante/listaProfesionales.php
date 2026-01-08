<?php
// Enlazamos la ruta para tomar la session del administrador
require_once BASE_PATH . '/app/helpers/session_representante.php';
// Enlazamos el controlador de profesional para listar los profesionales
require_once BASE_PATH . '/app/controllers/profesionalController.php';
// Llamamos la función para listar los profesionales

$datos = listarUsuarios($_SESSION['user']['id_veterinaria']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de profesionales</title>

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
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'
        ?>


        <!-- ÁREA DE CONTENIDO - MÓDULO GESTIÓN DE profesionales -->

        <div class="area-contenido">

            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>Lista profesionales Registrados</h3>
            </div>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarProfesionales" placeholder="Buscar profesionales...">
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

                    <!-- <a href="<?= BASE_URL ?>/admin/registro-profesional">
                        <button class="btn-agregar" id="btnAgregarNuevo">
                            <i class="bi bi-plus-lg"></i> Agregar Nuevo
                        </button>
                    </a> -->
                </div>
            </div>

            <!-- Tabla de profesionales -->
            <div class="contenedor-tabla">
                <table id="tablaListaProfesionales" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto Perfil</th>
                            <th>Documento</th>
                            <th>Nombres y Apellidos</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>Especialidades</th>
                            <th>Estado</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($datos)) : ?>
                            <?php foreach ($datos as $profesional):  ?>
                                <tr class="fila-blanca">
                                    <td class="tb_foto"><?php if (!empty($profesional['img_perfil'])): ?><img src="<?= BASE_URL ?>/public/uploads/profesionales/<?= $profesional['img_perfil'] ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif; ?></td>
                                    <td><?= $profesional['tipo_documento'] ?> - <?= $profesional['numero_documento'] ?></td>
                                    <td><?= $profesional['nombres'] ?> <?= $profesional['apellidos'] ?></td>
                                    <td><?= $profesional['telefono'] ?></td>
                                    <td><?= $profesional['email'] ?></td>
                                    <td><?= $profesional['especialidad'] ?></td>
                                    <td><?= $profesional['estado'] ?></td>
                                    <td><?= $profesional['rol'] ?></td>
                                    <td class="content-action">
                                        <button class="btn-accion btn-editar" title="Editar">
                                            <a href="<?= BASE_URL ?>/representante/editar-profesional?id=<?= $profesional['id_usuario'] ?>"><i class="bi bi-pencil"></i></a>
                                        </button>
                                        <button class="btn-accion btn-eliminar" title="Eliminar">
                                            <a href="<?= BASE_URL ?>/admin/eliminar-profesional?accion=eliminar&id=<?= $profesional['id_usuario'] ?>"><i class="bi bi-trash"></i></a>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
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



        <!-- 5. Tu script de tabla AL FINAL -->
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/listaprofesionales.js"></script>

        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>