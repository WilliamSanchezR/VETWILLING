<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario - Registro Veterinario</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css">

</head>

<body>

    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';

    // <!-- PANEL DERECHO -->
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-heart-pulse-fill"></i>
                <h2>Registro de Veterinario</h2>
                <p class="text-muted">Complete todos los campos requeridos para registrar un nuevo Veterinario</p>
            </div>

            <div class="progress-wrapper">
                <div class="progress">
                    <div id="bar1" class="progress-bar active"></div>
                    <!-- <div id="bar2" class="progress-bar"></div>
                    <div id="bar3" class="progress-bar"></div>
                    <div id="bar4" class="progress-bar"></div>
                    <div id="bar5" class="progress-bar"></div> -->
                    <div id="bar6" class="progress-bar"></div>
                </div>
                <div class="progress-labels">
                    <span class="active">Propietario</span>
                    <!-- <span>Mascota</span>
                    <span>Historial</span>
                    <span>Atención</span>
                    <span>Tratamiento</span> -->
                    <span>Confirmar</span>
                </div>
            </div>

            <form id="vetForm" action="<?= BASE_URL ?>/veterinario/guardar-veterinario" method="POST" enctype="multipart/form-data">

                <!-- Paso 1: Datos del Veterinario -->
                <div class="step active">
                    <h3><i class="bi bi-person-badge me-2"></i>Datos del Veterinario</h3>

                    <!-- INFORMACIÓN PERSONAL -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombres *</label>
                                <input type="text" name="nombres" required placeholder="Ej: Juan Carlos">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Apellidos *</label>
                                <input type="text" name="apellidos" required placeholder="Ej: Pérez García">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                <select name="tipo_documento" required>
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
                                <input type="text" name="numero_documento" required placeholder="Ej: 1234567890" maxlength="20">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" id="correo" name="email" required placeholder="ejemplo@correo.com">
                            </div>
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Foto </label>
                                <input type="file" accept=".jpg, .png, .jpeg" id="img_perfil" name="img_perfil">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Telefono *</label>
                                <input type="number" id="telefono" name="telefono" required placeholder="313 407 2068">
                            </div>
                            <div class="form-group">
                                <label><i class="bi bi-card-checklist"></i> Número de licencia profesional</label>
                                <input type="text" name="numero_licencia_profesional" placeholder="Ej: 12345">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-building"></i> Ciudad *</label>
                                <input type="text" id="ciudad" required placeholder="Bogotá">
                            </div>
                        </div>
                    </div>

                    <div class="buttons">
                        <span></span>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Datos de la Mascota
                <div class="step">
                    <h3><i class="bi bi-heart me-2"></i>Datos de la Mascota</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-tag"></i> Nombre de la mascota *</label>
                                <input type="text" id="nombreMascota" required placeholder="Ej: Max">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-palette"></i> Especie *</label>
                                <select id="especie" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Perro">Perro</option>
                                    <option value="Gato">Gato</option>
                                    <option value="Ave">Ave</option>
                                    <option value="Conejo">Conejo</option>
                                    <option value="Hamster">Hamster</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-award"></i> Raza</label>
                                <input type="text" id="raza" placeholder="Ej: Labrador">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" name="email" required placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                    </div>

                    <div class="buttons">
                        <span></span>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
        </div>

        <!-- Paso de confirmación -->
                <div class="step">
                    <h1>¿Deseas confirmar el envío del formulario?</h1>
                    <p>Por favor, revisa que toda la información sea correcta antes de continuar.</p>

                    <!-- RESUMEN DE DATOS -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle"></i> Resumen de Datos</h5>
                        <div id="resumenDatos">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">
                            <i class="bi bi-arrow-left"></i> Volver a revisar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Confirmar y enviar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>