<?php

require_once __DIR__ . '/../helpers/session_veterinario.php';
require_once __DIR__ . '/../models/Laboratorio.php';

header('Content-Type: application/json; charset=utf-8');

$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuario no autenticado',
    ]);
    exit();
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
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

$action = $action ?: ($payload['action'] ?? '');
$model = new Laboratorio();

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'listar':
                echo json_encode([
                    'status' => 'success',
                    'data' => $model->obtenerListadoOrdenes($idUsuario),
                ]);
                break;

            case 'estadisticas':
                echo json_encode([
                    'status' => 'success',
                    'data' => $model->obtenerEstadisticas($idUsuario),
                ]);
                break;

            case 'detalle':
                $idOrden = (int) ($_GET['id_orden_laboratorio'] ?? 0);
                $detalle = $model->obtenerDetalleOrden($idUsuario, $idOrden);
                if (!$detalle) {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'No se encontró la orden solicitada',
                    ]);
                    break;
                }

                echo json_encode([
                    'status' => 'success',
                    'data' => $detalle,
                ]);
                break;

            case 'resultado':
                $idOrdenPrueba = (int) ($_GET['id_orden_prueba'] ?? 0);
                $resultado = $model->obtenerResultadoPrueba($idUsuario, $idOrdenPrueba);
                if (!$resultado) {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'No se encontró la prueba solicitada',
                    ]);
                    break;
                }

                echo json_encode([
                    'status' => 'success',
                    'data' => $resultado,
                ]);
                break;

            case 'pacientes':
                echo json_encode([
                    'status' => 'success',
                    'data' => $model->obtenerPacientesDisponibles($idUsuario),
                ]);
                break;

            case 'catalogo':
                echo json_encode([
                    'status' => 'success',
                    'data' => $model->obtenerCatalogoPruebas(),
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
        break;

    case 'POST':
        switch ($action) {
            case 'crear-orden':
                $resultado = $model->crearOrden($idUsuario, $payload);
                http_response_code($resultado['ok'] ? 200 : 422);
                echo json_encode([
                    'status' => $resultado['ok'] ? 'success' : 'error',
                    'message' => $resultado['message'],
                    'data' => $resultado,
                ]);
                break;

            case 'guardar-resultado':
                $resultado = $model->guardarResultado($idUsuario, $payload);
                http_response_code($resultado['ok'] ? 200 : 422);
                echo json_encode([
                    'status' => $resultado['ok'] ? 'success' : 'error',
                    'message' => $resultado['message'],
                    'data' => $resultado,
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
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Metodo no permitido',
        ]);
        break;
}
