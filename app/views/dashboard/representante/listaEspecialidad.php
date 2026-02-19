<?php
// Enlazamos la ruta para tomar la session del representante
require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/controllers/especialidadController.php';
require_once BASE_PATH . '/app/controllers/servicioController.php';


$datos = listarEspecialidadesRegistradas($_SESSION['user']['id_veterinaria']);
$id_EspecialidadEditar = null;

$listaServicios = listarServiciosActivos($_SESSION['user']['id_veterinaria']);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Especialidades</title>

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/modalStyle.css">

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
                <h3>Lista Especialidades Registradas</h3>
            </div>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarEspecialidad" placeholder="Buscar Especialidad...">
                    </div>
                </div>
                <div class="controles-derecha">
                    <button class="btn-control" id="btnVer">
                        <i class="bi bi-eye"></i> Ver 0/0
                    </button>

                    <button class="btn-control" id="btnOrdenar">
                        <i class="bi bi-sort-down"></i> Ordenar
                    </button>

                    <button class="btn-control" id="btnExport">
                        <i class="bi bi-download"></i> Export
                    </button>

                    <!-- <a href="<?= BASE_URL ?>/admin/pdf-veterinarias?action=reporteVeterinariasPDF" target="_blank">
                        <button class="btn-control" id="btnGenerarPdf">
                            <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                        </button>
                    </a> -->


                    <button class="btn-agregar" id="btnAgregarNuevo" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="bi bi-plus-lg"></i> Crear Nueva Especialidad
                    </button>

                </div>
            </div>

            <!-- Tabla de Especialidades -->
            <div class="contenedor-tabla">
                <table id="tablaListaEspecialidades" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre Especialidad</th>
                            <th>Nombre Servicio</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($datos)) : ?>
                            <?php foreach ($datos as $especialidad):  ?>
                                <tr class="fila-blanca">

                                    <td><?= htmlspecialchars($especialidad['nombre_esp']) ?></td>
                                    <td><?= htmlspecialchars($especialidad['nombre_ser']) ?></td>
                                    <td><?= htmlspecialchars($especialidad['estado']) ?></td>

                                    <td>
                                        <button class="btn-accion btn-editar" title="Editar" data-id="<?= $especialidad['id_especialidad'] ?>" data-name="<?= htmlspecialchars($especialidad['nombre_esp']) ?>" data-servicio="<?= $especialidad['id_servicio'] ?>" data-bs-toggle="modal" data-bs-target="#modalEditarEspecialidad">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button class="btn-accion btn-eliminar" title="Eliminar">
                                            <a href="<?= BASE_URL ?>/representante/eliminar-especialidad?action=eliminar&id_especialidad=<?= $especialidad['id_especialidad'] ?>&id_veterinaria=<?= $_SESSION['user']['id_veterinaria'] ?>"><i class="bi bi-trash"></i></a>
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

    <!--  Modal regitro especialidad  -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><i class="bi bi-newspaper"></i> Registro de Especialidad</h2>
                </div>
                <form class="registro" action="<?= BASE_URL ?>/representante/guardar-especialidad" method="post">
                    <input type="hidden" name="id_veterinaria" value="<?= $_SESSION['user']['id_veterinaria']; ?>">
                    <div class="modal-body">
                        <div class="form-modal">
                            <div class="form-group-ag">
                                <label class="form-label-ag" for="nombre">
                                    <i class="bi bi-card-heading"></i>
                                    Nombre
                                    <span class="required">*</span>
                                </label>
                                <input class="form-control-ag" type="text" id="nombre" name="nombre" required placeholder="Ej: Cardiología">
                            </div>

                            <div class="form-group-ag">
                                <label class="form-label-ag" for="servicio">
                                    <i class="bi bi-briefcase-fill"></i>
                                    Servicio
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control-ag" id="servicio" name="servicio" required>
                                    <option value="" disabled selected>Seleccione un servicio</option>
                                    <?php if (!empty($listaServicios)) : ?>
                                        <?php foreach ($listaServicios as $servicio):  ?>
                                            <option value="<?= $servicio['id_servicio'] ?>"><?= htmlspecialchars($servicio['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"> <i class="bi bi-floppy"></i> Guardar</button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <!--  Modal Editar especialidad  -->
    <div class="modal fade" id="modalEditarEspecialidad" tabindex="-1" aria-labelledby="modalEditarEspecialidad" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><i class="bi bi-newspaper"></i> Editar de Especialidad</h2>
                </div>
                <form action="<?= BASE_URL ?>/representante/actualizar-especialidad" method="post">
                    <input type="hidden" id="id_especialidad" name="id_especialidad" value="">
                    <input type="hidden" name="accion" value="actualizar">
                    <div class="modal-body">

                        <div class="form-modal">
                            <div class="form-group-ag">
                                <label class="form-label-ag" for="nombre_especialidad"><i class="bi bi-card-heading"></i>
                                    Nombre
                                    <span class="required">*</span>
                                </label>
                                <input class="form-control-ag" type="text" id="nombre_especialidad" name="nombre_especialidad" required placeholder="Ej: Cardiología">

                            </div>


                            <div class="col-md-12">
                                <div class="form-group-ag">
                                    <label class="form-label-ag" for="servicio_especialidad"><i class="bi bi-briefcase-fill"></i>
                                        Servicio
                                        <span class="required">*</span>
                                    </label>
                                    <select class="form-control-ag" id="servicio_especialidad" name="servicio_especialidad" required>
                                        <option value="" disabled selected>Seleccione un servicio</option>


                                        <?php if (!empty($listaServicios)) : ?>
                                            <?php foreach ($listaServicios as $servicio):  ?>
                                                <option value="<?= $servicio['id_servicio'] ?>"><?= htmlspecialchars($servicio['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>

                </form>
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


    <!-- Modo dia  y noche -->
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script> -->

    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/especialidades.js"></script>




</body>

</html>