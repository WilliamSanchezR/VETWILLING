<?php

require_once __DIR__ . '/../helpers/session_veterinario.php';
require_once __DIR__ . '/../models/Veterinario.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

$rawInput = file_get_contents('php://input');
$payload  = [];
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}
if (empty($payload)) {
    $payload = $_POST;
}

$accion    = $payload['accion'] ?? '';
$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);

if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

$model = new Veterinario();

switch ($accion) {
    case 'listar':
        $citas = $model->listarCitasVistaVeterinario($idUsuario);
        $stats = $model->contarCitasPorEstadoVeterinario($idUsuario);
        echo json_encode([
            'status' => 'success',
            'data'   => ['citas' => $citas, 'stats' => $stats],
        ]);
        break;

    case 'confirmar':
        $idAgendamiento = (int) ($payload['id_agendamiento'] ?? 0);
        if ($idAgendamiento <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de cita inválido']);
            break;
        }
        $ok = $model->actualizarEstadoCitaVeterinario($idAgendamiento, 'Confirmada', $idUsuario);
        echo json_encode([
            'status'  => $ok ? 'success' : 'error',
            'message' => $ok ? 'Cita confirmada exitosamente' : 'No se pudo confirmar la cita',
        ]);
        break;

    case 'cancelar':
        $idAgendamiento = (int) ($payload['id_agendamiento'] ?? 0);
        if ($idAgendamiento <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de cita inválido']);
            break;
        }
        $ok = $model->actualizarEstadoCitaVeterinario($idAgendamiento, 'Cancelada', $idUsuario);
        echo json_encode([
            'status'  => $ok ? 'success' : 'error',
            'message' => $ok ? 'Cita cancelada' : 'No se pudo cancelar la cita',
        ]);
        break;

    case 'completar':
        $idAgendamiento = (int) ($payload['id_agendamiento'] ?? 0);
        if ($idAgendamiento <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de cita inválido']);
            break;
        }
        $ok = $model->actualizarEstadoCitaVeterinario($idAgendamiento, 'Completada', $idUsuario);
        echo json_encode([
            'status'  => $ok ? 'success' : 'error',
            'message' => $ok ? 'Cita marcada como completada' : 'No se pudo actualizar la cita',
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
        break;
}
