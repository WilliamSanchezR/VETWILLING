<?php

require_once __DIR__ . '/../models/Veterinario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];
$accion = ($method === 'POST') ? ($_POST['accion'] ?? '') : ($_GET['accion'] ?? '');

switch ($method) {
    case 'GET':
        if ($accion === 'perfil') {
            directorioPerfil();
        } else {
            directorioListar();
        }
        break;

    case 'POST':
        if ($accion === 'resenia') {
            directorioAgregarResenia();
        } elseif ($accion === 'estado') {
            directorioActualizarEstado();
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acción no reconocida']);
        }
        break;

    default:
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

function directorioListar(): void
{
    $id_veterinaria = isset($_GET['id_veterinaria']) ? (int) $_GET['id_veterinaria'] : null;

    $filtros = [];
    if (!empty($_GET['especialidad'])) {
        $filtros['especialidad'] = trim($_GET['especialidad']);
    }
    if (!empty($_GET['disponibilidad'])) {
        $filtros['disponibilidad'] = trim($_GET['disponibilidad']);
    }
    if (!empty($_GET['busqueda'])) {
        $filtros['busqueda'] = trim($_GET['busqueda']);
    }

    $modelo = new Veterinario();
    $profesionales = $modelo->listarDirectorioProfesionales($id_veterinaria, $filtros);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $profesionales]);
}

function directorioPerfil(): void
{
    $id_usuario = (int) ($_GET['id_usuario'] ?? 0);
    if ($id_usuario <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de usuario inválido']);
        return;
    }

    $modelo  = new Veterinario();
    $perfil  = $modelo->obtenerPerfilDirectorio($id_usuario);
    $resenias = $modelo->listarResenias($id_usuario);

    header('Content-Type: application/json');
    if ($perfil) {
        $perfil['resenias'] = $resenias;
        echo json_encode(['success' => true, 'data' => $perfil]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Profesional no encontrado']);
    }
}

function directorioActualizarEstado(): void
{
    $id_usuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
    if ($id_usuario <= 0) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado']);
        return;
    }

    $estado = trim($_POST['estado'] ?? '');
    $modelo = new Veterinario();
    $ok     = $modelo->actualizarEstadoDirectorio($id_usuario, $estado);

    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
}

function directorioAgregarResenia(): void
{
    $id_autor = (int) ($_SESSION['user']['id_usuario'] ?? 0);
    if ($id_autor <= 0) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado']);
        return;
    }

    $id_profesional = (int) ($_POST['id_usuario_profesional'] ?? 0);
    $calificacion   = (int) ($_POST['calificacion'] ?? 0);
    $comentario     = trim($_POST['comentario'] ?? '');

    if ($id_profesional <= 0 || $calificacion < 1 || $calificacion > 5) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Datos inválidos: id_usuario_profesional requerido y calificacion entre 1 y 5']);
        return;
    }

    $modelo = new Veterinario();
    $ok     = $modelo->agregarResenia([
        'id_usuario_profesional' => $id_profesional,
        'id_usuario_autor'       => $id_autor,
        'calificacion'           => $calificacion,
        'comentario'             => $comentario,
    ]);

    header('Content-Type: application/json');
    echo json_encode(['success' => $ok]);
}
