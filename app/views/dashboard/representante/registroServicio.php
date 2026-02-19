<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Servicios</title>
    <!-- Icono de la página -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Propio -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/formularioAdminStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/registroServicio.styles.css">


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

        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-person-vcard"></i>
                <h2>Registro de Servicios</h2>
                <p class="text-muted">Complete todos los campos requeridos para registrar el servicio</p>
            </div>


            <form id="registroProfesional" action="<?= BASE_URL ?>/representante/guardar-servicio" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_veterinaria" value="<?= $_SESSION['user']['id_veterinaria'] ?>">
                <input type="hidden" name="horarios" id="horariosInput" value="">


                <!-- Paso 1: Datos del Profesional -->
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos del servicio</h3>

                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i>Nombre *</label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Nombre del servicio">
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="bi bi-pencil-square"></i> Descripción </label>
                                <textarea maxlength="200" rows="4" cols="50" name="descripcion" id="descripcion" placeholder="Descripción del servicio"></textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label><i class="bi bi-clock"></i> Horarios de Atención *</label>

                                <!-- Horario 1 -->
                                <div class="horario-item mb-3 p-3 border rounded">
                                    <div class="row align-items-end">
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label small">Días</label>
                                            <div class="btn-group d-flex flex-wrap gap-2" role="group">
                                                <input type="checkbox" class="btn-check" name="dias[]" value="1" id="lunes1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="lunes1">LUNES</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="2" id="martes1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="martes1">MARTES</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="3" id="miercoles1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="miercoles1">MIERCOLES</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="4" id="jueves1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="jueves1">JUEVES</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="5" id="viernes1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="viernes1">VIERNES</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="6" id="sabado1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="sabado1">SABADO</label>

                                                <input type="checkbox" class="btn-check" name="dias[]" value="7" id="domingo1" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="domingo1">DOMINGO</label>
                                            </div>
                                        </div>
                                        <div class="col-md-5 row">
                                            <div class="col-md-12">
                                                <h3 class="titulo-jornada">Primera Jornada</h3>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Hora Inicio</label>
                                                <input type="time" class="form-control" id="hora_inicio_1" name="hora_inicio">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Hora Fin</label>
                                                <input type="time" class="form-control" id="hora_fin_1" name="hora_fin">
                                            </div>
                                        </div>


                                        <div class="col-md-5 row">
                                            <div class="col-md-12">
                                                <h3 class="titulo-jornada">Segunda Jornada</h3>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Hora Inicio</label>
                                                <input type="time" class="form-control" id="hora_inicio_2" name="hora_inicio">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Hora Fin</label>
                                                <input type="time" class="form-control" id="hora_fin_2" name="hora_fin">
                                            </div>
                                        </div>
                                        <div class="col-md-2 mb-2 text-end">
                                            <button type="button" class="btn btn-primary btn-sm btn-agregar-horario ">
                                                <i class="bi bi-plus"></i> Agregar Horario
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label><i class="bi bi-calendar-check"></i> Horarios Registrados</label>
                                <div class="table-responsive">
                                    <table id="tablaListaServicios" class="display tabla-admin" style="width:100%" id="tablaHorarios">
                                        <thead>
                                            <tr>
                                                <th>Días</th>
                                                <th>Horario Mañana</th>
                                                <th>Horario Tarde</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="horariosBody">
                                            <tr class="text-center">
                                                <td colspan="4" class="text-muted">No hay horarios registrados</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="buttons">
                        <span></span>
                        <button type="submit" class="btn btn-success" id="btnGuardarVeterinaria">
                            Guardar <i class="bi bi-floppy"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>



        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Bootstrap -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- JS Propio -->
        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/registroServicio.js"></script>


</body>

</html>