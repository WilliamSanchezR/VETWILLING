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
            $sql = "SELECT  tk.id, tk.titulo, tk.fecha_creacion, tk.categoria, tk.prioridad, tk.estado, pr.id_usuario as id_asignado, pr.nombres as nombre_asignado, pr.apellidos as apellido_asignado, tk.descripcion, tk.usuario_id as id_usuario, 
            (SELECT mensaje FROM historico_tickets WHERE ticket_id = tk.id ORDER BY fecha DESC LIMIT 1) as resultado
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

    public function actualizarTicket($ticketId, $nuevoEstado, $solucion, $reasignarA)
    {
        try {
            // consultamos el ticket para verificar su estado actual
            $sqlConsulta = "SELECT * FROM tickets WHERE id = :ticketId";
            $stmtConsulta = $this->conexion->prepare($sqlConsulta);
            $stmtConsulta->bindParam(
                ':ticketId',
                $ticketId,
                PDO::PARAM_INT
            );
            $stmtConsulta->execute();
            $ticket = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) {
                echo "Ticket no encontrado.";
                return false;
            }
            $estadoActual = $ticket['estado'];
            // Validamos si el estado actual es diferente al nuevo estado o si se ha asignado un nuevo usuario para reasignar
            if ($estadoActual !== $nuevoEstado ||  $reasignarA) {
                $this->actualizarInformacionTicket($ticketId, $nuevoEstado, $reasignarA ?? $ticket['asignado_a']);
                return $this->registrarHistoricoTicket($ticketId, $solucion, $reasignarA ?? $ticket['asignado_a']);
            }

            // consultamos el ultimo registro del historico para verificar si se ha registrado una solución
            $sqlHistorico = "SELECT mensaje FROM historico_tickets WHERE ticket_id = :ticketId ORDER BY fecha DESC LIMIT 1";
            $stmtHistorico = $this->conexion->prepare($sqlHistorico);
            $stmtHistorico->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmtHistorico->execute();
            $ultimoHistorico = $stmtHistorico->fetch(PDO::FETCH_ASSOC);

            // validamos si se ha registrado una solución y si la solución es diferente a la última solución registrada en el histórico
            if (!empty($solucion) && (!$ultimoHistorico || $ultimoHistorico['mensaje'] !== $solucion)) {
                return $this->registrarHistoricoTicket($ticketId, $solucion, $ticket['asignado_a']);
            }

            return true; // No se realizaron cambios, pero la operación fue exitosa

        } catch (PDOException $e) {
            echo "Error al actualizar el ticket: " . $e->getMessage();
            return false;
        }
    }

    function actualizarInformacionTicket($ticketId, $nuevoEstado, $reasignarA)
    {
        try {
            $sql = "UPDATE tickets SET estado = :nuevoEstado";
            if ($reasignarA) {
                $sql .= ", asignado_a = :reasignarA";
            }
            if ($nuevoEstado === 'cerrado') {
                $sql .= ", fecha_cierre = NOW()";
            }
            $sql .= " WHERE id = :ticketId";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nuevoEstado', $nuevoEstado);
            if ($reasignarA) {
                $stmt->bindParam(':reasignarA', $reasignarA, PDO::PARAM_INT);
            }
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar el ticket: " . $e->getMessage();
            return false;
        }
    }

    function registrarHistoricoTicket($ticketId, $solucion, $usuarioId)
    {
        try {
            $sql = "INSERT INTO historico_tickets (ticket_id, usuario_id, mensaje, fecha) VALUES (:ticketId, :usuarioId, :mensaje, NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmt->bindParam(':mensaje', $solucion);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error al registrar el histórico del ticket: " . $e->getMessage();
            return false;
        }
    }
}
