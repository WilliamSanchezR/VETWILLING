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

        // if ($action === 'eliminar') {
        //     eliminarUsuario($_GET['id']);
        // } else if (isset($_GET['id'])) {
        //     consultarTicket($_GET['id']);
        // } else {
        //     listarTickets();
        // }
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
        // recibir datos del formulario (enviar como JSON desde el frontend)
        $data = json_decode(file_get_contents('php://input'), true);

        $titulo = $data['asunto'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $categoria = $data['categoria'] ?? '';
        $id_usuario = $data['id_usuario'] ?? null;



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
            'id_usuario' => $id_usuario
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
