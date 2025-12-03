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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">

</head>

<body>

    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';

    // <!-- PANEL DERECHO -->
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- ENCABEZADO CON ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="tarjeta-estadistica">
                        <div class="icono-estadistica bg-primary-soft">
                            <i class="bi bi-heart-fill text-danger"></i>
                        </div>
                        <div>
                            <h3 classs="mb-0">248</h3>
                            <p class="text-muted mb-0">Total Pacientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-estadistica">
                        <div class="icono-estadistica bg-success-soft">
                            <i class="bi bi-calendar-check-fill text-success"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">32</h3>
                            <p class="text-muted mb-0">Citas Hoy</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-estadistica">
                        <div class="icono-estadistica bg-warning-soft">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">8</h3>
                            <p class="text-muted mb-0">Urgencias</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="tarjeta-estadistica">
                        <div class="icono-estadistica bg-info-soft">
                            <i class="bi bi-person-plus-fill text-info"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">15</h3>
                            <p class="text-muted mb-0">Nuevos Este Mes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BARRA DE ACCIONES -->
            <div class="barra-acciones-pacientes">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex gap-2">
                        <button class="boton-accion-primario" onclick="abrirModalNuevoPaciente()">
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