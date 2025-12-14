<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio - Registro Paciente</title>
    <!-- Icono de la página -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Propio -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">


    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Aqui va el include -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'
    ?>


    <!-- PANEL DERECHO -->
    <!-- aqui va el inclunde notifi -->
    <?php
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>

        <!-- FORMULARIO DE REGISTRO -->
        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-flask"></i>
                <h2>Registro de Paciente Laboratorio</h2>
                <p class="text-muted">Complete todos los campos requeridos para registrar un nuevo paciente</p>
            </div>

            <div class="progress-wrapper">
                <div class="progress">
                    <div id="bar1" class="progress-bar active"></div>
                    <div id="bar2" class="progress-bar"></div>
                    <div id="bar3" class="progress-bar"></div>
                    <div id="bar4" class="progress-bar"></div>
                </div>
                <div class="progress-labels">
                    <span class="active">Propietario</span>
                    <span>Mascota</span>
                    <span>Laboratorio</span>
                    <span>Confirmar</span>
                </div>
            </div>

            <form id="vetForm">

                <!-- Paso 1: Datos del Propietario -->
                <div class="step active">
                    <h3><i class="bi bi-person-badge me-2"></i>Datos del Propietario</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombre completo *</label>
                                <input type="text" id="nombrePropietario" required placeholder="Ej: Juan Pérez García">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                <select id="tipoDocumento" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Número de documento *</label>
                                <input type="number" id="documento" required placeholder="12345678">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" required placeholder="+57 300 123 4567">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" id="correo" required placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-geo-alt"></i> Dirección completa *</label>
                        <input type="text" id="direccion" required placeholder="Calle 12 # 34-56, Apto 102">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-building"></i> Ciudad *</label>
                                <input type="text" id="ciudad" required placeholder="Bogotá">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-map"></i> Barrio</label>
                                <input type="text" id="barrio" placeholder="Chapinero">
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

                <!-- Paso 2: Datos de la Mascota -->
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
                                <label><i class="bi bi-palette-fill"></i> Color / Señas particulares</label>
                                <input type="text" id="color" placeholder="Ej: Blanco con manchas negras">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="bi bi-cake2"></i> Edad (años) *</label>
                                <input type="number" id="edad" required min="0" max="30" placeholder="3">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="bi bi-speedometer2"></i> Peso (kg) *</label>
                                <input type="number" id="peso" required step="0.1" min="0" placeholder="15.5">
                            </div>
                        </div>
                        <div class="col-md-4">
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
                                <label><i class="bi bi-scissors"></i> ¿Esterilizado/Castrado? *</label>
                                <select id="esterilizado" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-upc-scan"></i> Microchip (si aplica)</label>
                                <input type="text" id="microchip" placeholder="123456789012345">
                            </div>
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

                <!-- Paso 3: Historial Médico -->
                <div class="step">
                    <h3><i class="bi bi-file-medical me-2"></i>Registro Laboratorios</h3>

                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarExamenes" placeholder="Buscar Examenes" autocomplete="off">
                        <ul class="lista-sugerencias autocomplete-items" id="listaSugerencias"></ul>
                    </div>

                    <div class="contenedor-tabla-laboratorio">
                        <table class="tabla-laboratorio" id="lista-laboratorios">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Laboratorio</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody id="cont-laboratorio">
                            </tbody>
                        </table>
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

                <!--Paso de confirmación -->
                <div class="step">
                    <h1>¿Deseas confirmar el envío del formulario?</h1>
                    <p>Por favor, revisa que toda la información sea correcta antes de continuar.</p>

                    <div class="buttons">
                        <button type="button" class="btn btn-secondary" id="btnVolver">Volver a revisar</button>
                        <button type="submit" class="btn btn-success" id="btnConfirmar">Confirmar y enviar</button>
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
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/registroPacientesLaboratorio.js"></script>


</body>

</html>