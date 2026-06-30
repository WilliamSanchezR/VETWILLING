<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Paciente</title>

    <!-- Icono de la página -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Tipografías (Fredoka + Open Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400..600&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Propio -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">

    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'; ?>

        <!-- FORMULARIO DE REGISTRO -->
        <div class="wizard-container">

            <div class="wizard-header">
                <span class="wizard-chip"><i class="bi bi-clipboard2-pulse"></i></span>
                <h2>Registro de paciente</h2>
                <p>Completa los datos del propietario y sus mascotas para crear el historial.</p>
            </div>

            <!-- Stepper -->
            <div class="wz-progress">
                <div class="wz-steps" role="list" aria-label="Progreso del registro">
                    <div class="wz-track"><div class="wz-track-fill" id="wzFill"></div></div>
                    <div class="wz-step is-active" role="listitem">
                        <span class="wz-step-num">1</span>
                        <span class="wz-step-label">Propietario</span>
                    </div>
                    <div class="wz-step" role="listitem">
                        <span class="wz-step-num">2</span>
                        <span class="wz-step-label">Mascota</span>
                    </div>
                    <div class="wz-step" role="listitem">
                        <span class="wz-step-num">3</span>
                        <span class="wz-step-label">Confirmar</span>
                    </div>
                </div>
            </div>

            <!-- novalidate: la validación la maneja el JS por pasos -->
            <form id="vetForm" novalidate>

                <!-- Paso 1: Datos del propietario -->
                <div class="step active">
                    <h3><i class="bi bi-person-badge"></i> Datos del propietario</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombres *</label>
                                <input type="text" id="nombres" required placeholder="Ej: Juan Carlos">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Apellidos *</label>
                                <input type="text" id="apellidos" required placeholder="Ej: Pérez García">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                <select id="tipoDocumento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC">CC - Cédula de Ciudadanía</option>
                                    <option value="TI">TI - Tarjeta de Identidad</option>
                                    <option value="CE">CE - Cédula de Extranjería</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Número de documento *</label>
                                <input type="text" id="numeroDocumento" required
                                       inputmode="numeric" maxlength="12"
                                       placeholder="12345678">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" required
                                       inputmode="tel" maxlength="20"
                                       placeholder="+57 300 123 4567">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" id="email" required
                                       inputmode="email"
                                       placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-geo-alt"></i> Dirección completa *</label>
                        <input type="text" id="direccion" required placeholder="Calle 12 # 34-56, Apto 102">
                    </div>

                    <div class="buttons">
                        <span></span>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Datos de la mascota -->
                <div class="step">
                    <h3><i class="bi bi-heart-pulse"></i> Datos de la mascota</h3>
                    <p class="step-sub">Puedes registrar varias mascotas para el mismo propietario.</p>

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
                                    <option value="Canino">Canino</option>
                                    <option value="Felino">Felino</option>
                                    <option value="Ave">Ave</option>
                                    <option value="Roedor">Roedor</option>
                                    <option value="Reptil">Reptil</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-award"></i> Raza *</label>
                                <input type="text" id="raza" required placeholder="Ej: Labrador">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-gender-ambiguous"></i> Sexo *</label>
                                <select id="sexo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Macho">Macho</option>
                                    <option value="Hembra">Hembra</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-cake2"></i> Edad (cantidad) *</label>
                                <input type="number" id="edadNumero" required min="0" max="99" placeholder="3">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-calendar"></i> Unidad de tiempo *</label>
                                <select id="edadUnidad" required>
                                    <option value="">Seleccione...</option>
                                    <option value="años">Años</option>
                                    <option value="meses">Meses</option>
                                    <option value="semanas">Semanas</option>
                                    <option value="días">Días</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mascotas-acciones">
                        <button type="button" class="btn-add-mascota" id="btnAgregarMascota">
                            <i class="bi bi-plus-circle"></i> Agregar mascota
                        </button>
                    </div>

                    <div class="mascotas-agregadas" id="mascotasAgregadasContainer">
                        <h4><i class="bi bi-list-check"></i> Mascotas agregadas</h4>
                        <div id="listaMascotasAgregadas" class="lista-mascotas-agregadas">
                            <div class="empty-mascotas"><i class="bi bi-clipboard-heart"></i> Aún no hay mascotas agregadas.</div>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 3: Confirmación -->
                <div class="step">
                    <h3><i class="bi bi-check2-circle"></i> Confirma el registro</h3>
                    <p class="step-sub">Revisa que toda la información sea correcta antes de continuar.</p>

                    <div class="buttons">
                        <button type="button" class="btn-prev" id="btnVolver">
                            <i class="bi bi-arrow-left"></i> Volver a revisar
                        </button>
                        <button type="submit" class="btn-submit" id="btnConfirmar">
                            <i class="bi bi-send-check"></i> Confirmar y enviar
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/registroPacientes.js"></script>

</body>

</html>