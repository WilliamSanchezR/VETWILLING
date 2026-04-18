<?php 
require_once BASE_PATH . '/app/helpers/session_propietario.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - VetWilling</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/confi.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer" role="status" aria-live="polite"></div>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- HEADER -->
                <div class="header-config">
                    <h1>⚙️ Configuración</h1>
                    <p>Personaliza tu experiencia en VetWilling</p>
                </div>

                <!-- TABS -->
                <div class="tabs-config">
                    <button class="tab-config active" onclick="cambiarTab('cuenta')">
                        <i class="bi bi-person-fill"></i>
                        Mi Cuenta
                    </button>
                    <button class="tab-config" onclick="cambiarTab('general')">
                        <i class="bi bi-gear-fill"></i>
                        General
                    </button>
                    <button class="tab-config" onclick="cambiarTab('notificaciones')">
                        <i class="bi bi-bell-fill"></i>
                        Notificaciones
                    </button>
                    <button class="tab-config" onclick="cambiarTab('seguridad')">
                        <i class="bi bi-key-fill"></i>
                        Seguridad
                    </button>
                </div>

                <!-- TAB: MI CUENTA -->
                <div class="tab-content" id="tab-cuenta">
                    <form method="POST" action="<?= BASE_URL ?>/cliente/actualizar" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_propietario" value="<?= $_SESSION['user']['id_usuario'] ?>">

                        <!-- Foto de Perfil -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                                <div>
                                    <h3>Foto de Perfil</h3>
                                    <p>Actualiza tu imagen de perfil</p>
                                </div>
                            </div>

                            <div class="foto-perfil-container">
                                <div class="avatar-grande">
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt="Foto de perfil">
                                </div>
                                <div class="foto-acciones">
                                    <h4>Cambiar Foto de Perfil</h4>
                                    <p>JPG, PNG o GIF. Tamaño máximo 2MB</p>
                                    <div class="foto-btns">
                                        <input type="file" name="img_perfil" id="inputFoto" class="input-file" accept="image/*" onchange="previewFoto(event)">
                                        <label for="inputFoto" class="btn-upload">
                                            <i class="bi bi-upload"></i> Subir Foto
                                        </label>
                                        <button type="button" class="btn-remove" onclick="eliminarFoto()">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Personal -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div>
                                    <h3>Información Personal</h3>
                                    <p>Actualiza tus datos personales</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Nombre(s)</label>
                                    <input type="text" name="nombres" value="<?= $usuario['nombres'] ?>" required>
                                </div>

                                <div class="form-group-config">
                                    <label>Apellido(s)</label>
                                    <input type="text" name="apellidos" value="<?= $usuario['apellidos'] ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Tipo de Documento</label>
                                    <select name="tipo_documento" required>
                                        <option <?= $usuario['tipo_documento'] == 'Cédula de Ciudadanía' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                        <option <?= $usuario['tipo_documento'] == 'Cédula de Extranjería' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                        <option <?= $usuario['tipo_documento'] == 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                    </select>
                                </div>

                                <div class="form-group-config">
                                    <label>Número de Documento</label>
                                    <input type="text" name="numero_documento" value="<?= $usuario['numero_documento'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <h3>Información de Contacto</h3>
                                    <p>Actualiza tu email y teléfono</p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label>Email Principal</label>
                                <input type="email" name="email" value="<?= $usuario['email'] ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Teléfono</label>
                                    <input type="tel" name="telefono" value="<?= $usuario['telefono'] ?>" required>
                                </div>

                                <div class="form-group-config">
                                    <label>Teléfono Alternativo</label>
                                    <input type="tel" name="telefono_alt" value="<?= isset($usuario['telefono_alt']) ? $usuario['telefono_alt'] : '' ?>" placeholder="+57 300 000 0000">
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <div>
                                    <h3>Información Adicional</h3>
                                    <p>Datos complementarios opcionales</p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label>Dirección</label>
                                <input type="text" name="direccion" value="<?= isset($usuario['direccion']) ? $usuario['direccion'] : '' ?>">
                            </div>

                            <div class="form-group-config">
                                <label>Biografía</label>
                                <textarea name="biografia" rows="4" placeholder="Cuéntanos un poco sobre ti..."><?= isset($usuario['biografia']) ? $usuario['biografia'] : '' ?></textarea>
                            </div>

                            <div class="form-group-config">
                                <label>Cómo nos conociste</label>
                                <select name="como_conociste">
                                    <option value="">Selecciona una opción</option>
                                    <option value="redes_sociales" <?= isset($usuario['como_conociste']) && $usuario['como_conociste'] == 'redes_sociales' ? 'selected' : '' ?>>Redes sociales</option>
                                    <option value="recomendacion" <?= isset($usuario['como_conociste']) && $usuario['como_conociste'] == 'recomendacion' ? 'selected' : '' ?>>Recomendación</option>
                                    <option value="google" <?= isset($usuario['como_conociste']) && $usuario['como_conociste'] == 'google' ? 'selected' : '' ?>>Búsqueda en Google</option>
                                    <option value="publicidad" <?= isset($usuario['como_conociste']) && $usuario['como_conociste'] == 'publicidad' ? 'selected' : '' ?>>Publicidad</option>
                                    <option value="otro" <?= isset($usuario['como_conociste']) && $usuario['como_conociste'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="submit" class="btn-config btn-primary-config">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
                                </button>
                                <button type="reset" class="btn-config btn-secondary-config">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Restablecer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB: GENERAL -->
                <div class="tab-content" id="tab-general" style="display: none;">
                    <!-- Idioma y Región -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-globe2"></i>
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
                                <option value="fr">Français</option>
                                <option value="de">Deutsch</option>
                            </select>
                        </div>

                        <div class="language-info" id="languageInfo" style="display: none;">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Idioma cambiado exitosamente</span>
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
                                <option value="gmt-0">GMT+0 (Londres)</option>
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

                    <!-- Centro de Ayuda y Soporte -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div>
                                <h3>Centro de Ayuda</h3>
                                <p>Encuentra respuestas rápidas o contáctanos</p>
                            </div>
                        </div>

                        <!-- FAQ Section -->
                        <div class="faq-section">
                            <h4 style="margin-bottom: 12px; color: #2d3748;">Preguntas Frecuentes</h4>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿Cómo cambio mi contraseña?</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Ve a Configuración > Seguridad > Cambiar Contraseña. Necesitarás tu contraseña actual para establecer una nueva.
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿Cómo exporto mis datos?</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Puedes exportar tus datos desde Configuración > Privacidad > Exportar Datos. Recibirás un archivo ZIP con toda tu información.
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFAQ(this)">
                                    <span>¿La aplicación funciona sin internet?</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Algunas funciones básicas están disponibles offline, pero necesitarás conexión para sincronizar tus datos y acceder a todas las características.
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-clock"></i>
                            <span>Tiempo de respuesta estimado: 24-48 horas</span>
                        </div>
                    </div>
                </div>

                <!-- TAB: NOTIFICACIONES -->
                <div class="tab-content" id="tab-notificaciones" style="display: none;">
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div>
                                <h3>Preferencias de Notificación</h3>
                                <p>Configura cómo deseas recibir recordatorios de tus citas</p>
                            </div>
                        </div>

                        <div id="notifAlertContainer" style="margin-bottom: 16px;"></div>

                        <div id="loadingPreferencias" class="alert alert-info" style="display: none;">
                            Cargando tus preferencias de notificación...
                        </div>

                        <div class="config-item" style="align-items: flex-start;">
                            <div class="config-info" style="flex: 1;">
                                <h4>Recibir Notificaciones por Email</h4>
                                <p>Incluye confirmación de cita, recordatorio 24 horas antes y cancelaciones.</p>
                            </div>
                            <div>
                                <input type="radio" name="preferencia_notificacion" id="pref_email" value="email" checked>
                            </div>
                        </div>

                        <div class="config-item" style="align-items: flex-start;">
                            <div class="config-info" style="flex: 1;">
                                <h4>No Recibir Notificaciones</h4>
                                <p>Desactiva el envío de correos de notificación.</p>
                            </div>
                            <div>
                                <input type="radio" name="preferencia_notificacion" id="pref_ninguno" value="ninguno">
                            </div>
                        </div>

                        <div class="alert alert-info" style="margin-top: 16px;">
                            <i class="bi bi-info-circle"></i>
                            <span>Estas preferencias aplican a tus próximas citas. El sistema no usa SMS.</span>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 16px;">
                            <button type="button" class="btn-config btn-primary-config" id="btnGuardarPreferenciaNotificacion">
                                <i class="bi bi-check-lg"></i>
                                Guardar Preferencia
                            </button>
                        </div>
                    </div>

                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h3>Historial de Notificaciones</h3>
                                <p>Últimos envíos realizados por el sistema</p>
                            </div>
                        </div>

                        <div id="loadingHistorial" class="alert alert-info" style="display: none;">
                            Cargando historial de notificaciones...
                        </div>

                        <div id="sinHistorial" class="alert alert-warning" style="display: none;">
                            No hay notificaciones registradas todavía.
                        </div>

                        <div id="historialContent" style="display: none; overflow-x: auto;">
                            <table class="table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Fecha</th>
                                        <th style="text-align: left;">Medio</th>
                                        <th style="text-align: left;">Mascota</th>
                                        <th style="text-align: left;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaHistorialNotificaciones"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB: SEGURIDAD -->
                <div class="tab-content" id="tab-seguridad" style="display: none;">
                    <!-- Cambiar Contraseña -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <h3>Cambiar Contraseña</h3>
                                <p>Actualiza tu contraseña para mantener tu cuenta segura</p>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/cliente/actualizar-contrasena" id="passwordForm">
                            <input type="hidden" name="id_usuario" value="<?= $_SESSION['user']['id_usuario'] ?>">
                            <input type="hidden" name="accion" value="modificar-contrasena">

                            <div class="form-group-config">
                                <label>Contraseña Actual</label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="contrasena-actual" id="currentPassword" required placeholder="Ingresa tu contraseña actual">
                                    <button type="button" class="toggle-password" onclick="togglePassword('currentPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Nueva Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="nueva-contrasena" id="newPassword" required minlength="8" placeholder="Mínimo 8 caracteres" oninput="checkPasswordStrength()">
                                        <button type="button" class="toggle-password" onclick="togglePassword('newPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="strengthIndicator" style="display: none;">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <span class="strength-text" id="strengthText"></span>
                                    </div>
                                </div>

                                <div class="form-group-config">
                                    <label>Confirmar Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="confi-contrasena" id="confirmPassword" required minlength="8" placeholder="Confirma tu contraseña" oninput="checkPasswordMatch()">
                                        <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="matchIndicator" style="margin-top: 8px; font-size: 12px;"></div>
                                </div>
                            </div>

                            <div class="requirements">
                                <div class="requirements-title">
                                    <i class="bi bi-shield-check"></i>
                                    Requisitos de la contraseña
                                </div>
                                <div class="requirement-item" id="req-length">
                                    <i class="bi bi-circle"></i>
                                    Mínimo 8 caracteres
                                </div>
                                <div class="requirement-item" id="req-uppercase">
                                    <i class="bi bi-circle"></i>
                                    Al menos una letra mayúscula
                                </div>
                                <div class="requirement-item" id="req-number">
                                    <i class="bi bi-circle"></i>
                                    Al menos un número
                                </div>
                            </div>

                            <button type="submit" class="btn-config btn-primary-config">
                                <i class="bi bi-check-lg"></i>
                                Actualizar Contraseña
                            </button>
                        </form>
                    </div>

                    <!-- Actividad Reciente -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h3>Actividad Reciente</h3>
                                <p>Últimos accesos a tu cuenta</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <span>Si notas alguna actividad sospechosa, cambia tu contraseña inmediatamente</span>
                        </div>
                    </div>
                </div>

                <!-- Notificación de guardado GLOBAL -->
                <div class="save-notification" id="saveNotification">
                    <i class="bi bi-check-circle-fill"></i>
                    <span id="notificationText">Configuración guardada</span>
                </div>

            </div>
        </div>

    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/confi.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGuardar = document.getElementById('btnGuardarPreferenciaNotificacion');
            if (!btnGuardar) return;

            cargarPreferenciasNotificacion();
            cargarHistorialNotificacionesCliente();

            btnGuardar.addEventListener('click', guardarPreferenciaNotificacion);
        });

        function mostrarAlertaNotificacion(mensaje, tipo) {
            const container = document.getElementById('notifAlertContainer');
            if (!container) return;
            const clase = tipo === 'success' ? 'alert-success' : (tipo === 'warning' ? 'alert-warning' : 'alert-danger');
            container.innerHTML = '<div class="alert ' + clase + '">' + mensaje + '</div>';
            setTimeout(function() { container.innerHTML = ''; }, 4000);
        }

        function cargarPreferenciasNotificacion() {
            const loading = document.getElementById('loadingPreferencias');
            if (loading) loading.style.display = 'block';

            fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php?accion=obtener')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (loading) loading.style.display = 'none';
                    if (data.status !== 'success') return;

                    const radio = document.querySelector('input[name="preferencia_notificacion"][value="' + data.preferencia + '"]');
                    if (radio) radio.checked = true;
                })
                .catch(function() {
                    if (loading) loading.style.display = 'none';
                    mostrarAlertaNotificacion('No se pudo cargar tu preferencia actual.', 'error');
                });
        }

        function guardarPreferenciaNotificacion() {
            const seleccionado = document.querySelector('input[name="preferencia_notificacion"]:checked');
            if (!seleccionado) {
                mostrarAlertaNotificacion('Selecciona una preferencia antes de guardar.', 'warning');
                return;
            }

            const btnGuardar = document.getElementById('btnGuardarPreferenciaNotificacion');
            const textoOriginal = btnGuardar ? btnGuardar.innerHTML : '';
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            }

            fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    accion: 'actualizar',
                    preferencia: seleccionado.value
                })
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (btnGuardar) {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = textoOriginal;
                    }

                    if (data.status === 'success') {
                        mostrarAlertaNotificacion('Preferencia actualizada correctamente.', 'success');
                        cargarHistorialNotificacionesCliente();
                        return;
                    }

                    mostrarAlertaNotificacion(data.message || 'No se pudo actualizar la preferencia.', 'error');
                })
                .catch(function() {
                    if (btnGuardar) {
                        btnGuardar.disabled = false;
                        btnGuardar.innerHTML = textoOriginal;
                    }
                    mostrarAlertaNotificacion('Error de comunicación al guardar la preferencia.', 'error');
                });
        }

        function cargarHistorialNotificacionesCliente() {
            const loading = document.getElementById('loadingHistorial');
            const sinHistorial = document.getElementById('sinHistorial');
            const historialContent = document.getElementById('historialContent');
            const tbody = document.getElementById('tablaHistorialNotificaciones');

            if (loading) loading.style.display = 'block';
            if (sinHistorial) sinHistorial.style.display = 'none';
            if (historialContent) historialContent.style.display = 'none';

            fetch('<?= BASE_URL ?>/app/controllers/preferenciasNotificacionController.php?accion=historial&limite=10')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (loading) loading.style.display = 'none';

                    if (data.status !== 'success' || !Array.isArray(data.historial) || data.historial.length === 0) {
                        if (sinHistorial) sinHistorial.style.display = 'block';
                        return;
                    }

                    if (tbody) tbody.innerHTML = '';

                    data.historial.forEach(function(notif) {
                        const fecha = notif.fecha_envio ? new Date(notif.fecha_envio) : null;
                        const fechaFormato = fecha ? fecha.toLocaleString('es-CO') : 'N/A';
                        const estadoTexto = notif.estado_envio === 'exitoso' ? 'Entregado' : 'Fallido';

                        const fila = document.createElement('tr');
                        fila.innerHTML =
                            '<td>' + fechaFormato + '</td>' +
                            '<td>' + (notif.medio_notificacion || 'email') + '</td>' +
                            '<td>' + (notif.nombre_mascota || 'N/A') + '</td>' +
                            '<td>' + estadoTexto + '</td>';

                        if (tbody) tbody.appendChild(fila);
                    });

                    if (historialContent) historialContent.style.display = 'block';
                })
                .catch(function() {
                    if (loading) loading.style.display = 'none';
                    if (sinHistorial) {
                        sinHistorial.style.display = 'block';
                        sinHistorial.textContent = 'No se pudo cargar el historial de notificaciones.';
                    }
                });
        }
    </script>
</body>

</html>