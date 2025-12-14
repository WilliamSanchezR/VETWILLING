<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
//Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/rolController.php';
// Enlazamos la ruta del controlador de veterinarias para listar las veterinarias
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/veterinariaController.php';


$id = $_GET['id'];
$veterinariaData = consultarVeterinariasRegistradas($id);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veterinaria</title>

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


    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Include de la barra lateral izquierda -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <!-- Include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'
        ?>

        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-person-vcard"></i>
                <h2>Registro de veterinaria</h2>
                <p class="text-muted">Complete todos los campos requeridos para registrar la veterinaria</p>
            </div>


            <form id="vetForm" action="<?= BASE_URL ?>/admin/actualizar-veterinaria" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_veterinaria" value="<?= $veterinariaData['id_veterinaria'] ?>">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="foto_actual" value="<?= $veterinariaData['foto'] ?>">

                <!-- Paso 1: Datos del Veterinaria -->
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos de la Veterinaria</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="bi bi-hash"></i> Nit *</label>
                                    <input type="text" id="nit" name="nit" required placeholder="000.123.456-7" value="<?= $veterinariaData['nit'] ?>">
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombre *</label>
                                <input type="text" id="nombrePropietario" name="nombre" required placeholder="Ej: Juan Pérez García" value="<?= $veterinariaData['nombre'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-geo-alt"></i> Ciudad *</label>
                                <input type="text" id="ciudad" name="ciudad" required placeholder="Ej: Bogotá" value="<?= $veterinariaData['ciudad'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-building"></i> Dirección *</label>
                                <input type="text" id="direccion" name="direccion" required placeholder="Ej: Calle 12 # 34-56" value="<?= $veterinariaData['direccion'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" required placeholder="+57 300 123 4567" value="<?= $veterinariaData['telefono'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Email *</label>
                                <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com" value="<?= $veterinariaData['email'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Estado *</label>
                                <select id="estado" name="estado" required>
                                    <option value="<?= $veterinariaData['estado'] ?>"><?= $veterinariaData['estado'] ?></option>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Bloqueado">Bloqueado</option>
                                </select>

                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <label><i class="bi bi-envelope"></i> Foto </label>
                                    <input type="file" accept=".jpg, .png, .jpeg" id="foto" name="foto">
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
    </div>


    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>