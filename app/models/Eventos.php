<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/PacienteProfesionalAsignacion.php';

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
                tipo, observaciones, fecha_hora, fecha_hora_fin, estado, id_usuario, id_paciente, id_servicio, id_subservicio, id_especialidad
            ) VALUES(
                :tipo, :observaciones, :fecha_hora, :fecha_hora_fin, :estado, :id_usuario, :id_paciente, :id_servicio, :id_subservicio, :id_especialidad
            )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo', $data['tipo']);
            $observaciones = $data['observaciones'] ?? null;
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':fecha_hora', $data['fecha_hora']);
            if ($data['fecha_hora_fin'] !== null && $data['fecha_hora_fin'] !== '') {
                $stmt->bindParam(':fecha_hora_fin', $data['fecha_hora_fin']);
            } else {
                $stmt->bindValue(':fecha_hora_fin', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':estado', $data['estado']);

            if (isset($data['id_usuario']) && $data['id_usuario'] !== null && $data['id_usuario'] !== '') {
                $stmt->bindValue(':id_usuario', (int)$data['id_usuario'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_usuario', null, PDO::PARAM_NULL);
            }

            // Bind de campos requeridos
            $id_paciente = isset($data['id_paciente']) ? (int)$data['id_paciente'] : null;
            $id_servicio = isset($data['id_servicio']) ? (int)$data['id_servicio'] : null;
            $id_subservicio = isset($data['id_subservicio']) && $data['id_subservicio'] !== '' ? (int)$data['id_subservicio'] : null;
            $id_especialidad = isset($data['id_especialidad']) ? (int)$data['id_especialidad'] : null;

            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_INT);

            // Bind seguro para id_subservicio
            if ($id_subservicio !== null) {
                $stmt->bindValue(':id_subservicio', $id_subservicio, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_subservicio', null, PDO::PARAM_NULL);
            }
            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error en execute: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            $idAgendamiento = (int) $this->conexion->lastInsertId();

            if (!empty($data['id_paciente']) && !empty($data['id_usuario'])) {
                $asignacionModel = new PacienteProfesionalAsignacion();
                $okAsignacion = $asignacionModel->asegurarAsignacionActiva(
                    (int) $data['id_paciente'],
                    (int) $data['id_usuario'],
                    (int) $data['id_usuario'],
                    'Asignación automática desde agendamiento'
                );

                if (!$okAsignacion) {
                    error_log('Advertencia en Eventos::registrar -> no se pudo sincronizar asignación en paciente_profesional_asignacion');
                }
            }

            return $idAgendamiento;
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
                id_subservicio,
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

    // FUNCION PARA LISTAR AGENDAMIENTOS POR VETERINARIO
    public function listarPorVeterinario($id_usuario)
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
                id_subservicio,
                id_especialidad
            FROM agendamiento
            WHERE id_usuario = :id_usuario
            ORDER BY fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::listarPorVeterinario -> " . $e->getMessage());
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
                id_subservicio,
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
                id_propietario = :id_propietario,
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

            $id_propietario = $data['id_propietario'] ?: null;
            $stmt->bindParam(':id_propietario', $id_propietario);
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

    // FUNCION PARA VERIFICAR DISPONIBILIDAD DE HORARIO
    public function verificarDisponibilidad($fecha_inicio, $fecha_fin, $id_agendamiento = null)
    {
        try {
            $consulta = "SELECT COUNT(*) as conflictos 
                        FROM agendamiento 
                        WHERE estado NOT IN ('Cancelada', 'Realizada')
                        AND (
                            (fecha_hora < :fecha_fin AND fecha_hora_fin > :fecha_inicio)
                        )";

            // Si estamos actualizando, excluir el agendamiento actual
            if ($id_agendamiento) {
                $consulta .= " AND id_agendamiento != :id_agendamiento";
            }

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':fecha_inicio', $fecha_inicio);
            $resultado->bindParam(':fecha_fin', $fecha_fin);

            if ($id_agendamiento) {
                $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            }

            $resultado->execute();
            $row = $resultado->fetch(PDO::FETCH_ASSOC);

            // Retorna true si NO hay conflictos, false si hay conflictos
            return $row['conflictos'] == 0;
        } catch (PDOException $e) {
            error_log("Error en Eventos::verificarDisponibilidad -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA OBTENER CITAS QUE CAUSAN CONFLICTO
    public function obtenerCitasConflicto($fecha_inicio, $fecha_fin, $id_agendamiento = null)
    {
        try {
            $consulta = "SELECT 
                            a.id_agendamiento,
                            a.fecha_hora,
                            a.fecha_hora_fin,
                            a.tipo,
                            CONCAT(p.nombres, ' ', p.apellidos) as propietario,
                            pac.nombre as mascota
                        FROM agendamiento a
                        LEFT JOIN propietario p ON a.id_propietario = p.id_propietario
                        LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                        WHERE a.estado NOT IN ('Cancelada', 'Realizada')
                        AND (
                            (a.fecha_hora < :fecha_fin AND a.fecha_hora_fin > :fecha_inicio)
                        )";

            if ($id_agendamiento) {
                $consulta .= " AND a.id_agendamiento != :id_agendamiento";
            }

            $consulta .= " ORDER BY a.fecha_hora ASC";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':fecha_inicio', $fecha_inicio);
            $resultado->bindParam(':fecha_fin', $fecha_fin);

            if ($id_agendamiento) {
                $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            }

            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::obtenerCitasConflicto -> " . $e->getMessage());
            return [];
        }
    }

    // =========================================
    // FUNCION PARA OBTENER DETALLES COMPLETOS DE UN AGENDAMIENTO
    // =========================================
    public function obtenerDetallesCita($id_agendamiento)
    {
        try {
            $consulta = "SELECT
                            a.id_agendamiento,
                            a.tipo,
                            a.observaciones,
                            a.fecha_hora,
                            a.fecha_hora_fin,
                            a.estado,
                            prop.id_propietario,
                            CONCAT(prop.nombres, ' ', prop.apellidos) as nombre_propietario,
                            u.id_usuario as id_usuario_propietario,
                            u.email as email_propietario,
                            pac.id_paciente,
                            pac.nombre as nombre_mascota,
                            s.nombre as nombre_servicio
                        FROM agendamiento a
                        LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                        LEFT JOIN propietario prop ON pac.id_propietario = prop.id_propietario
                        LEFT JOIN usuario u ON prop.id_usuario = u.id_usuario
                        LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                        WHERE a.id_agendamiento = :id_agendamiento";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::obtenerDetallesCita -> " . $e->getMessage());
            return null;
        }
    }

    // ╔══════════════════════════════════════════════════════════════════════════════╗
    // ║                    RFS 34: FILTRAR CITAS PROGRAMADAS                        ║
    // ║  Permite filtrar citas por propietario, estado, fecha con criterios múltiples║
    // ╚══════════════════════════════════════════════════════════════════════════════╝

    /**
     * FILTRAR CITAS CON MÚLTIPLES CRITERIOS
     * 
     * @param array $filtros - Array con claves: 'id_propietario', 'estado', 'fecha_inicio', 'fecha_fin'
     * @return array - Citas que coinciden con los filtros
     * 
     * Ejemplo:
     * $filtros = [
     *     'id_propietario' => 5,
     *     'estado' => 'Pendiente',
     *     'fecha_inicio' => '2024-01-15',
     *     'fecha_fin' => '2024-02-15'
     * ]
     */
    public function filtrarCitas($filtros = [])
    {
        try {
            // ┌─ BASE DE LA CONSULTA
            $sql = "SELECT 
                        id_agendamiento,
                        tipo,
                        observaciones,
                        fecha_hora,
                        fecha_hora_fin,
                        estado,
                        id_usuario,
                        id_propietario,
                        id_paciente,
                        id_servicio,
                        id_especialidad
                    FROM agendamiento
                    WHERE 1=1";

            $parametros = [];

            // ┌─ FILTRO POR PROPIETARIO
            if (!empty($filtros['id_propietario'])) {
                $sql .= " AND id_propietario = :id_propietario";
                $parametros['id_propietario'] = $filtros['id_propietario'];
            }

            // ┌─ FILTRO POR ESTADO
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = :estado";
                $parametros['estado'] = $filtros['estado'];
            }

            // ┌─ FILTRO POR FECHA INICIO
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(fecha_hora) >= :fecha_inicio";
                $parametros['fecha_inicio'] = $filtros['fecha_inicio'];
            }

            // ┌─ FILTRO POR FECHA FIN
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(fecha_hora) <= :fecha_fin";
                $parametros['fecha_fin'] = $filtros['fecha_fin'];
            }

            // ┌─ FILTRO POR VETERINARIO (ID_USUARIO)
            if (!empty($filtros['id_usuario'])) {
                $sql .= " AND id_usuario = :id_usuario";
                $parametros['id_usuario'] = $filtros['id_usuario'];
            }

            // ┌─ FILTRO POR TIPO DE CITA
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo LIKE :tipo";
                $parametros['tipo'] = '%' . $filtros['tipo'] . '%';
            }

            // ┌─ ORDENAR RESULTADOS
            $sql .= " ORDER BY fecha_hora ASC";

            // ┌─ EJECUTAR CONSULTA
            $stmt = $this->conexion->prepare($sql);

            foreach ($parametros as $clave => $valor) {
                $stmt->bindParam(':' . $clave, $parametros[$clave]);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::filtrarCitas -> " . $e->getMessage());
            return [];
        }
    }

    // ╔══════════════════════════════════════════════════════════════════════════════╗
    // ║                    RFS 37: SISTEMA DE RECORDATORIOS                          ║
    // ║  Funciones para obtener citas y marcar recordatorios enviados               ║
    // ╚══════════════════════════════════════════════════════════════════════════════╝

    /**
     * OBTENER CITAS QUE NECESITAN RECORDATORIO
     * 
     * Obtiene todas las citas pendientes dentro de un rango de fechas
     * que aún no han sido notificadas (para envío de recordatorios 24h antes)
     * 
     * @param string $fechaInicio - Fecha y hora de inicio del rango (Y-m-d H:i:s)
     * @param string $fechaFin - Fecha y hora de fin del rango (Y-m-d H:i:s)
     * @return array - Array de citas con información del propietario y mascota
     */
    public function obtenerCitasParaRecordatorio($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.tipo,
                        a.fecha_hora,
                        a.estado,
                        p.id_propietario,
                        CONCAT(prop.nombres, ' ', prop.apellidos) as nombre_propietario,
                        u.email as email_propietario,
                        u.preferencia_notificacion,
                        p.id_paciente,
                        p.nombre as nombre_mascota
                    FROM agendamiento a
                    INNER JOIN paciente p ON a.id_paciente = p.id_paciente
                    INNER JOIN propietario prop ON p.id_propietario = prop.id_propietario
                    INNER JOIN usuario u ON prop.id_usuario = u.id_usuario
                    WHERE a.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
                    AND a.estado IN ('Pendiente', 'Confirmada')
                    AND (a.recordatorio_enviado IS NULL OR a.recordatorio_enviado = 0)
                    ORDER BY a.fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::obtenerCitasParaRecordatorio -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * MARCAR RECORDATORIO COMO ENVIADO
     * 
     * Actualiza el campo recordatorio_enviado de una cita específica
     * para evitar envíos duplicados
     * 
     * @param int $id_agendamiento - ID de la cita
     * @return bool - True si se actualizó correctamente
     */
    public function marcarRecordatorioEnviado($id_agendamiento)
    {
        try {
            $sql = "UPDATE agendamiento 
                    SET recordatorio_enviado = 1,
                        fecha_recordatorio = NOW()
                    WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Eventos::marcarRecordatorioEnviado -> " . $e->getMessage());
            return false;
        }
    }
}
