<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

// Obtener las mascotas del propietario actual
$mascotas = listarMascotas();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - VetWilling</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS Personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

    <style>
        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTILOS PARA FORMULARIO DE AGENDAR CITA                   */
        /* ═══════════════════════════════════════════════════════════ */
        
        .agendar-cita-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header-agendar {
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            border-radius: 20px;
            padding: 40px 30px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(10, 147, 44, 0.2);
            position: relative;
            overflow: hidden;
        }

        .header-agendar::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 0;
        }

        .header-agendar-content {
            position: relative;
            z-index: 1;
        }

        .header-agendar h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-agendar h1 i {
            font-size: 36px;
        }

        .header-agendar p {
            font-size: 16px;
            margin: 0;
            opacity: 0.95;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .form-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #00304D;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0a932c;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-section-title i {
            font-size: 24px;
            color: #0a932c;
        }

        .form-group-custom {
            margin-bottom: 25px;
        }

        .form-label-custom {
            display: block;
            font-weight: 600;
            color: #00304D;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .form-label-custom i {
            margin-right: 8px;
            color: #0a932c;
        }

        .form-label-custom .required {
            color: #e74c3c;
            margin-left: 3px;
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #0a932c;
            background: white;
            box-shadow: 0 0 0 4px rgba(10, 147, 44, 0.1);
        }

        .form-control-custom:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
            opacity: 0.6;
        }

        textarea.form-control-custom {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-helper-text {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-helper-text i {
            font-size: 12px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TARJETAS DE MASCOTAS SELECCIONABLES                        */
        /* ═══════════════════════════════════════════════════════════ */

        .mascotas-grid-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .mascota-card-selector {
            position: relative;
            background: white;
            border: 3px solid #e0e0e0;
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .mascota-card-selector:hover {
            border-color: #0a932c;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(10, 147, 44, 0.15);
        }

        .mascota-card-selector.selected {
            border-color: #0a932c;
            background: linear-gradient(135deg, rgba(10, 147, 44, 0.05) 0%, rgba(10, 147, 44, 0.1) 100%);
            box-shadow: 0 8px 20px rgba(10, 147, 44, 0.2);
        }

        .mascota-card-selector.selected::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: #0a932c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .mascota-avatar-selector {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 3px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .mascota-card-selector:hover .mascota-avatar-selector,
        .mascota-card-selector.selected .mascota-avatar-selector {
            border-color: #0a932c;
        }

        .mascota-nombre-selector {
            font-weight: 700;
            font-size: 16px;
            color: #00304D;
            margin-bottom: 5px;
        }

        .mascota-especie-selector {
            font-size: 14px;
            color: #666;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  BOTONES DE ACCIÓN                                          */
        /* ═══════════════════════════════════════════════════════════ */

        .botones-accion {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn-cancelar {
            padding: 14px 30px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #666;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-cancelar:hover {
            border-color: #ccc;
            background: #f5f5f5;
            color: #333;
        }

        .btn-agendar {
            padding: 14px 40px;
            border: none;
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            color: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(10, 147, 44, 0.3);
        }

        .btn-agendar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10, 147, 44, 0.4);
        }

        .btn-agendar:active {
            transform: translateY(0);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  INDICADOR DE PASO ACTUAL (OPCIONAL)                        */
        /* ═══════════════════════════════════════════════════════════ */

        .steps-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .steps-indicator::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .step-item.active .step-circle,
        .step-item.completed .step-circle {
            background: #0a932c;
            color: white;
            box-shadow: 0 4px 12px rgba(10, 147, 44, 0.3);
        }

        .step-label {
            font-size: 13px;
            color: #999;
            font-weight: 600;
        }

        .step-item.active .step-label,
        .step-item.completed .step-label {
            color: #0a932c;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  DARK MODE                                                  */
        /* ═══════════════════════════════════════════════════════════ */

        body.dark-mode .form-card {
            background: #1e1e1e;
        }

        body.dark-mode .form-section-title {
            color: #ffffff;
        }

        body.dark-mode .form-label-custom {
            color: #dddddd;
        }

        body.dark-mode .form-control-custom {
            background: #2d2d2d;
            border-color: #444;
            color: #ffffff;
        }

        body.dark-mode .form-control-custom:focus {
            background: #333;
            border-color: #0a932c;
        }

        body.dark-mode .mascota-card-selector {
            background: #2d2d2d;
            border-color: #444;
        }

        body.dark-mode .mascota-card-selector:hover {
            background: #333;
        }

        body.dark-mode .mascota-card-selector.selected {
            background: rgba(10, 147, 44, 0.2);
            border-color: #0a932c;
        }

        body.dark-mode .mascota-nombre-selector {
            color: #ffffff;
        }

        body.dark-mode .btn-cancelar {
            background: #2d2d2d;
            border-color: #444;
            color: #dddddd;
        }

        body.dark-mode .btn-cancelar:hover {
            background: #3d3d3d;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  RESPONSIVE                                                 */
        /* ═══════════════════════════════════════════════════════════ */

        @media (max-width: 768px) {
            .header-agendar {
                padding: 30px 20px;
            }

            .header-agendar h1 {
                font-size: 24px;
            }

            .form-card {
                padding: 25px 20px;
            }

            .mascotas-grid-selector {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }

            .botones-accion {
                flex-direction: column;
            }

            .btn-cancelar,
            .btn-agendar {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <div class="agendar-cita-container">

                <!-- HEADER -->
                <div class="header-agendar">
                    <div class="header-agendar-content">
                        <h1>
                            <i class="bi bi-calendar-plus"></i>
                            Agendar Nueva Cita
                        </h1>
                        <p>Programa una cita veterinaria para tu mascota de forma rápida y sencilla</p>
                    </div>
                </div>

                <!-- INDICADOR DE PASOS (OPCIONAL) -->
                <div class="steps-indicator">
                    <div class="step-item active" id="step1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Mascota</div>
                    </div>
                    <div class="step-item" id="step2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Servicio</div>
                    </div>
                    <div class="step-item" id="step3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Fecha y Hora</div>
                    </div>
                    <div class="step-item" id="step4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Confirmar</div>
                    </div>
                </div>

                <!-- FORMULARIO -->
                <form id="formAgendarCita">

                    <!-- SECCIÓN 1: SELECCIONAR MASCOTA -->
                    <div class="form-card">
                        <div class="form-section-title">
                            <i class="bi bi-heart-fill"></i>
                            Selecciona tu Mascota
                        </div>

                        <?php if (empty($mascotas)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                No tienes mascotas registradas. 
                                <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="alert-link">
                                    Registra una mascota primero
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mascotas-grid-selector">
                                <?php foreach ($mascotas as $mascota): ?>
                                    <div class="mascota-card-selector" 
                                         data-mascota-id="<?= $mascota['id_paciente'] ?>"
                                         data-mascota-nombre="<?= htmlspecialchars($mascota['nombre']) ?>">
                                        <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $mascota['img_mascota'] ?>" 
                                             alt="<?= htmlspecialchars($mascota['nombre']) ?>"
                                             class="mascota-avatar-selector">
                                        <div class="mascota-nombre-selector">
                                            <?= htmlspecialchars($mascota['nombre']) ?>
                                        </div>
                                        <div class="mascota-especie-selector">
                                            <?= htmlspecialchars($mascota['especie']) ?> - <?= htmlspecialchars($mascota['raza']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="id_paciente" id="id_paciente" required>
                        <?php endif; ?>
                    </div>

                    <!-- SECCIÓN 2: SELECCIONAR SERVICIO Y SUBSERVICIO -->
                    <div class="form-card">
                        <div class="form-section-title">
                            <i class="bi bi-briefcase-fill"></i>
                            Tipo de Servicio
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-list-ul"></i>
                                Servicio Principal
                                <span class="required">*</span>
                            </label>
                            <select name="id_servicio" id="id_servicio" class="form-control-custom" required>
                                <option value="">Cargando servicios...</option>
                            </select>
                            <div class="form-helper-text">
                                <i class="bi bi-info-circle"></i>
                                Selecciona el tipo general de servicio que necesitas
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-check-circle"></i>
                                Subservicio Específico
                                <span class="required">*</span>
                            </label>
                            <select name="id_subservicio" id="id_subservicio" class="form-control-custom" disabled required>
                                <option value="">Primero selecciona un servicio...</option>
                            </select>
                            <div class="form-helper-text">
                                <i class="bi bi-info-circle"></i>
                                Especifica qué tipo exacto de servicio necesitas
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: FECHA Y HORA -->
                    <div class="form-card">
                        <div class="form-section-title">
                            <i class="bi bi-clock-fill"></i>
                            Fecha y Horario
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">
                                        <i class="bi bi-calendar-event"></i>
                                        Fecha y Hora de Inicio
                                        <span class="required">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           name="fecha_hora" 
                                           id="fecha_hora" 
                                           class="form-control-custom" 
                                           required>
                                    <div class="form-helper-text">
                                        <i class="bi bi-info-circle"></i>
                                        Selecciona cuándo quieres la cita
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">
                                        <i class="bi bi-calendar-check"></i>
                                        Fecha y Hora de Fin
                                        <span class="required">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           name="fecha_hora_fin" 
                                           id="fecha_hora_fin" 
                                           class="form-control-custom" 
                                           required>
                                    <div class="form-helper-text">
                                        <i class="bi bi-clock"></i>
                                        Duración estimada del servicio
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: OBSERVACIONES -->
                    <div class="form-card">
                        <div class="form-section-title">
                            <i class="bi bi-chat-left-text-fill"></i>
                            Observaciones Adicionales
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-pencil"></i>
                                Detalles o comentarios (Opcional)
                            </label>
                            <textarea name="observaciones" 
                                      id="observaciones" 
                                      class="form-control-custom" 
                                      placeholder="Ej: Mi mascota es nerviosa con otros animales, necesita ayuda para subir a la mesa, etc."></textarea>
                            <div class="form-helper-text">
                                <i class="bi bi-info-circle"></i>
                                Información adicional que el veterinario deba saber
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="botones-accion">
                        <a href="<?= BASE_URL ?>/cliente/citas" class="btn-cancelar">
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn-agendar">
                            <i class="bi bi-calendar-check"></i>
                            Agendar Cita
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        // ═══════════════════════════════════════════════════════════
        //  CONFIGURACIÓN Y CONSTANTES - ✅ CORREGIDO
        // ═══════════════════════════════════════════════════════════
        
        const BASE_URL = '<?= BASE_URL ?>';
        const URLS = {
            GET_SERVICIOS: BASE_URL + '/cliente/api/servicios?accion=servicios',
            GET_SUBSERVICIOS: BASE_URL + '/cliente/api/subservicios?accion=subservicios',
            CREATE_CITA: BASE_URL + '/cliente/api/citas/crear'
        };

        // ═══════════════════════════════════════════════════════════
        //  FUNCIÓN PARA FORMATEAR FECHAS A MYSQL
        // ═══════════════════════════════════════════════════════════
        
        function formatDateForMySQL(date) {
            if (!date) return null;
            
            if (typeof date === 'string') {
                date = new Date(date);
            }
            
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        // ═══════════════════════════════════════════════════════════
        //  SELECCIÓN DE MASCOTA
        // ═══════════════════════════════════════════════════════════
        
        document.addEventListener('DOMContentLoaded', function() {
            const mascotasCards = document.querySelectorAll('.mascota-card-selector');
            const inputMascota = document.getElementById('id_paciente');

            mascotasCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remover selección de todas las tarjetas
                    mascotasCards.forEach(c => c.classList.remove('selected'));
                    
                    // Marcar como seleccionada
                    this.classList.add('selected');
                    
                    // Guardar ID en input hidden
                    const mascotaId = this.getAttribute('data-mascota-id');
                    inputMascota.value = mascotaId;

                    // Actualizar indicador de paso
                    updateStepIndicator(2);

                    console.log('Mascota seleccionada:', mascotaId);
                });
            });

            // ═══════════════════════════════════════════════════════════
            //  CARGAR SERVICIOS AL INICIO
            // ═══════════════════════════════════════════════════════════
            
            cargarServicios();

            // ═══════════════════════════════════════════════════════════
            //  CAMBIO DE SERVICIO → CARGAR SUBSERVICIOS
            // ═══════════════════════════════════════════════════════════
            
            const selectServicio = document.getElementById('id_servicio');
            const selectSubservicio = document.getElementById('id_subservicio');

            selectServicio.addEventListener('change', function() {
                const idServicio = this.value;

                if (idServicio) {
                    cargarSubserviciosPorServicio(idServicio);
                    updateStepIndicator(2);
                } else {
                    selectSubservicio.innerHTML = '<option value="">Primero selecciona un servicio...</option>';
                    selectSubservicio.disabled = true;
                }
            });

            selectSubservicio.addEventListener('change', function() {
                if (this.value) {
                    updateStepIndicator(3);
                }
            });

            // ═══════════════════════════════════════════════════════════
            //  CAMBIO EN FECHAS → VALIDAR Y ACTUALIZAR PASO
            // ═══════════════════════════════════════════════════════════
            
            const inputFechaInicio = document.getElementById('fecha_hora');
            const inputFechaFin = document.getElementById('fecha_hora_fin');

            inputFechaInicio.addEventListener('change', function() {
                // Auto-calcular fecha de fin (1 hora después)
                if (this.value && !inputFechaFin.value) {
                    const fechaInicio = new Date(this.value);
                    const fechaFin = new Date(fechaInicio);
                    fechaFin.setHours(fechaFin.getHours() + 1);
                    
                    const year = fechaFin.getFullYear();
                    const month = String(fechaFin.getMonth() + 1).padStart(2, '0');
                    const day = String(fechaFin.getDate()).padStart(2, '0');
                    const hours = String(fechaFin.getHours()).padStart(2, '0');
                    const minutes = String(fechaFin.getMinutes()).padStart(2, '0');
                    
                    inputFechaFin.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                }
                updateStepIndicator(3);
            });

            inputFechaFin.addEventListener('change', function() {
                updateStepIndicator(4);
            });

            // ═══════════════════════════════════════════════════════════
            //  ENVÍO DEL FORMULARIO - ✅ CORREGIDO
            // ═══════════════════════════════════════════════════════════
            
            const form = document.getElementById('formAgendarCita');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Validar que se haya seleccionado una mascota
                const idPaciente = document.getElementById('id_paciente').value;
                if (!idPaciente) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Mascota no seleccionada',
                        text: 'Por favor selecciona una mascota',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                // Recopilar datos del formulario
                const formData = new FormData(form);
                const data = {
                    accion: 'crear', // ✅ AGREGADO
                    id_paciente: parseInt(formData.get('id_paciente')),
                    id_servicio: parseInt(formData.get('id_servicio')),
                    id_subservicio: parseInt(formData.get('id_subservicio')),
                    id_especialidad: 1, // Por defecto
                    tipo: document.querySelector('#id_subservicio option:checked').text.split(' - ')[0],
                    observaciones: formData.get('observaciones') || '',
                    fecha_hora: formatDateForMySQL(new Date(formData.get('fecha_hora'))),
                    fecha_hora_fin: formatDateForMySQL(new Date(formData.get('fecha_hora_fin'))),
                    estado: 'Pendiente'
                };

                console.log('Datos a enviar:', data);

                // Mostrar loading
                Swal.fire({
                    title: 'Agendando cita...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(URLS.CREATE_CITA, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Cita Agendada!',
                            html: `
                                <p>Tu cita ha sido registrada exitosamente.</p>
                                <p><strong>Mascota:</strong> ${document.querySelector('.mascota-card-selector.selected .mascota-nombre-selector').textContent}</p>
                                <p><strong>Servicio:</strong> ${data.tipo}</p>
                            `,
                            confirmButtonText: 'Ver mis citas',
                            showCancelButton: true,
                            cancelButtonText: 'Agendar otra'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = BASE_URL + '/cliente/citas';
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        throw new Error(result.message || 'Error al agendar cita');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No se pudo agendar la cita. Por favor intenta nuevamente.',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // ═══════════════════════════════════════════════════════════
        //  FUNCIÓN PARA CARGAR SERVICIOS
        // ═══════════════════════════════════════════════════════════
        
        async function cargarServicios() {
            const selectServicio = document.getElementById('id_servicio');
            
            try {
                const response = await fetch(URLS.GET_SERVICIOS);
                const result = await response.json();

                if (result.status === 'success' && result.data.length > 0) {
                    let options = '<option value="">Selecciona un servicio...</option>';
                    result.data.forEach(servicio => {
                        options += `<option value="${servicio.id_servicio}">${servicio.nombre}</option>`;
                    });
                    selectServicio.innerHTML = options;
                } else {
                    selectServicio.innerHTML = '<option value="">No hay servicios disponibles</option>';
                }
            } catch (error) {
                console.error('Error al cargar servicios:', error);
                selectServicio.innerHTML = '<option value="">Error al cargar servicios</option>';
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  FUNCIÓN PARA CARGAR SUBSERVICIOS POR SERVICIO - ✅ CORREGIDO
        // ═══════════════════════════════════════════════════════════
        
        async function cargarSubserviciosPorServicio(idServicio) {
            const selectSubservicio = document.getElementById('id_subservicio');
            
            selectSubservicio.disabled = true;
            selectSubservicio.innerHTML = '<option value="">Cargando subservicios...</option>';

            try {
                const response = await fetch(`${URLS.GET_SUBSERVICIOS}&id_servicio=${idServicio}`); // ✅ Cambiado ? por &
                const result = await response.json();

                if (result.status === 'success' && result.data.length > 0) {
                    let options = '<option value="">Selecciona un subservicio...</option>';
                    result.data.forEach(sub => {
                        const precio = new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP',
                            minimumFractionDigits: 0
                        }).format(sub.costo);
                        options += `<option value="${sub.id_subservicio}">${sub.nombre} - ${precio}</option>`;
                    });
                    selectSubservicio.innerHTML = options;
                    selectSubservicio.disabled = false;
                } else {
                    selectSubservicio.innerHTML = '<option value="">No hay subservicios disponibles</option>';
                }
            } catch (error) {
                console.error('Error al cargar subservicios:', error);
                selectSubservicio.innerHTML = '<option value="">Error al cargar subservicios</option>';
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  ACTUALIZAR INDICADOR DE PASOS
        // ═══════════════════════════════════════════════════════════
        
        function updateStepIndicator(currentStep) {
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById('step' + i);
                if (i < currentStep) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else if (i === currentStep) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active', 'completed');
                }
            }
        }

        console.log('✅ Vista de Agendar Cita cargada correctamente');
    </script>

</body>

</html>