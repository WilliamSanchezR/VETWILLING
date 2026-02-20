<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$idVeterinaria =  $_SESSION['user']['id_veterinaria'];
$veterinariaData = consultarVeterinariasRegistradas($idVeterinaria);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Veterinaria</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap"
        rel="stylesheet">

    <!-- Tus CSS -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/configVeterinaria.styles.css">
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

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
            <form id="vetForm" action="<?= BASE_URL ?>/representante/actualizar-veterinaria" method="POST" enctype="multipart/form-data">

                <div class="wizard-header">
                    <i class="bi bi-person-vcard"></i>
                    <h2>Editar veterinaria</h2>
                    <p class="text-muted">Complete todos los campos requeridos para registrar la veterinaria</p>
                </div>


                <input type="hidden" id="id_veterianaria" name="id_veterinaria" value="<?= $veterinariaData['id_veterinaria'] ?>">
                <input type="hidden" name="accion" value="actualizarInfo">
                <input type="hidden" name="foto_actual" value="<?= $veterinariaData['foto'] ?>">
                <input type="hidden" name="horarios" id="horariosInput">

                <!-- Paso 1: Datos del Veterinaria -->
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos de la Veterinaria</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Nit *</label>
                                <input type="text" id="nit" name="nit" required placeholder="000.123.456-7" value="<?= $veterinariaData['nit'] ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-clipboard2-data"></i> Razón Social *</label>
                                <input type="text" id="nombrePropietario" name="nombre" required placeholder="Ej: Juan Pérez García" value="<?= $veterinariaData['nombre'] ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Email *</label>
                                <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com" value="<?= $veterinariaData['email'] ?>" disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" required placeholder="+57 300 123 4567" value="<?= $veterinariaData['telefono'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <label><i class="bi bi-envelope"></i> Logo </label>
                                    <input type="file" accept=".jpg, .png, .jpeg" id="foto" name="foto">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-pin-map"></i>Ciudad *</label>
                                <select name="ciudad" id="ciudad" data-value="<?= $veterinariaData['ciudad'] ?>" required></select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-geo-alt"></i> Dirección *</label>
                                <input type="text" id="direccion" name="direccion" required placeholder="Ej: Calle 12 # 34-56" value="<?= $veterinariaData['direccion'] ?>">
                            </div>
                        </div>

                    </div>


                </div>

                <div class="step active">
                    <h3><i class="bi bi-clock-history"></i> Horarios de atención</h3>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
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
                                    <table id="tablaHorarios" class="display tabla-admin" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Días</th>
                                                <th>Horario Primera Jornada</th>
                                                <th>Horario Segunda Jornada</th>
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
                </div>


                <div class="buttons">
                    <span></span>
                    <button type="submit" class="btn btn-success" id="btnGuardarVeterinaria">
                        Guardar <i class="bi bi-floppy"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- SCRIPTS -->
    <!-- 1. jQuery PRIMERO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2. Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <!-- 5. Tu script de tabla AL FINAL -->
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

    <!-- Script de municipios -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/representante/js/municipios.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/representante/js/configVeterinaria.js"></script>

    <!-- C:\xampp\htdocs\vetwilling\public\assets\dashBoard\representante\js\configVeterinaria.js -->

</body>

</html>