<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Ticket.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            // actualizarTicket();
        } 
        else if ($accion === 'cambiar-estado') {
            // cambiarEstadoTicket();
        }
    
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        // if ($accion === 'eliminar') {
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