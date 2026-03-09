<?php

require_once __DIR__ . '/../models/Ticket.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $action = $_POST['action'] ?? '';
        if ($action === 'actualizar') {
            // actualizarTicket();
        } else if ($action === 'cambiar-estado') {
            // cambiarEstadoTicket();
        } else {
            crearTicket();
        }

        break;

    case 'GET':
        $action = $_GET['action'] ?? '';

        if ($action === 'historico' && isset($_GET['id'])) {
            consultarHistoricoTicket($_GET['id']);
        }

        // if ($action === 'eliminar') {
        //     eliminarUsuario($_GET['id']);
        // } else if (isset($_GET['id'])) {
        //     consultarTicket($_GET['id']);
        // } else {
        //     listarTickets();
        // }
        break;

    case 'PUT':
        $action = $_GET['action'] ?? '';
        if ($action === 'asignar') {
            asignarTicket();
        } else {
            actualizarTicket();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// FUNCIÓN PARA LISTAR TODOS LOS TICKETS
function listarTickets()
{
    // Llamar al modelo para listar los tickets
    $ticketModel = new Ticket();
    $tickets = $ticketModel->listarTickets();
    return $tickets;
}

// FUNCIÓN PARA CONSULTAR UN TICKET POR ID
function consultarTicket($id)
{
    $ticketModel = new Ticket();
    $ticket = $ticketModel->obtenerTicketPorId($id);
    return $ticket;
}

function crearTicket()
{
    try {
        // recibir datos del formulario (enviar FormData desde el frontend)
        $data = $_POST;

        $titulo = $data['asunto'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $categoria = $data['categoria'] ?? '';
        $id_usuario = $data['id_usuario'] ?? null;
        $archivo = null;

        if (!empty($_FILES['archivo']['name'])) {

            $file = $_FILES['archivo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $permitidas = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx'];
            // Validar extensión y tamaño
            if (!in_array($ext, $permitidas)) {
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Extensión no permitida', 'message' => 'Solo archivos PNG, JPEG, JPG, PDF, DOC, DOCX']);

                exit();
            }
            // Validar tamaño
            if ($file['size'] > 2 * 1024 * 1024) {
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Archivo demasiado grande', 'message' => 'El archivo supera las 2MB']);
                exit();
            }
            // Generar un nombre único para la imagen
            $ruta_archivo = uniqid('archivo_') . '.' . $ext;
            $destino = BASE_PATH . '/public/uploads/tickets/' . $ruta_archivo;
            move_uploaded_file($file['tmp_name'], $destino);
            $archivo = $ruta_archivo;
        }



        if (empty($titulo) || empty($descripcion) || empty($id_usuario) || empty($categoria)) {
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        $ticketModel = new Ticket();

        // Construimos el objeto de datos para crear el ticket
        $ticketData = [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'categoria' => $categoria,
            'id_usuario' => $id_usuario,
            'archivo' => $archivo
        ];

        $resultado = $ticketModel->crearTicket($ticketData);

        if ($resultado) {
            $ticketNumber = str_pad($resultado, 4, '0', STR_PAD_LEFT);
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode(['status' => 'success', 'message' => 'Ticket creado exitosamente con número de ticket: ' . $ticketNumber]);
        } else {
            header('Content-Type: application/json');
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['status' => 'error', 'message' => 'Error al crear el ticket']);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Excepción: ' . $e->getMessage()]);
        return;
    }
}

function asignarTicket()
{
    // recibimos los datos del formulario en formato JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $ticketId = $data['id_ticket'] ?? null;
    $adminId = $data['id_usuario'] ?? null;
    $usuarioIdAuth = $data['id_usuario_auth'] ?? null;

    if (empty($ticketId) || empty($adminId)) {
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Faltan campos requeridos']);
        return;
    }

    $ticketModel = new Ticket();
    $resultado = $ticketModel->asignarTicket($ticketId, $adminId, $usuarioIdAuth);

    if ($resultado) {
        header('Content-Type: application/json');
        header('HTTP/1.1 200 OK');
        echo json_encode(['status' => 'success', 'message' => 'Ticket asignado correctamente']);
    } else {
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Error al asignar el ticket']);
    }
}

function actualizarTicket()
{
    try {
        // recibimos los datos del formulario en formato JSON
        $data = json_decode(file_get_contents('php://input'), true);
        $ticketId = $data['id_ticket'] ?? null;
        $nuevoEstado = $data['estado'] ?? null;
        $solucion = $data['solucion'] ?? null;
        $reasignarA = $data['id_usuario_reasignado'] ?? null;
        $usuarioIdAuth = $data['id_usuario_auth'] ?? null;

        if (empty($ticketId) || empty($nuevoEstado)) {
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        $ticketModel = new Ticket();
        $resultado = $ticketModel->actualizarTicket($ticketId, $nuevoEstado, $solucion, $reasignarA, $usuarioIdAuth);

        // Retornar respuesta exitosa
        if ($resultado) {
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode(['status' => 'success', 'message' => 'Ticket actualizado correctamente']);
            return;
        }

        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el ticket']);
        return;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => 'Excepción: ' . $e->getMessage()]);
        return;
    }
}

function consultarHistoricoTicket($id)
{
    $ticketModel = new Ticket();
    $historial = $ticketModel->consultarHistoricoTicket($id);
    header('Content-Type: application/json');
    header('HTTP/1.1 200 OK');
    echo json_encode(['status' => 'success', 'data' => $historial]);
}
