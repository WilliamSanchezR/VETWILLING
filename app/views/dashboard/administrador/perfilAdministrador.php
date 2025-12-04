<?php
require_once BASE_PATH . '/app/helpers/session_all.php';
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

$rol = $_SESSION['user']['id_rol'];
$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">


</head>

<body>

    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';

    // <!-- PANEL DERECHO -->
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php';

        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">
            <div class="container">
                <!-- Sección de información del usuario -->
                <div class="row g-3 mb-4">
                    <!-- Foto y datos básicos -->
                    <div class="col-md-4">
                        <div class="foto">
                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" class="fotito"
                                alt="Pedro Perez" width="100">
                            <h3><?= $usuario['nombres'] ?> <br> <?= $usuario['apellidos'] ?></h3>
                            <h4><span>+57</span> <?= $usuario['telefono'] ?></h4>
                            <h5><?= $usuario['email'] ?></h5>
                        </div>
                    </div>

                    <!-- Información General -->
                    <div class="col-md-4">
                        <div class="info">
                            <h2>
                                Información General
                                <a href="#" aria-label="Editar información"><i class="bi bi-pencil-square"></i></a>
                            </h2>
                            <p><span>Dirección: </span>Calle 6 # 23-34</p>
                            <p><span>Fecha de Registro: </span>20 - Ago - 2025</p>
                            <p><span>Correo: </span>pepitoperaz@gmail.com</p>
                            <p><span>Teléfono: </span>+57 312 405 5678</p>
                        </div>
                    </div>

                    <!-- Tarjeta para contraseña -->
                    <div class="col-md-4">
                        <div class="info">
                            <h2>
                                Seguaridad

                            </h2>
                            <div class="actions">
                                <button class="btn_change_password" data-bs-toggle="modal" data-bs-target="#exampleModal">Cambiar contraseña</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para cambiar contraseña -->
        <div class="modal fade modal-notificacion" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Cambiar contraseña</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <form action="<?= BASE_URL ?>/admin/actualizar-contrasena" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $id ?>">
                            <input type="hidden" name="accion" value="actualizar-constrasena">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group password contrasena-actual">
                                            <label for="contrasena-actual">Contraseña actual:</label>
                                            <input type="password" id="contrasena-actual" name="contrasena-actual" />
                                            <input type="checkbox" id="ver_contrasena-actual" class="ver" onChange="hideOrShowPassword(this)" />
                                            <button type="button" class="icon-view">
                                                <i class="bi bi-eye" id="contrasena-actual-visible"></i>
                                                <i class="bi bi-eye-slash" style="display: none;" id="contrasena-actual-hidden"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group password nueva-contrasena">
                                            <label for="nueva-contrasena">Nueva contraseña:</label>
                                            <input type="password" id="nueva-contrasena" name="nueva-contrasena" />

                                            <input type="checkbox" id="ver_nueva-contrasena" class="ver" onChange="hideOrShowPassword(this)" />
                                            <button type="button" class="icon-view">
                                                <i class="bi bi-eye" id="nueva-contrasena-visible"></i>
                                                <i class="bi bi-eye-slash" style="display: none;" id="nueva-contrasena-hidden"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-group password confi-contrasena">
                                            <label for="confi-contrasena">Confirmar contraseña:</label>
                                            <input type="password" id="confi-contrasena"
                                                name="confi-contrasena" />

                                            <input type="checkbox" id="ver_confi-contrasena" class="ver" onChange="hideOrShowPassword(this)" />
                                            <button type="button" class="icon-view">
                                                <i class="bi bi-eye" id="confi-contrasena-visible"></i>
                                                <i class="bi bi-eye-slash" style="display: none;" id="confi-contrasena-hidden"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Guardar <i class="bi bi-floppy"></i></button>
                            </div>
                        </form>
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
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/perfil.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>


</body>

</html>