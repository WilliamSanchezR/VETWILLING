<?php
// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/propetarioController.php';

// asignamos el valor id del registro según la tabla
$id = $_SESSION['user']['id_propietario'] ?? '';

// Llamamos la funcion del controlador
$usuario = consultarPropietarioId($id);
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

    <!-- estilos propios y algunas cosas que son importantes para que se vea mas wonito -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/perfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/confi.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/sidebar.css">

</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="contenido-principal">
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
                    <form method="POST" action="<?= BASE_URL ?>/Cliente/actualizar" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_propietario" value="<?= $_SESSION['user']['id'] ?>">

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
                                        <button type="button" class="btn-remove" onclick="event.preventDefault(); eliminarFoto();">
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
                                    <input type="tel" name="telefono_alt" placeholder="+57 300 000 0000">
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
                                <input type="text" name="direccion" value="<?= $usuario['direccion'] ?>">
                            </div>

                            <div class="form-group-config">
                                <label>Biografía</label>
                                <textarea name="biografia" rows="4" placeholder="Cuéntanos un poco sobre ti..."></textarea>
                            </div>

                            <div class="form-group-config">
                                <label>Cómo nos conociste</label>
                                <select name="como_conociste">
                                    <option>Selecciona una opción</option>
                                    <option>Redes sociales</option>
                                    <option>Recomendación</option>
                                    <option>Búsqueda en Google</option>
                                    <option>Publicidad</option>
                                    <option>Otro</option>
                                </select>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="submit" class="btn-config btn-primary-config">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
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
                                <h3 data-translate="language_region">Idioma y Región</h3>
                                <p data-translate="language_region_desc">Configura tu idioma y zona horaria</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-translate="language">Idioma</h4>
                                <p data-translate="language_desc">Selecciona el idioma de la aplicación</p>
                            </div>
                            <select class="config-select" id="selectIdioma">
                                <option value="es">Español</option>
                                <option value="en">English</option>
                                <option value="pt">Português</option>
                            </select>
                        </div>

                        <div class="language-info" id="languageInfo" style="display: none;">
                            <i class="bi bi-info-circle"></i>
                            <span data-translate="language_changed">Idioma cambiado exitosamente</span>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-translate="timezone">Zona Horaria</h4>
                                <p data-translate="timezone_desc">Tu zona horaria local</p>
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
                    </div>

                    <!-- Apariencia -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-palette-fill"></i>
                            </div>
                            <div>
                                <h3 data-translate="appearance">Apariencia</h3>
                                <p data-translate="appearance_desc">Personaliza el aspecto de la aplicación</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-translate="theme">Tema</h4>
                                <p data-translate="theme_desc">Elige entre tema claro u oscuro</p>
                            </div>
                            <select class="config-select" id="selectTema">
                                <option value="light" data-translate="light">Claro</option>
                                <option value="dark" data-translate="dark">Oscuro</option>
                                <option value="auto" data-translate="auto">Automático</option>
                            </select>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-translate="text_size">Tamaño de Texto</h4>
                                <p data-translate="text_size_desc">Ajusta el tamaño del texto</p>
                            </div>
                            <select class="config-select" id="selectTamanoTexto">
                                <option value="14" data-translate="small">Pequeño</option>
                                <option value="16" data-translate="normal">Normal</option>
                                <option value="18" data-translate="large">Grande</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TAB: NOTIFICACIONES -->
                <div class="tab-content" id="tab-notificaciones" style="display: none;">
                    <!-- Notificaciones Push -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div>
                                <h3>Notificaciones Push</h3>
                                <p>Recibe alertas en tiempo real</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>
                                    Activar Notificaciones
                                    <span class="badge badge-new">Principal</span>
                                </h4>
                                <p>Recibir notificaciones en el navegador</p>
                            </div>
                            <div class="toggle-switch" data-setting="push-enabled"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Citas Próximas</h4>
                                <p>Recordatorios de citas programadas</p>
                            </div>
                            <div class="toggle-switch" data-setting="push-appointments"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Vacunas Pendientes</h4>
                                <p>Alertas de vacunación de tus mascotas</p>
                            </div>
                            <div class="toggle-switch" data-setting="push-vaccines"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Mensajes Nuevos</h4>
                                <p>Notificación de mensajes del veterinario</p>
                            </div>
                            <div class="toggle-switch" data-setting="push-messages"></div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <h3>Notificaciones por Email</h3>
                                <p>Configura tus preferencias de correo</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Recordatorios de Citas</h4>
                                <p>Recibir emails antes de cada cita</p>
                            </div>
                            <div class="toggle-switch" data-setting="email-appointments"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Promociones y Ofertas</h4>
                                <p>Recibir información sobre descuentos</p>
                            </div>
                            <div class="toggle-switch" data-setting="email-promotions"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Newsletter Mensual</h4>
                                <p>Consejos de cuidado para mascotas</p>
                            </div>
                            <div class="toggle-switch" data-setting="email-newsletter"></div>
                        </div>
                    </div>

                    <!-- SMS -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-chat-text-fill"></i>
                            </div>
                            <div>
                                <h3>Notificaciones SMS</h3>
                                <p>Recibe mensajes de texto</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Activar SMS</h4>
                                <p>Recibir mensajes de texto importantes</p>
                            </div>
                            <div class="toggle-switch" data-setting="sms-enabled"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Confirmaciones de Citas</h4>
                                <p>SMS de confirmación de citas</p>
                            </div>
                            <div class="toggle-switch" data-setting="sms-confirmations"></div>
                        </div>

                        <!-- Estadísticas de notificaciones -->
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number" id="totalNotifications">0</div>
                                <div class="stat-label">Notificaciones Activas</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="pushCount">0</div>
                                <div class="stat-label">Push Activas</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="emailCount">0</div>
                                <div class="stat-label">Email Activos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="smsCount">0</div>
                                <div class="stat-label">SMS Activos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: SEGURIDAD -->
                <div class="tab-content" id="tab-seguridad" style="display: none;">
                    <!-- Cambiar Contraseña -->
                    <div class="config-card">
                        <div class="card-header">
                            <h2>Cambiar Contraseña</h2>
                            <p>Actualiza tu contraseña para mantener tu cuenta segura</p>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/Cliente/actualizar-contrasena" id="passwordForm">
                            <input type="hidden" name="id_usuario" value="<?= $_SESSION['user']['id_usuario'] ?>">
                            <input type="hidden" name="accion" value="modificar-constrasena">

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
                </div>

                <!-- Notificación de guardado GLOBAL -->
                <div class="save-notification" id="saveNotification">
                    <i class="bi bi-check-circle-fill"></i>
                    <span id="notificationText">Configuración guardada</span>
                </div>

            </div>
        </div>
    </main>
    
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/confi.js"></script>
</body>

</html>