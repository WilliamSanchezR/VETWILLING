<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - VetWilling</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- estilos propios y algunas cosas que son importantes para que se vea mas wonito -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/clientes.css">
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
                    <button class="tab-config" onclick="cambiarTab('privacidad')">
                        <i class="bi bi-shield-lock-fill"></i>
                        Privacidad
                    </button>
                    <button class="tab-config" onclick="cambiarTab('seguridad')">
                        <i class="bi bi-key-fill"></i>
                        Seguridad
                    </button>
                </div>

                <!-- TAB: MI CUENTA -->
                <div class="tab-content" id="tab-cuenta">

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
                            <img src="https://ui-avatars.com/api/?name=Carlos+Ramirez&background=667eea&color=fff&size=120"
                                class="foto-preview"
                                id="fotoPreview"
                                alt="Foto de perfil">
                            <div class="foto-acciones">
                                <h4>Cambiar Foto de Perfil</h4>
                                <p>JPG, PNG o GIF. Tamaño máximo 2MB</p>
                                <div class="foto-btns">
                                    <input type="file" id="inputFoto" class="input-file" accept="image/*" onchange="previewFoto(event)">
                                    <label for="inputFoto" class="btn-upload">
                                        <i class="bi bi-upload"></i> Subir Foto
                                    </label>
                                    <button class="btn-remove" onclick="eliminarFoto()">
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

                        <form id="formDatosPersonales">
                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Nombre(s)</label>
                                    <input type="text" value="Carlos" placeholder="Tu nombre">
                                </div>

                                <div class="form-group-config">
                                    <label>Apellido(s)</label>
                                    <input type="text" value="Ramírez Pérez" placeholder="Tus apellidos">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Tipo de Documento</label>
                                    <select>
                                        <option selected>Cédula de Ciudadanía</option>
                                        <option>Cédula de Extranjería</option>
                                        <option>Pasaporte</option>
                                    </select>
                                </div>

                                <div class="form-group-config">
                                    <label>Número de Documento</label>
                                    <input type="text" value="1234567890" placeholder="Número de documento">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Fecha de Nacimiento</label>
                                    <input type="date" value="1988-03-15">
                                </div>

                                <div class="form-group-config">
                                    <label>Género</label>
                                    <select>
                                        <option selected>Masculino</option>
                                        <option>Femenino</option>
                                        <option>Otro</option>
                                        <option>Prefiero no decir</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert-config alert-success" id="alertExito" style="display: none;">
                                <i class="bi bi-check-circle-fill" style="font-size: 20px;"></i>
                                <div>
                                    <strong>¡Éxito!</strong> Tus datos se han actualizado correctamente.
                                </div>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="button" class="btn-config btn-primary-config" onclick="guardarDatosPersonales()">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
                                </button>
                                <button type="button" class="btn-config btn-secondary-config">
                                    <i class="bi bi-x-lg"></i>
                                    Cancelar
                                </button>
                            </div>
                        </form>
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

                        <form>
                            <div class="form-group-config">
                                <label>Email Principal</label>
                                <input type="email" value="carlos.ramirez@email.com" placeholder="tu@email.com">
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Teléfono</label>
                                    <input type="tel" value="+57 300 123 4567" placeholder="+57 300 000 0000">
                                </div>

                                <div class="form-group-config">
                                    <label>Teléfono Alternativo</label>
                                    <input type="tel" placeholder="+57 300 000 0000">
                                </div>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="button" class="btn-config btn-primary-config" onclick="guardarContacto()">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
                                </button>
                                <button type="button" class="btn-config btn-secondary-config">
                                    <i class="bi bi-x-lg"></i>
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Dirección -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h3>Dirección</h3>
                                <p>Actualiza tu dirección de residencia</p>
                            </div>
                        </div>

                        <form>
                            <div class="form-group-config">
                                <label>Dirección Completa</label>
                                <input type="text" value="Calle 123 #45-67" placeholder="Calle, carrera, número">
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Ciudad</label>
                                    <input type="text" value="Bogotá" placeholder="Ciudad">
                                </div>

                                <div class="form-group-config">
                                    <label>Departamento</label>
                                    <select>
                                        <option selected>Cundinamarca</option>
                                        <option>Antioquia</option>
                                        <option>Valle del Cauca</option>
                                        <option>Atlántico</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Código Postal</label>
                                    <input type="text" value="110111" placeholder="Código postal">
                                </div>

                                <div class="form-group-config">
                                    <label>País</label>
                                    <select>
                                        <option selected>Colombia</option>
                                        <option>Argentina</option>
                                        <option>Chile</option>
                                        <option>México</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label>Información Adicional</label>
                                <textarea rows="3" placeholder="Apartamento, conjunto, referencia..."></textarea>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="button" class="btn-config btn-primary-config" onclick="guardarDireccion()">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
                                </button>
                                <button type="button" class="btn-config btn-secondary-config">
                                    <i class="bi bi-x-lg"></i>
                                    Cancelar
                                </button>
                            </div>
                        </form>
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

                        <form>
                            <div class="form-group-config">
                                <label>Ocupación</label>
                                <input type="text" placeholder="Tu profesión u ocupación">
                            </div>

                            <div class="form-group-config">
                                <label>Biografía</label>
                                <textarea rows="4" placeholder="Cuéntanos un poco sobre ti..."></textarea>
                            </div>

                            <div class="form-group-config">
                                <label>Cómo nos conociste</label>
                                <select>
                                    <option>Selecciona una opción</option>
                                    <option>Redes sociales</option>
                                    <option>Recomendación</option>
                                    <option>Búsqueda en Google</option>
                                    <option>Publicidad</option>
                                    <option>Otro</option>
                                </select>
                            </div>

                            <div style="display: flex; gap: 15px; margin-top: 20px;">
                                <button type="button" class="btn-config btn-primary-config" onclick="guardarAdicional()">
                                    <i class="bi bi-check-lg"></i>
                                    Guardar Cambios
                                </button>
                                <button type="button" class="btn-config btn-secondary-config">
                                    <i class="bi bi-x-lg"></i>
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

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
                            <select class="config-select">
                                <option selected>Español</option>
                                <option>English</option>
                                <option>Português</option>
                            </select>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Zona Horaria</h4>
                                <p>Tu zona horaria local</p>
                            </div>
                            <select class="config-select">
                                <option selected>GMT-5 (Bogotá)</option>
                                <option>GMT-4 (Santiago)</option>
                                <option>GMT-3 (Buenos Aires)</option>
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
                                <h3>Apariencia</h3>
                                <p>Personaliza el aspecto de la aplicación</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Tema</h4>
                                <p>Elige entre tema claro u oscuro</p>
                            </div>
                            <select class="config-select" id="selectTema">
                                <option value="light" selected>Claro</option>
                                <option value="dark">Oscuro</option>
                                <option value="auto">Automático</option>
                            </select>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Tamaño de Texto</h4>
                                <p>Ajusta el tamaño del texto</p>
                            </div>
                            <select class="config-select">
                                <option>Pequeño</option>
                                <option selected>Normal</option>
                                <option>Grande</option>
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
                                <h4>Activar Notificaciones</h4>
                                <p>Recibir notificaciones en el navegador</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Citas Próximas</h4>
                                <p>Recordatorios de citas programadas</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Vacunas Pendientes</h4>
                                <p>Alertas de vacunación de tus mascotas</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Mensajes Nuevos</h4>
                                <p>Notificación de mensajes del veterinario</p>
                            </div>
                            <div class="toggle-switch active"></div>
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
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Promociones y Ofertas</h4>
                                <p>Recibir información sobre descuentos</p>
                            </div>
                            <div class="toggle-switch"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Newsletter Mensual</h4>
                                <p>Consejos de cuidado para mascotas</p>
                            </div>
                            <div class="toggle-switch active"></div>
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
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Confirmaciones de Citas</h4>
                                <p>SMS de confirmación de citas</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>
                    </div>

                </div>

                <!-- TAB: PRIVACIDAD -->
                <div class="tab-content" id="tab-privacidad" style="display: none;">

                    <!-- Privacidad de Datos -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h3>Privacidad de Datos</h3>
                                <p>Controla quién puede ver tu información</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Perfil Público</h4>
                                <p>Permitir que otros usuarios vean tu perfil</p>
                            </div>
                            <div class="toggle-switch"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Compartir Historial Médico</h4>
                                <p>Permitir compartir historial con otros veterinarios</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Uso de Datos para Mejorar Servicio</h4>
                                <p>Ayúdanos a mejorar usando datos anónimos</p>
                            </div>
                            <div class="toggle-switch active"></div>
                        </div>
                    </div>

                    <!-- Descargar Datos -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-download"></i>
                            </div>
                            <div>
                                <h3>Tus Datos</h3>
                                <p>Descarga o elimina tu información</p>
                            </div>
                        </div>

                        <div class="alert-config alert-warning">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px;"></i>
                            <div>
                                <strong>Información importante:</strong> Puedes descargar una copia de todos tus datos en cualquier momento.
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button class="btn-config btn-primary-config">
                                <i class="bi bi-download"></i>
                                Descargar Mis Datos
                            </button>
                            <button class="btn-config btn-secondary-config">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Exportar PDF
                            </button>
                        </div>
                    </div>

                </div>

                <!-- TAB: SEGURIDAD -->
                <div class="tab-content" id="tab-seguridad" style="display: none;">

                    <!-- Cambiar Contraseña -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <div>
                                <h3>Cambiar Contraseña</h3>
                                <p>Actualiza tu contraseña regularmente</p>
                            </div>
                        </div>

                        <form>
                            <div class="form-group-config">
                                <label>Contraseña Actual</label>
                                <input type="password" placeholder="Ingresa tu contraseña actual">
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label>Nueva Contraseña</label>
                                    <input type="password" placeholder="Mínimo 8 caracteres">
                                </div>

                                <div class="form-group-config">
                                    <label>Confirmar Contraseña</label>
                                    <input type="password" placeholder="Confirma tu contraseña">
                                </div>
                            </div>

                            <button type="button" class="btn-config btn-primary-config" onclick="cambiarPassword()">
                                <i class="bi bi-check-lg"></i>
                                Actualizar Contraseña
                            </button>
                        </form>
                    </div>

                    <!-- Autenticación en Dos Pasos -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div>
                                <h3>Autenticación en Dos Pasos</h3>
                                <p>Añade una capa extra de seguridad</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Activar 2FA</h4>
                                <p>Verificación en dos pasos para mayor seguridad</p>
                            </div>
                            <div class="toggle-switch"></div>
                        </div>

                        <div class="alert-config alert-success">
                            <i class="bi bi-check-circle-fill" style="font-size: 20px;"></i>
                            <div>
                                Tu cuenta está protegida. Última actividad: Hoy a las 10:30 AM
                            </div>
                        </div>
                    </div>

                    <!-- Sesiones Activas -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-laptop"></i>
                            </div>
                            <div>
                                <h3>Sesiones Activas</h3>
                                <p>Dispositivos con acceso a tu cuenta</p>
                            </div>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Chrome - Windows</h4>
                                <p>Bogotá, Colombia • Última actividad: Ahora</p>
                            </div>
                            <button class="btn-config btn-secondary-config">
                                <i class="bi bi-check-circle-fill"></i>
                                Actual
                            </button>
                        </div>

                        <div class="config-item">
                            <div class="config-info">
                                <h4>Safari - iPhone</h4>
                                <p>Bogotá, Colombia • Hace 2 horas</p>
                            </div>
                            <button class="btn-config btn-danger-config">
                                <i class="bi bi-x-circle"></i>
                                Cerrar Sesión
                            </button>
                        </div>

                        <div style="margin-top: 20px;">
                            <button class="btn-config btn-danger-config">
                                <i class="bi bi-x-octagon"></i>
                                Cerrar Todas las Sesiones
                            </button>
                        </div>
                    </div>

                    <!-- Eliminar Cuenta -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon" style="background: linear-gradient(135deg, #f44336, #e91e63);">
                                <i class="bi bi-trash-fill"></i>
                            </div>
                            <div>
                                <h3>Zona de Peligro</h3>
                                <p>Acciones irreversibles</p>
                            </div>
                        </div>

                        <div class="alert-config alert-danger">
                            <i class="bi bi-exclamation-octagon-fill" style="font-size: 20px;"></i>
                            <div>
                                <strong>¡Atención!</strong> Eliminar tu cuenta es permanente y no se puede deshacer.
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <button class="btn-config btn-danger-config" onclick="confirmarEliminar()">
                                <i class="bi bi-trash"></i>
                                Eliminar Mi Cuenta
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>
    <!-- este es el js de la confi -->
    <script>
        // Cambiar Tab
        function cambiarTab(tab) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });

            // Remover active de todos los botones
            document.querySelectorAll('.tab-config').forEach(btn => {
                btn.classList.remove('active');
            });

            // Mostrar tab seleccionado
            document.getElementById(`tab-${tab}`).style.display = 'block';

            // Activar botón correspondiente
            event.target.closest('.tab-config').classList.add('active');
        }
    </script>
</body>

</html>