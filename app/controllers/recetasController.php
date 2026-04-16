<?php

require_once __DIR__ . '/../helpers/session_veterinario.php';
require_once __DIR__ . '/../models/Recetas.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Metodo no permitido'
    ]);
    exit();
}

$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuario no autenticado'
    ]);
    exit();
}

$action = $_GET['action'] ?? '';
$model = new Recetas();

switch ($action) {
    case 'estadisticas':
        echo json_encode([
            'status' => 'success',
            'data' => $model->obtenerEstadisticas($idUsuario),
        ]);
        break;

    case 'pacientes':
        echo json_encode([
            'status' => 'success',
            'data' => $model->obtenerPacientesAsignados($idUsuario),
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Accion no valida',
        ]);
        break;
}
