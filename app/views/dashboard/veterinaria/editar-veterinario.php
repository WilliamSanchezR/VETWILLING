<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';

// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos

require_once BASE_PATH . '/app/controllers/veterinarioController.php';



// asignamos el valo id del registro segun la tabla

$id = $_GET['id'];

// Llamamos la funcion especifica que existe en dicho controlador y le pasamos los datos a una variable que podamos manipular en este archivo

$veterinario = listarVeterinario($id);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modulo edicion datos veterinario</title>

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
                <i class="bi bi-feather"></i>
                <h2>Edicion datos Veterinario</h2>
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

            <form id="vetForm" action="<?= BASE_URL ?>/veterinario/actualizar-veterinario" method="POST">
                <input type="hidden" name="id_usuario" value="<?= $veterinario['id_usuario'] ?>">
                <input type="hidden" name="accion" value="actualizar">
                <!-- Paso 1: Datos del Propietario -->
                <div class="step active">
                    <h3><i class="bi bi-person-badge me-2"></i>Datos del veterinario</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombres</label>
                                <input type="text" id="nombrePropietario" name="nombres" value="<?= $veterinario['nombres'] ?>" placeholder="Ej: Juan Pérez García">
                            </div>
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Apellidos</label>
                                <input type="text" id="apellidoPropietario" name="apellidos" value="<?= $veterinario['apellidos'] ?>" placeholder="Ej: García">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                <select id="tipoDocumento" name="tipo_documento">
                                    <option value="<?= $veterinario['tipo_documento'] ?>"><?= $veterinario['tipo_documento'] ?></option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Rol</label>
                                <select id="rol" name="id_rol">
                                    <option value="<?= $veterinario['id_rol'] ?>"><?= $veterinario['id_rol'] ?></option>
                                    <option value="2">Administrador</option>
                                    <option value="1">Veterinario</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Estado</label>
                                <select id="estado" name="estado">
                                    <option value="<?= $veterinario['estado'] ?>"><?= $veterinario['estado'] ?></option>
                                    <option value="Activo">Activo</option>
                                    <option value="Bloqueado">Bloqueado</option>
                                </select>
                            </div>
                                      
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Número de documento *</label>
                                <input type="number" id="documento" name="numero_documento" value="<?= $veterinario['numero_documento'] ?>" placeholder="12345678">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" value="<?= $veterinario['telefono'] ?>" placeholder="+57 300 123 4567">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email" id="correo" name="email" value="<?= $veterinario['email'] ?>" placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                    </div>



                    <div class="buttons">
                        <span></span>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Editar datos <i class="bi bi-arrow-right"></i>
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

                <-- Paso 3: Historial Médico -->
                <!-- <div class="step">
                    <h3><i class="bi bi-file-medical me-2"></i>Historial Médico</h3>

                    <div class="form-group">
                        <label><i class="bi bi-shield-check"></i> Vacunas aplicadas</label>
                        <textarea id="vacunas" rows="3"
                            placeholder="Ej: Rabia (2024), Parvovirus (2023), Triple Felina..."></textarea>
                        <small class="form-text">Incluya nombre de vacuna y año de aplicación</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-calendar-event"></i> Última visita al veterinario</label>
                                <input type="date" id="ultimaVisita">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-calendar-check"></i> Última desparasitación</label>
                                <input type="date" id="ultimaDesparasitacion">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-bandaid"></i> Enfermedades previas</label>
                        <textarea id="enfermedades" rows="3"
                            placeholder="Ej: Otitis (2023), Parásitos intestinales (2022)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-exclamation-triangle"></i> Alergias conocidas</label>
                        <textarea id="alergias" rows="2"
                            placeholder="Ej: Alérgico a penicilina, pollo, etc."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-capsule"></i> Medicamentos actuales</label>
                        <textarea id="medicamentos" rows="3"
                            placeholder="Ej: Antibiótico X, 1 tableta cada 12 horas..."></textarea>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div> -->

                <!-- Paso 4: Información de Atención -->
                <!-- <div class="step">
                    <h3><i class="bi bi-clipboard-check me-2"></i>Información de Atención</h3>

                    <div class="form-group">
                        <label><i class="bi bi-chat-left-text"></i> Motivo de consulta *</label>
                        <textarea id="motivoConsulta" rows="4" required
                            placeholder="Describa el motivo principal de la visita..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-calendar-plus"></i> Fecha de ingreso *</label>
                                <input type="date" id="fechaIngreso" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-alarm"></i> Hora de ingreso *</label>
                                <input type="time" id="horaIngreso" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-person-badge-fill"></i> Veterinario asignado *</label>
                        <select id="veterinario" required>
                            <option value="">Seleccione un veterinario...</option>
                            <option value="Dr. Carlos Martínez">Dr. Carlos Martínez</option>
                            <option value="Dra. Ana López">Dra. Ana López</option>
                            <option value="Dr. Pedro Ramírez">Dr. Pedro Ramírez</option>
                            <option value="Dra. María González">Dra. María González</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-bar-chart-line"></i> Prioridad de atención *</label>
                        <select id="prioridad" required>
                            <option value="">Seleccione...</option>
                            <option value="Baja">Baja - Control de rutina</option>
                            <option value="Media">Media - Requiere atención pronta</option>
                            <option value="Alta">Alta - Urgencia</option>
                            <option value="Crítica">Crítica - Emergencia</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-thermometer-half"></i> Síntomas observados</label>
                        <textarea id="sintomas" rows="3"
                            placeholder="Ej: Fiebre, vómito, diarrea, inapetencia..."></textarea>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div> -->

                <!-- Paso 5: Tratamiento y Observaciones -->
                <!-- <div class="step">
                    <h3><i class="bi bi-clipboard2-pulse me-2"></i>Tratamiento y Observaciones</h3>

                    <div class="form-group">
                        <label><i class="bi bi-journal-medical"></i> Diagnóstico</label>
                        <textarea id="diagnostico" rows="3" placeholder="Diagnóstico del veterinario..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-prescription"></i> Tratamiento recomendado</label>
                        <textarea id="tratamiento" rows="4"
                            placeholder="Ej: Antibiótico X cada 12 horas por 7 días, reposo absoluto..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-calendar-heart"></i> Próxima cita</label>
                                <input type="date" id="proximaCita">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-clock"></i> Hora de próxima cita</label>
                                <input type="time" id="horaProximaCita">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="bi bi-cash-stack"></i> Costo del servicio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="costoServicio" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-credit-card"></i> Método de pago</label>
                        <select id="metodoPago">
                            <option value="">Seleccione...</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta de crédito/débito</option>
                            <option value="Transferencia">Transferencia bancaria</option>
                            <option value="PSE">PSE</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="bi bi-sticky-fill"></i> Observaciones adicionales</label>
                        <textarea id="observaciones" rows="3"
                            placeholder="Cualquier información adicional relevante..."></textarea>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div> -->

                <!--Paso de confirmación -->
                <div class="step">
                    <h1>¿Deseas confirmar el envío del formulario?</h1>
                    <p>Por favor, revisa que toda la información sea correcta antes de continuar.</p>

                    <div class="buttons">
                        <button href="<?= BASE_URL ?>/veterinario/consultar-veterinario" type="button" class="btn btn-secondary" id="btnVolver">Volver a revisar</button>
                        <button type="submit" class="btn btn-success" id="btnConfirmar">Confirmar y enviar</button>
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