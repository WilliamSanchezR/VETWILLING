<?php
// Enlazamos la ruta para tomar la session del veterinario
require_once BASE_PATH . "/app/helpers/session_veterinario.php";

require_once BASE_PATH . "/app/controllers/disponibilidadUsuarioController.php";

// Obtener el ID del veterinario autenticado
$id = $_SESSION["user"]["id_usuario"];
$id_veterinaria = $_SESSION["user"]["id_veterinaria"] ?? null;

// Obtener especialidades del veterinario
$disponibilidadModel = new DisponibilidadUsuario();
if (empty($id_veterinaria)) {
    $id_veterinaria = $disponibilidadModel->obtenerVeterinariaPorUsuario($id);
}

$listaEspecialidades = [];
if (!empty($id_veterinaria)) {
    $listaEspecialidades = $disponibilidadModel->obtenerEspecialidadesUsuario(
        $id,
        $id_veterinaria,
    );
}

// Obtener disponibilidades del veterinario
$agendaVeterinario = !empty($id_veterinaria)
    ? obtenerDisponibilidadesPorUsuario($id, $id_veterinaria)
    : [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Agenda - Veterinario</title>

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/agendaProfesional.css">

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/modalStyle.css">

</head>

<body class="agenda-veterinario">

    <!-- BARRA LATERAL IZQUIERDA -->
    <?php include_once __DIR__ . "/../../layouts/sidebar_veterinario.php"; ?>


    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php include_once __DIR__ .
            "/../../layouts/panel_superior_veterinario.php"; ?>

        <!-- ÁREA DE CONTENIDO -->

        <div class="area-contenido contenedor-agenda-profesional">

            <div class="info-profesional">
                <div class="info-header">
                    <div>
                        <h3><i class="bi bi-person-check"></i> Tu Agenda Profesional</h3>
                        <span class="rol">Veterinario</span>
                    </div>
                </div>

                <div class="info-body">
                    <div class="info-item">
                        <span class="label">Especialidades Registradas</span>
                        <span class="value">
                            <?php if (!empty($listaEspecialidades)): ?>
                                <?php foreach ($listaEspecialidades as $index => $especialidad): ?>
                                    <?= htmlspecialchars($especialidad['nombre']) ?><?php if ($index !== array_key_last($listaEspecialidades)) echo ', '; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                No tienes especialidades registradas
                            <?php endif; ?>
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
                        <button class="btn-agregar" data-bs-toggle="modal" data-bs-target="#modalAgregarAgenda" id="btnAgregarNuevaAgenda">
                            <i class="bi bi-plus-lg"></i> Agregar Horario
                        </button>
                    </div>
                </div>

                <div class="contenedor-tabla">
                    <table id="tablaListaAgenda" class="display tabla-admin agenda-table-full">
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
                            <?php foreach (
                                $agendaVeterinario
                                as $disponibilidad
                            ): ?>
                                <tr>
                                    <td><?= htmlspecialchars(
                                        $disponibilidad["dia"],
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                        $disponibilidad["hora_inicio"],
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                        $disponibilidad["hora_fin"],
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                        $disponibilidad["duracion"],
                                    ) ?></td>
                                    <td><?= htmlspecialchars(
                                        $disponibilidad["especialidad"],
                                    ) ?></td>
                                    <td class="content-action">
                                        <button class="btn-accion btn-editar btn-editar-agenda" data-dispo="<?= htmlspecialchars(
                                            json_encode($disponibilidad),
                                        ) ?>" title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditarAgenda">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn-accion btn-eliminar btn-eliminar-agenda" id="<?= $disponibilidad[
                                            "id_disponibilidad"
                                        ] ?>" title="Eliminar">
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
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="modalAgregarAgendaLabel">
                                <i class="bi bi-calendar-plus"></i> Agregar Disponibilidad
                            </h2>
                        </div>
                        <form id="formAgenda" method="POST" action="<?= BASE_URL ?>/veterinario/agregar-disponibilidad-agenda">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id) ?>">
                            <input type="hidden" name="id_veterinaria" value="<?= htmlspecialchars($id_veterinaria) ?>">
                            <div class="modal-body">
                                <div class="form-modal">

                                    <div class="form-group-ag">
                                        <label for="especialidad" class="form-label-ag">
                                            <i class="bi bi-patch-plus"></i> Especialidad
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-control-ag" id="especialidad" name="id_especialidad" required>
                                            <option value="" disabled selected>Seleccione una especialidad...</option>
                                            <?php foreach ($listaEspecialidades as $especialidad): ?>
                                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>">
                                                    <?= htmlspecialchars($especialidad['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group-ag">
                                        <label for="dia_semana" class="form-label-ag">
                                            <i class="bi bi-calendar-week"></i> Día de la semana
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-control-ag" id="dia_semana" name="dia_semana" required>
                                            <option value="" disabled selected>Seleccione un día...</option>
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
                                        <div class="col-md-12 form-label-ag" style="font-weight:600;">
                                            <i class="bi bi-clock-history"></i> Primera Jornada
                                            <span class="required">*</span>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hora_inicio" class="form-label-ag">
                                                <i class="bi bi-clock"></i> Hora Inicio
                                            </label>
                                            <input type="time" class="form-control-ag" id="hora_inicio" name="hora_inicio">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hora_fin" class="form-label-ag">
                                                <i class="bi bi-clock"></i> Hora Fin
                                            </label>
                                            <input type="time" class="form-control-ag" id="hora_fin" name="hora_fin">
                                        </div>
                                    </div>

                                    <div class="row form-group-ag">
                                        <div class="col-md-12 form-label-ag" style="font-weight:600;">
                                            <i class="bi bi-clock-history"></i> Segunda Jornada
                                            <small style="font-weight:400;color:#888;">(opcional)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hora_inicio_seccond" class="form-label-ag">
                                                <i class="bi bi-clock"></i> Hora Inicio
                                            </label>
                                            <input type="time" class="form-control-ag" id="hora_inicio_seccond" name="hora_inicio_seccond">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hora_fin_seccond" class="form-label-ag">
                                                <i class="bi bi-clock"></i> Hora Fin
                                            </label>
                                            <input type="time" class="form-control-ag" id="hora_fin_seccond" name="hora_fin_seccond">
                                        </div>
                                    </div>

                                    <div class="form-group-ag">
                                        <label for="duracion_minutos" class="form-label-ag">
                                            <i class="bi bi-hourglass-split"></i> Duración por cita
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-control-ag" id="duracion_minutos" name="duracion_minutos" required>
                                            <option value="15">15 minutos</option>
                                            <option value="20">20 minutos</option>
                                            <option value="30" selected>30 minutos</option>
                                            <option value="45">45 minutos</option>
                                            <option value="60">60 minutos</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Agregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>

        <!-- Modal Editar Agenda -->
        <div class="modal fade" id="modalEditarAgenda" tabindex="-1" aria-labelledby="modalEditarAgendaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="modalEditarAgendaLabel">
                            <i class="bi bi-calendar-check"></i> Editar Disponibilidad
                        </h2>
                    </div>
                    <form id="formEditAgenda" method="POST" action="<?= BASE_URL ?>/veterinario/editar-disponibilidad-agenda">
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="id_veterinaria" value="<?= htmlspecialchars($id_veterinaria) ?>">
                        <input type="hidden" name="id_disponibilidad" id="edit_id_disponibilidad" value="">
                        <input type="hidden" name="action" value="editar">
                        <div class="modal-body">
                            <div class="form-modal">

                                <div class="form-group-ag">
                                    <label for="edit_especialidad" class="form-label-ag">
                                        <i class="bi bi-patch-plus"></i> Especialidad
                                        <span class="required">*</span>
                                    </label>
                                    <select class="form-control-ag" id="edit_especialidad" name="id_especialidad" required>
                                        <option value="" disabled selected>Seleccione una especialidad...</option>
                                        <?php foreach ($listaEspecialidades as $especialidad): ?>
                                            <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>">
                                                <?= htmlspecialchars($especialidad['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group-ag">
                                    <label for="edit_dia" class="form-label-ag">
                                        <i class="bi bi-calendar-week"></i> Día de la semana
                                        <span class="required">*</span>
                                    </label>
                                    <select class="form-control-ag" id="edit_dia" name="dia_semana" required>
                                        <option value="" disabled selected>Seleccione un día...</option>
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
                                    <div class="col-md-12 form-label-ag" style="font-weight:600;">
                                        <i class="bi bi-clock-history"></i> Horario
                                        <span class="required">*</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_hora_inicio" class="form-label-ag">
                                            <i class="bi bi-clock"></i> Hora Inicio
                                        </label>
                                        <input type="time" class="form-control-ag" id="edit_hora_inicio" name="hora_inicio" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit_hora_fin" class="form-label-ag">
                                            <i class="bi bi-clock"></i> Hora Fin
                                        </label>
                                        <input type="time" class="form-control-ag" id="edit_hora_fin" name="hora_fin" required>
                                    </div>
                                </div>

                                <div class="form-group-ag">
                                    <label for="edit_duracion" class="form-label-ag">
                                        <i class="bi bi-hourglass-split"></i> Duración por cita
                                        <span class="required">*</span>
                                    </label>
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
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
