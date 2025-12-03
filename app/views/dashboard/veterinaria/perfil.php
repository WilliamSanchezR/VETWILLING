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
    if ($rol === '1') {
        include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    } else {
        include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';
    }
    // <!-- PANEL DERECHO -->
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        if ($rol === '1') {
            include_once __DIR__ . '/../../layouts/panel_superior_administrador.php';
        } else {
            include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        }
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


                    <!-- Mascotas -->
                    <?php if ($rol !== '1') : ?>
                        <div class="col-md-4">
                            <div class="mascota">
                                <h2>
                                    Mascotas
                                    <a href="#" aria-label="Editar mascotas"><i class="bi bi-pencil-square"></i></a>
                                </h2>
                                <p><span>Perro: </span>Rocky</p>
                                <p><span>Gato: </span>Pelusa</p>
                                <p><span>Vaca: </span>Lechera</p>
                                <p><span>Loro: </span>Magola</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($rol !== '1') : ?>
                    <!-- Sección de Citas y Notas -->
                    <div class="row g-3">
                        <!-- Citas y Tratamientos -->
                        <div class="col-md-6">
                            <div class="citas">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                            aria-selected="true">
                                            Citas <span>(2)</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-contact" type="button" role="tab"
                                            aria-controls="pills-contact" aria-selected="false">
                                            Plan de Tratamientos <span>(2)</span>
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    <!-- Tab de Citas -->
                                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                                        aria-labelledby="pills-home-tab" tabindex="0">

                                        <!-- Cita 1 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:00 - 11:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-agendado">Agendado</h4>
                                            </div>
                                        </div>

                                        <!-- Cita 2 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:30 - 12:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-atendido">Atendido</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab de Tratamientos -->
                                    <div class="tab-pane fade" id="pills-contact" role="tabpanel"
                                        aria-labelledby="pills-contact-tab" tabindex="0">

                                        <!-- Tratamiento 1 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:00 - 11:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-agendado">Agendado</h4>
                                            </div>
                                        </div>

                                        <!-- Tratamiento 2 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:30 - 12:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-atendido">Atendido</h4>
                                            </div>
                                        </div>

                                        <!-- Tratamiento 3 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:30 - 12:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-atendido">Atendido</h4>
                                            </div>
                                        </div>

                                        <!-- Tratamiento 5 -->
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span>11:30 - 12:30</span>
                                                <h4>26-Sep-2025</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4>Baño y Peluqueria</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Doctor:</span>
                                                <h4>Chapatin</h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="estado-atendido">Atendido</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documentos y Notas -->
                        <div class="col-md-6">
                            <div class="notas">
                                <div class="row g-3">
                                    <!-- Documentos -->
                                    <div class="col-12">
                                        <div class="card-documentos">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="fw-semibold mb-0">Documentos</h5>
                                                <button class="btn btn-outline-success btn-sm fw-semibold">
                                                    <i class="bi bi-download me-1"></i>Descargar
                                                </button>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Radiografía.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Orden_medica_medicamentos.pdf</span>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted">123 kb</small>
                                                    </div>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Resumen_consulta.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Carnet_vacunas.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Notas -->
                                    <div class="col-12">
                                        <div class="card-notas">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="fw-semibold mb-0">Notas</h5>
                                                <button class="btn btn-outline-success btn-sm fw-semibold">
                                                    <i class="bi bi-download me-1"></i>Descargar
                                                </button>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Consulta_Dic.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Consulta_Nov.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Alimentacion.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                                <li
                                                    class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span>Cuidado.pdf</span>
                                                    </div>
                                                    <small class="text-muted">123 kb</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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

                        <form action="" method="post">
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

                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success">Guardar <i class="bi bi-floppy"></i></button>
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