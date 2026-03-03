<?php

session_start();

require_once BASE_PATH . '/app/models/Veterinario.php';
require_once BASE_PATH . '/app/helpers/pdf_helpers.php';

if (!isset($_SESSION['user']['id_usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$idUsuario = (int) $_SESSION['user']['id_usuario'];
$modelo = new Veterinario();

$filtros = [
    'paciente' => trim((string) ($_GET['paciente'] ?? '')),
    'fecha' => trim((string) ($_GET['fecha'] ?? '')),
    'veterinario' => trim((string) ($_GET['veterinario'] ?? '')),
    'acceso' => trim((string) ($_GET['acceso'] ?? '')),
];

$historiales = $modelo->listarHistorialesClinicosPorProfesional($idUsuario, $filtros);

$payload = [
    'meta' => [
        'fecha_generacion' => date('Y-m-d H:i:s'),
        'filtros' => $filtros,
    ],
    'historiales' => $historiales,
];

ob_start();
require BASE_PATH . '/app/views/pdf/reporte_historiales_clinicos_pdf.php';
$html = ob_get_clean();

generarPDF($html, 'reporte_historiales_clinicos.pdf', false);
