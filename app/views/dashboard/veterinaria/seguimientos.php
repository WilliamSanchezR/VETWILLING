<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
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

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css"> -->

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
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- FILTROS Y ACCIONES -->
            <div class="barra-filtros-seguimiento">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="boton-filtro-seg active" data-filtro="todos">
                            <i class="bi bi-grid-3x3-gap"></i> Todos
                        </button>
                        <button class="boton-filtro-seg" data-filtro="activos">
                            <i class="bi bi-activity"></i> Activos
                        </button>
                        <button class="boton-filtro-seg" data-filtro="criticos">
                            <i class="bi bi-exclamation-triangle"></i> Críticos
                        </button>
                        <button class="boton-filtro-seg" data-filtro="completados">
                            <i class="bi bi-check-circle"></i> Completados
                        </button>
                    </div>
                    <button class="boton-nuevo-seguimiento" onclick="nuevoSeguimiento()">
                        <i class="bi bi-plus-circle"></i> Nuevo Seguimiento
                    </button>
                </div>
            </div>

            <!-- TARJETAS DE ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="tarjeta-stat-seguimiento">
                        <div class="icono-stat-seg bg-primary-soft">
                            <i class="bi bi-activity text-primary"></i>
                        </div>
                        <div class="info-stat-seg">
                            <h3>42</h3>
                            <p>Seguimientos Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-stat-seguimiento">
                        <div class="icono-stat-seg bg-danger-soft">
                            <i class="bi bi-exclamation-octagon text-danger"></i>
                        </div>
                        <div class="info-stat-seg">
                            <h3>8</h3>
                            <p>Críticos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-stat-seguimiento">
                        <div class="icono-stat-seg bg-warning-soft">
                            <i class="bi bi-clock-history text-warning"></i>
                        </div>
                        <div class="info-stat-seg">
                            <h3>15</h3>
                            <p>Medicaciones Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-stat-seguimiento">
                        <div class="icono-stat-seg bg-success-soft">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                        <div class="info-stat-seg">
                            <h3>128</h3>
                            <p>Completados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE SEGUIMIENTOS -->
            <div class="contenedor-tabla-seguimientos">
                <div class="encabezado-tabla-seg">
                    <h5>Seguimientos Activos</h5>
                    <div class="opciones-tabla">
                        <select class="select-ordenar">
                            <option>Más recientes</option>
                            <option>Por prioridad</option>
                            <option>Por paciente</option>
                            <option>Por fecha fin</option>
                        </select>
                    </div>
                </div>

                <div class="lista-seguimientos">
                    <!-- Seguimiento 1 - Crítico -->
                    <div class="card-seguimiento critico">
                        <div class="header-seguimiento">
                            <div class="info-paciente-seg">
                                <img src="https://api.dicebear.com/7.x/bottts/svg?seed=luna" alt="Luna"
                                    class="avatar-seg">
                                <div>
                                    <h6>Luna</h6>
                                    <span class="tipo-paciente">Golden Retriever - 3 años</span>
                                </div>
                            </div>
                            <div class="badges-seguimiento">
                                <span class="badge-prioridad critica">Crítico</span>
                                <span class="badge-tipo post-operatorio">Post-Operatorio</span>
                            </div>
                        </div>

                        <div class="body-seguimiento">
                            <div class="diagnostico-seg">
                                <i class="bi bi-clipboard-pulse"></i>
                                <strong>Diagnóstico:</strong> Cirugía de cadera - Displasia
                            </div>
                            <div class="tratamiento-seg">
                                <i class="bi bi-capsule"></i>
                                <strong>Tratamiento:</strong> Antibióticos + Analgésicos
                            </div>
                            <div class="doctor-seg">
                                <i class="bi bi-person-badge"></i>
                                <strong>Doctor:</strong> Dra. María García
                            </div>
                        </div>

                        <div class="progreso-seguimiento">
                            <div class="label-progreso">
                                <span>Progreso del tratamiento</span>
                                <span class="porcentaje-prog">45%</span>
                            </div>
                            <div class="barra-progreso-seg">
                                <div class="fill-progreso fill-progreso-45"></div>
                            </div>
                        </div>

                        <div class="timeline-seguimiento">
                            <div class="evento-timeline">
                                <div class="punto-evento completado"></div>
                                <div class="info-evento">
                                    <strong>Cirugía Realizada</strong>
                                    <span>28/10/2024 - 10:00 AM</span>
                                </div>
                            </div>
                            <div class="evento-timeline">
                                <div class="punto-evento completado"></div>
                                <div class="info-evento">
                                    <strong>Primera Revisión</strong>
                                    <span>29/10/2024 - 02:00 PM</span>
                                </div>
                            </div>
                            <div class="evento-timeline">
                                <div class="punto-evento activo"></div>
                                <div class="info-evento">
                                    <strong>Control de Medicación</strong>
                                    <span>Hoy - 04:00 PM</span>
                                </div>
                            </div>
                            <div class="evento-timeline">
                                <div class="punto-evento pendiente"></div>
                                <div class="info-evento">
                                    <strong>Revisión Final</strong>
                                    <span>05/11/2024 - 11:00 AM</span>
                                </div>
                            </div>
                        </div>

                        <div class="footer-seguimiento">
                            <button class="btn-accion-seg btn-actualizar">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                            <button class="btn-accion-seg btn-detalles">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                            <button class="btn-accion-seg btn-nota">
                                <i class="bi bi-journal-plus"></i> Agregar Nota
                            </button>
                        </div>
                    </div>

                    <!-- Seguimiento 2 - Activo -->
                    <div class="card-seguimiento activo">
                        <div class="header-seguimiento">
                            <div class="info-paciente-seg">
                                <img src="https://api.dicebear.com/7.x/bottts/svg?seed=max" alt="Max"
                                    class="avatar-seg">
                                <div>
                                    <h6>Max</h6>
                                    <span class="tipo-paciente">Labrador - 5 años</span>
                                </div>
                            </div>
                            <div class="badges-seguimiento">
                                <span class="badge-prioridad media">Media</span>
                                <span class="badge-tipo tratamiento">Tratamiento</span>
                            </div>
                        </div>

                        <div class="body-seguimiento">
                            <div class="diagnostico-seg">
                                <i class="bi bi-clipboard-pulse"></i>
                                <strong>Diagnóstico:</strong> Dermatitis alérgica
                            </div>
                            <div class="tratamiento-seg">
                                <i class="bi bi-capsule"></i>
                                <strong>Tratamiento:</strong> Antihistamínicos + Dieta especial
                            </div>
                            <div class="doctor-seg">
                                <i class="bi bi-person-badge"></i>
                                <strong>Doctor:</strong> Dr. Juan Pérez
                            </div>
                        </div>

                        <div class="progreso-seguimiento">
                            <div class="label-progreso">
                                <span>Progreso del tratamiento</span>
                                <span class="porcentaje-prog">75%</span>
                            </div>
                            <div class="barra-progreso-seg">
                                <div class="fill-progreso fill-progreso-75"></div>
                            </div>
                        </div>

                        <div class="medicaciones-activas">
                            <div class="titulo-medicaciones">
                                <i class="bi bi-capsule-pill"></i> Medicaciones Activas
                            </div>
                            <div class="lista-medicaciones">
                                <div class="item-medicacion">
                                    <div class="nombre-med">Cetirizina 10mg</div>
                                    <div class="dosis-med">1 tab c/12h</div>
                                    <div class="proxima-dosis">Próxima: 18:00</div>
                                </div>
                                <div class="item-medicacion">
                                    <div class="nombre-med">Dieta Hipoalergénica</div>
                                    <div class="dosis-med">200g c/día</div>
                                    <div class="proxima-dosis">Próxima: 20:00</div>
                                </div>
                            </div>
                        </div>

                        <div class="footer-seguimiento">
                            <button class="btn-accion-seg btn-actualizar">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                            <button class="btn-accion-seg btn-detalles">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                            <button class="btn-accion-seg btn-nota">
                                <i class="bi bi-journal-plus"></i> Agregar Nota
                            </button>
                        </div>
                    </div>

                    <!-- Seguimiento 3 - Activo Normal -->
                    <div class="card-seguimiento activo">
                        <div class="header-seguimiento">
                            <div class="info-paciente-seg">
                                <img src="https://api.dicebear.com/7.x/bottts/svg?seed=miau" alt="Miau"
                                    class="avatar-seg">
                                <div>
                                    <h6>Miau</h6>
                                    <span class="tipo-paciente">Gato Persa - 2 años</span>
                                </div>
                            </div>
                            <div class="badges-seguimiento">
                                <span class="badge-prioridad baja">Baja</span>
                                <span class="badge-tipo preventivo">Preventivo</span>
                            </div>
                        </div>

                        <div class="body-seguimiento">
                            <div class="diagnostico-seg">
                                <i class="bi bi-clipboard-pulse"></i>
                                <strong>Diagnóstico:</strong> Control post-vacunación
                            </div>
                            <div class="tratamiento-seg">
                                <i class="bi bi-capsule"></i>
                                <strong>Tratamiento:</strong> Observación y monitoreo
                            </div>
                            <div class="doctor-seg">
                                <i class="bi bi-person-badge"></i>
                                <strong>Doctor:</strong> Dr. Carlos López
                            </div>
                        </div>

                        <div class="progreso-seguimiento">
                            <div class="label-progreso">
                                <span>Progreso del tratamiento</span>
                                <span class="porcentaje-prog">90%</span>
                            </div>
                            <div class="barra-progreso-seg">
                                <div class="fill-progreso fill-progreso-90"></div>
                            </div>
                        </div>

                        <div class="proximas-acciones">
                            <div class="titulo-acciones">
                                <i class="bi bi-calendar-check"></i> Próximas Acciones
                            </div>
                            <div class="lista-acciones">
                                <div class="item-accion">
                                    <i class="bi bi-circle-fill text-success"></i>
                                    <span>Control Final - 02/11/2024</span>
                                </div>
                                <div class="item-accion">
                                    <i class="bi bi-circle-fill text-info"></i>
                                    <span>Alta Médica - 05/11/2024</span>
                                </div>
                            </div>
                        </div>

                        <div class="footer-seguimiento">
                            <button class="btn-accion-seg btn-actualizar">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                            <button class="btn-accion-seg btn-detalles">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                            <button class="btn-accion-seg btn-completar">
                                <i class="bi bi-check-circle"></i> Completar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->

    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script> -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardSeguimientos.js"></script>
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script> -->

</body>

</html>