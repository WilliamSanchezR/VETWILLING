<?php
// Enlazamos la ruta para tomar la session del administrador
require_once BASE_PATH . '/app/helpers/session_representante.php';

require_once BASE_PATH . '/app/controllers/profesionalController.php';
require_once BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';

$id = $_GET['id'];

$datosProfesional = consultarProfesional($id);
$listaEspecialidadesProfesional = listarEspecialidadesPorProfesional($id, $_SESSION['user']['id_veterinaria']);

$agendaProfesional = obtenerDisponibilidadesPorUsuario($id, $_SESSION['user']['id_veterinaria']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda profesional</title>

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

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/agendaProfesional.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/modalStyle.css">

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

        <div class="area-contenido contenedor-agenda-profesional">

            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>Agenda profesional</h3>
            </div>

            <div class="info-profesional">
                <div class="info-header">
                    <div>
                        <h3><?= htmlspecialchars($datosProfesional['nombres']) ?> <?= htmlspecialchars($datosProfesional['apellidos']) ?></h3>
                        <span class="rol"></strong> <?= htmlspecialchars($datosProfesional['nombre']) ?></span>
                    </div>
                </div>

                <div class="info-body">
                    <div class="info-item">
                        <span class="label">Registro Médico</span>
                        <span class="value"><?= htmlspecialchars($datosProfesional['registro_medico']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="label">Especialidades</span>
                        <span class="value">
                            <?php foreach ($listaEspecialidadesProfesional as $index => $especialidad): ?>
                                <?= htmlspecialchars($especialidad['nombre']) ?><?php if ($index !== array_key_last($listaEspecialidadesProfesional)) echo ', '; ?>
                            <?php endforeach; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="container">

                <div class="controles-tabla">
                    <div class="controles-izquierda">
                        <div class="campo-buscar">

                        </div>
                    </div>
                    <div class="controles-derecha">

                        <button class="btn-control" id="btnOrdenar">
                            <i class="bi bi-sort-down"></i> Ordenar
                        </button>


                        <button class="btn-agregar" data-bs-toggle="modal" data-bs-target="#modalAgregarAgenda" id="btnAgregarNuevaAgenda">
                            <i class="bi bi-plus-lg"></i> Agendar Nuevo
                        </button>
                    </div>
                </div>

                <div class="contenedor-tabla">
                    <table id="tablaListaAgenda" class="display tabla-admin" style="width:100%">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Duración (minutos)</th>
                                <th>Especialidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendaProfesional as $disponibilidad): ?>
                                <tr>
                                    <td><?= htmlspecialchars($disponibilidad['dia']) ?></td>
                                    <td><?= htmlspecialchars($disponibilidad['hora_inicio']) ?></td>
                                    <td><?= htmlspecialchars($disponibilidad['hora_fin']) ?></td>
                                    <td><?= htmlspecialchars($disponibilidad['duracion']) ?></td>
                                    <td><?= htmlspecialchars($disponibilidad['especialidad']) ?></td>
                                    <td class="content-action">
                                        <button class="btn-accion btn-editar btn-editar-agenda" data-dispo="<?= htmlspecialchars(json_encode($disponibilidad)) ?>" title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditarAgenda">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn-accion btn-eliminar btn-eliminar-agenda" id="<?= $disponibilidad['id_disponibilidad'] ?>" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>


            <!-- Modal Registro Agenda -->

            <div class="modal fade" id="modalAgregarAgenda" tabindex="-1" aria-labelledby="modalAgregarAgendaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="formAgenda" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($_GET['id']) ?>">
                        <input type="hidden" name="id_veterinaria" value="<?= htmlspecialchars($_SESSION['user']['id_veterinaria']) ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title" id="modalAgregarAgendaLabel"><i class="bi bi-calendar-plus"></i> Agregar disponibilidad de agenda</h2>
                            </div>
                            <div class="modal-body">

                                <div class="form-modal">
                                    <div class="form-group-ag">
                                        <label for="especialidad" class="form-label-ag"><i class="bi bi-card-heading"></i> Especialidad</label>
                                        <select class="form-control-ag" id="especialidad" name="id_especialidad" required>
                                            <option value="" disabled selected>Seleccione una especialidad</option>
                                            <?php foreach ($listaEspecialidadesProfesional as $especialidad): ?>
                                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>"><?= htmlspecialchars($especialidad['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group-ag">
                                        <label for="dia_semana" class="form-label-ag"> <i class="bi bi-calendar3"></i> Día de la semana</label>
                                        <select class="form-control-ag" id="dia_semana" name="dia_semana" required>
                                            <option value="">Seleccione...</option>
                                            <option value="1">Lunes</option>
                                            <option value="2">Martes</option>
                                            <option value="3">Miércoles</option>
                                            <option value="4">Jueves</option>
                                            <option value="5">Viernes</option>
                                            <option value="6">Sábado</option>
                                            <option value="7">Domingo</option>
                                        </select>
                                    </div>


                                    <div class="row form-group-ag">
                                        <div class="col-md-12 form-label-ag bold"><i class="bi bi-clock-history"></i> Primera Jornada</div>
                                        <div class="col-md-6">
                                            <label for="hora_inicio" class="form-label-ag"><i class="bi bi-clock"></i> Hora Inicio</label>
                                            <input type="time" class="form-control-ag" id="hora_inicio" name="hora_inicio">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="hora_fin" class="form-label-ag"><i class="bi bi-clock"></i> Hora Fin</label>
                                            <input type="time" class="form-control-ag" id="hora_fin" name="hora_fin">
                                        </div>
                                    </div>

                                    <div class="row form-group-ag">
                                        <div class="col-md-12 form-label-ag bold"><i class="bi bi-clock-history"></i> Segunda Jornada</div>
                                        <div class="col-md-6">
                                            <label for="hora_inicio_seccond" class="form-label-ag"><i class="bi bi-clock"></i> Hora Inicio</label>
                                            <input type="time" class="form-control-ag" id="hora_inicio_seccond" name="hora_inicio_seccond">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="hora_fin_seccond" class="form-label-ag"><i class="bi bi-clock"></i> Hora Fin</label>
                                            <input type="time" class="form-control-ag" id="hora_fin_seccond" name="hora_fin_seccond">
                                        </div>
                                    </div>

                                    <div class="row form-group-ag">
                                        <div class="mb-3">
                                            <label for="duracion_minutos" class="form-label-ag"><i class="bi bi-stopwatch"></i> Duración por cita (minutos)</label>
                                            <select class="form-control-ag" id="duracion_minutos" name="duracion_minutos" required>
                                                <option value="15">15 minutos</option>
                                                <option value="20">20 minutos</option>
                                                <option value="30">30 minutos</option>
                                                <option value="45">45 minutos</option>
                                                <option value="60">60 minutos</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>


                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                    </form>
                </div>
            </div>


        </div>

        <!-- Modal Editar Agenda -->
        <div class="modal fade" id="modalEditarAgenda" tabindex="-1" aria-labelledby="modalEditarAgendaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formEditAgenda" method="POST" action="<?= BASE_URL ?>/representante/editar-disponibilidad-agenda" enctype="multipart/form-data">
                    <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($_GET['id']) ?>">
                    <input type="hidden" name="id_veterinaria" value="<?= htmlspecialchars($_SESSION['user']['id_veterinaria']) ?>">
                    <input type="hidden" name="id_disponibilidad" id="edit_id_disponibilidad" value="">
                    <input type="hidden" name="action" value="editar">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="modalEditarAgendaLabel"><i class="bi bi-calendar-plus"></i> Editar disponibilidad de agenda</h2>
                        </div>
                        <div class="modal-body">
                            <div class="form-modal">
                                <div class="form-group-ag">
                                     <label for="edit_especialidad" class="form-label-ag"><i class="bi bi-card-heading"></i> Especialidad</label>
                                    <select class="form-control-ag" id="edit_especialidad" name="id_especialidad" required>
                                        <option value="" disabled selected>Seleccione una especialidad</option>
                                        <?php foreach ($listaEspecialidadesProfesional as $especialidad): ?>
                                            <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>"><?= htmlspecialchars($especialidad['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_dia" class="form-label-ag"><i class="bi bi-calendar3"></i> Día de la semana</label>
                                    <select class="form-control-ag" id="edit_dia" name="dia_semana" required>
                                        <option value="">Seleccione...</option>
                                        <option value="1">Lunes</option>
                                        <option value="2">Martes</option>
                                        <option value="3">Miércoles</option>
                                        <option value="4">Jueves</option>
                                        <option value="5">Viernes</option>
                                        <option value="6">Sábado</option>
                                        <option value="7">Domingo</option>
                                    </select>
                                </div>


                                <div class="row">

                                    <div class="col-md-6">
                                        <label for="edit_hora_inicio" class="form-label-ag"><i class="bi bi-clock"></i> Hora Inicio</label>
                                        <input type="time" class="form-control-ag" id="edit_hora_inicio" name="hora_inicio" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="edit_hora_fin" class="form-label-ag"><i class="bi bi-clock"></i> Hora Fin</label>
                                        <input type="time" class="form-control-ag" id="edit_hora_fin" name="hora_fin" required>
                                    </div>
                                </div>


                                <div class="mb-3">
                                    <label for="edit_duracion" class="form-label-ag"><i class="bi bi-clock"></i> Duración por cita (minutos)</label>
                                    <select class="form-control-ag" id="edit_duracion" name="duracion_minutos" required>
                                        <option value="15">15 minutos</option>
                                        <option value="20">20 minutos</option>
                                        <option value="30">30 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="60">60 minutos</option>
                                    </select>
                                </div>


                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <!-- 5. Tu script de tabla AL FINAL -->
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/agendaProfesional.js"></script>

</body>

</html>