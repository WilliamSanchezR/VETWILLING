<?php
/**
 * confi.php – VetWilling
 * Vista de Configuración del Cliente — versión integrada completa
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/services/SessionManager.php';
require_once BASE_PATH . '/app/services/PreferenciasManager.php';
require_once BASE_PATH . '/app/lang/i18n.php';

$sm       = new SessionManager($_SESSION['user']['id_usuario']);
$sesiones = $sm->listar();

$pm    = new PreferenciasManager($_SESSION['user']['id_usuario']);
$prefs = $pm->obtener();
$t     = I18n::cargar($prefs['idioma']);

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Iniciales del usuario para el avatar fallback
$iniciales = mb_strtoupper(
    mb_substr($usuario['nombres']   ?? '?', 0, 1) .
    mb_substr($usuario['apellidos'] ?? '',  0, 1)
);
?>
<!DOCTYPE html>
<html lang="<?= e($prefs['idioma']) ?>" data-tema="<?= e($prefs['tema']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('config.titulo') ?> - VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS propios: sidebar primero (layout base) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/confi.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <!-- tema-oscuro.css DESPUÉS de todos para tener precedencia -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/tema-oscuro.css">

    <!-- Inyección de preferencias y BASE_URL para preferencias.js -->
    <script>
        var BASE_URL     = '<?= BASE_URL ?>';
        window.__prefs   = <?= json_encode($prefs, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    </script>
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer" role="status" aria-live="polite"></div>

    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- Contenido principal -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- Header -->
                <div class="header-config">
                    <h1>⚙️ <span data-i18n="config.titulo"><?= $t('config.titulo') ?></span></h1>
                    <p data-i18n="config.subtitulo"><?= $t('config.subtitulo') ?></p>
                </div>

                <!-- ── Tabs ── -->
                <div class="tabs-config" role="tablist">
                    <button class="tab-config active" onclick="cambiarTab('cuenta')"
                            role="tab" aria-selected="true" aria-controls="tab-cuenta">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.cuenta"><?= $t('tab.cuenta') ?></span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('general')"
                            role="tab" aria-selected="false" aria-controls="tab-general">
                        <i class="bi bi-gear-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.general"><?= $t('tab.general') ?></span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('notificaciones')"
                            role="tab" aria-selected="false" aria-controls="tab-notificaciones">
                        <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.notificaciones"><?= $t('tab.notificaciones') ?></span>
                    </button>
                    <button class="tab-config" onclick="cambiarTab('seguridad')"
                            role="tab" aria-selected="false" aria-controls="tab-seguridad">
                        <i class="bi bi-key-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.seguridad"><?= $t('tab.seguridad') ?></span>
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
                                    <h3 data-i18n="cuenta.foto"><?= $t('cuenta.foto') ?></h3>
                                    <p  data-i18n="cuenta.foto.sub"><?= $t('cuenta.foto.sub') ?></p>
                                </div>
                            </div>

                            <div class="foto-perfil-container">
                                <div class="avatar-grande">
                                    <!--
                                        SIN onerror inline.
                                        preferencias.js detecta error de carga y reemplaza
                                        con canvas de iniciales. data-avatar-fallback
                                        provee las letras a dibujar.
                                    -->
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= e($usuario['img_perfil']) ?>"
                                         alt="Foto de perfil de <?= e($usuario['nombres']) ?>"
                                         data-avatar-fallback="<?= e($iniciales) ?>">
                                </div>
                                <div class="foto-acciones">
                                    <h4 data-i18n="cuenta.foto.cambiar"><?= $t('cuenta.foto.cambiar') ?></h4>
                                    <p  data-i18n="cuenta.foto.info"><?= $t('cuenta.foto.info') ?></p>
                                    <div class="foto-btns">
                                        <input type="file" name="img_perfil" id="inputFoto"
                                               class="input-file" accept="image/*"
                                               onchange="previewFoto(event)">
                                        <label for="inputFoto" class="btn-upload">
                                            <i class="bi bi-upload" aria-hidden="true"></i>
                                            <span data-i18n="cuenta.foto.subir"><?= $t('cuenta.foto.subir') ?></span>
                                        </label>
                                        <button type="button" class="btn-remove" onclick="eliminarFoto()">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                            <span data-i18n="cuenta.foto.eliminar"><?= $t('cuenta.foto.eliminar') ?></span>
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
                                    <h3 data-i18n="cuenta.personal"><?= $t('cuenta.personal') ?></h3>
                                    <p  data-i18n="cuenta.personal.sub"><?= $t('cuenta.personal.sub') ?></p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-nombres" data-i18n="cuenta.nombres"><?= $t('cuenta.nombres') ?></label>
                                    <input type="text" id="inp-nombres" name="nombres"
                                           value="<?= e($usuario['nombres']) ?>" required>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-apellidos" data-i18n="cuenta.apellidos"><?= $t('cuenta.apellidos') ?></label>
                                    <input type="text" id="inp-apellidos" name="apellidos"
                                           value="<?= e($usuario['apellidos']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tipo-doc" data-i18n="cuenta.tipo_doc"><?= $t('cuenta.tipo_doc') ?></label>
                                    <select id="inp-tipo-doc" name="tipo_documento" required>
                                        <?php
                                        $tipos = ['Cédula de Ciudadanía', 'Cédula de Extranjería', 'Pasaporte'];
                                        foreach ($tipos as $tipo): ?>
                                            <option value="<?= e($tipo) ?>"
                                                <?= ($usuario['tipo_documento'] ?? '') === $tipo ? 'selected' : '' ?>>
                                                <?= e($tipo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-num-doc" data-i18n="cuenta.num_doc"><?= $t('cuenta.num_doc') ?></label>
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
                                    <h3 data-i18n="cuenta.contacto"><?= $t('cuenta.contacto') ?></h3>
                                    <p  data-i18n="cuenta.contacto.sub"><?= $t('cuenta.contacto.sub') ?></p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-email" data-i18n="cuenta.email"><?= $t('cuenta.email') ?></label>
                                <input type="email" id="inp-email" name="email"
                                       value="<?= e($usuario['email']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tel" data-i18n="cuenta.telefono"><?= $t('cuenta.telefono') ?></label>
                                    <input type="tel" id="inp-tel" name="telefono"
                                           value="<?= e($usuario['telefono']) ?>" required>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-tel-alt" data-i18n="cuenta.telefono_alt"><?= $t('cuenta.telefono_alt') ?></label>
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
                                    <h3 data-i18n="cuenta.adicional"><?= $t('cuenta.adicional') ?></h3>
                                    <p  data-i18n="cuenta.adicional.sub"><?= $t('cuenta.adicional.sub') ?></p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-dir" data-i18n="cuenta.direccion"><?= $t('cuenta.direccion') ?></label>
                                <input type="text" id="inp-dir" name="direccion"
                                       value="<?= e($usuario['direccion'] ?? '') ?>">
                            </div>

                            <div class="form-group-config">
                                <label for="inp-bio" data-i18n="cuenta.biografia"><?= $t('cuenta.biografia') ?></label>
                                <textarea id="inp-bio" name="biografia" rows="4"
                                          data-i18n-placeholder="cuenta.biografia.ph"
                                          placeholder="<?= $t('cuenta.biografia.ph') ?>"><?= e($usuario['biografia'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-como" data-i18n="cuenta.como"><?= $t('cuenta.como') ?></label>
                                <select id="inp-como" name="como_conociste">
                                    <option value=""><?= $t('cuenta.como.opcion') ?></option>
                                    <?php
                                    $opciones = [
                                        'redes_sociales' => $t('cuenta.como.redes'),
                                        'recomendacion'  => $t('cuenta.como.recom'),
                                        'google'         => $t('cuenta.como.google'),
                                        'publicidad'     => $t('cuenta.como.pub'),
                                        'otro'           => $t('cuenta.como.otro'),
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
                                    <span data-i18n="general.guardar"><?= $t('general.guardar') ?></span>
                                </button>
                                <button type="reset" class="btn-config btn-secondary-config">
                                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                    <span data-i18n="general.restablecer"><?= $t('general.restablecer') ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: GENERAL
                ══════════════════════════════════════════════════ -->
                <div class="tab-content tab-content--hidden" id="tab-general" role="tabpanel">

                    <!-- Idioma, tema, zona, fecha -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-globe2" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3 data-i18n="gen.idioma"><?= $t('gen.idioma') ?></h3>
                                <p  data-i18n="gen.idioma.sub"><?= $t('gen.idioma.sub') ?></p>
                            </div>
                        </div>

                        <!-- Apariencia -->
                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-i18n="gen.tema"><?= $t('gen.tema') ?></h4>
                                <p  data-i18n="gen.tema.sub"><?= $t('gen.tema.sub') ?></p>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" class="tema-btn"
                                        data-accion-tema="claro"
                                        aria-pressed="<?= $prefs['tema'] === 'claro' ? 'true' : 'false' ?>">
                                    <i class="bi bi-sun-fill" aria-hidden="true"></i>
                                    <span data-i18n="gen.tema.claro"><?= $t('gen.tema.claro') ?></span>
                                </button>
                                <button type="button" class="tema-btn"
                                        data-accion-tema="oscuro"
                                        aria-pressed="<?= $prefs['tema'] === 'oscuro' ? 'true' : 'false' ?>">
                                    <i class="bi bi-moon-fill" aria-hidden="true"></i>
                                    <span data-i18n="gen.tema.oscuro"><?= $t('gen.tema.oscuro') ?></span>
                                </button>
                            </div>
                        </div>

                        <!-- Idioma -->
                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-i18n="gen.idioma.label"><?= $t('gen.idioma.label') ?></h4>
                            </div>
                            <select class="config-select" id="selectIdioma">
                                <option value="es" <?= $prefs['idioma'] === 'es' ? 'selected' : '' ?>>Español</option>
                                <option value="en" <?= $prefs['idioma'] === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="pt" <?= $prefs['idioma'] === 'pt' ? 'selected' : '' ?>>Português</option>
                            </select>
                        </div>

                        <!-- Zona horaria -->
                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-i18n="gen.zona"><?= $t('gen.zona') ?></h4>
                                <p  data-i18n="gen.zona.sub"><?= $t('gen.zona.sub') ?></p>
                            </div>
                            <select class="config-select" id="selectZonaHoraria">
                                <?php
                                $zonas = [
                                    'gmt-6' => 'GMT-6 (Ciudad de México)',
                                    'gmt-5' => 'GMT-5 (Bogotá)',
                                    'gmt-4' => 'GMT-4 (Santiago)',
                                    'gmt-3' => 'GMT-3 (Buenos Aires)',
                                    'gmt+1' => 'GMT+1 (Madrid)',
                                ];
                                foreach ($zonas as $val => $label): ?>
                                    <option value="<?= e($val) ?>"
                                        <?= $prefs['zona_horaria'] === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Formato de fecha -->
                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-i18n="gen.fecha"><?= $t('gen.fecha') ?></h4>
                                <p  data-i18n="gen.fecha.sub"><?= $t('gen.fecha.sub') ?></p>
                            </div>
                            <select class="config-select" id="selectFormatoFecha">
                                <?php
                                $formatos = [
                                    'dd/mm/yyyy' => 'DD/MM/YYYY (19/01/2026)',
                                    'mm/dd/yyyy' => 'MM/DD/YYYY (01/19/2026)',
                                    'yyyy-mm-dd' => 'YYYY-MM-DD (2026-01-19)',
                                ];
                                foreach ($formatos as $val => $label): ?>
                                    <option value="<?= e($val) ?>"
                                        <?= $prefs['formato_fecha'] === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Centro de Ayuda / FAQ -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-question-circle" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3 data-i18n="gen.ayuda"><?= $t('gen.ayuda') ?></h3>
                                <p  data-i18n="gen.ayuda.sub"><?= $t('gen.ayuda.sub') ?></p>
                            </div>
                        </div>

                        <div class="faq-section">
                            <?php
                            $faqs = [
                                ['gen.faq1.q', 'gen.faq1.a'],
                                ['gen.faq2.q', 'gen.faq2.a'],
                                ['gen.faq3.q', 'gen.faq3.a'],
                            ];
                            foreach ($faqs as [$qKey, $aKey]): ?>
                                <div class="faq-item">
                                    <div class="faq-question" onclick="toggleFAQ(this)">
                                        <span data-i18n="<?= $qKey ?>"><?= $t($qKey) ?></span>
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </div>
                                    <div class="faq-answer" data-i18n="<?= $aKey ?>">
                                        <?= $t($aKey) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-clock" aria-hidden="true"></i>
                            <span data-i18n="gen.tiempo_respuesta"><?= $t('gen.tiempo_respuesta') ?></span>
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
                                <h3 data-i18n="notif.titulo"><?= $t('notif.titulo') ?></h3>
                                <p  data-i18n="notif.sub"><?= $t('notif.sub') ?></p>
                            </div>
                        </div>

                        <div id="notifAlertContainer" aria-live="polite"></div>
                        <div class="config-item" style="align-items:flex-start;">
                            <div class="config-info">
                                <h4 data-i18n="notif.email"><?= $t('notif.email') ?></h4>
                                <p  data-i18n="notif.email.sub"><?= $t('notif.email.sub') ?></p>
                            </div>
                            <input type="radio" name="preferencia_notificacion"
                                   id="pref_email" value="email">
                        </div>
                        <div class="config-item" style="align-items:flex-start;">
                            <div class="config-info">
                                <h4 data-i18n="notif.ninguno"><?= $t('notif.ninguno') ?></h4>
                                <p  data-i18n="notif.ninguno.sub"><?= $t('notif.ninguno.sub') ?></p>
                            </div>
                            <input type="radio" name="preferencia_notificacion"
                                   id="pref_ninguno" value="ninguno">
                        </div>
                        <div class="form-acciones" style="margin-top:16px;">
                            <button type="button" class="btn-config btn-primary-config"
                                    id="btnGuardarPreferenciaNotificacion">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                <span data-i18n="general.guardar">Guardar Preferencia</span>
                            </button>
                        </div>
                        <div class="alert alert-info" style="margin-top:16px;">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span data-i18n="notif.info"><?= $t('notif.info') ?></span>
                        </div>
                    </div>

                    <!-- Historial de notificaciones -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon">
                                <i class="bi bi-clock-history" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3 data-i18n="notif.historial"><?= $t('notif.historial') ?></h3>
                                <p  data-i18n="notif.historial.sub"><?= $t('notif.historial.sub') ?></p>
                            <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/preferencias.js"></script>
                            <script>
                            // Unificación de preferencias de notificación con Preferencias.js
                            document.addEventListener('DOMContentLoaded', function () {
                                // Sincronizar radio con valor actual
                                Preferencias.init().then(() => {
                                    // Ya sincroniza radios automáticamente
                                });
                                // Guardar preferencia de notificación
                                var btnGuardar = document.getElementById('btnGuardarPreferenciaNotificacion');
                                if (btnGuardar) {
                                    btnGuardar.addEventListener('click', function () {
                                        var sel = document.querySelector('input[name="preferencia_notificacion"]:checked');
                                        if (!sel) {
                                            Toast.mostrar(Preferencias.t('notif.selecciona'), 'warning');
                                            return;
                                        }
                                        btnGuardar.disabled = true;
                                        var txt = btnGuardar.innerHTML;
                                        btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> ' + Preferencias.t('general.guardando');
                                        Preferencias.guardar({ notificaciones: sel.value }).then(function (ok) {
                                            btnGuardar.disabled = false;
                                            btnGuardar.innerHTML = txt;
                                            if (ok) {
                                                Toast.mostrar(Preferencias.t('notif.ok'), 'success');
                                            }
                                        });
                                    });
                                }
                                // Cargar historial de notificaciones (puede seguir usando el endpoint antiguo)
                                function cargarHistorialNotificacionesCliente() {
                                    var loading  = document.getElementById('loadingHistorial');
                                    var sinH     = document.getElementById('sinHistorial');
                                    var content  = document.getElementById('historialContent');
                                    var tbody    = document.getElementById('tablaHistorialNotificaciones');
                                    if (loading) loading.style.display = 'block';
                                    if (sinH)    sinH.style.display    = 'none';
                                    if (content) content.style.display = 'none';
                                    fetch(BASE_URL + '/app/controllers/preferenciasNotificacionController.php?accion=historial&limite=10')
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
                                            if (sinH) { sinH.style.display = 'block'; sinH.textContent = Preferencias.t('notif.historial.error'); }
                                        });
                                }
                                cargarHistorialNotificacionesCliente();
                            });
                            </script>
</body>
</html>