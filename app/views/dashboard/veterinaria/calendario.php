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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleCalendario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css">

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

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- BARRA DE ACCIONES -->
            <div class="barra-acciones-citas">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="boton-vista active" data-vista="calendario">
                            <i class="bi bi-calendar3"></i> Calendario
                        </button>
                        <button class="boton-vista" data-vista="lista">
                            <i class="bi bi-list-ul"></i> Lista
                        </button>
                        <button class="boton-vista" data-vista="dia">
                            <i class="bi bi-clock"></i> Día
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <select class="select-veterinario">
                            <option value="">Todos los veterinarios</option>
                            <option value="1">Dr. Juan Pérez</option>
                            <option value="2">Dra. María García</option>
                            <option value="3">Dr. Carlos López</option>
                        </select>
                        <button class="boton-nueva-cita" onclick="abrirModalNuevaCita()">
                            <i class="bi bi-plus-circle"></i> Nueva Cita
                        </button>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE RESUMEN -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-success-soft">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                        <div>
                            <h3>24</h3>
                            <p>Citas Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-warning-soft">
                            <i class="bi bi-clock-fill text-warning"></i>
                        </div>
                        <div>
                            <h3>8</h3>
                            <p>Pendientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-danger-soft">
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        </div>
                        <div>
                            <h3>3</h3>
                            <p>Urgencias</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-resumen-cita">
                        <div class="icono-resumen bg-info-soft">
                            <i class="bi bi-calendar-week-fill text-info"></i>
                        </div>
                        <div>
                            <h3>156</h3>
                            <p>Esta Semana</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA CALENDARIO -->
            <div class="contenedor-calendario-citas" id="vistaCalendario">
                <div class="encabezado-calendario">
                    <button class="boton-nav-mes" onclick="cambiarMes(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h4 id="mesActual">Octubre 2024</h4>
                    <button class="boton-nav-mes" onclick="cambiarMes(1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div class="calendario-grid">
                    <!-- Días de la semana -->
                    <div class="dia-semana">Dom</div>
                    <div class="dia-semana">Lun</div>
                    <div class="dia-semana">Mar</div>
                    <div class="dia-semana">Mié</div>
                    <div class="dia-semana">Jue</div>
                    <div class="dia-semana">Vie</div>
                    <div class="dia-semana">Sáb</div>

                    <!-- Días del mes (ejemplo) -->
                    <div class="celda-dia otro-mes">29</div>
                    <div class="celda-dia otro-mes">30</div>
                    <div class="celda-dia otro-mes">31</div>
                    <div class="celda-dia">1</div>
                    <div class="celda-dia">2</div>
                    <div class="celda-dia">3</div>
                    <div class="celda-dia">4</div>

                    <div class="celda-dia">5</div>
                    <div class="celda-dia">6</div>
                    <div class="celda-dia">7</div>
                    <div class="celda-dia">8</div>
                    <div class="celda-dia">9</div>
                    <div class="celda-dia">10</div>
                    <div class="celda-dia">11</div>

                    <div class="celda-dia">12</div>
                    <div class="celda-dia">13</div>
                    <div class="celda-dia">14</div>
                    <div class="celda-dia tiene-citas">
                        15
                        <div class="indicadores-citas">
                            <span class="indicador-cita consulta"></span>
                            <span class="indicador-cita cirugia"></span>
                            <span class="indicador-cita vacuna"></span>
                        </div>
                    </div>
                    <div class="celda-dia">16</div>
                    <div class="celda-dia">17</div>
                    <div class="celda-dia">18</div>

                    <div class="celda-dia">19</div>
                    <div class="celda-dia">20</div>
                    <div class="celda-dia">21</div>
                    <div class="celda-dia tiene-citas">
                        22
                        <div class="indicadores-citas">
                            <span class="indicador-cita consulta"></span>
                            <span class="indicador-cita urgencia"></span>
                        </div>
                    </div>
                    <div class="celda-dia">23</div>
                    <div class="celda-dia">24</div>
                    <div class="celda-dia">25</div>

                    <div class="celda-dia">26</div>
                    <div class="celda-dia">27</div>
                    <div class="celda-dia">28</div>
                    <div class="celda-dia">29</div>
                    <div class="celda-dia hoy tiene-citas">
                        30
                        <div class="indicadores-citas">
                            <span class="indicador-cita consulta"></span>
                            <span class="indicador-cita consulta"></span>
                            <span class="indicador-cita cirugia"></span>
                            <span class="indicador-cita urgencia"></span>
                        </div>
                    </div>
                    <div class="celda-dia">31</div>
                    <div class="celda-dia otro-mes">1</div>
                </div>

                <div class="leyenda-calendario">
                    <div class="item-leyenda-cal">
                        <span class="indicador-cita consulta"></span>
                        <span>Consulta</span>
                    </div>
                    <div class="item-leyenda-cal">
                        <span class="indicador-cita cirugia"></span>
                        <span>Cirugía</span>
                    </div>
                    <div class="item-leyenda-cal">
                        <span class="indicador-cita vacuna"></span>
                        <span>Vacunación</span>
                    </div>
                    <div class="item-leyenda-cal">
                        <span class="indicador-cita urgencia"></span>
                        <span>Urgencia</span>
                    </div>
                </div>
            </div>

            <!-- VISTA DÍA (Timeline) -->
            <div class="contenedor-vista-dia" id="vistaDia" style="display: none;">
                <div class="encabezado-vista-dia">
                    <div class="navegacion-dia">
                        <button class="boton-nav-dia"><i class="bi bi-chevron-left"></i></button>
                        <h4>Miércoles, 30 de Octubre 2024</h4>
                        <button class="boton-nav-dia"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <button class="boton-hoy">Hoy</button>
                </div>

                <div class="timeline-dia">
                    <div class="linea-horario">
                        <div class="hora-timeline">08:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">09:00</div>
                        <div class="citas-hora">
                            <div class="cita-timeline consulta">
                                <div class="header-cita-timeline">
                                    <strong>Luna - Consulta General</strong>
                                    <span class="duracion-cita">30 min</span>
                                </div>
                                <div class="body-cita-timeline">
                                    <p><i class="bi bi-person"></i> María González</p>
                                    <p><i class="bi bi-telephone"></i> +57 300 123 4567</p>
                                </div>
                                <div class="footer-cita-timeline">
                                    <button class="btn-accion-cita btn-confirmar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">10:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">11:00</div>
                        <div class="citas-hora">
                            <div class="cita-timeline cirugia">
                                <div class="header-cita-timeline">
                                    <strong>Max - Cirugía Programada</strong>
                                    <span class="duracion-cita">2 hrs</span>
                                </div>
                                <div class="body-cita-timeline">
                                    <p><i class="bi bi-person"></i> Carlos Pérez</p>
                                    <p><i class="bi bi-clipboard-plus"></i> Esterilización</p>
                                </div>
                                <div class="footer-cita-timeline">
                                    <button class="btn-accion-cita btn-confirmar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">12:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">13:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">14:00</div>
                        <div class="citas-hora">
                            <div class="cita-timeline vacuna">
                                <div class="header-cita-timeline">
                                    <strong>Miau - Vacunación</strong>
                                    <span class="duracion-cita">20 min</span>
                                </div>
                                <div class="body-cita-timeline">
                                    <p><i class="bi bi-person"></i> Ana Martínez</p>
                                    <p><i class="bi bi-heart-pulse"></i> Triple Felina</p>
                                </div>
                                <div class="footer-cita-timeline">
                                    <button class="btn-accion-cita btn-confirmar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">15:00</div>
                        <div class="citas-hora">
                            <div class="cita-timeline urgencia">
                                <div class="header-cita-timeline">
                                    <strong>Rocky - URGENCIA</strong>
                                    <span class="duracion-cita">45 min</span>
                                </div>
                                <div class="body-cita-timeline">
                                    <p><i class="bi bi-person"></i> Luis Rodríguez</p>
                                    <p><i class="bi bi-exclamation-triangle"></i> Trauma / Accidente</p>
                                </div>
                                <div class="footer-cita-timeline">
                                    <button class="btn-accion-cita btn-confirmar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-accion-cita btn-cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">16:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>

                    <div class="linea-horario">
                        <div class="hora-timeline">17:00</div>
                        <div class="citas-hora">
                            <!-- Espacio vacío -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA LISTA -->
            <div class="contenedor-vista-lista" id="vistaLista" style="display: none;">
                <div class="filtros-lista">
                    <select class="filtro-lista">
                        <option>Todas las citas</option>
                        <option>Hoy</option>
                        <option>Esta semana</option>
                        <option>Este mes</option>
                    </select>
                    <select class="filtro-lista">
                        <option>Todos los estados</option>
                        <option>Confirmadas</option>
                        <option>Pendientes</option>
                        <option>Completadas</option>
                        <option>Canceladas</option>
                    </select>
                </div>

                <div class="lista-citas">
                    <div class="item-cita-lista">
                        <div class="fecha-cita-lista">
                            <div class="dia-numero">30</div>
                            <div class="mes-nombre">Oct</div>
                        </div>
                        <div class="info-cita-lista">
                            <h5>Luna - Consulta General</h5>
                            <p><i class="bi bi-clock"></i> 09:00 AM - 09:30 AM</p>
                            <p><i class="bi bi-person"></i> María González | <i class="bi bi-telephone"></i> +57 300 123
                                4567</p>
                            <p><i class="bi bi-person-badge"></i> Dr. Juan Pérez</p>
                        </div>
                        <div class="acciones-cita-lista">
                            <span class="badge-estado-lista confirmada">Confirmada</span>
                            <div class="botones-accion-lista">
                                <button class="btn-lista" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-lista" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-lista" title="Cancelar">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="item-cita-lista">
                        <div class="fecha-cita-lista">
                            <div class="dia-numero">30</div>
                            <div class="mes-nombre">Oct</div>
                        </div>
                        <div class="info-cita-lista">
                            <h5>Max - Cirugía Programada</h5>
                            <p><i class="bi bi-clock"></i> 11:00 AM - 01:00 PM</p>
                            <p><i class="bi bi-person"></i> Carlos Pérez | <i class="bi bi-telephone"></i> +57 301 234
                                5678</p>
                            <p><i class="bi bi-person-badge"></i> Dra. María García</p>
                        </div>
                        <div class="acciones-cita-lista">
                            <span class="badge-estado-lista pendiente">Pendiente</span>
                            <div class="botones-accion-lista">
                                <button class="btn-lista" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-lista" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-lista" title="Cancelar">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="item-cita-lista">
                        <div class="fecha-cita-lista">
                            <div class="dia-numero">30</div>
                            <div class="mes-nombre">Oct</div>
                        </div>
                        <div class="info-cita-lista">
                            <h5>Rocky - URGENCIA</h5>
                            <p><i class="bi bi-clock"></i> 03:00 PM - 03:45 PM</p>
                            <p><i class="bi bi-person"></i> Luis Rodríguez | <i class="bi bi-telephone"></i> +57 302 345
                                6789</p>
                            <p><i class="bi bi-person-badge"></i> Dr. Carlos López</p>
                        </div>
                        <div class="acciones-cita-lista">
                            <span class="badge-estado-lista urgente">Urgente</span>
                            <div class="botones-accion-lista">
                                <button class="btn-lista" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-lista" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-lista" title="Cancelar">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
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

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>