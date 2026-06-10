<?php
/**
 * langController.php
 * GET /cliente/api/preferencias/lang?idioma=en
 * Devuelve los strings de traducción para que preferencias.js los use.
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/lang/i18n.php';

header('Content-Type: application/json; charset=utf-8');
// Cache de 1 hora (los strings no cambian salvo deploy)
header('Cache-Control: public, max-age=3600');

$idioma    = $_GET['idioma'] ?? 'es';
$permitidos = ['es', 'en', 'pt'];

if (!in_array($idioma, $permitidos, true)) {
    $idioma = 'es';
}

echo json_encode([
    'status'  => 'ok',
    'idioma'  => $idioma,
    'strings' => I18n::obtenerStrings($idioma),
]);