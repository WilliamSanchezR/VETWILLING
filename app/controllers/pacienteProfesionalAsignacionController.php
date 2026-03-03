<?php

session_start();

require_once __DIR__ . '/../models/PacienteProfesionalAsignacion.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$accion = $_REQUEST['accion'] ?? '';

if (!isset($_SESSION['user']['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit();
}

$idUsuarioSesion = (int) $_SESSION['user']['id_usuario'];
$idRolSesion = isset($_SESSION['user']['id_rol']) ? (int) $_SESSION['user']['id_rol'] : 0;
$idVeterinariaSesion = isset($_SESSION['user']['id_veterinaria']) ? (int) $_SESSION['user']['id_veterinaria'] : null;

$modelo = new PacienteProfesionalAsignacion();

if (!$modelo->tablaExiste()) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'La tabla de asignación no existe en la base de datos'
    ]);
    exit();
}

switch ($method) {
    case 'GET':
        manejarGet($accion, $modelo, $idUsuarioSesion, $idRolSesion, $idVeterinariaSesion);
        break;

    case 'POST':
        manejarPost($accion, $modelo, $idUsuarioSesion, $idRolSesion);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
        break;
}

function manejarGet($accion, $modelo, $idUsuarioSesion, $idRolSesion, $idVeterinariaSesion)
{
    if ($accion === 'listar_activos') {
        if (in_array($idRolSesion, [1, 4], true)) {
            $data = $modelo->listarPacientesActivosGlobal($idVeterinariaSesion ?: null);
        } elseif ($idRolSesion === 2) {
            $data = $modelo->listarPacientesActivosPorProfesional($idUsuarioSesion);
        } else {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para consultar asignaciones activas'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit();
    }

    if ($accion === 'historial_paciente') {
        $idPaciente = isset($_GET['id_paciente']) ? (int) $_GET['id_paciente'] : 0;

        if ($idPaciente <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'id_paciente es obligatorio'
            ]);
            exit();
        }

        if ($idRolSesion === 2 && !$modelo->profesionalTienePacienteActivo($idUsuarioSesion, $idPaciente)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para ver el historial de este paciente'
            ]);
            exit();
        }

        $data = $modelo->obtenerHistorialPorPaciente($idPaciente);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit();
    }

    if ($accion === 'asignacion_activa') {
        $idPaciente = isset($_GET['id_paciente']) ? (int) $_GET['id_paciente'] : 0;

        if ($idPaciente <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'id_paciente es obligatorio'
            ]);
            exit();
        }

        if ($idRolSesion === 2 && !$modelo->profesionalTienePacienteActivo($idUsuarioSesion, $idPaciente)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para ver la asignación de este paciente'
            ]);
            exit();
        }

        $data = $modelo->obtenerAsignacionActivaPorPaciente($idPaciente);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Acción GET no válida'
    ]);
    exit();
}

function manejarPost($accion, $modelo, $idUsuarioSesion, $idRolSesion)
{
    if ($accion !== 'reasignar') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción POST no válida'
        ]);
        exit();
    }

    if (!in_array($idRolSesion, [1, 4], true)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'No tiene permisos para reasignar pacientes'
        ]);
        exit();
    }

    $idPaciente = isset($_POST['id_paciente']) ? (int) $_POST['id_paciente'] : 0;
    $idUsuarioProfesional = isset($_POST['id_usuario_profesional']) ? (int) $_POST['id_usuario_profesional'] : 0;
    $motivo = trim($_POST['motivo_cambio'] ?? 'Reasignación manual');
    $observacion = trim($_POST['observacion'] ?? '');

    if ($idPaciente <= 0 || $idUsuarioProfesional <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'id_paciente e id_usuario_profesional son obligatorios'
        ]);
        exit();
    }

    $ok = $modelo->reasignarPaciente(
        $idPaciente,
        $idUsuarioProfesional,
        $idUsuarioSesion,
        $motivo,
        ($observacion !== '' ? $observacion : null)
    );

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo reasignar el paciente'
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Paciente reasignado correctamente'
    ]);
    exit();
}
