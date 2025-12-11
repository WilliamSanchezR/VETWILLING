<?php
require_once __DIR__ . '/../../config/database.php';

class Eventos
{

    private $conexion; // Propiedad que guarda la conexión (PDO o mysqli)

    public function __construct()
    {
        // 1. Instanciamos tu clase de conexión
        $db = new conexion();
        // 2. Asignamos el objeto de conexión devuelto por getConexion() a la propiedad interna
        $this->conexion = $db->getConexion();
    }

    // ----------------------------------------------------------------------
    // 1. OBTENER TODOS LOS AGENDAMIENTOS (Usado por calendarioController::loadEvents)
    // ----------------------------------------------------------------------
    public function getAllAgendamientos()
    {
        $sql = "SELECT id_agendamiento, tipo, fecha_hora, fecha_hora_fin, estado, id_paciente, id_servicio, id_especialidad 
                FROM agendamiento";

        // CORRECCIÓN: Usar $this->conexion en lugar de $this->db
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        // Asumiendo que $this->conexion es un objeto PDO:
        // Si usas mysqli, la sintaxis de fetch cambiaría (ej: $stmt->get_result()->fetch_all(MYSQLI_ASSOC))
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------------
    // 2. CREAR UN NUEVO AGENDAMIENTO (Usado por calendarioController::storeEvent)
    // ----------------------------------------------------------------------
    public function createAgendamiento(array $data)
    {
        $sql = "INSERT INTO agendamiento (tipo, fecha_hora, fecha_hora_fin, estado, id_usuario, id_paciente, id_servicio, id_especialidad) 
                VALUES (:tipo, :fecha_hora, :fecha_hora_fin, :estado, :id_usuario, :id_paciente, :id_servicio, :id_especialidad)";

        // CORRECCIÓN: Usar $this->conexion
        $stmt = $this->conexion->prepare($sql);

        // Asignación de valores
        $stmt->bindValue(':tipo', $data['tipo'] ?? 'Desconocido');
        $stmt->bindValue(':fecha_hora', $data['fecha_hora']);
        $stmt->bindValue(':fecha_hora_fin', $data['fecha_hora_fin'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? 'Pendiente');
        $stmt->bindValue(':id_usuario', $data['id_usuario'] ?? null);
        $stmt->bindValue(':id_paciente', $data['id_paciente'] ?? null);
        $stmt->bindValue(':id_servicio', $data['id_servicio'] ?? null);
        $stmt->bindValue(':id_especialidad', $data['id_especialidad'] ?? null);

        if ($stmt->execute()) {
            // CORRECCIÓN: Usar $this->conexion para obtener el último ID insertado
            // Nota: lastInsertId() es un método de PDO
            return $this->conexion->lastInsertId();
        } else {
            return false;
        }
    }

    // ----------------------------------------------------------------------
    // 3. ACTUALIZAR FECHAS DE AGENDAMIENTO (Usado por calendarioController::updateEvent)
    // ----------------------------------------------------------------------
    public function updateAgendamientoDates(array $data)
    {
        $sql = "UPDATE agendamiento 
                SET fecha_hora = :start, 
                    fecha_hora_fin = :end
                WHERE id_agendamiento = :id";

        // CORRECCIÓN: Usar $this->conexion
        $stmt = $this->conexion->prepare($sql);

        // Asignación de valores del array de datos
        // Nota: Los placeholders en el SQL son ':start' y ':end', pero los datos vienen como 'fecha_hora' y 'fecha_hora_fin'
        $stmt->bindValue(':start', $data['fecha_hora']); // Mapeo de $data['fecha_hora'] a :start
        $stmt->bindValue(':end', $data['fecha_hora_fin'] ?? null); // Mapeo de $data['fecha_hora_fin'] a :end
        $stmt->bindValue(':id', $data['id_agendamiento']);

        return $stmt->execute(); // Devuelve TRUE o FALSE
    }

    // Opcional: Función para debugging si usas PDO
    public function getError()
    {
        if (isset($this->conexion) && method_exists($this->conexion, 'errorInfo')) {
            return $this->conexion->errorInfo();
        }
        return ['No hay conexión PDO o error desconocido.'];
    }
}
