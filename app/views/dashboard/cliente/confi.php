<?php
/**
 * Vista de Configuración del Cliente
 * CORRECCIONES:
 * - htmlspecialchars() en todos los valores del usuario (XSS)
 * - isset() reemplazado por operador ?? más limpio
 * - Bootstrap CSS agregado (antes solo estaba el JS)
 * - sidebar.css movido al inicio de los CSS (base del layout)
 * - Tabs manejados con clase CSS en lugar de style="display:none"
 * - Sección "Actividad Reciente" integrada con SessionManager
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/services/SessionManager.php';

// Inicializar SessionManager para listar sesiones activas
$sm       = new SessionManager($_SESSION['user']['id_usuario']);
$sesiones = $sm->listar();

// Helper para escapar output HTML
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - VetWilling</title>

    <!-- Bootstrap CSS (CORRECCIÓN: antes faltaba, solo estaba el JS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS propios
         CORRECCIÓN: sidebar.css va primero porque define el layout base
         (.contenido-principal, .area-contenido). Si va al final puede
         ser pisado por confi.css. -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/confi.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer" role="status" aria-live="polite"></div>

    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- Contenido principal -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- Navbar superior -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- Header -->
                <div class="header-config">
                    <h1>⚙️ Configuración</h1>
                    <p>Personaliza tu experiencia en VetWilling</p>
                </div>

                <!-- ── Tabs ──
                     CORRECCIÓN: los tabs no usan style="display:none" en el HTML.
                     La visibilidad se controla con la clase .tab-content--hidden
                     que se agrega/quita por JS para evitar el flash de contenido. -->
                <div class="tabs-config" role="tablist">
                    <button class="tab-config active" onclick="cambiarTab('cuenta')"
                            role="tab" aria-selected="true" aria-controls="tab-cuenta">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                        <span>Mi Cuenta</span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('general')"
                            role="tab" aria-selected="false" aria-controls="tab-general">
                        <i class="bi bi-gear-fill" aria-hidden="true"></i>
                        <span>General</span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('notificaciones')"
                            role="tab" aria-selected="false" aria-controls="tab-notificaciones">
                        <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        <span>Notificaciones</span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('seguridad')"
                            role="tab" aria-selected="false" aria-controls="tab-seguridad">
                        <i class="bi bi-key-fill" aria-hidden="true"></i>
                        <span>Seguridad</span>
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: MI CUENTA
                ══════════════════════════════════════════════════ -->
                <div class="tab-content" id="tab-cuenta" role="tabpanel">
                    <form method="POST"
                          action="<?= BASE_URL ?>/cliente/actualizar"
                          enctype="multipart/form-data"
                          novalidate>
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_propietario"
                               value="<?= e($_SESSION['user']['id_usuario']) ?>">

                        <!-- Foto de perfil -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-camera-fill" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h3>Foto de Perfil</h3>
                                    <p>Actualiza tu imagen de perfil</p>
                                </div>
                            </div>

                            <div class="foto-perfil-container">
                                <div class="avatar-grande">
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= e($usuario['img_perfil']) ?>"
                                         alt="Foto de perfil de <?= e($usuario['nombres']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>/public/assets/webSite/img/default-avatar.png'">
                                </div>
                                <div class="foto-acciones">
                                    <h4>Cambiar Foto de Perfil</h4>
                                    <p>JPG, PNG o GIF. Tamaño máximo 2 MB</p>
                                    <div class="foto-btns">
                                        <input type="file" name="img_perfil" id="inputFoto"
                                               class="input-file" accept="image/*"
                                               onchange="previewFoto(event)">
                                        <label for="inputFoto" class="btn-upload">
                                            <i class="bi bi-upload" aria-hidden="true"></i> Subir Foto
                                        </label>
                                        <button type="button" class="btn-remove" onclick="eliminarFoto()">
                                            <i class="bi bi-trash" aria-hidden="true"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información personal -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-person-badge-fill" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h3>Información Personal</h3>
                                    <p>Actualiza tus datos personales</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-nombres">Nombre(s)</label>
                                    <!-- CORRECCIÓN: e() en todos los valores del usuario -->
                                    <input type="text" id="inp-nombres" name="nombres"
                                           value="<?= e($usuario['nombres']) ?>" required>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-apellidos">Apellido(s)</label>
                                    <input type="text" id="inp-apellidos" name="apellidos"
                                           value="<?= e($usuario['apellidos']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tipo-doc">Tipo de Documento</label>
                                    <select id="inp-tipo-doc" name="tipo_documento" required>
                                        <?php
                                        $tipos = ['Cédula de Ciudadanía', 'Cédula de Extranjería', 'Pasaporte'];
                                        foreach ($tipos as $t): ?>
                                            <option value="<?= e($t) ?>"
                                                <?= ($usuario['tipo_documento'] ?? '') === $t ? 'selected' : '' ?>>
                                                <?= e($t) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-num-doc">Número de Documento</label>
                                    <input type="text" id="inp-num-doc" name="numero_documento"
                                           value="<?= e($usuario['numero_documento']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Información de contacto -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h3>Información de Contacto</h3>
                                    <p>Actualiza tu email y teléfono</p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-email">Email Principal</label>
                                <input type="email" id="inp-email" name="email"
                                       value="<?= e($usuario['email']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tel">Teléfono</label>
                                    <input type="tel" id="inp-tel" name="telefono"
                                           value="<?= e($usuario['telefono']) ?>" required>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-tel-alt">Teléfono Alternativo</label>
                                    <!-- CORRECCIÓN: ?? '' en lugar de isset() innecesario -->
                                    <input type="tel" id="inp-tel-alt" name="telefono_alt"
                                           value="<?= e($usuario['telefono_alt'] ?? '') ?>"
                                           placeholder="+57 300 000 0000">
                                </div>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h3>Información Adicional</h3>
                                    <p>Datos complementarios opcionales</p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-dir">Dirección</label>
                                <input type="text" id="inp-dir" name="direccion"
                                       value="<?= e($usuario['direccion'] ?? '') ?>">
                            </div>

                            <div class="form-group-config">
                                <label for="inp-bio">Biografía</label>
                                <textarea id="inp-bio" name="biografia" rows="4"
                                          placeholder="Cuéntanos un poco sobre ti..."><?= e($usuario['biografia'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-como">Cómo nos conociste</label>
                                <select id="inp-como" name="como_conociste">
                                    <option value="">Selecciona una opción</option>
                                    <?php
                                    $opciones = [
                                        'redes_sociales' => 'Redes sociales',
                                        'recomendacion'  => 'Recomendación',
                                        'google'         => 'Búsqueda en Google',
                                        'publicidad'     => 'Publicidad',
                                        'otro'           => 'Otro',
                                    ];
                                    foreach ($opciones as $val => $label): ?>
                                        <option value="<?= e($val) ?>"
                                            <?= ($usuario['como_conociste'] ?? '') === $val ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-acciones">
                                <button type="submit" class="btn-config btn-primary-config">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    Guardar Cambios
                                </button>
                                <button type="reset" class="btn-config btn-secondary-config">
                                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                    Restablecer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: GENERAL
                ══════════════════════════════════════════════════ -->
                <div class="tab-content tab-content--hidden" id="tab-general" role="tabpanel">

                    <!-- Idioma y Región -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-globe2" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Idioma y Región</h3>
                                <p>Configura tu idioma y zona horaria</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Idioma</h4>
                                <p>Selecciona el idioma de la aplicación</p>
                            </div>
                            <select class="config-select" id="selectIdioma">
                                <option value="es">Español</option>
                                <option value="en">English</option>
                                <option value="pt">Português</option>
                            </select>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Zona Horaria</h4>
                                <p>Tu zona horaria local</p>
                            </div>
                            <select class="config-select" id="selectZonaHoraria">
                                <option value="gmt-5">GMT-5 (Bogotá)</option>
                                <option value="gmt-4">GMT-4 (Santiago)</option>
                                <option value="gmt-3">GMT-3 (Buenos Aires)</option>
                                <option value="gmt-6">GMT-6 (Ciudad de México)</option>
                                <option value="gmt+1">GMT+1 (Madrid)</option>
                            </select>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Formato de Fecha</h4>
                                <p>Cómo quieres ver las fechas</p>
                            </div>
                            <select class="config-select" id="selectFormatoFecha">
                                <option value="dd/mm/yyyy">DD/MM/YYYY (19/01/2026)</option>
                                <option value="mm/dd/yyyy">MM/DD/YYYY (01/19/2026)</option>
                                <option value="yyyy-mm-dd">YYYY-MM-DD (2026-01-19)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Centro de Ayuda -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-question-circle" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Centro de Ayuda</h3>
                                <p>Preguntas frecuentes</p>
                            </div>
                        </div>

                        <div class="faq-section">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿Cómo cambio mi contraseña?</span>
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </div>
                                <div class="faq-answer">
                                    Ve a Configuración › Seguridad › Cambiar Contraseña. Necesitarás tu contraseña actual para establecer una nueva.
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿Cómo exporto mis datos?</span>
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </div>
                                <div class="faq-answer">
                                    Puedes exportar tus datos desde Configuración › Privacidad › Exportar Datos. Recibirás un archivo con toda tu información.
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿La aplicación funciona sin internet?</span>
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </div>
                                <div class="faq-answer">
                                    Algunas funciones básicas están disponibles offline, pero necesitarás conexión para sincronizar tus datos.
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <span>Tiempo de respuesta estimado: 24–48 horas</span>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: NOTIFICACIONES
                ══════════════════════════════════════════════════ -->
                <div class="tab-content tab-content--hidden" id="tab-notificaciones" role="tabpanel">

                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Preferencias de Notificación</h3>
                                <p>Configura cómo deseas recibir recordatorios de tus citas</p>
                            </div>
                        </div>

                        <div id="notifAlertContainer" aria-live="polite"></div>

                        <div id="loadingPreferencias" class="alert alert-info" style="display:none;">
                            Cargando tus preferencias...
                        </div>

                        <div class="config-item" style="align-items:flex-start;">
                            <div class="config-info">
                                <h4>Recibir Notificaciones por Email</h4>
                                <p>Confirmación de cita, recordatorio 24 h antes y cancelaciones.</p>
                            </div>
                            <input type="radio" name="preferencia_notificacion"
                                   id="pref_email" value="email" checked>
                        </div>

                        <div class="config-item" style="align-items:flex-start;">
                            <div class="config-info">
                                <h4>No Recibir Notificaciones</h4>
                                <p>Desactiva el envío de correos de notificación.</p>
                            </div>
                            <input type="radio" name="preferencia_notificacion"
                                   id="pref_ninguno" value="ninguno">
                        </div>

                        <div class="alert alert-info" style="margin-top:16px;">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Estas preferencias aplican a tus próximas citas. El sistema no usa SMS.</span>
                        </div>

                        <div class="form-acciones" style="margin-top:16px;">
                            <button type="button" class="btn-config btn-primary-config"
                                    id="btnGuardarPreferenciaNotificacion">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                Guardar Preferencia
                            </button>
                        </div>
                    </div>

                    <!-- Historial de notificaciones -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-clock-history" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Historial de Notificaciones</h3>
                                <p>Últimos envíos realizados por el sistema</p>
                            </div>
                        </div>

                        <div id="loadingHistorial" class="alert alert-info" style="display:none;">
                            Cargando historial...
                        </div>
                        <div id="sinHistorial" class="alert alert-warning" style="display:none;">
                            No hay notificaciones registradas todavía.
                        </div>
                        <div id="historialContent" style="display:none; overflow-x:auto;">
                            <table class="tabla-sesiones" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Medio</th>
                                        <th>Mascota</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaHistorialNotificaciones"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: SEGURIDAD
                ══════════════════════════════════════════════════ -->
                <div class="tab-content tab-content--hidden" id="tab-seguridad" role="tabpanel">

                    <!-- Cambiar contraseña -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Cambiar Contraseña</h3>
                                <p>Actualiza tu contraseña para mantener tu cuenta segura</p>
                            </div>
                        </div>

                        <form method="POST"
                              action="<?= BASE_URL ?>/cliente/actualizar-contrasena"
                              id="passwordForm" novalidate>
                            <input type="hidden" name="id_usuario"
                                   value="<?= e($_SESSION['user']['id_usuario']) ?>">
                            <input type="hidden" name="accion" value="modificar-contrasena">

                            <div class="form-group-config">
                                <label for="currentPassword">Contraseña Actual</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="currentPassword"
                                           name="contrasena-actual" required
                                           placeholder="Ingresa tu contraseña actual">
                                    <button type="button" class="toggle-password"
                                            onclick="togglePassword('currentPassword', this)"
                                            aria-label="Mostrar/ocultar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="newPassword">Nueva Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="newPassword"
                                               name="nueva-contrasena" required minlength="8"
                                               placeholder="Mínimo 8 caracteres"
                                               oninput="checkPasswordStrength()">
                                        <button type="button" class="toggle-password"
                                                onclick="togglePassword('newPassword', this)"
                                                aria-label="Mostrar/ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="strengthIndicator"
                                         style="display:none;">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <span class="strength-text" id="strengthText"></span>
                                    </div>
                                </div>

                                <div class="form-group-config">
                                    <label for="confirmPassword">Confirmar Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="confirmPassword"
                                               name="confi-contrasena" required minlength="8"
                                               placeholder="Confirma tu contraseña"
                                               oninput="checkPasswordMatch()">
                                        <button type="button" class="toggle-password"
                                                onclick="togglePassword('confirmPassword', this)"
                                                aria-label="Mostrar/ocultar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="matchIndicator"
                                         style="margin-top:8px; font-size:12px;"
                                         aria-live="polite"></div>
                                </div>
                            </div>

                            <!-- Requisitos de contraseña -->
                            <div class="requirements">
                                <div class="requirements-title">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                                    Requisitos de la contraseña
                                </div>
                                <div class="requirement-item" id="req-length">
                                    <i class="bi bi-circle"></i> Mínimo 8 caracteres
                                </div>
                                <div class="requirement-item" id="req-uppercase">
                                    <i class="bi bi-circle"></i> Al menos una letra mayúscula
                                </div>
                                <div class="requirement-item" id="req-number">
                                    <i class="bi bi-circle"></i> Al menos un número
                                </div>
                            </div>

                            <button type="submit" class="btn-config btn-primary-config"
                                    style="margin-top:20px;">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                Actualizar Contraseña
                            </button>
                        </form>
                    </div>

                    <!-- ── Sesiones Activas ── -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-shield-shaded" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>Sesiones Activas</h3>
                                <p>Dispositivos donde tienes sesión iniciada</p>
                            </div>
                        </div>

                        <div class="alert alert-info" style="margin-bottom:20px;">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Si notas actividad sospechosa, cierra la sesión y cambia tu contraseña.</span>
                        </div>

                        <!-- Lista de sesiones generada en PHP para no depender de JS -->
                        <div class="sesiones-lista" id="sesionesLista">

                            <?php if (empty($sesiones)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                    <span>No se encontraron sesiones activas.</span>
                                </div>

                            <?php else: ?>
                                <?php foreach ($sesiones as $sesion): ?>
                                    <?php
                                    $esCurrent  = $sesion['is_current'] ?? false;
                                    $esNueva    = $sesion['es_nueva']    ?? false;
                                    $token      = e($sesion['token']     ?? '');
                                    $navegador  = e($sesion['navegador']['nombre'] ?? 'Navegador desconocido');
                                    $navIcono   = e($sesion['navegador']['icono']  ?? 'bi-globe');
                                    $soNombre   = e($sesion['so']['nombre']        ?? 'SO desconocido');
                                    $soIcono    = e($sesion['so']['icono']         ?? 'bi-display');
                                    $ciudad     = e($sesion['ciudad']             ?? 'Desconocida');
                                    $tipoNombre = e($sesion['tipo']['nombre']      ?? 'Escritorio');
                                    $tipoIcono  = e($sesion['tipo']['icono']       ?? 'bi-laptop-fill');

                                    // Calcular tiempo relativo
                                    $lastSeen = $sesion['last_seen'] ?? 0;
                                    $diff     = time() - $lastSeen;
                                    if ($diff < 120)        $tiempo = 'Ahora';
                                    elseif ($diff < 3600)   $tiempo = 'Hace ' . floor($diff / 60) . ' min';
                                    elseif ($diff < 86400)  $tiempo = 'Hace ' . floor($diff / 3600) . ' h';
                                    else                    $tiempo = 'Hace ' . floor($diff / 86400) . ' días';
                                    ?>

                                    <div class="sesion-item <?= $esCurrent ? 'sesion-item--actual' : '' ?>"
                                         data-token="<?= $token ?>">

                                        <!-- Ícono del dispositivo -->
                                        <div class="sesion-icono">
                                            <i class="bi <?= $navIcono ?>" aria-hidden="true"></i>
                                        </div>

                                        <!-- Info de la sesión -->
                                        <div class="sesion-info">
                                            <div class="sesion-titulo">
                                                <?= $navegador ?> · <?= $ciudad ?>

                                                <?php if ($esCurrent): ?>
                                                    <span class="sesion-badge sesion-badge--actual">
                                                        Sesión Actual
                                                    </span>
                                                <?php endif; ?>

                                                <?php if ($esNueva && !$esCurrent): ?>
                                                    <span class="sesion-badge sesion-badge--nueva">
                                                        Nuevo
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="sesion-meta">
                                                <span>
                                                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                                    <?= $ciudad ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                                    <?= $tiempo ?>
                                                </span>
                                                <span>
                                                    <i class="bi <?= $soIcono ?>" aria-hidden="true"></i>
                                                    <?= $soNombre ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Botón cerrar (solo sesiones que no son la actual) -->
                                        <?php if (!$esCurrent): ?>
                                            <button type="button"
                                                    class="btn-cerrar-sesion"
                                                    data-token="<?= $token ?>"
                                                    onclick="cerrarSesion('<?= $token ?>', this)"
                                                    aria-label="Cerrar esta sesión">
                                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                                Cerrar
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                <?php endforeach; ?>

                                <!-- Botón "Cerrar todas" solo si hay más de una sesión -->
                                <?php if (count($sesiones) > 1): ?>
                                    <div class="sesiones-footer">
                                        <button type="button"
                                                class="btn-config btn-secondary-config btn-cerrar-todas"
                                                onclick="cerrarTodasSesiones(this)">
                                            <i class="bi bi-shield-x" aria-hidden="true"></i>
                                            Cerrar todas las otras sesiones
                                        </button>
                                    </div>
                                <?php endif; ?>

                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /tab-seguridad -->

                <!-- Notificación de guardado global -->
                <div class="save-notification" id="saveNotification" role="status" aria-live="polite">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <span id="notificationText">Configuración guardada</span>
                </div>

            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/confi.js"></script>

    <script>
    /* ================================================================
       TABS
       CORRECCIÓN: en lugar de style="display:none" en el HTML,
       se usa la clase .tab-content--hidden que confi.css define.
       Así no hay flash de contenido al cargar la página.
    ================================================================ */
    function cambiarTab(tab) {
        document.querySelectorAll('.tab-content').forEach(function (el) {
            el.classList.add('tab-content--hidden');
        });
        document.querySelectorAll('.tab-config').forEach(function (btn) {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });

        var panel = document.getElementById('tab-' + tab);
        if (panel) panel.classList.remove('tab-content--hidden');

        var btn = document.querySelector('[aria-controls="tab-' + tab + '"]');
        if (btn) {
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
        }
    }

    /* ================================================================
       FAQ
    ================================================================ */
    function toggleFAQ(btn) {
        var item = btn.closest('.faq-item');
        var icon = btn.querySelector('i');
        var isOpen = item.classList.contains('active');

        document.querySelectorAll('.faq-item.active').forEach(function (el) {
            el.classList.remove('active');
            var ic = el.querySelector('.faq-question i');
            if (ic) ic.style.transform = 'rotate(0deg)';
        });

        if (!isOpen) {
            item.classList.add('active');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    /* ================================================================
       SESIONES ACTIVAS — cerrar una sesión específica
       CORRECCIÓN: cerrar() en SessionManager ahora devuelve un string
       descriptivo ('ok', 'not_found', 'is_current', 'write_error')
       en lugar de bool, así el frontend puede mostrar el error correcto.
    ================================================================ */
    function cerrarSesion(token, btnEl) {
        if (!confirm('¿Estás seguro de cerrar esta sesión?')) return;

        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Cerrando...';

        fetch('<?= BASE_URL ?>/cliente/api/sesiones/cerrar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status === 'ok') {
                /* Eliminar la fila del DOM sin recargar la página */
                var item = document.querySelector('.sesion-item[data-token="' + token + '"]');
                if (item) {
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity    = '0';
                    item.style.transform  = 'translateX(20px)';
                    setTimeout(function () {
                        item.remove();
                        /* Ocultar botón "cerrar todas" si ya solo queda la actual */
                        var restantes = document.querySelectorAll('.sesion-item:not(.sesion-item--actual)');
                        if (restantes.length === 0) {
                            var footer = document.querySelector('.sesiones-footer');
                            if (footer) footer.remove();
                        }
                    }, 300);
                }
                mostrarToast('Sesión cerrada correctamente', 'success');
            } else {
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="bi bi-box-arrow-right"></i> Cerrar';
                /* Mensaje específico según el código devuelto por SessionManager */
                var mensajes = {
                    'not_found':   'La sesión no fue encontrada.',
                    'is_current':  'No puedes cerrar tu sesión actual desde aquí.',
                    'write_error': 'Error al guardar el cambio. Intenta de nuevo.'
                };
                mostrarToast(mensajes[data.code] || 'No se pudo cerrar la sesión.', 'error');
            }
        })
        .catch(function () {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-box-arrow-right"></i> Cerrar';
            mostrarToast('Error de conexión. Verifica tu internet.', 'error');
        });
    }

    /* ================================================================
       SESIONES ACTIVAS — cerrar todas menos la actual
    ================================================================ */
    function cerrarTodasSesiones(btnEl) {
        if (!confirm('¿Cerrar todas las otras sesiones? Esta acción no se puede deshacer.')) return;

        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Cerrando...';

        fetch('<?= BASE_URL ?>/cliente/api/sesiones/cerrar-todas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status === 'ok') {
                /* Eliminar todas las sesiones que no son la actual */
                document.querySelectorAll('.sesion-item:not(.sesion-item--actual)').forEach(function (el) {
                    el.remove();
                });
                var footer = document.querySelector('.sesiones-footer');
                if (footer) footer.remove();
                mostrarToast('Todas las otras sesiones fueron cerradas.', 'success');
            } else {
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="bi bi-shield-x"></i> Cerrar todas las otras sesiones';
                mostrarToast('No se pudieron cerrar las sesiones.', 'error');
            }
        })
        .catch(function () {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-shield-x"></i> Cerrar todas las otras sesiones';
            mostrarToast('Error de conexión. Verifica tu internet.', 'error');
        });
    }

    /* ================================================================
       TOAST HELPER
    ================================================================ */
    function mostrarToast(mensaje, tipo) {
        var container = document.getElementById('toastContainer');
        if (!container) return;

        var toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + tipo;

        var iconos = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill',
                       warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        toast.innerHTML =
            '<i class="bi ' + (iconos[tipo] || 'bi-info-circle-fill') + '"></i>' +
            '<span>' + mensaje + '</span>';

        container.appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('show'); });

        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3500);
    }

    /* ================================================================
       NOTIFICACIONES
    ================================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        var btnGuardar = document.getElementById('btnGuardarPreferenciaNotificacion');
        if (btnGuardar) {
            cargarPreferenciasNotificacion();
            cargarHistorialNotificacionesCliente();
            btnGuardar.addEventListener('click', guardarPreferenciaNotificacion);
        }
    });

    function mostrarAlertaNotificacion(mensaje, tipo) {
        var container = document.getElementById('notifAlertContainer');
        if (!container) return;
        var clase = tipo === 'success' ? 'alert-success' : (tipo === 'warning' ? 'alert-warning' : 'alert-error');
        container.innerHTML = '<div class="alert ' + clase + '">' + mensaje + '</div>';
        setTimeout(function () { container.innerHTML = ''; }, 4000);
    }

    function cargarPreferenciasNotificacion() {
        var loading = document.getElementById('loadingPreferencias');
        if (loading) loading.style.display = 'block';

        fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php?accion=obtener')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                if (data.status !== 'success') return;
                var radio = document.querySelector('input[name="preferencia_notificacion"][value="' + data.preferencia + '"]');
                if (radio) radio.checked = true;
            })
            .catch(function () {
                if (loading) loading.style.display = 'none';
                mostrarAlertaNotificacion('No se pudo cargar tu preferencia actual.', 'error');
            });
    }

    function guardarPreferenciaNotificacion() {
        var sel = document.querySelector('input[name="preferencia_notificacion"]:checked');
        if (!sel) {
            mostrarAlertaNotificacion('Selecciona una preferencia antes de guardar.', 'warning');
            return;
        }

        var btn = document.getElementById('btnGuardarPreferenciaNotificacion');
        var txt = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...'; }

        fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'actualizar', preferencia: sel.value })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (btn) { btn.disabled = false; btn.innerHTML = txt; }
            if (data.status === 'success') {
                mostrarAlertaNotificacion('Preferencia actualizada correctamente.', 'success');
                cargarHistorialNotificacionesCliente();
            } else {
                mostrarAlertaNotificacion(data.message || 'No se pudo actualizar.', 'error');
            }
        })
        .catch(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = txt; }
            mostrarAlertaNotificacion('Error de comunicación.', 'error');
        });
    }

    function cargarHistorialNotificacionesCliente() {
        var loading  = document.getElementById('loadingHistorial');
        var sinH     = document.getElementById('sinHistorial');
        var content  = document.getElementById('historialContent');
        var tbody    = document.getElementById('tablaHistorialNotificaciones');

        if (loading) loading.style.display = 'block';
        if (sinH)    sinH.style.display    = 'none';
        if (content) content.style.display = 'none';

        fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php?accion=historial&limite=10')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                if (data.status !== 'success' || !Array.isArray(data.historial) || !data.historial.length) {
                    if (sinH) sinH.style.display = 'block';
                    return;
                }
                if (tbody) tbody.innerHTML = '';
                data.historial.forEach(function (n) {
                    var fecha = n.fecha_envio ? new Date(n.fecha_envio).toLocaleString('es-CO') : 'N/A';
                    var fila  = document.createElement('tr');
                    fila.innerHTML =
                        '<td>' + fecha + '</td>' +
                        '<td>' + (n.medio_notificacion || 'email') + '</td>' +
                        '<td>' + (n.nombre_mascota || 'N/A') + '</td>' +
                        '<td>' + (n.estado_envio === 'exitoso' ? 'Entregado' : 'Fallido') + '</td>';
                    if (tbody) tbody.appendChild(fila);
                });
                if (content) content.style.display = 'block';
            })
            .catch(function () {
                if (loading) loading.style.display = 'none';
                if (sinH) { sinH.style.display = 'block'; sinH.textContent = 'No se pudo cargar el historial.'; }
            });
    }
    </script>

</body>
</html>