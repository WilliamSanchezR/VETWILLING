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
            $sql = "SELECT tic.id, tic.titulo, tic.categoria, tic.prioridad, tic.estado, RL.nombres, RL.apellidos, tic.fecha_creacion
            FROM tickets tic
            INNER JOIN usuario ON tic.usuario_id = usuario.id_usuario
            INNER JOIN representante_legal RL ON usuario.id_usuario = RL.id_usuario";

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
}
