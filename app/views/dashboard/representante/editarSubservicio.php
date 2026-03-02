<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/controllers/servicioController.php';
require_once BASE_PATH . '/app/controllers/subservicioController.php';

$id = $_GET['id'];
$subservicio = obtenerSubservicioPorId($id);

$servicios = listaServiciosActivosPorVeterinaria($_SESSION['user']['id_veterinaria']);

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
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
                <h2>Edición de Subservicio</h2>
                <p class="text-muted">Complete todos los campos requeridos para editar el subservicio</p>
            </div>


            <form id="registroProfesional" action="<?= BASE_URL ?>/representante/actualizar-subservicio" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_subservicio" value="<?= $_GET['id'] ?>">
                <input type="hidden" name="action" value="actualizar">


                <!-- Paso 1: Datos del Profesional -->
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos del subservicio</h3>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i>Nombre *</label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Nombre del subservicio" value="<?= $subservicio['nombre'] ?>">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-flag"></i> Servicio *</label>
                                <select id="servicio" name="servicio" required>
                                    <option value="" disabled selected>Seleccione un servicio</option>


                                    <?php if (!empty($servicios)) : ?>
                                        <?php foreach ($servicios as $servicio):  ?>
                                            <option value="<?= $servicio['id_servicio'] ?>" <?= $servicio['id_servicio'] == $subservicio['id_servicio'] ? 'selected' : '' ?>><?= $servicio['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-currency-dollar"></i> Costo *</label>
                                <input type="number" step="0.01" id="costo" name="costo" required placeholder="Costo del subservicio" value="<?= $subservicio['costo'] ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-flag"></i> Estado *</label>
                                <select name="estado" id="estado" required>
                                    <option value="" disabled selected>Seleccione el estado</option>
                                    <option <?= $subservicio['estado'] === 'Activo' ? 'selected' : '' ?> value="Activo">Activo</option>
                                    <option <?= $subservicio['estado'] === 'Inactivo' ? 'selected' : '' ?> value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="bi bi-pencil-square"></i> Descripción </label>
                                <textarea maxlength="200" rows="4" cols="50" name="descripcion" id="descripcion" placeholder="Descripción"><?= $subservicio['descripcion'] ?></textarea>
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


</body>

</html>