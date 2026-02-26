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
                                <tr data-id-paciente="<?= (int)($paciente['id_paciente'] ?? 0) ?>">
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
                                        <button class="btn-accion btn-editar" title="Editar" data-id-paciente="<?= (int)($paciente['id_paciente'] ?? 0) ?>"><i class="bi bi-pencil"></i></button>
                                        <button class="btn-accion btn-ver-detalle" title="Ver información" data-id-paciente="<?= (int)($paciente['id_paciente'] ?? 0) ?>"><i class="bi bi-eye"></i></button>
                                        <button class="btn-accion btn-eliminar" title="Eliminar" data-id-paciente="<?= (int)($paciente['id_paciente'] ?? 0) ?>"><i class="bi bi-trash"></i></button>
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

            const pacientesEndpoint = '<?= BASE_URL ?>/veterinaria/pacientes/acciones';

            const asegurarEstilosModalPaciente = () => {
                const styleId = 'swal-paciente-styles';
                if (document.getElementById(styleId)) return;

                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = `
                    .swal2-popup .form-paciente {
                        text-align: left;
                    }

                    .swal2-popup .form-grid-2 {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 12px;
                    }

                    .swal2-popup .form-group-paciente {
                        margin-bottom: 12px;
                    }

                    .swal2-popup .form-label-paciente {
                        font-weight: 600;
                        font-size: 13px;
                        color: #00304d;
                        margin-bottom: 6px;
                        display: block;
                    }

                    .swal2-popup .form-control-paciente {
                        width: 100%;
                        padding: 10px 12px;
                        border-radius: 8px;
                        border: 1px solid #e0e0e0;
                        font-size: 14px;
                        outline: none;
                        background: #ffffff;
                    }

                    .swal2-popup .form-control-paciente:focus {
                        border-color: #0a932c;
                        box-shadow: 0 0 0 3px rgba(10, 147, 44, 0.12);
                    }

                    .swal2-popup .form-divider-paciente {
                        height: 1px;
                        background: linear-gradient(to right, transparent, #e0e0e0, transparent);
                        margin: 16px 0;
                    }

                    .popup-paciente {
                        border-radius: 16px !important;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
                    }

                    .title-paciente {
                        font-size: 24px !important;
                        font-weight: 700 !important;
                        color: #00304d !important;
                        padding: 20px 20px 10px 20px !important;
                    }

                    .swal2-actions {
                        gap: 12px !important;
                        margin-top: 20px !important;
                    }

                    .btn-confirmar-paciente,
                    .btn-cancelar-paciente {
                        padding: 12px 30px !important;
                        border-radius: 10px !important;
                        font-weight: 600 !important;
                        font-size: 15px !important;
                        transition: all 0.3s ease !important;
                        border: none !important;
                        cursor: pointer !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 8px !important;
                    }

                    .btn-confirmar-paciente {
                        background: linear-gradient(135deg, #0a932c 0%, #0a932c 100%) !important;
                        color: #ffffff !important;
                    }

                    .btn-confirmar-paciente:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: 0 8px 20px rgba(10, 147, 44, 0.35) !important;
                    }

                    .btn-cancelar-paciente {
                        background: #f0f0f0 !important;
                        color: #666666 !important;
                    }

                    .btn-cancelar-paciente:hover {
                        background: #e0e0e0 !important;
                    }
                `;
                document.head.appendChild(style);
            };

            const construirModalPaciente = (opciones = {}) => ({
                width: '700px',
                padding: '24px',
                background: '#ffffff',
                customClass: {
                    popup: 'popup-paciente',
                    title: 'title-paciente',
                    confirmButton: 'btn-confirmar-paciente',
                    cancelButton: 'btn-cancelar-paciente'
                },
                buttonsStyling: false,
                didOpen: asegurarEstilosModalPaciente,
                ...opciones
            });

            const escapeHtml = (value) => {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            const obtenerIdPaciente = (boton) => {
                const idBoton = Number(boton.dataset.idPaciente || 0);
                if (idBoton > 0) return idBoton;
                const fila = boton.closest('tr');
                return Number(fila?.dataset.idPaciente || 0);
            };

            const consultarPaciente = async (idPaciente) => {
                const response = await fetch(pacientesEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: 'consultar',
                        id_paciente: idPaciente
                    })
                });

                const payload = await response.json();
                if (!response.ok || payload.status !== 'success') {
                    throw new Error(payload.message || 'No se pudo consultar el paciente.');
                }

                return payload.data;
            };

            $('#tablaPacientesDashboard tbody').on('click', '.btn-ver-detalle', async function() {
                const idPaciente = obtenerIdPaciente(this);
                if (!idPaciente) return;

                try {
                    const data = await consultarPaciente(idPaciente);
                    const paciente = data.paciente || {};
                    const historial = Array.isArray(data.historial) ? data.historial : [];

                    const historialHtml = historial.length > 0
                        ? historial.map((item) => {
                            const fecha = item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-CO') : 'Sin fecha';
                            const tipo = escapeHtml(item.tipo || 'Consulta');
                            const estado = escapeHtml(item.estado || 'Sin estado');
                            return `<li><strong>${tipo}</strong> · ${fecha} · ${estado}</li>`;
                        }).join('')
                        : '<li>Sin registros clínicos recientes</li>';

                    Swal.fire(construirModalPaciente({
                        title: `Paciente: ${escapeHtml(paciente.nombre || 'Sin nombre')}`,
                        html: `
                            <div class="form-paciente">
                                <div class="form-group-paciente">
                                    <label class="form-label-paciente">Especie</label>
                                    <div class="form-control-paciente">${escapeHtml(paciente.especie || 'N/A')}</div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Raza</label>
                                        <div class="form-control-paciente">${escapeHtml(paciente.raza || 'N/A')}</div>
                                    </div>
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Edad</label>
                                        <div class="form-control-paciente">${escapeHtml(paciente.edad_numero || '0')} ${escapeHtml(paciente.edad_unidad || '')}</div>
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Sexo</label>
                                        <div class="form-control-paciente">${escapeHtml(paciente.sexo || 'N/A')}</div>
                                    </div>
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Propietario</label>
                                        <div class="form-control-paciente">${escapeHtml(paciente.propietario_nombre || 'N/A')}</div>
                                    </div>
                                </div>
                                <div class="form-divider-paciente"></div>
                                <div class="form-group-paciente">
                                    <label class="form-label-paciente">Resumen clínico reciente</label>
                                    <ul style="padding-left:18px; margin:0; font-size:14px;">${historialHtml}</ul>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Cerrar'
                    }));
                } catch (error) {
                    Swal.fire('Error', error.message || 'No se pudo cargar la información.', 'error');
                }
            });

            $('#tablaPacientesDashboard tbody').on('click', '.btn-editar', async function() {
                const idPaciente = obtenerIdPaciente(this);
                if (!idPaciente) return;

                try {
                    const data = await consultarPaciente(idPaciente);
                    const paciente = data.paciente || {};

                    const result = await Swal.fire(construirModalPaciente({
                        title: 'Editar paciente',
                        html: `
                            <div class="form-paciente">
                                <div class="form-group-paciente">
                                    <label class="form-label-paciente">Nombre</label>
                                    <input id="swal-nombre" class="form-control-paciente" value="${escapeHtml(paciente.nombre || '')}">
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Especie</label>
                                        <select id="swal-especie" class="form-control-paciente">
                                        <option value="Perro" ${(paciente.especie === 'Perro') ? 'selected' : ''}>Perro</option>
                                        <option value="Gato" ${(paciente.especie === 'Gato') ? 'selected' : ''}>Gato</option>
                                        <option value="Ave" ${(paciente.especie === 'Ave') ? 'selected' : ''}>Ave</option>
                                        <option value="Conejo" ${(paciente.especie === 'Conejo') ? 'selected' : ''}>Conejo</option>
                                        <option value="Hamster" ${(paciente.especie === 'Hamster') ? 'selected' : ''}>Hamster</option>
                                        <option value="Otro" ${(paciente.especie === 'Otro') ? 'selected' : ''}>Otro</option>
                                        </select>
                                    </div>
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Raza</label>
                                        <input id="swal-raza" class="form-control-paciente" value="${escapeHtml(paciente.raza || '')}">
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Edad número</label>
                                        <input id="swal-edad-numero" type="number" min="1" class="form-control-paciente" value="${escapeHtml(paciente.edad_numero || '')}">
                                    </div>
                                    <div class="form-group-paciente">
                                        <label class="form-label-paciente">Edad unidad</label>
                                        <select id="swal-edad-unidad" class="form-control-paciente">
                                        <option value="meses" ${(paciente.edad_unidad === 'meses') ? 'selected' : ''}>meses</option>
                                        <option value="años" ${(paciente.edad_unidad === 'años') ? 'selected' : ''}>años</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-paciente">
                                    <label class="form-label-paciente">Sexo</label>
                                    <select id="swal-sexo" class="form-control-paciente">
                                        <option value="Macho" ${(paciente.sexo === 'Macho') ? 'selected' : ''}>Macho</option>
                                        <option value="Hembra" ${(paciente.sexo === 'Hembra') ? 'selected' : ''}>Hembra</option>
                                    </select>
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Guardar cambios',
                        cancelButtonText: 'Cancelar',
                        preConfirm: async () => {
                            const payload = {
                                accion: 'actualizar',
                                id_paciente: idPaciente,
                                nombre: document.getElementById('swal-nombre').value.trim(),
                                especie: document.getElementById('swal-especie').value,
                                raza: document.getElementById('swal-raza').value.trim(),
                                edad_numero: Number(document.getElementById('swal-edad-numero').value || 0),
                                edad_unidad: document.getElementById('swal-edad-unidad').value,
                                sexo: document.getElementById('swal-sexo').value
                            };

                            if (!payload.nombre || !payload.especie || !payload.raza || payload.edad_numero <= 0 || !payload.edad_unidad || !payload.sexo) {
                                Swal.showValidationMessage('Completa todos los campos obligatorios.');
                                return false;
                            }

                            try {
                                const response = await fetch(pacientesEndpoint, {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(payload)
                                });

                                const result = await response.json();
                                if (!response.ok || result.status !== 'success') {
                                    throw new Error(result.message || 'No se pudo actualizar.');
                                }

                                return payload;
                            } catch (error) {
                                Swal.showValidationMessage(error.message || 'Error al actualizar.');
                                return false;
                            }
                        }
                    }));

                    if (result.isConfirmed && result.value) {
                        const fila = $(this).closest('tr');
                        tablaPacientesDashboard.cell(fila, 0).data(escapeHtml(result.value.nombre));
                        tablaPacientesDashboard.cell(fila, 1).data(escapeHtml(result.value.especie));
                        tablaPacientesDashboard.cell(fila, 2).data(escapeHtml(result.value.raza));
                        tablaPacientesDashboard.cell(fila, 3).data(`${result.value.edad_numero} ${escapeHtml(result.value.edad_unidad)}`);
                        tablaPacientesDashboard.draw(false);

                        Swal.fire('Actualizado', 'Los datos de la mascota fueron actualizados.', 'success');
                    }
                } catch (error) {
                    Swal.fire('Error', error.message || 'No se pudo cargar el formulario de edición.', 'error');
                }
            });

            $('#tablaPacientesDashboard tbody').on('click', '.btn-eliminar', async function() {
                const idPaciente = obtenerIdPaciente(this);
                if (!idPaciente) return;

                const confirmacion = await Swal.fire(construirModalPaciente({
                    icon: 'warning',
                    title: 'Desactivar paciente',
                    text: 'Esta acción no elimina la mascota. Solo la dejará inactiva para este profesional.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }));

                if (!confirmacion.isConfirmed) return;

                try {
                    const response = await fetch(pacientesEndpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'desactivar',
                            id_paciente: idPaciente
                        })
                    });

                    const result = await response.json();
                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'No se pudo desactivar el paciente.');
                    }

                    const fila = $(this).closest('tr');
                    tablaPacientesDashboard.row(fila).remove().draw(false);

                    Swal.fire('Desactivado', 'La mascota quedó inactiva para este profesional.', 'success');
                } catch (error) {
                    Swal.fire('Error', error.message || 'No se pudo completar la acción.', 'error');
                }
            });
        });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/master-handler.js"></script>

</body>

</html>