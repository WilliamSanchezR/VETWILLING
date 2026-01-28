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
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

</head>

<body>

    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';

    // <!-- PANEL DERECHO -->
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">
            <div class="container">
                <!-- Sección de información del usuario -->
                <div class="row g-3 mb-4">
                    <!-- Foto y datos básicos -->
                    <div class="col-md-4">
                        <div class="foto">
                            <img src="<?= BASE_URL ?>/public/uploads/veterinarios/<?= $usuario['img_perfil'] ?>" class="fotito"
                                alt="Pedro Perez" width="100">
                            <h3><?= $usuario['nombres'] ?> <br> <?= $usuario['apellidos'] ?></h3>
                            <h4><span>+57</span> <?= $usuario['telefono'] ?></h4>
                            <h5><?= $usuario['email'] ?></h5>
                            <div class="actions">
                                <button class="btn_change_password" data-bs-toggle="modal" data-bs-target="#exampleModal">Cambiar contraseña</button>
                            </div>
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

                    <!-- Especialidades -->
                    <div class="col-md-4">
                        <div class="especialidades">
                            <h2>
                                Especialidades
                                <a href="#" aria-label="Editar especialidades"><i class="bi bi-pencil-square"></i></a>
                            </h2>
                            <div class="mt-3">
                                <span class="especialidad-tag"><i class="bi bi-heart-pulse-fill me-2"></i>Cirugía General</span>
                                <span class="especialidad-tag"><i class="bi bi-clipboard2-pulse-fill me-2"></i>Medicina Interna</span>
                                <span class="especialidad-tag"><i class="bi bi-capsule me-2"></i>Odontología</span>
                                <span class="especialidad-tag"><i class="bi bi-bandaid-fill me-2"></i>Traumatología</span>
                                <span class="especialidad-tag"><i class="bi bi-moisture me-2"></i>Dermatología</span>
                            </div>
                        </div>
                    </div>

                </div>



                <div class="row g-3 mb-4">
                    <!-- Estadísticas -->
                    <div class="col-md-8">
                        <div class="estadisticas">
                            <h2>Estadísticas del Mes</h2>
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3>42</h3>
                                        <p>Consultas</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3>15</h3>
                                        <p>Cirugías</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3>28</h3>
                                        <p>Seguimientos</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3>8</h3>
                                        <p>Emergencias</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Horario de Atención -->
                    <div class="col-md-4">
                        <div class="horarios">
                            <h2>
                                Horario de Atención
                                <a href="#" aria-label="Editar horarios"><i class="bi bi-pencil-square"></i></a>
                            </h2>
                            <p><span>Lunes a Viernes: </span>8:00 AM - 6:00 PM</p>
                            <p><span>Sábados: </span>9:00 AM - 2:00 PM</p>
                            <p><span>Domingos: </span>Emergencias</p>
                            <p><span>Consultorio: </span>Sala 3</p>
                            <p><span>Teléfono Directo: </span>Ext. 103</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Citas y Consultas -->
                <div class="row g-3">
                    <!-- Agenda del Día -->
                    <div class="col-md-6">
                        <div class="citas">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-agenda-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-agenda" type="button" role="tab" aria-controls="pills-agenda"
                                        aria-selected="true">
                                        Agenda de Hoy <span>(5)</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-cirugias-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-cirugias" type="button" role="tab"
                                        aria-controls="pills-cirugias" aria-selected="false">
                                        Cirugías Programadas <span>(3)</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <!-- Tab de Agenda -->
                                <div class="tab-pane fade show active" id="pills-agenda" role="tabpanel"
                                    aria-labelledby="pills-agenda-tab" tabindex="0">

                                    <!-- Cita 1 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>08:00 - 08:30</span>
                                            <h4>Hoy</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Max (Perro)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Servicio:</span>
                                            <h4>Consulta General</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-completado">Completado</h4>
                                        </div>
                                    </div>

                                    <!-- Cita 2 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>09:00 - 09:45</span>
                                            <h4>Hoy</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Luna (Gato)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Servicio:</span>
                                            <h4>Vacunación</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-confirmado">Confirmado</h4>
                                        </div>
                                    </div>

                                    <!-- Cita 3 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>10:30 - 11:15</span>
                                            <h4>Hoy</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Rocky (Perro)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Servicio:</span>
                                            <h4>Control Post-Operatorio</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-confirmado">Confirmado</h4>
                                        </div>
                                    </div>

                                    <!-- Cita 4 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>14:00 - 15:00</span>
                                            <h4>Hoy</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Coco (Ave)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Servicio:</span>
                                            <h4>Consulta Especializada</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-pendiente">Pendiente</h4>
                                        </div>
                                    </div>

                                    <!-- Cita 5 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>16:00 - 17:00</span>
                                            <h4>Hoy</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Mia (Conejo)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Servicio:</span>
                                            <h4>Revisión Dental</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-pendiente">Pendiente</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab de Cirugías -->
                                <div class="tab-pane fade" id="pills-cirugias" role="tabpanel"
                                    aria-labelledby="pills-cirugias-tab" tabindex="0">

                                    <!-- Cirugía 1 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>09:00 - 11:00</span>
                                            <h4>12-Nov-2025</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Thor (Perro)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Procedimiento:</span>
                                            <h4>Esterilización</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-confirmado">Confirmado</h4>
                                        </div>
                                    </div>

                                    <!-- Cirugía 2 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>14:00 - 16:00</span>
                                            <h4>13-Nov-2025</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Pelusa (Gato)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Procedimiento:</span>
                                            <h4>Extracción Dental</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-confirmado">Confirmado</h4>
                                        </div>
                                    </div>

                                    <!-- Cirugía 3 -->
                                    <div class="row mb-3 cita-item">
                                        <div class="col-6 col-md-3">
                                            <span>10:00 - 13:00</span>
                                            <h4>15-Nov-2025</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Paciente:</span>
                                            <h4>Simba (Perro)</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Procedimiento:</span>
                                            <h4>Cirugía de Cadera</h4>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <span>Estado:</span>
                                            <h4 class="estado-pendiente">Pendiente</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Consultas y Certificaciones -->
                    <div class="col-md-6">
                        <div class="consultas-notas">
                            <div class="row g-3">
                                <!-- Certificaciones -->
                                <div class="col-12">
                                    <div class="card-documentos">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-semibold mb-0"><i class="bi bi-award me-2"></i>Certificaciones</h5>
                                            <button class="btn btn-outline-success btn-sm fw-semibold">
                                                <i class="bi bi-plus-circle me-1"></i>Agregar
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Certificado_Cirugía_Avanzada.pdf</span>
                                                </div>
                                                <small class="text-muted">2023</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Especialización_Traumatología.pdf</span>
                                                </div>
                                                <small class="text-muted">2021</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Curso_Medicina_Interna_Felina.pdf</span>
                                                </div>
                                                <small class="text-muted">2022</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Certificado_Odontología_Veterinaria.pdf</span>
                                                </div>
                                                <small class="text-muted">2024</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Documentos Profesionales -->
                                <div class="col-12">
                                    <div class="card-notas">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-semibold mb-0"><i class="bi bi-folder me-2"></i>Documentos</h5>
                                            <button class="btn btn-outline-success btn-sm fw-semibold">
                                                <i class="bi bi-download me-1"></i>Descargar Todo
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Licencia_Profesional.pdf</span>
                                                </div>
                                                <small class="text-muted">245 kb</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Título_Profesional.pdf</span>
                                                </div>
                                                <small class="text-muted">312 kb</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Hoja_de_Vida.pdf</span>
                                                </div>
                                                <small class="text-muted">156 kb</small>
                                            </li>
                                            <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                    <span>Póliza_Seguro_Profesional.pdf</span>
                                                </div>
                                                <small class="text-muted">198 kb</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
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
                            <input type="hidden" name="accion" value="vet-constrasena">
                            <div class="container clave">
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
    </div>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/perfil.js"></script>



</body>

</html>