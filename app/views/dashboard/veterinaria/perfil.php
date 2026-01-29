<?php
require_once BASE_PATH . '/app/helpers/session_all.php';
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

$rol = $_SESSION['user']['id_rol'];
$id = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);

// Aquí puedes agregar consultas para obtener datos dinámicos
// $especialidades = obtenerEspecialidadesVeterinario($id);
// $estadisticas = obtenerEstadisticasMes($id);
// $citasHoy = obtenerCitasHoy($id);
// $cirugiasProgra = obtenerCirugiasProgramadas($id);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard del perfil de veterinario">
    <title>Perfil Veterinario - Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Estilos propios -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <?php
    // BARRA LATERAL IZQUIERDA
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->
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
                            <img src="<?= BASE_URL ?>/public/uploads/veterinarios/<?= htmlspecialchars($usuario['img_perfil']) ?>" 
                                 class="fotito"
                                 alt="Foto de perfil de <?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>" 
                                 width="100"
                                 onerror="this.src='<?= BASE_URL ?>/public/assets/img/default-avatar.png'">
                            <div class="avatar-edit" role="button" aria-label="Editar foto de perfil">
                                <i class="bi bi-camera-fill" style="color: white; font-size: 16px;"></i>
                            </div>
                            <h3><?= htmlspecialchars($usuario['nombres']) ?> <br> <?= htmlspecialchars($usuario['apellidos']) ?></h3>
                            <h4><span>+57</span> <?= htmlspecialchars($usuario['telefono']) ?></h4>
                            <h5><?= htmlspecialchars($usuario['email']) ?></h5>
                            <div class="actions">
                                <button class="btn_change_password" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalCambiarContrasena"
                                        aria-label="Abrir formulario para cambiar contraseña">
                                    Cambiar contraseña
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Información General -->
                    <div class="col-md-4">
                        <div class="info">
                            <h2>
                                Información General
                                <a href="#" aria-label="Editar información general" title="Editar información">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </h2>
                            <p><span>Dirección: </span><?= htmlspecialchars($usuario['direccion'] ?? 'No registrada') ?></p>
                            <p><span>Fecha de Registro: </span><?= isset($usuario['fecha_registro']) ? date('d - M - Y', strtotime($usuario['fecha_registro'])) : 'No disponible' ?></p>
                            <p><span>Correo: </span><?= htmlspecialchars($usuario['email']) ?></p>
                            <p><span>Teléfono: </span>+57 <?= htmlspecialchars($usuario['telefono']) ?></p>
                        </div>
                    </div>

                    <!-- Especialidades -->
                    <div class="col-md-4">
                        <div class="especialidades">
                            <h2>
                                Especialidades
                                <a href="#" aria-label="Editar especialidades" title="Editar especialidades">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </h2>
                            <div class="mt-3">
                                <?php
                                // Aquí deberías cargar las especialidades desde la base de datos
                                // Por ahora dejo las estáticas pero con un comentario para que sepas dónde cambiar
                                $especialidades_ejemplo = [
                                    ['nombre' => 'Cirugía General', 'icono' => 'bi-heart-pulse-fill'],
                                    ['nombre' => 'Medicina Interna', 'icono' => 'bi-clipboard2-pulse-fill'],
                                    ['nombre' => 'Odontología', 'icono' => 'bi-capsule'],
                                    ['nombre' => 'Traumatología', 'icono' => 'bi-bandaid-fill'],
                                    ['nombre' => 'Dermatología', 'icono' => 'bi-moisture']
                                ];
                                
                                foreach ($especialidades_ejemplo as $especialidad): 
                                ?>
                                    <span class="especialidad-tag">
                                        <i class="bi <?= $especialidad['icono'] ?> me-2"></i><?= htmlspecialchars($especialidad['nombre']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas y Horarios -->
                <div class="row g-3 mb-4">
                    <!-- Estadísticas -->
                    <div class="col-md-8">
                        <div class="estadisticas">
                            <h2>Estadísticas del Mes</h2>
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3><?= isset($estadisticas['consultas']) ? $estadisticas['consultas'] : '42' ?></h3>
                                        <p>Consultas</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3><?= isset($estadisticas['cirugias']) ? $estadisticas['cirugias'] : '15' ?></h3>
                                        <p>Cirugías</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3><?= isset($estadisticas['seguimientos']) ? $estadisticas['seguimientos'] : '28' ?></h3>
                                        <p>Seguimientos</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-card">
                                        <h3><?= isset($estadisticas['emergencias']) ? $estadisticas['emergencias'] : '8' ?></h3>
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
                                <a href="#" aria-label="Editar horarios de atención" title="Editar horarios">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </h2>
                            <p><span>Lunes a Viernes: </span><?= htmlspecialchars($usuario['horario_lv'] ?? '8:00 AM - 6:00 PM') ?></p>
                            <p><span>Sábados: </span><?= htmlspecialchars($usuario['horario_sabado'] ?? '9:00 AM - 2:00 PM') ?></p>
                            <p><span>Domingos: </span><?= htmlspecialchars($usuario['horario_domingo'] ?? 'Emergencias') ?></p>
                            <p><span>Consultorio: </span><?= htmlspecialchars($usuario['consultorio'] ?? 'Sala 3') ?></p>
                            <p><span>Teléfono Directo: </span><?= htmlspecialchars($usuario['extension'] ?? 'Ext. 103') ?></p>
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
                                    <button class="nav-link active" 
                                            id="pills-agenda-tab" 
                                            data-bs-toggle="pill"
                                            data-bs-target="#pills-agenda" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="pills-agenda"
                                            aria-selected="true">
                                        Agenda de Hoy <span>(5)</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" 
                                            id="pills-cirugias-tab" 
                                            data-bs-toggle="pill"
                                            data-bs-target="#pills-cirugias" 
                                            type="button" 
                                            role="tab"
                                            aria-controls="pills-cirugias" 
                                            aria-selected="false">
                                        Cirugías Programadas <span>(3)</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <!-- Tab de Agenda -->
                                <div class="tab-pane fade show active" 
                                     id="pills-agenda" 
                                     role="tabpanel"
                                     aria-labelledby="pills-agenda-tab" 
                                     tabindex="0">

                                    <?php
                                    // Aquí deberías cargar las citas desde la base de datos
                                    // Por ahora dejo ejemplos estáticos
                                    $citas_ejemplo = [
                                        ['hora' => '08:00 - 08:30', 'fecha' => 'Hoy', 'paciente' => 'Max (Perro)', 'servicio' => 'Consulta General', 'estado' => 'Completado', 'clase_estado' => 'estado-completado'],
                                        ['hora' => '09:00 - 09:45', 'fecha' => 'Hoy', 'paciente' => 'Luna (Gato)', 'servicio' => 'Vacunación', 'estado' => 'Confirmado', 'clase_estado' => 'estado-confirmado'],
                                        ['hora' => '10:30 - 11:15', 'fecha' => 'Hoy', 'paciente' => 'Rocky (Perro)', 'servicio' => 'Control Post-Operatorio', 'estado' => 'Confirmado', 'clase_estado' => 'estado-confirmado'],
                                        ['hora' => '14:00 - 15:00', 'fecha' => 'Hoy', 'paciente' => 'Coco (Ave)', 'servicio' => 'Consulta Especializada', 'estado' => 'Pendiente', 'clase_estado' => 'estado-pendiente'],
                                        ['hora' => '16:00 - 17:00', 'fecha' => 'Hoy', 'paciente' => 'Mia (Conejo)', 'servicio' => 'Revisión Dental', 'estado' => 'Pendiente', 'clase_estado' => 'estado-pendiente']
                                    ];

                                    foreach ($citas_ejemplo as $cita):
                                    ?>
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span><?= htmlspecialchars($cita['hora']) ?></span>
                                                <h4><?= htmlspecialchars($cita['fecha']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Paciente:</span>
                                                <h4><?= htmlspecialchars($cita['paciente']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Servicio:</span>
                                                <h4><?= htmlspecialchars($cita['servicio']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="<?= $cita['clase_estado'] ?>"><?= htmlspecialchars($cita['estado']) ?></h4>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Tab de Cirugías -->
                                <div class="tab-pane fade" 
                                     id="pills-cirugias" 
                                     role="tabpanel"
                                     aria-labelledby="pills-cirugias-tab" 
                                     tabindex="0">

                                    <?php
                                    // Aquí deberías cargar las cirugías desde la base de datos
                                    $cirugias_ejemplo = [
                                        ['hora' => '09:00 - 11:00', 'fecha' => '12-Nov-2025', 'paciente' => 'Thor (Perro)', 'procedimiento' => 'Esterilización', 'estado' => 'Confirmado', 'clase_estado' => 'estado-confirmado'],
                                        ['hora' => '14:00 - 16:00', 'fecha' => '13-Nov-2025', 'paciente' => 'Pelusa (Gato)', 'procedimiento' => 'Extracción Dental', 'estado' => 'Confirmado', 'clase_estado' => 'estado-confirmado'],
                                        ['hora' => '10:00 - 13:00', 'fecha' => '15-Nov-2025', 'paciente' => 'Simba (Perro)', 'procedimiento' => 'Cirugía de Cadera', 'estado' => 'Pendiente', 'clase_estado' => 'estado-pendiente']
                                    ];

                                    foreach ($cirugias_ejemplo as $cirugia):
                                    ?>
                                        <div class="row mb-3 cita-item">
                                            <div class="col-6 col-md-3">
                                                <span><?= htmlspecialchars($cirugia['hora']) ?></span>
                                                <h4><?= htmlspecialchars($cirugia['fecha']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Paciente:</span>
                                                <h4><?= htmlspecialchars($cirugia['paciente']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Procedimiento:</span>
                                                <h4><?= htmlspecialchars($cirugia['procedimiento']) ?></h4>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <span>Estado:</span>
                                                <h4 class="<?= $cirugia['clase_estado'] ?>"><?= htmlspecialchars($cirugia['estado']) ?></h4>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
                                            <h5 class="fw-semibold mb-0">
                                                <i class="bi bi-award me-2"></i>Certificaciones
                                            </h5>
                                            <button class="btn btn-outline-success btn-sm fw-semibold" 
                                                    aria-label="Agregar nueva certificación">
                                                <i class="bi bi-plus-circle me-1"></i>Agregar
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            // Aquí deberías cargar las certificaciones desde la base de datos
                                            $certificaciones_ejemplo = [
                                                ['nombre' => 'Certificado_Cirugía_Avanzada.pdf', 'anio' => '2023'],
                                                ['nombre' => 'Especialización_Traumatología.pdf', 'anio' => '2021'],
                                                ['nombre' => 'Curso_Medicina_Interna_Felina.pdf', 'anio' => '2022'],
                                                ['nombre' => 'Certificado_Odontología_Veterinaria.pdf', 'anio' => '2024']
                                            ];

                                            foreach ($certificaciones_ejemplo as $cert):
                                            ?>
                                                <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span><?= htmlspecialchars($cert['nombre']) ?></span>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($cert['anio']) ?></small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Documentos Profesionales -->
                                <div class="col-12">
                                    <div class="card-notas">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="fw-semibold mb-0">
                                                <i class="bi bi-folder me-2"></i>Documentos
                                            </h5>
                                            <button class="btn btn-outline-success btn-sm fw-semibold"
                                                    aria-label="Descargar todos los documentos">
                                                <i class="bi bi-download me-1"></i>Descargar Todo
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            // Aquí deberías cargar los documentos desde la base de datos
                                            $documentos_ejemplo = [
                                                ['nombre' => 'Licencia_Profesional.pdf', 'tamanio' => '245 kb'],
                                                ['nombre' => 'Título_Profesional.pdf', 'tamanio' => '312 kb'],
                                                ['nombre' => 'Hoja_de_Vida.pdf', 'tamanio' => '156 kb'],
                                                ['nombre' => 'Póliza_Seguro_Profesional.pdf', 'tamanio' => '198 kb']
                                            ];

                                            foreach ($documentos_ejemplo as $doc):
                                            ?>
                                                <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-file-earmark-pdf me-2 text-success"></i>
                                                        <span><?= htmlspecialchars($doc['nombre']) ?></span>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($doc['tamanio']) ?></small>
                                                </li>
                                            <?php endforeach; ?>
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
        <div class="modal fade modal-notificacion" 
             id="modalCambiarContrasena" 
             tabindex="-1" 
             aria-labelledby="modalCambiarContrasenaLabel" 
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalCambiarContrasenaLabel">Cambiar contraseña</h1>
                        <button type="button" 
                                class="btn-close" 
                                data-bs-dismiss="modal" 
                                aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <form action="<?= BASE_URL ?>/admin/actualizar-contrasena" 
                              method="POST" 
                              id="formCambiarContrasena"
                              novalidate>
                            <input type="hidden" name="id_usuario" value="<?= $id ?>">
                            <input type="hidden" name="accion" value="vet-contrasena">
                            
                            <div class="container clave">
                                <div class="row">
                                    <!-- Contraseña actual -->
                                    <div class="col-md-12">
                                        <div class="form-group password contrasena-actual">
                                            <label for="contrasena-actual" class="form-label">
                                                Contraseña actual: <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       id="contrasena-actual" 
                                                       name="contrasena-actual"
                                                       class="form-control"
                                                       required
                                                       autocomplete="current-password"
                                                       aria-describedby="contrasenaActualHelp" />
                                                <button type="button" 
                                                        class="icon-view btn btn-link position-absolute end-0 top-50 translate-middle-y"
                                                        data-target="contrasena-actual"
                                                        aria-label="Mostrar u ocultar contraseña actual">
                                                    <i class="bi bi-eye" data-visible="false"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                Por favor ingrese su contraseña actual
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nueva contraseña -->
                                    <div class="col-md-12">
                                        <div class="form-group password nueva-contrasena">
                                            <label for="nueva-contrasena" class="form-label">
                                                Nueva contraseña: <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       id="nueva-contrasena" 
                                                       name="nueva-contrasena"
                                                       class="form-control"
                                                       required
                                                       minlength="8"
                                                       autocomplete="new-password"
                                                       aria-describedby="nuevaContrasenaHelp" />
                                                <button type="button" 
                                                        class="icon-view btn btn-link position-absolute end-0 top-50 translate-middle-y"
                                                        data-target="nueva-contrasena"
                                                        aria-label="Mostrar u ocultar nueva contraseña">
                                                    <i class="bi bi-eye" data-visible="false"></i>
                                                </button>
                                            </div>
                                            <small id="nuevaContrasenaHelp" class="form-text text-muted">
                                                Mínimo 8 caracteres
                                            </small>
                                            <div class="invalid-feedback">
                                                La contraseña debe tener al menos 8 caracteres
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirmar contraseña -->
                                    <div class="col-md-12">
                                        <div class="form-group password confi-contrasena">
                                            <label for="confi-contrasena" class="form-label">
                                                Confirmar contraseña: <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       id="confi-contrasena"
                                                       name="confi-contrasena"
                                                       class="form-control"
                                                       required
                                                       autocomplete="new-password"
                                                       aria-describedby="confirmarContrasenaHelp" />
                                                <button type="button" 
                                                        class="icon-view btn btn-link position-absolute end-0 top-50 translate-middle-y"
                                                        data-target="confi-contrasena"
                                                        aria-label="Mostrar u ocultar confirmación de contraseña">
                                                    <i class="bi bi-eye" data-visible="false"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="errorConfirmacion">
                                                Las contraseñas no coinciden
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mensaje de error general -->
                                    <div class="col-12">
                                        <div id="mensajeError" class="alert alert-danger d-none" role="alert"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" 
                                        class="btn btn-secondary" 
                                        data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="btn btn-success">
                                    Guardar <i class="bi bi-floppy"></i>
                                </button>
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

    <!-- Scripts propios -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/perfil.js"></script>

</body>

</html>