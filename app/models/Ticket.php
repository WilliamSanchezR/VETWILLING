<?php

require_once __DIR__ . '/../../config/database.php';
require_once BASE_PATH . '/app/controllers/ticketController.php';


class Ticket
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }



    // FUNCIÓN PARA LISTAR TODOS LOS TICKETS
    public function listarTickets()
    {
        // Listar todos los tickets desde la base de datos.
        try {
            $sql = "SELECT tic.id, tic.titulo, tic.categoria, tic.prioridad, tic.estado, RL.nombres, RL.apellidos, tic.fecha_creacion, CONCAT(adm.nombres, ' ', adm.apellidos) as asignado
            FROM tickets tic
            INNER JOIN usuario us ON tic.usuario_id = us.id_usuario
            INNER JOIN representante_legal RL ON us.id_usuario = RL.id_usuario
            LEFT JOIN usuario usa ON tic.asignado_a = usa.id_usuario
            LEFT JOIN administrador adm ON usa.id_usuario = adm.id_usuario
            UNION
            (
                SELECT tic.id, tic.titulo, tic.categoria, tic.prioridad, tic.estado, pr.nombres, pr.apellidos, tic.fecha_creacion, CONCAT(adm.nombres, ' ', adm.apellidos) as asignado
            FROM tickets tic
            INNER JOIN usuario us ON tic.usuario_id = us.id_usuario
            INNER JOIN profesional pr ON us.id_usuario = pr.id_usuario
            LEFT JOIN usuario usa ON tic.asignado_a = usa.id_usuario
            LEFT JOIN administrador adm ON usa.id_usuario = adm.id_usuario
            )
            ORDER by id DESC";

            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            // Obtener los resultados
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tickets;
            // Manejar errores de la base de datos
        } catch (PDOException $e) {
            echo "Error al listar los tickets: " . $e->getMessage();
            return [];
        }
    }

    // FUNCIÓN PARA OBTENER LOS DETALLES DE UN TICKET POR ID
    public function obtenerTicketPorId($id)
    {
        try {
            $sql = "SELECT  tk.id, tk.titulo, tk.fecha_creacion, tk.categoria, tk.prioridad, tk.estado, pr.id_usuario as id_asignado, pr.nombres as nombre_asignado, pr.apellidos as apellido_asignado, tk.descripcion, tk.usuario_id as id_usuario
            FROM tickets TK
            LEFT JOIN usuario us ON tk.asignado_a = us.id_usuario
            LEFT JOIN administrador pr ON pr.id_usuario = us.id_usuario
            WHERE tk.id = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $ticket;
        } catch (PDOException $e) {
            echo "Error al obtener el ticket: " . $e->getMessage();
            return null;
        }
    }

    public function crearTicket($data)
    {
        try {
            // Calculamos la prioridad en función de la categoría
            $prioridad = 'media'; // Valor por defecto
            if ($data['categoria'] === 'tecnico') {
                $prioridad = 'alta';
            } elseif ($data['categoria'] === 'cuenta') {
                $prioridad = 'baja';
            } elseif ($data['categoria'] === 'facturacion') {
                $prioridad = 'critica';
            }

            $estado = 'abierto'; // Estado inicial del ticket

            $sql = "INSERT INTO tickets (titulo, descripcion, categoria, usuario_id, prioridad, estado) VALUES (:titulo, :descripcion, :categoria, :usuario_id, :prioridad, :estado)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':titulo', $data['titulo']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':categoria', $data['categoria']);
            $stmt->bindParam(':usuario_id', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':prioridad', $prioridad);
            $stmt->execute();
            $nuevoTicketId = $this->conexion->lastInsertId();
            return $nuevoTicketId;
        } catch (PDOException $e) {
            echo "Error al crear el ticket: " . $e->getMessage();
            return null;
        }
    }

    public function asignarTicket($ticketId, $adminId)
    {
        try {
            $sql = "UPDATE tickets SET asignado_a = :adminId, estado = 'en_proceso' WHERE id = :ticketId";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error al asignar el ticket: " . $e->getMessage();
            return false;
        }
    }
}
