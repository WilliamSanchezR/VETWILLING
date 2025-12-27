<?php

require_once __DIR__ . '/../../config/database.php';

class Eventos
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // =========================================
    //  FUNCIONES CRUD
    // =========================================

    // FUNCION PARA REGISTRAR UN NUEVO AGENDAMIENTO
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO agendamiento(
                tipo, observaciones, fecha_hora, fecha_hora_fin, estado, id_usuario, id_paciente, id_servicio, id_especialidad
            ) VALUES(
                :tipo, :observaciones, :fecha_hora, :fecha_hora_fin, :estado, :id_usuario, :id_paciente, :id_servicio, :id_especialidad
            )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo', $data['tipo']);
            $observaciones = $data['observaciones'] ?? null;
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora']);
            $stmt->bindParam(':fecha_hora_fin', $data['fecha_hora_fin']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);

            // Permitir NULL en campos opcionales
            $id_paciente = $data['id_paciente'] ?: null;
            $id_servicio = $data['id_servicio'] ?: null;
            $id_especialidad = $data['id_especialidad'] ?: null;

            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_INT);

            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error en execute: " . print_r($stmt->errorInfo(), true));
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en Eventos::registrar -> " . $e->getMessage());
            error_log("Datos recibidos: " . print_r($data, true));
            return false;
        }
    }

    // FUNCION PARA LISTAR LOS AGENDAMIENTOS REGISTRADOS
    public function listar()
    {
        try {
            $sql = "SELECT 
                id_agendamiento,
                tipo,
                observaciones,
                fecha_hora,
                fecha_hora_fin,
                estado,
                id_usuario,
                id_paciente,
                id_servicio,
                id_especialidad
            FROM agendamiento
            ORDER BY fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::listar -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA CONSULTAR UN AGENDAMIENTO POR ID
    public function consultarAgendamiento($id)
    {
        try {
            $sql = "SELECT 
                id_agendamiento,
                tipo,
                observaciones,
                fecha_hora,
                fecha_hora_fin,
                estado,
                id_usuario,
                id_paciente,
                id_servicio,
                id_especialidad
            FROM agendamiento
            WHERE id_agendamiento = :id LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::consultarAgendamiento -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA ACTUALIZAR UN AGENDAMIENTO
    public function actualizar($data)
    {
        try {
            $sql = "UPDATE agendamiento SET
                tipo = :tipo,
                observaciones = :observaciones,
                fecha_hora = :fecha_hora,
                fecha_hora_fin = :fecha_hora_fin,
                estado = :estado,
                id_paciente = :id_paciente,
                id_servicio = :id_servicio,
                id_especialidad = :id_especialidad
            WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $data['id_agendamiento']);
            $stmt->bindParam(':tipo', $data['tipo']);
            $observaciones = $data['observaciones'] ?? null;
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora']);
            $stmt->bindParam(':fecha_hora_fin', $data['fecha_hora_fin']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':id_paciente', $data['id_paciente']);
            $stmt->bindParam(':id_servicio', $data['id_servicio']);
            $stmt->bindParam(':id_especialidad', $data['id_especialidad']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Eventos::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UN AGENDAMIENTO
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM agendamiento WHERE id_agendamiento = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Eventos::eliminar -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA OBTENER TODOS LOS AGENDAMIENTOS (Alternativa a listar)
    public function getAllAgendamientos()
    {
        return $this->listar();
    }

    // FUNCION PARA CREAR UN AGENDAMIENTO (Alias de registrar)
    public function createAgendamiento(array $data)
    {
        if ($this->registrar($data)) {
            return $this->conexion->lastInsertId();
        }
        return false;
    }

    // FUNCION PARA ACTUALIZAR FECHAS DE AGENDAMIENTO
    public function updateAgendamientoDates(array $data)
    {
        try {
            $sql = "UPDATE agendamiento SET
                fecha_hora = :fecha_hora,
                fecha_hora_fin = :fecha_hora_fin
            WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $data['id_agendamiento']);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora']);
            $stmt->bindParam(':fecha_hora_fin', $data['fecha_hora_fin']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Eventos::updateAgendamientoDates -> " . $e->getMessage());
            return false;
        }
    }
}
