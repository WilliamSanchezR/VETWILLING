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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardReportes.css">


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
            <!-- FILTROS DE PERIODO -->
            <div class="filtros-reporte">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="boton-periodo active" data-periodo="hoy">Hoy</button>
                        <button class="boton-periodo" data-periodo="semana">Esta Semana</button>
                        <button class="boton-periodo" data-periodo="mes">Este Mes</button>
                        <button class="boton-periodo" data-periodo="ano">Este Año</button>
                        <button class="boton-periodo" data-periodo="personalizado">
                            <i class="bi bi-calendar3"></i> Personalizado
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="boton-exportar" onclick="exportarPDF()">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </button>
                        <button class="boton-exportar" onclick="exportarExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE RESUMEN -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="tarjeta-reporte">
                        <div class="icono-reporte bg-success-soft">
                            <i class="bi bi-currency-dollar text-success"></i>
                        </div>
                        <div class="info-reporte">
                            <p class="etiqueta-reporte">Ingresos Totales</p>
                            <h3 class="valor-reporte">$45,280</h3>
                            <span class="cambio-positivo">
                                <i class="bi bi-arrow-up"></i> +12.5%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-reporte">
                        <div class="icono-reporte bg-primary-soft">
                            <i class="bi bi-calendar-check text-primary"></i>
                        </div>
                        <div class="info-reporte">
                            <p class="etiqueta-reporte">Citas Atendidas</p>
                            <h3 class="valor-reporte">342</h3>
                            <span class="cambio-positivo">
                                <i class="bi bi-arrow-up"></i> +8.2%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-reporte">
                        <div class="icono-reporte bg-warning-soft">
                            <i class="bi bi-heart-pulse text-warning"></i>
                        </div>
                        <div class="info-reporte">
                            <p class="etiqueta-reporte">Nuevos Pacientes</p>
                            <h3 class="valor-reporte">89</h3>
                            <span class="cambio-positivo">
                                <i class="bi bi-arrow-up"></i> +15.3%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-reporte">
                        <div class="icono-reporte bg-danger-soft">
                            <i class="bi bi-star-fill text-danger"></i>
                        </div>
                        <div class="info-reporte">
                            <p class="etiqueta-reporte">Satisfacción</p>
                            <h3 class="valor-reporte">4.8/5</h3>
                            <span class="cambio-positivo">
                                <i class="bi bi-arrow-up"></i> +0.3
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICOS -->
            <div class="row g-3 mb-4">
                <!-- Gráfico de Ingresos -->
                <div class="col-lg-8">
                    <div class="tarjeta-grafico">
                        <div class="encabezado-grafico">
                            <div>
                                <h5>Ingresos Mensuales</h5>
                                <p class="text-muted mb-0">Comparativa últimos 6 meses</p>
                            </div>
                            <select class="select-grafico">
                                <option>2024</option>
                                <option>2023</option>
                            </select>
                        </div>
                        <div class="contenedor-grafico">
                            <canvas id="graficoIngresos"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Distribución por Servicio -->
                <div class="col-lg-4">
                    <div class="tarjeta-grafico">
                        <div class="encabezado-grafico">
                            <h5>Servicios Más Solicitados</h5>
                        </div>
                        <div class="contenedor-grafico">
                            <canvas id="graficoServicios"></canvas>
                        </div>
                        <div class="leyenda-servicios">
                            <div class="item-leyenda">
                                <span class="punto-leyenda" style="background: #22c55e;"></span>
                                <span>Consultas (45%)</span>
                            </div>
                            <div class="item-leyenda">
                                <span class="punto-leyenda" style="background: #3b82f6;"></span>
                                <span>Vacunación (28%)</span>
                            </div>
                            <div class="item-leyenda">
                                <span class="punto-leyenda" style="background: #f59e0b;"></span>
                                <span>Cirugías (18%)</span>
                            </div>
                            <div class="item-leyenda">
                                <span class="punto-leyenda" style="background: #ef4444;"></span>
                                <span>Emergencias (9%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Detalladas -->
            <div class="row g-3 mb-4">
                <!-- Top Tratamientos -->
                <div class="col-lg-6">
                    <div class="tarjeta-estadisticas">
                        <h5 class="mb-3">Tratamientos Más Realizados</h5>
                        <div class="lista-estadisticas">
                            <div class="item-estadistica">
                                <div class="info-estadistica">
                                    <span class="nombre-estadistica">Vacunación Antirrábica</span>
                                    <span class="valor-estadistica">156 pacientes</span>
                                </div>
                                <div class="barra-progreso-estadistica">
                                    <div class="progreso-estadistica" style="width: 85%;"></div>
                                </div>
                            </div>
                            <div class="item-estadistica">
                                <div class="info-estadistica">
                                    <span class="nombre-estadistica">Desparasitación</span>
                                    <span class="valor-estadistica">132 pacientes</span>
                                </div>
                                <div class="barra-progreso-estadistica">
                                    <div class="progreso-estadistica" style="width: 72%;"></div>
                                </div>
                            </div>
                            <div class="item-estadistica">
                                <div class="info-estadistica">
                                    <span class="nombre-estadistica">Control de Peso</span>
                                    <span class="valor-estadistica">98 pacientes</span>
                                </div>
                                <div class="barra-progreso-estadistica">
                                    <div class="progreso-estadistica" style="width: 54%;"></div>
                                </div>
                            </div>
                            <div class="item-estadistica">
                                <div class="info-estadistica">
                                    <span class="nombre-estadistica">Limpieza Dental</span>
                                    <span class="valor-estadistica">76 pacientes</span>
                                </div>
                                <div class="barra-progreso-estadistica">
                                    <div class="progreso-estadistica" style="width: 42%;"></div>
                                </div>
                            </div>
                            <div class="item-estadistica">
                                <div class="info-estadistica">
                                    <span class="nombre-estadistica">Esterilización</span>
                                    <span class="valor-estadistica">54 pacientes</span>
                                </div>
                                <div class="barra-progreso-estadistica">
                                    <div class="progreso-estadistica" style="width: 30%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribución por Especie -->
                <div class="col-lg-6">
                    <div class="tarjeta-estadisticas">
                        <h5 class="mb-3">Pacientes por Especie</h5>
                        <div class="lista-estadisticas">
                            <div class="item-estadistica-especie">
                                <div class="icono-especie bg-warning-soft">
                                    <i class="bi bi-heart-fill text-warning"></i>
                                </div>
                                <div class="info-especie">
                                    <span class="nombre-especie">Perros</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="barra-mini">
                                            <div class="progreso-mini" style="width: 68%;"></div>
                                        </div>
                                        <span class="porcentaje-especie">68%</span>
                                    </div>
                                </div>
                                <span class="cantidad-especie">168</span>
                            </div>
                            <div class="item-estadistica-especie">
                                <div class="icono-especie bg-info-soft">
                                    <i class="bi bi-heart-fill text-info"></i>
                                </div>
                                <div class="info-especie">
                                    <span class="nombre-especie">Gatos</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="barra-mini">
                                            <div class="progreso-mini" style="width: 45%;"></div>
                                        </div>
                                        <span class="porcentaje-especie">25%</span>
                                    </div>
                                </div>
                                <span class="cantidad-especie">62</span>
                            </div>
                            <div class="item-estadistica-especie">
                                <div class="icono-especie bg-success-soft">
                                    <i class="bi bi-heart-fill text-success"></i>
                                </div>
                                <div class="info-especie">
                                    <span class="nombre-especie">Aves</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="barra-mini">
                                            <div class="progreso-mini" style="width: 12%;"></div>
                                        </div>
                                        <span class="porcentaje-especie">5%</span>
                                    </div>
                                </div>
                                <span class="cantidad-especie">12</span>
                            </div>
                            <div class="item-estadistica-especie">
                                <div class="icono-especie bg-danger-soft">
                                    <i class="bi bi-heart-fill text-danger"></i>
                                </div>
                                <div class="info-especie">
                                    <span class="nombre-especie">Otros</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="barra-mini">
                                            <div class="progreso-mini" style="width: 8%;"></div>
                                        </div>
                                        <span class="porcentaje-especie">2%</span>
                                    </div>
                                </div>
                                <span class="cantidad-especie">6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Resumen Financiero -->
            <div class="tarjeta-tabla-financiera">
                <div class="encabezado-tabla-financiera">
                    <h5>Resumen Financiero Mensual</h5>
                    <button class="boton-ver-mas">Ver Detalle <i class="bi bi-arrow-right"></i></button>
                </div>
                <div class="tabla-responsive">
                    <table class="tabla-financiera">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Enero</th>
                                <th>Febrero</th>
                                <th>Marzo</th>
                                <th>Abril</th>
                                <th>Mayo</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="concepto">Consultas</td>
                                <td>$8,450</td>
                                <td>$9,200</td>
                                <td>$8,900</td>
                                <td>$9,500</td>
                                <td>$10,230</td>
                                <td class="total">$46,280</td>
                            </tr>
                            <tr>
                                <td class="concepto">Cirugías</td>
                                <td>$12,300</td>
                                <td>$11,800</td>
                                <td>$13,200</td>
                                <td>$12,900</td>
                                <td>$14,100</td>
                                <td class="total">$64,300</td>
                            </tr>
                            <tr>
                                <td class="concepto">Vacunación</td>
                                <td>$5,600</td>
                                <td>$6,100</td>
                                <td>$5,800</td>
                                <td>$6,400</td>
                                <td>$6,900</td>
                                <td class="total">$30,800</td>
                            </tr>
                            <tr>
                                <td class="concepto">Laboratorio</td>
                                <td>$3,200</td>
                                <td>$3,500</td>
                                <td>$3,800</td>
                                <td>$3,600</td>
                                <td>$4,100</td>
                                <td class="total">$18,200</td>
                            </tr>
                            <tr class="fila-total">
                                <td class="concepto"><strong>Total Mensual</strong></td>
                                <td><strong>$29,550</strong></td>
                                <td><strong>$30,600</strong></td>
                                <td><strong>$31,700</strong></td>
                                <td><strong>$32,400</strong></td>
                                <td><strong>$35,330</strong></td>
                                <td class="total"><strong>$159,580</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Propio -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardReportes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>


</body>

</html>