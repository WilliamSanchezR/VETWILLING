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
            tk.solucion as resultado
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

            // Registramos el histórico del ticket con el mensaje de creación
            $this->registrarHistoricoTicket($nuevoTicketId, 'creacion', '', '', "Ticket creado con título: {$data['titulo']}", $data['id_usuario']);

            return $nuevoTicketId;
        } catch (PDOException $e) {
            echo "Error al crear el ticket: " . $e->getMessage();
            return null;
        }
    }

    public function asignarTicket($ticketId, $adminId, $usuarioIdAuth)
    {
        try {
            $sql = "UPDATE tickets SET asignado_a = :adminId, estado = 'en_proceso' WHERE id = :ticketId";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmt->execute();

            // Consultamos el nombre del administrador para registrar en el histórico
            $sqlAdmin = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM administrador WHERE id_usuario = :adminId";
            $stmtAdmin = $this->conexion->prepare($sqlAdmin);
            $stmtAdmin->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmtAdmin->execute();
            $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
            $nombreAdmin = $admin ? $admin['nombre_completo'] : 'Desconocido';

            // Registramos el histórico del ticket con el mensaje de asignación
            $this->registrarHistoricoTicket($ticketId, 'asignacion', 'Sin asignar', $nombreAdmin, "Ticket asignado a $nombreAdmin", $usuarioIdAuth);

            return true;
        } catch (PDOException $e) {
            echo "Error al asignar el ticket: " . $e->getMessage();
            return false;
        }
    }

    public function actualizarTicket($ticketId, $nuevoEstado, $solucion, $reasignarA, $usuarioIdAuth)
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

            // Actualizamos el ticket solo si el estado ha cambiado o si se requiere reasignación o si se ha cambiado la solución
            if ($estadoActual !== $nuevoEstado || $reasignarA || $solucion != $ticket['solucion']) {
                $this->actualizarInformacionTicket($ticketId, $nuevoEstado, $solucion, $reasignarA);

                // Registramos el histórico del ticket con el mensaje de cambio de estado o reasignación o modificación
                if ($estadoActual !== $nuevoEstado) {
                    $this->registrarHistoricoTicket($ticketId, 'cambio_estado', $estadoActual, $nuevoEstado, "Cambio de estado de '$estadoActual' a '$nuevoEstado'", $usuarioIdAuth);
                }
                if ($reasignarA) {
                    // Consultamos el nombre del nuevo administrador para registrar en el histórico
                    $sqlAdmin = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM administrador WHERE id_usuario = :adminId";
                    $stmtAdmin = $this->conexion->prepare($sqlAdmin);
                    $stmtAdmin->bindParam(':adminId', $reasignarA, PDO::PARAM_INT);
                    $stmtAdmin->execute();
                    $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
                    $nombreAdmin = $admin ? $admin['nombre_completo'] : 'Desconocido';

                    // Obtenemos el nombre del usuario anterior asignado para registrar en el histórico
                    $nombreAnterior = 'Sin asignar';
                    if ($ticket['asignado_a']) {
                        $sqlAdminAnterior = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM administrador WHERE id_usuario = :adminId";
                        $stmtAdminAnterior = $this->conexion->prepare($sqlAdminAnterior);
                        $stmtAdminAnterior->bindParam(':adminId', $ticket['asignado_a'], PDO::PARAM_INT);
                        $stmtAdminAnterior->execute();
                        $adminAnterior = $stmtAdminAnterior->fetch(PDO::FETCH_ASSOC);
                        $nombreAnterior = $adminAnterior ? $adminAnterior['nombre_completo'] : 'Desconocido';
                    }


                    $this->registrarHistoricoTicket($ticketId, 'reasignacion', $nombreAnterior, $nombreAdmin, "Reasignación del ticket a $nombreAdmin", $usuarioIdAuth);
                }

                if ($solucion != $ticket['solucion']) {
                    $this->registrarHistoricoTicket($ticketId, 'modificacion', $ticket['solucion'], $solucion, "Modificación de la solución del ticket", $usuarioIdAuth);
                }
            }


            return true; // No se realizaron cambios, pero la operación fue exitosa

        } catch (PDOException $e) {
            echo "Error al actualizar el ticket: " . $e->getMessage();
            return false;
        }
    }

    public function consultarHistoricoTicket($ticketId)
    {
        try {
            $sql = "SELECT ht.fecha, ht.descripcion, ht.tipo, ht.valor_anterior, ht.valor_nuevo, CONCAT(adm.nombres, ' ', adm.apellidos) AS usuario
            FROM historico_tickets ht
            INNER JOIN usuario u ON ht.usuario_id = u.id_usuario
           	LEFT JOIN administrador adm ON u.id_usuario = adm.id_usuario
            WHERE ht.ticket_id = :ticketId
            ORDER BY ht.fecha DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmt->execute();
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $historico;
        } catch (PDOException $e) {
            echo "Error al consultar el histórico del ticket: " . $e->getMessage();
            return [];
        }
    }

    function actualizarInformacionTicket($ticketId, $nuevoEstado, $solucion, $reasignarA)
    {
        try {
            $sql = "UPDATE tickets SET estado = :nuevoEstado, solucion = :solucion";
            if ($reasignarA) {
                $sql .= ", asignado_a = :reasignarA";
            }
            if ($nuevoEstado === 'cerrado') {
                $sql .= ", fecha_cierre = NOW()";
            }
            $sql .= " WHERE id = :ticketId";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nuevoEstado', $nuevoEstado);
            $stmt->bindParam(':solucion', $solucion);
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

    function registrarHistoricoTicket($ticketId, $tipo, $valorAnterior, $valorNuevo, $descripcion, $usuarioId)
    {
        try {
            $sql = "INSERT INTO historico_tickets (ticket_id, usuario_id, tipo, valor_anterior, valor_nuevo, descripcion, fecha) VALUES (:ticketId, :usuarioId, :tipo, :valorAnterior, :valorNuevo, :descripcion, NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':valorAnterior', $valorAnterior);
            $stmt->bindParam(':valorNuevo', $valorNuevo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error al registrar el histórico del ticket: " . $e->getMessage();
            return false;
        }
    }
}
