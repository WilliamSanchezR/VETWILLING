<?php
/**
 * bootstrap.php — VetWilling
 * Inicialización global de sesión, i18n y helpers.
 * Se carga desde config/config.php antes de cualquier controlador o vista.
 */

// ── Sesión ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// ── I18n ───────────────────────────────────────────────────────────────────
if (!class_exists('I18n')) {
    require_once __DIR__ . '/app/lang/i18n.php';
}

$_vw_idioma = $_SESSION['lang'] ?? $_COOKIE['vw_lang'] ?? 'es';
if (!in_array($_vw_idioma, ['es', 'en', 'pt'], true)) {
    $_vw_idioma = 'es';
}

$GLOBALS['lang'] = $_vw_idioma;
$GLOBALS['t']    = I18n::cargar($_vw_idioma);

// ── Helpers ────────────────────────────────────────────────────────────────
if (file_exists(__DIR__ . '/app/helpers/lang/i18n_helper.php')) {
    require_once __DIR__ . '/app/helpers/lang/i18n_helper.php';
}
