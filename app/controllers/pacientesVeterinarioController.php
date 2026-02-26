<?php

require_once __DIR__ . '/../helpers/session_veterinario.php';
require_once __DIR__ . '/../models/Veterinario.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido'
    ]);
    exit();
}

$rawInput = file_get_contents('php://input');
$payload = [];

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

if (empty($payload)) {
    $payload = $_POST;
}

$accion = $payload['accion'] ?? '';
$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);

if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuario no autenticado'
    ]);
    exit();
}

$model = new Veterinario();

switch ($accion) {
    case 'consultar':
        consultarPaciente($model, $idUsuario, $payload);
        break;

    case 'actualizar':
        actualizarPaciente($model, $idUsuario, $payload);
        break;

    case 'desactivar':
        desactivarPaciente($model, $idUsuario, $payload);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no válida'
        ]);
        break;
}

function consultarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de paciente inválido'
        ]);
        return;
    }

    $paciente = $model->obtenerDetallePacientePorVeterinario($idUsuario, $idPaciente);
    if (!$paciente) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Paciente no encontrado o no asignado al profesional'
        ]);
        return;
    }

    $historial = $model->obtenerHistorialPacientePorVeterinario($idUsuario, $idPaciente, 8);

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente encontrado',
        'data' => [
            'paciente' => $paciente,
            'historial' => $historial
        ]
    ]);
}

function actualizarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $nombre = trim((string) ($payload['nombre'] ?? ''));
    $especie = trim((string) ($payload['especie'] ?? ''));
    $raza = trim((string) ($payload['raza'] ?? ''));
    $edadNumero = (int) ($payload['edad_numero'] ?? 0);
    $edadUnidad = trim((string) ($payload['edad_unidad'] ?? ''));
    $sexo = trim((string) ($payload['sexo'] ?? ''));

    if ($idPaciente <= 0 || $nombre === '' || $especie === '' || $raza === '' || $edadNumero <= 0 || $edadUnidad === '' || $sexo === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Todos los campos obligatorios deben ser válidos'
        ]);
        return;
    }

    $ok = $model->actualizarDatosPacientePorVeterinario($idUsuario, [
        'id_paciente' => $idPaciente,
        'nombre' => $nombre,
        'especie' => $especie,
        'raza' => $raza,
        'edad_numero' => $edadNumero,
        'edad_unidad' => $edadUnidad,
        'sexo' => $sexo
    ]);

    if (!$ok) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudo actualizar el paciente'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente actualizado correctamente'
    ]);
}

function desactivarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de paciente inválido'
        ]);
        return;
    }

    $ok = $model->desactivarPacientePorVeterinario($idUsuario, $idPaciente);

    if (!$ok) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudo desactivar el paciente'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente desactivado correctamente'
    ]);
}
