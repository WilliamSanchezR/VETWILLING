<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/models/Veterinario.php';

$idUsuario = $_SESSION['user']['id_usuario'] ?? null;
$statsVeterinario = new Veterinario();
$fechaHoy = date('Y-m-d');

// Obtener citas de hoy
$citasHoy = $idUsuario ? $statsVeterinario->obtenerCitasHoyPorVeterinario($idUsuario, $fechaHoy) : [];
$totalCitas = count($citasHoy);

// Obtener pacientes asociados al profesional en sesión
$pacientesVeterinario = $idUsuario ? $statsVeterinario->obtenerPacientesPorVeterinario($idUsuario) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/master-styles.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/sidebar.css"> -->

</head>

<body>

    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';

    // <!-- PANEL DERECHO -->
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        ?>
        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- CARRUSEL DE CITAS DEL DÍA -->
            <div class="carrusel-citas">
                <div class="carrusel-header">
                    <h3><i class="bi bi-calendar-heart me-2"></i>Mis Citas de Hoy</h3>
                    <span class="badge bg-primary"><?= $totalCitas ?> <?= $totalCitas === 1 ? 'cita' : 'citas' ?></span>
                </div>

                <?php if (empty($citasHoy)): ?>
                    <div class="sin-citas">
                        <i class="bi bi-calendar-x"></i>
                        <h4>No tienes citas programadas para hoy</h4>
                        <p>Disfruta tu día libre o revisa el calendario para próximas citas</p>
                    </div>
                <?php else: ?>
                    <?php $citasPorSlide = array_chunk($citasHoy, 3); ?>
                    <div id="carruselCitas" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4200" data-bs-pause="false" data-bs-wrap="true" data-bs-touch="true">
                        <div class="carousel-indicators">
                            <?php foreach ($citasPorSlide as $index => $grupoCitas): ?>
                                <button type="button" data-bs-target="#carruselCitas" data-bs-slide-to="<?= $index ?>"
                                    <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>
                                    aria-label="Grupo de citas <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($citasPorSlide as $index => $grupoCitas): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <div class="row g-3 justify-content-center">
                                        <?php foreach ($grupoCitas as $cita):
                                            $horaInicio = date('h:i A', strtotime($cita['fecha_hora']));
                                            $horaFin = !empty($cita['fecha_hora_fin']) ? date('h:i A', strtotime($cita['fecha_hora_fin'])) : '';
                                            $nombreCompleto = trim(($cita['propietario_nombres'] ?? '') . ' ' . ($cita['propietario_apellidos'] ?? ''));
                                        ?>
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="cita-card">
                                                    <div class="cita-header">
                                                        <div class="cita-hora">
                                                            <i class="bi bi-clock me-2"></i><?= $horaInicio ?><?= $horaFin ? ' - ' . $horaFin : '' ?>
                                                        </div>
                                                        <div class="cita-estado">
                                                            <?= htmlspecialchars($cita['estado'] ?? 'Pendiente') ?>
                                                        </div>
                                                    </div>

                                                    <div class="paciente-info">
                                                        <div class="paciente-nombre">
                                                            <i class="bi bi-heart-pulse me-2"></i>
                                                            <?= htmlspecialchars($cita['paciente_nombre'] ?? 'Paciente sin nombre') ?>
                                                        </div>
                                                        <div class="paciente-detalle">
                                                            <i class="bi bi-tag"></i>
                                                            <span><?= htmlspecialchars($cita['especie'] ?? 'N/A') ?> - <?= htmlspecialchars($cita['raza'] ?? 'N/A') ?></span>
                                                        </div>
                                                        <div class="paciente-detalle">
                                                            <i class="bi bi-cake"></i>
                                                            <span>
                                                                <?php
                                                                if (!empty($cita['edad_numero']) && !empty($cita['edad_unidad'])) {
                                                                    echo htmlspecialchars($cita['edad_numero'] . ' ' . $cita['edad_unidad']);
                                                                } else {
                                                                    echo 'Edad no especificada';
                                                                }
                                                                ?>
                                                            </span>
                                                        </div>
                                                        <div class="paciente-detalle">
                                                            <i class="bi bi-gender-ambiguous"></i>
                                                            <span><?= htmlspecialchars($cita['sexo'] ?? 'N/A') ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="propietario-info">
                                                        <div class="propietario-titulo">PROPIETARIO</div>
                                                        <div class="propietario-nombre">
                                                            <i class="bi bi-person me-2"></i>
                                                            <?= htmlspecialchars($nombreCompleto ?: 'Propietario no registrado') ?>
                                                        </div>
                                                        <?php if (!empty($cita['propietario_telefono'])): ?>
                                                            <div class="propietario-contacto">
                                                                <i class="bi bi-telephone me-2"></i>
                                                                <?= htmlspecialchars($cita['propietario_telefono']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>


                    </div>
                <?php endif; ?>
            </div>

            <!-- BARRA DE ACCIONES -->
            <div class="barra-acciones-pacientes">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2">
                        <button class="boton-accion-primario" id="btnNuevoPaciente">
                            <i class="bi bi-plus-circle"></i> Nuevo Paciente
                        </button>
                        <button class="boton-accion-secundario">
                            <i class="bi bi-file-earmark-arrow-down"></i> Exportar
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="filtro-select" id="filtroEspecie">
                            <option value="">Todas las especies</option>
                            <option value="perro">Perros</option>
                            <option value="gato">Gatos</option>
                            <option value="ave">Aves</option>
                            <option value="otro">Otros</option>
                        </select>
                        <select class="filtro-select" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="tratamiento">En Tratamiento</option>
                            <option value="alta">Alta Médica</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TABLA DE PACIENTES (FORMATO CONTENEDOR-TABLA) -->
            <div class="contenedor-tabla">
                <table id="tablaPacientesDashboard" class="display tabla-admin">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Edad</th>
                            <th>Dueño</th>
                            <th>Última Visita</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPacientesBody">
                        <?php if (!empty($pacientesVeterinario)): ?>
                            <?php foreach ($pacientesVeterinario as $paciente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($paciente['paciente_nombre'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($paciente['especie'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($paciente['raza'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        if (!empty($paciente['edad_numero']) && !empty($paciente['edad_unidad'])) {
                                            echo htmlspecialchars($paciente['edad_numero'] . ' ' . $paciente['edad_unidad']);
                                        } else {
                                            echo 'No definida';
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($paciente['propietario_nombre'] ?? 'N/A') ?></td>
                                    <td>
                                        <?= !empty($paciente['ultima_visita'])
                                            ? htmlspecialchars(date('d/m/Y', strtotime($paciente['ultima_visita'])))
                                            : 'Sin visitas' ?>
                                    </td>
                                    <td><?= htmlspecialchars($paciente['estado_ultima_cita'] ?? 'Sin estado') ?></td>
                                    <td class="content-action">
                                        <button class="btn-accion btn-editar" title="Editar"><i class="bi bi-pencil"></i></button>
                                        <button class="btn-accion btn-eliminar" title="Historia clínica"><i class="bi bi-file-medical"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
    <!-- Bootstrap -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            const tablaPacientesDashboard = $('#tablaPacientesDashboard').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay pacientes registrados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ pacientes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 pacientes",
                    "infoFiltered": "(filtrado de _MAX_ pacientes totales)",
                    "lengthMenu": "Mostrar _MENU_ pacientes",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron pacientes",
                    "paginate": {
                        "first": "Primera",
                        "last": "Última",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                pageLength: 9,
                lengthMenu: [
                    [9, 15, 25, 50, -1],
                    [9, 15, 25, 50, "Todas"]
                ],
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }],
                dom: '<"row"<"col-sm-12"tr>>' + '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            $('#filtroEspecie').on('change', function() {
                tablaPacientesDashboard.column(1).search(this.value).draw();
            });

            $('#filtroEstado').on('change', function() {
                tablaPacientesDashboard.column(6).search(this.value).draw();
            });
        });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/master-handler.js"></script>

</body>

</html>