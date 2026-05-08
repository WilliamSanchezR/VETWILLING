<?php

session_start();

require_once BASE_PATH . '/app/models/Veterinario.php';
require_once BASE_PATH . '/app/models/Reportes.php';
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
$idPaciente = (int) ($_GET['id_paciente'] ?? 0);

if ($idPaciente <= 0) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'ID de paciente inválido']);
    exit;
}

$modelo = new Veterinario();
$ficha = $modelo->obtenerFichaClinicaPdfPorProfesional($idUsuario, $idPaciente);

if (!$ficha) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'No autorizado o sin datos para este paciente']);
    exit;
}

$payload = [
    'meta' => [
        'fecha_generacion' => date('Y-m-d H:i:s'),
    ],
    'ficha' => $ficha,
];

ob_start();
require BASE_PATH . '/app/views/pdf/ficha_clinica_paciente_pdf.php';
$html = ob_get_clean();

$nombrePaciente = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($ficha['paciente']['nombre'] ?? 'paciente'));

// RFS 32 subtask 6 & 8: registrar generación en historial
$reportesModel = new Reportes();
$reportesModel->registrarGeneracion($idUsuario, 'ficha_pdf', $idPaciente, [
    'nombre_paciente' => $ficha['paciente']['nombre'] ?? '',
    'fecha_generacion' => date('Y-m-d H:i:s'),
]);

generarPDF($html, 'ficha_clinica_' . $nombrePaciente . '.pdf', false);
