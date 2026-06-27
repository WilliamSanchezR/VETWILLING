<?php

// Cargar preferencias e i18n
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) { require_once BASE_PATH . '/app/services/PreferenciasManager.php'; }
    $_pm = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs = $_pm->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) { require_once BASE_PATH . '/app/lang/i18n.php'; }
    $t = I18n::cargar($prefs['idioma']);
}require_once BASE_PATH . '/app/controllers/mascotasController.php';

// Obtener las mascotas del propietario actual
$mascotas = listarMascotas();
?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - VetWilling</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS Personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
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
        /*  BOTONES DE FECHAS DISPONIBLES EN MODAL                    */
        /* ═══════════════════════════════════════════════════════════ */

        .btn-fecha-disponible {
            padding: 10px 12px;
            min-width: 70px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            text-align: center;
            color: #333;
        }

        .btn-fecha-disponible:hover {
            border-color: #0a932c;
            background: #f0f9f5;
            transform: translateY(-2px);
        }

        .btn-fecha-disponible:active {
            transform: translateY(0);
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

        /* ═══════════════════════════════════════════════════════════ */
        /*  DARK MODE — body.dark-theme                                */
        /* ═══════════════════════════════════════════════════════════ */

        body.dark-theme .form-card {
            background : #1a1a2e;
            box-shadow : 0 5px 20px rgba(0,0,0,.45);
        }

        body.dark-theme .form-section-title {
            color        : #d1d9e6;
            border-bottom-color: #0a932c;
        }

        body.dark-theme .form-label-custom {
            color: #d1d9e6;
        }

        body.dark-theme .form-control-custom {
            background   : #1e2535;
            border-color : rgba(255,255,255,.12);
            color        : #f0f4f8;
        }

        body.dark-theme .form-control-custom::placeholder {
            color: #6b7280;
        }

        body.dark-theme .form-control-custom:focus {
            background   : #243044;
            border-color : #0a932c;
            box-shadow   : 0 0 0 4px rgba(10,147,44,.15);
        }

        body.dark-theme .form-control-custom:disabled {
            background   : #12121f;
            opacity      : 0.5;
        }

        body.dark-theme .form-helper-text {
            color: #6b7280;
        }

        body.dark-theme .mascota-card-selector {
            background   : #1a1a2e;
            border-color : rgba(255,255,255,.12);
        }

        body.dark-theme .mascota-card-selector:hover {
            border-color : #0a932c;
            box-shadow   : 0 8px 20px rgba(10,147,44,.20);
        }

        body.dark-theme .mascota-card-selector.selected {
            background   : rgba(10,147,44,.10);
            border-color : #0a932c;
            box-shadow   : 0 8px 20px rgba(10,147,44,.25);
        }

        body.dark-theme .mascota-avatar-selector {
            border-color : rgba(255,255,255,.15);
        }

        body.dark-theme .mascota-nombre-selector {
            color: #f0f4f8;
        }

        body.dark-theme .mascota-especie-selector {
            color: #9ca3af;
        }

        body.dark-theme .btn-cancelar {
            background   : #1e2535;
            border-color : rgba(255,255,255,.15);
            color        : #9ca3af;
        }

        body.dark-theme .btn-cancelar:hover {
            background   : #243044;
            border-color : rgba(255,255,255,.25);
            color        : #d1d9e6;
        }

        body.dark-theme .steps-indicator::before {
            background: rgba(255,255,255,.10);
        }

        body.dark-theme .step-circle {
            background : #1e2535;
            color      : #6b7280;
        }

        body.dark-theme .step-label {
            color: #6b7280;
        }

        body.dark-theme .btn-fecha-disponible {
            background   : #1e2535;
            border-color : rgba(255,255,255,.12);
            color        : #d1d9e6;
        }

        body.dark-theme .btn-fecha-disponible:hover {
            border-color : #0a932c;
            background   : rgba(10,147,44,.12);
            color        : #4ade80;
        }
    </style>
</head>

<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

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
                        <div class="form-group-custom">
                            <button type="button" id="btnBuscarDisponibilidades" class="btn btn-success">
                                <i class="bi bi-search"></i> Buscar horarios disponibles
                            </button>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-person-bounding-box"></i>
                                Veterinario seleccionado
                            </label>
                            <input type="text" id="veterinario_seleccionado" class="form-control-custom" placeholder="Selecciona un horario disponible" readonly>
                        </div>

                        <input type="hidden" name="id_usuario" id="id_usuario">
                        <input type="hidden" name="fecha_hora" id="fecha_hora">
                        <input type="hidden" name="fecha_hora_fin" id="fecha_hora_fin">
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

                <!-- MODAL DE DISPONIBILIDADES -->
                <div class="modal fade" id="modalDisponibilidades" tabindex="-1" aria-labelledby="modalDisponibilidadesLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDisponibilidadesLabel">Horarios disponibles</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Fecha</label>
                                        <input type="date" id="fecha_disponibilidad" class="form-control-custom" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="button" id="btnCargarDisponibilidades" class="btn btn-primary w-100">
                                            <i class="bi bi-arrow-repeat"></i> Actualizar disponibilidad
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Contenedor de fechas disponibles -->
                                <div class="mb-4 p-3" style="background:#f8f9fa; border-radius:12px; border:1px solid #e0e0e0;">
                                    <label class="form-label-custom mb-3">Fechas disponibles (próximos 30 días)</label>
                                    <div id="fechasDisponiblesContainer" class="d-flex flex-wrap gap-2">
                                        <div class="text-center text-muted w-100 py-3">
                                            <small>Cargando fechas disponibles...</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="disponibilidadesContainer" class="list-group">
                                    <div class="text-center py-5 text-muted">Seleccione una fecha y haga clic en actualizar para ver horarios</div>
                                </div>
                                <!-- Selector de hora (oculto hasta que se seleccione una disponibilidad) -->
                                <div id="selectorHoraContainer" style="display:none; margin-top:20px; padding-top:20px; border-top:1px solid #ddd;">
                                    <h6 class="mb-3">Selecciona una hora dentro del rango disponible</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Hora</label>
                                            <input type="time" id="horaSeleccionada" class="form-control-custom">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Rango disponible</label>
                                            <p id="rangoDisponible" class="text-muted mb-0">-</p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" id="btnConfirmarHora" class="btn btn-success w-100">
                                            <i class="bi bi-check-circle"></i> Confirmar horario
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

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
            GET_VETERINARIOS: BASE_URL + '/calendario/getVeterinarios',
            GET_DISPONIBILIDADES: BASE_URL + '/disponibilidad/horarios?action=disponibilidades',
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
            //  ENVÍO DEL FORMULARIO - ✅ CORREGIDO Y VALIDACIÓN DE FECHA
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

                // Validación de fecha pasada
                const fechaInicio = new Date(formData.get('fecha_hora'));
                const ahora = new Date();
                if (fechaInicio < ahora) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'No puedes agendar una cita en el pasado.',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                const data = {
                    accion: 'crear', // ✅ AGREGADO
                    id_paciente: parseInt(formData.get('id_paciente')),
                    id_servicio: parseInt(formData.get('id_servicio')),
                    id_subservicio: parseInt(formData.get('id_subservicio')),
                    id_usuario: formData.get('id_usuario') ? parseInt(formData.get('id_usuario')) : null,
                    id_especialidad: 1, // Por defecto
                    tipo: document.querySelector('#id_subservicio option:checked').text.split(' - ')[0],
                    observaciones: formData.get('observaciones') || '',
                    fecha_hora: formatDateForMySQL(new Date(formData.get('fecha_hora'))),
                    fecha_hora_fin: formatDateForMySQL(new Date(formData.get('fecha_hora_fin'))),
                    estado: 'Pendiente'
                };

                if (!formData.get('id_usuario')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un horario',
                        text: 'Debes seleccionar un horario disponible para ver el veterinario asignado.',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'No se pudo agendar la cita. Por favor intenta nuevamente.',
                            confirmButtonText: 'Entendido'
                        });
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

            const modal = new bootstrap.Modal(document.getElementById('modalDisponibilidades'));

            document.getElementById('btnBuscarDisponibilidades').addEventListener('click', function() {
                modal.show();
                cargarFechasDisponibles(); // Cargar fechas disponibles al abrir el modal
                cargarDisponibilidades();
            });

            document.getElementById('btnCargarDisponibilidades').addEventListener('click', function() {
                cargarDisponibilidades();
            });

            document.getElementById('fecha_disponibilidad').addEventListener('change', function() {
                // Actualizar la lista cuando cambia la fecha
                cargarDisponibilidades();
            });

            async function cargarFechasDisponibles() {
                const container = document.getElementById('fechasDisponiblesContainer');
                const hoy = new Date();
                const diasDisponibles = new Set();

                try {
                    // Iterar sobre los próximos 30 días para obtener fechas con disponibilidad
                    for (let i = 0; i < 30; i++) {
                        const fecha = new Date(hoy);
                        fecha.setDate(fecha.getDate() + i);
                        const fechaStr = fecha.getFullYear() + '-' + 
                                       String(fecha.getMonth() + 1).padStart(2, '0') + '-' + 
                                       String(fecha.getDate()).padStart(2, '0');

                        try {
                            const response = await fetch(`${URLS.GET_DISPONIBILIDADES}&fecha=${encodeURIComponent(fechaStr)}`);
                            const result = await response.json();

                            if (result.status === 'success' && Array.isArray(result.data) && result.data.length > 0) {
                                diasDisponibles.add(fechaStr);
                            }
                        } catch (error) {
                            console.error(`Error cargando disponibilidad para ${fechaStr}:`, error);
                        }
                    }

                    // Mostrar fechas disponibles como botones
                    const fechasArray = Array.from(diasDisponibles).sort();
                    
                    if (fechasArray.length === 0) {
                        container.innerHTML = '<div class="text-center text-muted w-100"><small>No hay fechas disponibles en los próximos 30 días</small></div>';
                        return;
                    }

                    const botonesHtml = fechasArray.map(fecha => {
                        const d = new Date(fecha + 'T00:00:00');
                        const nombreDia = d.toLocaleDateString('es-ES', { weekday: 'short' });
                        const dia = d.getDate();
                        return `
                            <button type="button" class="btn-fecha-disponible" data-fecha="${fecha}">
                                <div style="font-weight:700; font-size:16px;">${dia}</div>
                                <div style="font-size:11px; color:#666;">${nombreDia}</div>
                            </button>
                        `;
                    }).join('');

                    container.innerHTML = botonesHtml;

                    // Agregar event listeners a los botones de fechas
                    document.querySelectorAll('.btn-fecha-disponible').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const fecha = this.getAttribute('data-fecha');
                            document.getElementById('fecha_disponibilidad').value = fecha;
                            
                            // Remover clase active de todos los botones
                            document.querySelectorAll('.btn-fecha-disponible').forEach(b => {
                                b.style.background = 'white';
                                b.style.borderColor = '#e0e0e0';
                                b.style.color = '#333';
                            });
                            
                            // Marcar este botón como activo
                            this.style.background = '#0a932c';
                            this.style.borderColor = '#0a932c';
                            this.style.color = 'white';
                            
                            cargarDisponibilidades();
                        });
                    });

                } catch (error) {
                    console.error('Error al cargar fechas disponibles:', error);
                    container.innerHTML = '<div class="text-center text-danger w-100"><small>Error al cargar fechas disponibles</small></div>';
                }
            }

            async function cargarDisponibilidades() {
                const fecha = document.getElementById('fecha_disponibilidad').value;
                const divisionesPrevias = document.getElementById('disponibilidadesContainer');
                divisionesPrevias.innerHTML = '<div class="text-center py-5 text-muted">Cargando horarios disponibles...</div>';

                try {
                    const response = await fetch(`${URLS.GET_DISPONIBILIDADES}&fecha=${encodeURIComponent(fecha)}`);
                    const result = await response.json();

                    if (result.status !== 'success') {
                        divisionesPrevias.innerHTML = `<div class="alert alert-warning">${result.message || 'No se pudo cargar la disponibilidad.'}</div>`;
                        return;
                    }

                    const disponibles = result.data;
                    if (!Array.isArray(disponibles) || disponibles.length === 0) {
                        divisionesPrevias.innerHTML = '<div class="text-center py-5 text-muted">No hay horarios disponibles para la fecha seleccionada.</div>';
                        return;
                    }

                    const itemsHtml = disponibles.map(item => {
                        const horaInicio = item.hora_inicio.substring(0, 5);
                        const horaFin = item.hora_fin.substring(0, 5);
                        return `
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btnSeleccionarHorario" data-id_usuario="${item.id_usuario}" data-veterinario_nombre="${item.veterinario_nombre}" data-hora_inicio="${item.hora_inicio}" data-hora_fin="${item.hora_fin}">
                                <div>
                                    <strong>${item.veterinario_nombre}</strong><br>
                                    <small>${horaInicio} - ${horaFin}</small>
                                </div>
                                <span class="badge bg-success">Seleccionar</span>
                            </button>
                        `;
                    }).join('');

                    divisionesPrevias.innerHTML = `<div class="list-group">${itemsHtml}</div>`;
                    
                    // Guardar datos de la disponibilidad seleccionada
                    let disponibilidadSeleccionada = null;
                    
                    document.querySelectorAll('.btnSeleccionarHorario').forEach(button => {
                        button.addEventListener('click', function() {
                            const idUsuario = this.getAttribute('data-id_usuario');
                            const nombreVet = this.getAttribute('data-veterinario_nombre');
                            const horaInicio = this.getAttribute('data-hora_inicio');
                            const horaFin = this.getAttribute('data-hora_fin');

                            // Guardar datos y mostrar selector de hora
                            disponibilidadSeleccionada = {
                                idUsuario,
                                nombreVet,
                                horaInicio: horaInicio.substring(0, 5),
                                horaFin: horaFin.substring(0, 5),
                                fecha: document.getElementById('fecha_disponibilidad').value
                            };

                            // Mostrar selector de hora
                            const selectorContainer = document.getElementById('selectorHoraContainer');
                            const inputHora = document.getElementById('horaSeleccionada');
                            const rangoDisplay = document.getElementById('rangoDisponible');
                            
                            selectorContainer.style.display = 'block';
                            inputHora.min = disponibilidadSeleccionada.horaInicio;
                            inputHora.max = disponibilidadSeleccionada.horaFin;
                            inputHora.value = disponibilidadSeleccionada.horaInicio;
                            rangoDisplay.textContent = `${disponibilidadSeleccionada.horaInicio} - ${disponibilidadSeleccionada.horaFin}`;

                            // Scroll al selector
                            selectorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    });

                    // Evento para confirmar hora
                    document.getElementById('btnConfirmarHora').addEventListener('click', function() {
                        if (!disponibilidadSeleccionada) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Error',
                                text: 'Por favor selecciona una disponibilidad primero.',
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }

                        const horaSeleccionada = document.getElementById('horaSeleccionada').value;
                        if (!horaSeleccionada) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Error',
                                text: 'Por favor selecciona una hora.',
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }

                        // Validar que la hora esté dentro del rango
                        if (horaSeleccionada < disponibilidadSeleccionada.horaInicio || horaSeleccionada > disponibilidadSeleccionada.horaFin) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Hora fuera de rango',
                                text: `Selecciona una hora entre ${disponibilidadSeleccionada.horaInicio} y ${disponibilidadSeleccionada.horaFin}`,
                                confirmButtonText: 'Entendido'
                            });
                            return;
                        }

                        // Rellenar campos del formulario
                        document.getElementById('id_usuario').value = disponibilidadSeleccionada.idUsuario;
                        
                        // Calcular hora fin (1 hora después)
                        const [horas, minutos] = horaSeleccionada.split(':');
                        const fechaFin = new Date(disponibilidadSeleccionada.fecha);
                        fechaFin.setHours(parseInt(horas) + 1, parseInt(minutos), 0);
                        const horaFin = fechaFin.toTimeString().substring(0, 5);
                        
                        // Mostrar veterinario + hora inicio - hora fin
                        document.getElementById('veterinario_seleccionado').value = `${disponibilidadSeleccionada.nombreVet} — ${horaSeleccionada} a ${horaFin}`;
                        document.getElementById('fecha_hora').value = `${disponibilidadSeleccionada.fecha}T${horaSeleccionada}`;
                        document.getElementById('fecha_hora_fin').value = `${disponibilidadSeleccionada.fecha}T${horaFin}`;
                        
                        modal.hide();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Horario confirmado',
                            text: `Cita agendada con ${disponibilidadSeleccionada.nombreVet} a las ${horaSeleccionada}`,
                            confirmButtonText: 'Perfecto',
                            timer: 2000
                        });
                    });
                    
                } catch (error) {
                    console.error('Error al cargar disponibilidades:', error);
                    divisionesPrevias.innerHTML = '<div class="alert alert-danger">Error al cargar la disponibilidad. Intenta de nuevo.</div>';
                }
            }
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

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js"></script>
</body>

</html>