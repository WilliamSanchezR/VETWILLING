<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
//Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/rolController.php';
// Enlazamos la ruta del controlador de veterinarias para listar las veterinarias
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/usuarioController.php';

//Llamamos la funcion especifica que existe en dicho controloador
$datosRol = listarRolAdmin();
$datosVeterinaria = listarVeterinariasRegistradas();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoardVeterinariaStyle.css">
    

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Include de la barra lateral izquierda -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>


    <!-- PANEL DERECHO -->
    <!-- Include de notificaciones -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

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
                <h2>Registro de usuario</h2>
                <p class="text-muted">Complete todos los campos requeridos para registrar el usuario</p>
            </div>


            <form id="vetForm" action="<?= BASE_URL ?>/admin/guardar-usuario" method="POST" enctype="multipart/form-data">

                <!-- Paso 1: Datos del Usuario -->
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos del Usuario</h3>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                <select id="tipoDocumento" name="tipo_documento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Número de documento *</label>
                                <input type="number" id="documento" name="numero_documento" required placeholder="12345678">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombres *</label>
                                <input type="text" id="nombres" name="nombres" required placeholder="Ej: Juan Martin">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" required placeholder="Ej: Pérez García">
                            </div>
                        </div>
                    </div>


                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" placeholder="+57 300 123 4567">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i>foto *</label>
                                <input type="file" accept=".jpg," id="img_perfil" name="img_perfil" placeholder="ejemplo@correo.com">
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person-rolodex"></i> Rol *</label>
                                <select id="rol" name="rol" required>
                                    <option value="" disabled selected>Seleccione un rol</option>


                                    <?php if (!empty($datosRol)) : ?>
                                        <?php foreach ($datosRol as $rol):  ?>
                                            <option value="<?= $rol['id_rol'] ?>"><?= $rol['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Veterinaria </label>
                                <select id="veterinaria" name="veterinaria">
                                    <option value="" disabled selected>Seleccione una veterinaria</option>
                                    <?php if (!empty($datosVeterinaria)) : ?>
                                        <?php foreach ($datosVeterinaria as $veterinaria):  ?>
                                            <option value="<?= $veterinaria['id_veterinaria'] ?>"><?= $veterinaria['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
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
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardVeterinaria.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>