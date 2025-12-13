<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Laboratorio</title>



    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardLaboratorio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/extras/css/globalStyles.css">

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

        <!-- ÁREA DE CONTENIDO LABORATORIO-->
        <div class="area-contenido">

            <h2>Listado de Pacientes - Laboratorio Clinico</h2>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarPaciente" placeholder="Buscar Pacientes...">
                    </div>
                </div>
                <div class="controles-derecha">

                    <button class="btn-control" id="btnOrdenar">
                        <i class="bi bi-sort-down"></i> Ordenar
                    </button>
                    <button class="btn-control" id="btnExport">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button class="btn-agregar" id="btnAgregarNuevo">
                        <i class="bi bi-plus-lg"></i> Agregar Nuevo
                    </button>
                </div>
            </div>

            <!-- TABLA DE LABORATORIO -->
            <div class="contenedor-tabla-laboratorio">
                <table class="tabla-laboratorio" id="tabla-pacientes">
                    <thead>
                        <tr>
                            <th></th>
                            <th>No.</th>
                            <th>Fecha</th>
                            <th>Propietario</th>
                            <th>Nombre Mascota</th>
                            <th>Animal</th>
                            <th>Raza</th>
                            <th>Laboratorios</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody id="tablaLaboratorioBody">
                        <!-- Laboratorio 1 -->
                    </tbody>
                </table>


            </div>

        </div>
    </div>

    <!-- Modal para ver laboratorio  -->
    <div class="modal modal-lg" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Laboratorios Clinicos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="info-paciente">
                        <span>Fecha: </span>
                        <span>Propietario: </span>
                        <span>Nombre Paciente: </span>
                        <span>Tipo de Animal: </span>
                        <span>Raza: </span>
                        <span>Sexo: </span>
                    </div>

                    <div>
                        <table class="tabla-laboratorio" id="list-laboratorios-asociados">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tipo de Laboratorio</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>

                            <tbody id="tablaResultados">
                                <!-- Laboratorio 1 -->
                                <tr>
                                    <td data-bs-toggle="modal" data-bs-target="#modalResultadoLab"
                                        data-bs-whatever="@mdo"><i class="bi bi-search"></i></td>
                                    <td>Glucosa</td>
                                    <td>Completado</td>
                                    <td>2024-08-15</td>
                                </tr>
                                <!-- Laboratorio 2 -->
                                <tr>
                                    <td data-bs-toggle="modal" data-bs-target="#modalResultadoLab"
                                        data-bs-whatever="@mdo"><i class="bi bi-search"></i></td>
                                    <td>Urea</td>
                                    <td>Pendiente</td>
                                    <td>2024-08-16</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>


    <!-- Modal para cargar Resultados laboratorio -->
    <div class="modal modal-xl" id="modalResultadoLab" tabindex="-1" aria-labelledby="modalResultadoLabLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalResultadoLabLabel">Resultado de Laboratorios Clinicos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="info-paciente">
                        <span>Fecha: </span>
                        <span>Propietario: </span>
                        <span>Nombre Paciente: </span>
                        <span>Tipo de Animal: </span>
                        <span>Raza: </span>
                        <span>Sexo: </span>
                    </div>

                    <div class="list-examenes">
                        <span>Laboratorios: </span>
                        <table class="tabla-laboratorio" id="lista-resultados">
                            <thead>
                                <tr>
                                    <th>Tipo de Prueba</th>
                                    <th>Resultado</th>
                                    <th>Unidades</th>
                                    <th>Rango de Referencia</th>
                                </tr>
                            </thead>

                            <tbody id="tablaDetalleResultados">
                                <!-- Resultado 1 -->
                                <tr>
                                    <td>Glucosa</td>
                                    <td><input type="text" value="95"></td>
                                    <td><select name="" id="">
                                            <option value="">Seleccione</option>
                                            <option value="mg/dL">mg/dL</option>
                                            <option value="mmol/L">mmol/L</option>
                                        </select></td>
                                    <td>70-120 mg/dL</td>
                                </tr>
                                <!-- Resultado 2 -->
                                <tr>
                                    <td>Urea</td>
                                    <td>25 mg/dL</td>
                                    <td>mg/dL</td>
                                    <td>10-30 mg/dL</td>
                                </tr>
                            </tbody>


                        </table>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Editar</button>
                            <button id="btn-guardar-resultados" type="button"
                                class="btn btn-primary btn-guardar">Guardar</button>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardLaboratorios.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardLaboratorios.js"></script>

</body>

</html>