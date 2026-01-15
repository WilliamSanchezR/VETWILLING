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
                tipo, observaciones, fecha_hora, fecha_hora_fin, estado, id_usuario, id_propietario, id_paciente, id_servicio, id_especialidad
            ) VALUES(
                :tipo, :observaciones, :fecha_hora, :fecha_hora_fin, :estado, :id_usuario, :id_propietario, :id_paciente, :id_servicio, :id_especialidad
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
            $id_propietario = $data['id_propietario'] ?: null;
            $id_paciente = $data['id_paciente'] ?: null;
            $id_servicio = $data['id_servicio'] ?: null;
            $id_especialidad = $data['id_especialidad'] ?: null;

            $stmt->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_INT);

            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error en execute: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            // Retornar el ID del último registro insertado
            return $this->conexion->lastInsertId();
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
                id_propietario,
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
                id_propietario,
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
                            p.id_propietario,
                            CONCAT(p.nombres, ' ', p.apellidos) as nombre_propietario,
                            p.email as email_propietario,
                            pac.id_paciente,
                            pac.nombre as nombre_mascota,
                            s.nombre as nombre_servicio
                         FROM agendamiento a
                         LEFT JOIN propietario p ON a.id_propietario = p.id_propietario
                         LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
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

    // =========================================
    //  RFS 37: FUNCIONES PARA NOTIFICACIONES Y RECORDATORIOS
    // =========================================

    /**
     * OBTENER CITAS QUE NECESITAN RECORDATORIO
     * 
     * Busca todas las citas que:
     * - Estan en estado 'Pendiente'
     * - Ocurriran en el rango de tiempo especificado
     * - NO tienen recordatorio enviado (recordatorio_enviado = 0 o NULL)
     * - El propietario tiene email valido
     * 
     * @param string $fechaInicio Fecha/hora inicio del rango (formato: Y-m-d H:i:s)
     * @param string $fechaFin Fecha/hora fin del rango (formato: Y-m-d H:i:s)
     * @return array Array de citas con informacion completa del propietario y mascota
     */
    public function obtenerCitasParaRecordatorio($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.tipo,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.estado,
                        a.observaciones,
                        a.recordatorio_enviado,
                        
                        -- DATOS DEL PROPIETARIO
                        p.id_propietario,
                        CONCAT(p.nombres, ' ', p.apellidos) as nombre_propietario,
                        p.nombres as propietario_nombres,
                        p.apellidos as propietario_apellidos,
                        p.email as email_propietario,
                        p.telefono as telefono_propietario,
                        p.preferencia_notificacion,
                        
                        -- DATOS DE LA MASCOTA
                        pac.id_paciente,
                        pac.nombre as nombre_mascota,
                        pac.especie as especie_mascota,
                        pac.raza as raza_mascota,
                        
                        -- DATOS DEL SERVICIO
                        s.id_servicio,
                        s.nombre as nombre_servicio,
                        
                        -- DATOS DEL VETERINARIO
                        u.id_usuario,
                        CONCAT(u.nombres, ' ', u.apellidos) as nombre_veterinario
                        
                    FROM agendamiento a
                    INNER JOIN propietario p ON a.id_propietario = p.id_propietario
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                    
                    WHERE a.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
                    AND a.estado = 'Pendiente'
                    AND (a.recordatorio_enviado IS NULL OR a.recordatorio_enviado = 0)
                    AND p.email IS NOT NULL
                    AND p.email != ''
                    AND p.preferencia_notificacion = 'email'
                    
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
     * MARCAR CITA COMO RECORDATORIO ENVIADO
     * 
     * Actualiza el campo recordatorio_enviado a 1 cuando se envia exitosamente
     * 
     * @param int $idAgendamiento ID de la cita
     * @return bool True si se actualizo correctamente
     */
    public function marcarRecordatorioEnviado($idAgendamiento)
    {
        try {
            $sql = "UPDATE agendamiento 
                    SET recordatorio_enviado = 1 
                    WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Eventos::marcarRecordatorioEnviado -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * OBTENER CITAS PROXIMAS DE UN PROPIETARIO ESPECIFICO
     * 
     * Busca las proximas citas de un propietario en particular
     * Util para mostrar recordatorios en el dashboard del usuario
     * 
     * @param int $idPropietario ID del propietario
     * @param int $diasAdelante Numero de dias hacia adelante a buscar (default: 7)
     * @return array Array de citas proximas
     */
    public function obtenerCitasProximasPropietario($idPropietario, $diasAdelante = 7)
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.tipo,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.estado,
                        a.observaciones,
                        
                        pac.nombre as nombre_mascota,
                        pac.especie as especie_mascota,
                        
                        s.nombre as nombre_servicio,
                        
                        CONCAT(u.nombres, ' ', u.apellidos) as nombre_veterinario,
                        u.telefono as telefono_veterinario
                        
                    FROM agendamiento a
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                    
                    WHERE a.id_propietario = :id_propietario
                    AND a.estado = 'Pendiente'
                    AND a.fecha_hora >= NOW()
                    AND a.fecha_hora <= DATE_ADD(NOW(), INTERVAL :dias_adelante DAY)
                    
                    ORDER BY a.fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_propietario', $idPropietario, PDO::PARAM_INT);
            $stmt->bindParam(':dias_adelante', $diasAdelante, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::obtenerCitasProximasPropietario -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * OBTENER ESTADISTICAS DE RECORDATORIOS ENVIADOS
     * 
     * Retorna estadisticas sobre recordatorios enviados en un periodo
     * Util para reportes y monitoreo del sistema
     * 
     * @param string $fechaInicio Fecha inicio del periodo (Y-m-d)
     * @param string $fechaFin Fecha fin del periodo (Y-m-d)
     * @return array Array con estadisticas
     */
    public function obtenerEstadisticasRecordatorios($fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_citas,
                        SUM(CASE WHEN recordatorio_enviado = 1 THEN 1 ELSE 0 END) as recordatorios_enviados,
                        SUM(CASE WHEN recordatorio_enviado = 0 OR recordatorio_enviado IS NULL THEN 1 ELSE 0 END) as sin_recordatorio,
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as citas_pendientes,
                        SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as citas_completadas,
                        SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as citas_canceladas
                        
                    FROM agendamiento
                    WHERE DATE(fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Eventos::obtenerEstadisticasRecordatorios -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * VERIFICAR SI UNA CITA YA TIENE RECORDATORIO ENVIADO
     * 
     * Consulta rapida para verificar el estado del recordatorio
     * 
     * @param int $idAgendamiento ID de la cita
     * @return bool True si ya se envio recordatorio
     */
    public function tieneRecordatorioEnviado($idAgendamiento)
    {
        try {
            $sql = "SELECT recordatorio_enviado 
                    FROM agendamiento 
                    WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado && $resultado['recordatorio_enviado'] == 1;
        } catch (PDOException $e) {
            error_log("Error en Eventos::tieneRecordatorioEnviado -> " . $e->getMessage());
            return false;
        }
    }
}
