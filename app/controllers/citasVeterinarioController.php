<?php

require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/models/CitasVeterinario.php';

header('Content-Type: application/json; charset=utf-8');

$modelo = new CitasVeterinario();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $action = $_GET['action'] ?? '';
        switch ($action) {

            case 'listar':
                $filtros = [];
                if (!empty($_GET['id_veterinario'])) $filtros['id_veterinario'] = $_GET['id_veterinario'];
                if (!empty($_GET['estado']))          $filtros['estado']         = $_GET['estado'];
                if (!empty($_GET['fecha']))           $filtros['fecha']          = $_GET['fecha'];

                $citas = $modelo->listarCitas($filtros);
                echo json_encode(['status' => 'success', 'data' => $citas]);
                break;

            case 'estadisticas':
                $stats = $modelo->obtenerEstadisticas();
                echo json_encode(['status' => 'success', 'data' => $stats]);
                break;

            case 'veterinarios':
                $vets = $modelo->obtenerVeterinarios();
                echo json_encode(['status' => 'success', 'data' => $vets]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
        }
        break;

    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        $accion          = $body['accion']          ?? '';
        $id_agendamiento = isset($body['id_agendamiento']) ? (int)$body['id_agendamiento'] : 0;

        if ($id_agendamiento <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID de agendamiento inválido']);
            exit;
        }

        $accionesPermitidas = ['confirmar', 'completar', 'cancelar'];
        if (!in_array($accion, $accionesPermitidas, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Acción no permitida']);
            exit;
        }

        $estadoMap = [
            'confirmar' => 'confirmada',
            'completar' => 'completada',
            'cancelar'  => 'cancelada',
        ];

        $ok = $modelo->actualizarEstado($id_agendamiento, $estadoMap[$accion]);

        if ($ok) {
            echo json_encode([
                'status'  => 'success',
                'message' => 'Estado actualizado correctamente',
                'nuevo_estado' => $estadoMap[$accion],
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
