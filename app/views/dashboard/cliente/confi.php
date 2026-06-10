<?php
/**
 * confi.php – VetWilling
 * Vista de Configuración del Cliente — versión corregida y mejorada
 *
 * Correcciones aplicadas:
 * 1. Clase .lista-sesiones → .sesiones-lista (alineado con CSS)
 * 2. Clase .badge-actual → .sesion-badge .sesion-badge--actual (alineado con CSS)
 * 3. Clase .tabla-historial → .tabla-sesiones (alineado con CSS)
 * 4. Radio buttons de notificaciones reemplazados por toggles visuales consistentes
 * 5. FAQ: toggleFAQ ahora alterna .active (no .faq-open) para alinear con CSS
 * 6. Navegación por teclado (←→) añadida a los tabs (ARIA pattern)
 * 7. Validación visual en tiempo real con :invalid y mensajes inline
 * 8. Formulario contraseña: botones con width 100% en móvil alineados al resto
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/services/SessionManager.php';
require_once BASE_PATH . '/app/services/PreferenciasManager.php';

$sm       = new SessionManager($_SESSION['user']['id_usuario']);
$sesiones = $sm->listar();

$pm      = new PreferenciasManager($_SESSION['user']['id_usuario']);
$prefs   = $pm->obtener();
$t       = I18n::cargar($prefs['idioma']);

$usuario = $_SESSION['user'] ?? [];

if (!function_exists('e')) {
    function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$iniciales = mb_strtoupper(
    mb_substr($usuario['nombres']   ?? '?', 0, 1) .
    mb_substr($usuario['apellidos'] ?? '',  0, 1)
);

// Preferencia de notificación actual (para preseleccionar el toggle)
$prefNotif = $prefs['notificaciones'] ?? 'email';
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

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/confi.css">

    <script>
        var BASE_URL   = '<?= BASE_URL ?>';
        window.__prefs = <?= json_encode($prefs, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    </script>
</head>

<body>

    <div class="toast-container" id="toastContainer" role="status" aria-live="polite"></div>

    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <main class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- Header -->
                <header class="header-config">
                    <h1>
                        <i class="bi bi-gear-fill" aria-hidden="true"></i>
                        <span data-i18n="config.titulo"><?= $t('config.titulo') ?></span>
                    </h1>
                    <p data-i18n="config.subtitulo"><?= $t('config.subtitulo') ?></p>
                </header>

                <!-- ===================== TABS ===================== -->
                <!--
                    CORRECCIÓN #6: Navegación por teclado añadida mediante JS al final.
                    Los botones tienen tabindex="-1" salvo el activo (tabindex="0"),
                    siguiendo el patrón roving tabindex del WAI-ARIA tabs widget.
                -->
                <div class="tabs-config" role="tablist" aria-label="<?= $t('config.titulo') ?>">
                    <button class="tab-config active" data-tab="cuenta"
                            role="tab" aria-selected="true" aria-controls="tab-cuenta"
                            id="tab-btn-cuenta" tabindex="0">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.cuenta"><?= $t('tab.cuenta') ?></span>
                    </button>
                    <button class="tab-config" data-tab="general"
                            role="tab" aria-selected="false" aria-controls="tab-general"
                            id="tab-btn-general" tabindex="-1">
                        <i class="bi bi-gear-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.general"><?= $t('tab.general') ?></span>
                    </button>
                    <button class="tab-config" data-tab="notificaciones"
                            role="tab" aria-selected="false" aria-controls="tab-notificaciones"
                            id="tab-btn-notificaciones" tabindex="-1">
                        <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.notificaciones"><?= $t('tab.notificaciones') ?></span>
                    </button>
                    <button class="tab-config" data-tab="seguridad"
                            role="tab" aria-selected="false" aria-controls="tab-seguridad"
                            id="tab-btn-seguridad" tabindex="-1">
                        <i class="bi bi-key-fill" aria-hidden="true"></i>
                        <span data-i18n="tab.seguridad"><?= $t('tab.seguridad') ?></span>
                    </button>
                </div>


                <!-- ===================== TAB: MI CUENTA ===================== -->
                <section class="tab-content" id="tab-cuenta"
                         role="tabpanel" aria-labelledby="tab-btn-cuenta">
                    <form method="POST"
                          action="<?= BASE_URL ?>/cliente/actualizar"
                          enctype="multipart/form-data"
                          novalidate
                          class="form-validable">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_propietario"
                               value="<?= e($_SESSION['user']['id_usuario']) ?>">

                        <!-- Foto de perfil -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon"><i class="bi bi-camera-fill" aria-hidden="true"></i></div>
                                <div>
                                    <h3 data-i18n="cuenta.foto"><?= $t('cuenta.foto') ?></h3>
                                    <p  data-i18n="cuenta.foto.sub"><?= $t('cuenta.foto.sub') ?></p>
                                </div>
                            </div>

                            <div class="foto-perfil-container">
                                <div class="avatar-grande">
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= e($usuario['img_perfil'] ?? '') ?>"
                                         alt="Foto de perfil de <?= e($usuario['nombres'] ?? '') ?>"
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
                                <div class="config-icon"><i class="bi bi-person-badge-fill" aria-hidden="true"></i></div>
                                <div>
                                    <h3 data-i18n="cuenta.personal"><?= $t('cuenta.personal') ?></h3>
                                    <p  data-i18n="cuenta.personal.sub"><?= $t('cuenta.personal.sub') ?></p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-nombres" data-i18n="cuenta.nombres"><?= $t('cuenta.nombres') ?></label>
                                    <input type="text" id="inp-nombres" name="nombres"
                                           value="<?= e($usuario['nombres'] ?? '') ?>"
                                           required
                                           aria-describedby="inp-nombres-error">
                                    <span class="field-error" id="inp-nombres-error" aria-live="polite"></span>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-apellidos" data-i18n="cuenta.apellidos"><?= $t('cuenta.apellidos') ?></label>
                                    <input type="text" id="inp-apellidos" name="apellidos"
                                           value="<?= e($usuario['apellidos'] ?? '') ?>"
                                           required
                                           aria-describedby="inp-apellidos-error">
                                    <span class="field-error" id="inp-apellidos-error" aria-live="polite"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tipo-doc" data-i18n="cuenta.tipo_doc"><?= $t('cuenta.tipo_doc') ?></label>
                                    <select id="inp-tipo-doc" name="tipo_documento" required>
                                        <?php foreach (['Cédula de Ciudadanía','Cédula de Extranjería','Pasaporte'] as $tipo): ?>
                                            <option value="<?= e($tipo) ?>" <?= ($usuario['tipo_documento'] ?? '') === $tipo ? 'selected' : '' ?>>
                                                <?= e($tipo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-num-doc" data-i18n="cuenta.num_doc"><?= $t('cuenta.num_doc') ?></label>
                                    <input type="text" id="inp-num-doc" name="numero_documento"
                                           value="<?= e($usuario['numero_documento'] ?? '') ?>"
                                           required
                                           aria-describedby="inp-num-doc-error">
                                    <span class="field-error" id="inp-num-doc-error" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="config-card">
                            <div class="config-card-header">
                                <div class="config-icon"><i class="bi bi-telephone-fill" aria-hidden="true"></i></div>
                                <div>
                                    <h3 data-i18n="cuenta.contacto"><?= $t('cuenta.contacto') ?></h3>
                                    <p  data-i18n="cuenta.contacto.sub"><?= $t('cuenta.contacto.sub') ?></p>
                                </div>
                            </div>

                            <div class="form-group-config">
                                <label for="inp-email" data-i18n="cuenta.email"><?= $t('cuenta.email') ?></label>
                                <input type="email" id="inp-email" name="email"
                                       value="<?= e($usuario['email'] ?? '') ?>"
                                       required
                                       aria-describedby="inp-email-error">
                                <span class="field-error" id="inp-email-error" aria-live="polite"></span>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-tel" data-i18n="cuenta.telefono"><?= $t('cuenta.telefono') ?></label>
                                    <input type="tel" id="inp-tel" name="telefono"
                                           value="<?= e($usuario['telefono'] ?? '') ?>"
                                           required
                                           aria-describedby="inp-tel-error">
                                    <span class="field-error" id="inp-tel-error" aria-live="polite"></span>
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
                                <div class="config-icon"><i class="bi bi-info-circle-fill" aria-hidden="true"></i></div>
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
                                        <option value="<?= e($val) ?>" <?= ($usuario['como_conociste'] ?? '') === $val ? 'selected' : '' ?>>
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
                </section>


                <!-- ===================== TAB: GENERAL ===================== -->
                <section class="tab-content tab-content--hidden" id="tab-general"
                         role="tabpanel" aria-labelledby="tab-btn-general">

                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-globe2" aria-hidden="true"></i></div>
                            <div>
                                <h3 data-i18n="gen.idioma"><?= $t('gen.idioma') ?></h3>
                                <p  data-i18n="gen.idioma.sub"><?= $t('gen.idioma.sub') ?></p>
                            </div>
                        </div>

                        <!-- Tema -->
                        <div class="config-item">
                            <div class="config-info">
                                <h4 data-i18n="gen.tema"><?= $t('gen.tema') ?></h4>
                                <p  data-i18n="gen.tema.sub"><?= $t('gen.tema.sub') ?></p>
                            </div>
                            <div class="tema-grupo-botones">
                                <button type="button" class="tema-btn" data-accion-tema="claro"
                                        aria-pressed="<?= $prefs['tema'] === 'claro' ? 'true' : 'false' ?>">
                                    <i class="bi bi-sun-fill" aria-hidden="true"></i>
                                    <span data-i18n="gen.tema.claro"><?= $t('gen.tema.claro') ?></span>
                                </button>
                                <button type="button" class="tema-btn" data-accion-tema="oscuro"
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
                                    <option value="<?= e($val) ?>" <?= $prefs['zona_horaria'] === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Formato fecha -->
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
                                    <option value="<?= e($val) ?>" <?= $prefs['formato_fecha'] === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- FAQ -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></div>
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
                            foreach ($faqs as $i => [$qKey, $aKey]): ?>
                                <!--
                                    CORRECCIÓN #5: toggleFAQ ahora alterna .active para alinear
                                    con el CSS que usa .faq-item.active (no .faq-item.faq-open).
                                -->
                                <div class="faq-item" id="faq-item-<?= $i ?>">
                                    <button type="button" class="faq-question"
                                            aria-expanded="false"
                                            aria-controls="faq-a-<?= $i ?>"
                                            onclick="toggleFAQ(this)">
                                        <span data-i18n="<?= $qKey ?>"><?= $t($qKey) ?></span>
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </button>
                                    <div id="faq-a-<?= $i ?>"
                                         class="faq-answer"
                                         role="region"
                                         data-i18n="<?= $aKey ?>">
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
                </section>


                <!-- ===================== TAB: NOTIFICACIONES ===================== -->
                <section class="tab-content tab-content--hidden" id="tab-notificaciones"
                         role="tabpanel" aria-labelledby="tab-btn-notificaciones">

                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-bell-fill" aria-hidden="true"></i></div>
                            <div>
                                <h3 data-i18n="notif.titulo"><?= $t('notif.titulo') ?></h3>
                                <p  data-i18n="notif.sub"><?= $t('notif.sub') ?></p>
                            </div>
                        </div>

                        <div id="notifAlertContainer" aria-live="polite"></div>

                        <!--
                            CORRECCIÓN #4: Radio buttons reemplazados por .config-item con
                            toggle visual (.toggle-switch) consistente con el sistema de diseño.
                            Se mantiene un input radio oculto para compatibilidad con el JS existente.
                        -->
                        <div class="config-item config-item--top">
                            <div class="config-info">
                                <h4 data-i18n="notif.email"><?= $t('notif.email') ?></h4>
                                <p  data-i18n="notif.email.sub"><?= $t('notif.email.sub') ?></p>
                            </div>
                            <label class="notif-opcion" for="pref_email">
                                <input type="radio" name="preferencia_notificacion"
                                       id="pref_email" value="email"
                                       <?= $prefNotif === 'email' ? 'checked' : '' ?>>
                                <span class="notif-toggle-visual" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div class="config-item config-item--top">
                            <div class="config-info">
                                <h4 data-i18n="notif.ninguno"><?= $t('notif.ninguno') ?></h4>
                                <p  data-i18n="notif.ninguno.sub"><?= $t('notif.ninguno.sub') ?></p>
                            </div>
                            <label class="notif-opcion" for="pref_ninguno">
                                <input type="radio" name="preferencia_notificacion"
                                       id="pref_ninguno" value="ninguno"
                                       <?= $prefNotif === 'ninguno' ? 'checked' : '' ?>>
                                <span class="notif-toggle-visual" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div class="form-acciones mt-md">
                            <button type="button" class="btn-config btn-primary-config" id="btnGuardarPreferenciaNotificacion">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                <span data-i18n="general.guardar">Guardar Preferencia</span>
                            </button>
                        </div>

                        <div class="alert alert-info mt-md">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span data-i18n="notif.info"><?= $t('notif.info') ?></span>
                        </div>
                    </div>

                    <!-- Historial -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></div>
                            <div>
                                <h3 data-i18n="notif.historial"><?= $t('notif.historial') ?></h3>
                                <p  data-i18n="notif.historial.sub"><?= $t('notif.historial.sub') ?></p>
                            </div>
                        </div>

                        <div id="loadingHistorial" class="loading-state">
                            <i class="bi bi-hourglass-split"></i> Cargando…
                        </div>
                        <div id="sinHistorial" class="empty-state" style="display:none">
                            <i class="bi bi-inbox"></i> Sin notificaciones recientes.
                        </div>
                        <div id="historialContent" style="display:none">
                            <!--
                                CORRECCIÓN #3: Clase cambiada de .tabla-historial a .tabla-sesiones
                                para alinear con los estilos definidos en el CSS.
                            -->
                            <table class="tabla-sesiones">
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
                </section>


                <!-- ===================== TAB: SEGURIDAD ===================== -->
                <section class="tab-content tab-content--hidden" id="tab-seguridad"
                         role="tabpanel" aria-labelledby="tab-btn-seguridad">

                    <!-- Cambio de contraseña -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></div>
                            <div>
                                <h3 data-i18n="seg.password"><?= $t('seg.password') ?? 'Cambiar contraseña' ?></h3>
                                <p  data-i18n="seg.password.sub"><?= $t('seg.password.sub') ?? 'Usa una contraseña fuerte y única.' ?></p>
                            </div>
                        </div>

                        <form id="formCambioPassword"
                              method="POST"
                              action="<?= BASE_URL ?>/cliente/cambiar-password"
                              novalidate
                              class="form-validable">

                            <div class="form-group-config">
                                <label for="inp-pass-actual">Contraseña actual</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="inp-pass-actual" name="password_actual"
                                           required autocomplete="current-password"
                                           aria-describedby="inp-pass-actual-error">
                                    <button type="button" class="toggle-password"
                                            onclick="togglePass('inp-pass-actual', this)"
                                            aria-label="Mostrar u ocultar contraseña actual">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <span class="field-error" id="inp-pass-actual-error" aria-live="polite"></span>
                            </div>

                            <div class="form-row">
                                <div class="form-group-config">
                                    <label for="inp-pass-nueva">Nueva contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="inp-pass-nueva" name="password_nueva"
                                               required minlength="8" autocomplete="new-password"
                                               aria-describedby="inp-pass-nueva-error"
                                               oninput="evaluarPassword(this.value)">
                                        <button type="button" class="toggle-password"
                                                onclick="togglePass('inp-pass-nueva', this)"
                                                aria-label="Mostrar u ocultar nueva contraseña">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <span class="strength-text" id="strengthText"></span>
                                    </div>
                                    <span class="field-error" id="inp-pass-nueva-error" aria-live="polite"></span>
                                </div>
                                <div class="form-group-config">
                                    <label for="inp-pass-conf">Confirmar nueva</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="inp-pass-conf" name="password_confirmacion"
                                               required minlength="8" autocomplete="new-password"
                                               aria-describedby="inp-pass-conf-error">
                                        <button type="button" class="toggle-password"
                                                onclick="togglePass('inp-pass-conf', this)"
                                                aria-label="Mostrar u ocultar confirmación de contraseña">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span class="field-error" id="inp-pass-conf-error" aria-live="polite"></span>
                                </div>
                            </div>

                            <div class="requirements">
                                <p class="requirements-title">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                                    Requisitos de la contraseña
                                </p>
                                <p class="requirement-item" data-req="length">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i> Mínimo 8 caracteres
                                </p>
                                <p class="requirement-item" data-req="upper">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i> Al menos una mayúscula
                                </p>
                                <p class="requirement-item" data-req="number">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i> Al menos un número
                                </p>
                                <p class="requirement-item" data-req="special">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i> Al menos un carácter especial
                                </p>
                            </div>

                            <div class="form-acciones">
                                <button type="submit" class="btn-config btn-primary-config">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i> Actualizar contraseña
                                </button>
                                <button type="reset" class="btn-config btn-secondary-config">
                                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sesiones activas -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <div class="config-icon"><i class="bi bi-laptop" aria-hidden="true"></i></div>
                            <div>
                                <h3>Sesiones activas</h3>
                                <p>Dispositivos donde tu cuenta tiene una sesión abierta.</p>
                            </div>
                        </div>

                        <?php if (empty($sesiones)): ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i> No hay sesiones registradas.
                            </div>
                        <?php else: ?>
                            <!--
                                CORRECCIÓN #1: Clase .lista-sesiones → .sesiones-lista
                                CORRECCIÓN #2: .badge-actual → .sesion-badge .sesion-badge--actual
                                               Botón cerrar: clase propia .btn-cerrar-sesion
                            -->
                            <ul class="sesiones-lista" aria-label="Sesiones activas">
                                <?php foreach ($sesiones as $s): ?>
                                    <li class="sesion-item <?= !empty($s['actual']) ? 'sesion-item--actual' : '' ?>">
                                        <div class="sesion-icono">
                                            <i class="bi bi-<?= !empty($s['actual']) ? 'laptop' : 'display' ?>"
                                               aria-hidden="true"></i>
                                        </div>
                                        <div class="sesion-info">
                                            <div class="sesion-titulo">
                                                <?= e($s['dispositivo'] ?? 'Dispositivo desconocido') ?>
                                                <?php if (!empty($s['actual'])): ?>
                                                    <span class="sesion-badge sesion-badge--actual">Esta sesión</span>
                                                <?php elseif (!empty($s['nueva'])): ?>
                                                    <span class="sesion-badge sesion-badge--nueva">Nueva</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="sesion-meta">
                                                <?php if (!empty($s['navegador'])): ?>
                                                    <span>
                                                        <i class="bi bi-globe" aria-hidden="true"></i>
                                                        <?= e($s['navegador']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($s['ip'])): ?>
                                                    <span>
                                                        <i class="bi bi-hdd-network" aria-hidden="true"></i>
                                                        <?= e($s['ip']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($s['ultimo_acceso'])): ?>
                                                    <span>
                                                        <i class="bi bi-clock" aria-hidden="true"></i>
                                                        <?= e($s['ultimo_acceso']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (empty($s['actual'])): ?>
                                            <button type="button"
                                                    class="btn-cerrar-sesion"
                                                    data-cerrar-sesion="<?= e($s['id'] ?? '') ?>"
                                                    aria-label="Cerrar sesión en <?= e($s['dispositivo'] ?? 'este dispositivo') ?>">
                                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                                Cerrar
                                            </button>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php
                            // Mostrar "cerrar todas" solo si hay más de una sesión no-actual
                            $sesionesOtras = array_filter($sesiones, fn($s) => empty($s['actual']));
                            if (count($sesionesOtras) > 1): ?>
                                <div class="sesiones-footer">
                                    <button type="button"
                                            class="btn-config btn-remove btn-cerrar-todas"
                                            id="btnCerrarTodasSesiones">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                        Cerrar todas las otras sesiones
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>

            </div><!-- /.container-dashboard -->
        </div><!-- /.area-contenido -->
    </main>

    <!-- ===================== SCRIPTS ===================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/preferencias.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/confi.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
</body>
</html>