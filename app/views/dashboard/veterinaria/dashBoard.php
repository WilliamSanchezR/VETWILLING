<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/models/Veterinario.php';

$idUsuario = $_SESSION['user']['id_usuario'] ?? null;
$statsVeterinario = new Veterinario();
$fechaHoy = date('Y-m-d');

// Obtener citas de hoy
$citasHoy = $idUsuario ? $statsVeterinario->obtenerCitasHoyPorVeterinario($idUsuario, $fechaHoy) : [];
$totalCitas = count($citasHoy);
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

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/master-styles.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/sidebar.css"> -->

    <style>
        .carrusel-citas {
            background: var(--bs-body-bg);
            border-radius: 10px;
            padding: 16px;
            border: 1px solid var(--color-verde);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 18px;
        }

        .carrusel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-verde);
        }

        .carrusel-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--color-primario);
            margin: 0;
        }

        .carrusel-header .badge {
            font-size: 0.82rem;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--color-primario) !important;
        }

        .cita-card {
            background: linear-gradient(135deg, var(--color-primario) 0%, #0b7a28 100%);
            border-radius: 12px;
            padding: 12px;
            color: white;
            min-height: 200px;
            max-width: 330px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        #carruselCitas {
            max-width: 1080px;
            margin: 0 auto;
        }

        #carruselCitas .carousel-inner {
            overflow: visible;
        }

        #carruselCitas .carousel-item {
            transition: transform 0.8s cubic-bezier(0.22, 0.61, 0.36, 1), opacity 0.8s ease;
        }

        #carruselCitas .carousel-item.active .cita-card {
            animation: citaCardIn 0.65s ease both;
        }

        #carruselCitas .carousel-item.active .col-lg-4:nth-child(2) .cita-card {
            animation-delay: .08s;
        }

        #carruselCitas .carousel-item.active .col-lg-4:nth-child(3) .cita-card {
            animation-delay: .16s;
        }

        @keyframes citaCardIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .cita-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .cita-hora {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 10px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .cita-estado {
            background: rgba(255, 255, 255, 0.25);
            padding: 5px 9px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .paciente-info {
            margin-bottom: 10px;
        }

        .paciente-nombre {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .paciente-detalle {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
            font-size: 0.83rem;
            opacity: 0.95;
        }

        .paciente-detalle i {
            font-size: 0.95rem;
        }

        .propietario-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .propietario-titulo {
            font-size: 0.72rem;
            opacity: 0.85;
            margin-bottom: 4px;
            font-weight: 500;
            letter-spacing: .5px;
        }

        .propietario-nombre {
            font-size: 0.92rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .propietario-contacto {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .carousel-control-prev,
        .carousel-control-next {
            display: none;
            width: 36px;
            height: 36px;
            background: rgba(75, 85, 99, 0.8);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev {
            left: 6px;
        }

        .carousel-control-next {
            right: 6px;
        }

        .carousel-indicators {
            margin-bottom: -20px;
        }

        .carousel-indicators button {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: #72c56a;
        }

        .carousel-indicators .active {
            background-color: var(--color-primario);
        }

        .sin-citas {
            text-align: center;
            padding: 30px 16px;
            color: #6c757d;
        }

        .sin-citas i {
            font-size: 2.6rem;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .sin-citas h4 {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 1.05rem;
        }

        .sin-citas p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
    </style>
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

            <!-- TABLA DE PACIENTES -->
            <div class="contenedor-tabla-pacientes">
                <table class="tabla-pacientes">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input">
                            </th>
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
                        <!-- Paciente 1 -->
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="info-paciente">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=luna" alt="Luna"
                                        class="avatar-tabla-paciente">
                                    <div>
                                        <div class="nombre-paciente">Luna</div>
                                        <small class="text-muted">ID: #001234</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-especie badge-perro"><i class="bi bi-circle-fill"></i>
                                    Perro</span></td>
                            <td>Golden Retriever</td>
                            <td>3 años</td>
                            <td>María González</td>
                            <td>15/10/2024</td>
                            <td><span class="badge-estado badge-activo">Activo</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <button class="boton-accion-tabla" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Historia clínica">
                                        <i class="bi bi-file-medical"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Paciente 2 -->
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="info-paciente">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=max" alt="Max"
                                        class="avatar-tabla-paciente">
                                    <div>
                                        <div class="nombre-paciente">Max</div>
                                        <small class="text-muted">ID: #001235</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-especie badge-perro"><i class="bi bi-circle-fill"></i>
                                    Perro</span></td>
                            <td>Labrador</td>
                            <td>5 años</td>
                            <td>Carlos Pérez</td>
                            <td>20/10/2024</td>
                            <td><span class="badge-estado badge-tratamiento">En Tratamiento</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <button class="boton-accion-tabla" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Historia clínica">
                                        <i class="bi bi-file-medical"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Paciente 3 -->
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="info-paciente">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=miau" alt="Miau"
                                        class="avatar-tabla-paciente">
                                    <div>
                                        <div class="nombre-paciente">Miau</div>
                                        <small class="text-muted">ID: #001236</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-especie badge-gato"><i class="bi bi-circle-fill"></i> Gato</span>
                            </td>
                            <td>Persa</td>
                            <td>2 años</td>
                            <td>Ana Martínez</td>
                            <td>22/10/2024</td>
                            <td><span class="badge-estado badge-activo">Activo</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <button class="boton-accion-tabla" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Historia clínica">
                                        <i class="bi bi-file-medical"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Paciente 4 -->
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="info-paciente">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=rocky" alt="Rocky"
                                        class="avatar-tabla-paciente">
                                    <div>
                                        <div class="nombre-paciente">Rocky</div>
                                        <small class="text-muted">ID: #001237</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-especie badge-perro"><i class="bi bi-circle-fill"></i>
                                    Perro</span></td>
                            <td>Bulldog</td>
                            <td>4 años</td>
                            <td>Luis Rodríguez</td>
                            <td>18/10/2024</td>
                            <td><span class="badge-estado badge-alta">Alta Médica</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <button class="boton-accion-tabla" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Historia clínica">
                                        <i class="bi bi-file-medical"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Paciente 5 -->
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>
                                <div class="info-paciente">
                                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=coco" alt="Coco"
                                        class="avatar-tabla-paciente">
                                    <div>
                                        <div class="nombre-paciente">Coco</div>
                                        <small class="text-muted">ID: #001238</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-especie badge-ave"><i class="bi bi-circle-fill"></i> Ave</span>
                            </td>
                            <td>Loro</td>
                            <td>6 años</td>
                            <td>Pedro Sánchez</td>
                            <td>25/10/2024</td>
                            <td><span class="badge-estado badge-activo">Activo</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <button class="boton-accion-tabla" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="boton-accion-tabla" title="Historia clínica">
                                        <i class="bi bi-file-medical"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="paginacion-contenedor">
                <div class="info-paginacion">
                    Mostrando <strong>1-5</strong> de <strong>248</strong> pacientes
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">...</a></li>
                        <li class="page-item"><a class="page-link" href="#">50</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>

    </div>
    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/master-handler.js"></script>

</body>

</html>