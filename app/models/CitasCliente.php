<?php

/**
 * ═══════════════════════════════════════════════════════════════════
 *  MODELO DE CITAS PARA CLIENTES/PROPIETARIOS
 *  Archivo: CitasCliente.php
 *  Descripción: Maneja todas las operaciones de BD para citas de clientes
 * ═══════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../../config/database.php';

class CitasCliente
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RFS 33: CREAR CITA
    // ═══════════════════════════════════════════════════════════════════
/**
 * Obtiene el id_propietario a partir del id_usuario
 *
 * @param int $id_usuario
 * @return int|null
 */
public function obtenerIdPropietarioPorUsuario($id_usuario)
{
    try {
        $sql = "SELECT id_propietario
                FROM propietario
                WHERE id_usuario = :id_usuario
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;

    } catch (PDOException $e) {
        error_log("❌ Error obtenerIdPropietarioPorUsuario -> " . $e->getMessage());
        return null;
    }
}



    /**
     * Crea una nueva cita en la base de datos
     * 
     * @param array $datos - Datos de la cita
     * @return int|false - ID de la cita creada o false si falla
     */
    public function crearCita($datos)
    {
        try {
            $sql = "INSERT INTO agendamiento (
                        id_propietario,
                        id_paciente,
                        id_servicio,
                        id_subservicio,
                        id_especialidad,
                        tipo,
                        observaciones,
                        fecha_hora,
                        fecha_hora_fin,
                        estado,
                        id_usuario
                    ) VALUES (
                        :id_propietario,
                        :id_paciente,
                        :id_servicio,
                        :id_subservicio,
                        :id_especialidad,
                        :tipo,
                        :observaciones,
                        :fecha_hora,
                        :fecha_hora_fin,
                        :estado,
                        :id_usuario
                    )";

            $stmt = $this->conexion->prepare($sql);

            // Bind de parámetros
            $stmt->bindParam(':id_propietario', $datos['id_propietario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_servicio', $datos['id_servicio'], PDO::PARAM_INT);
            $stmt->bindParam(':id_subservicio', $datos['id_subservicio'], PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $datos['id_especialidad'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_hora', $datos['fecha_hora'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $datos['estado'], PDO::PARAM_STR);

            // id_usuario puede ser NULL si aún no se asigna veterinario
            $id_usuario = $datos['id_usuario'] ?? null;
            if ($id_usuario === null) {
                $stmt->bindValue(':id_usuario', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }

            $resultado = $stmt->execute();

            if ($resultado) {
                return $this->conexion->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::crearCita -> " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RFS 34: LISTAR CITAS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Lista todas las citas de un propietario con filtros opcionales
     * 
     * @param int $id_propietario - ID del propietario
     * @param array $filtros - Filtros opcionales (estado, fecha_inicio, fecha_fin, id_paciente)
     * @return array - Array de citas
     */
    public function listarCitasPropietario($id_propietario, $filtros = [])
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.tipo,
                        a.observaciones,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.estado,
                        
                        -- Propietario
                        pr.id_propietario,
                        CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_nombre,
                        pr.telefono as propietario_telefono,
                        
                        -- Mascota
                        pac.id_paciente,
                        pac.nombre as mascota_nombre,
                        pac.especie as mascota_especie,
                        pac.raza as mascota_raza,
                        
                        -- Servicio
                        s.id_servicio,
                        s.nombre as servicio_nombre,
                        
                        -- Subservicio
                        sub.id_subservicio,
                        sub.nombre as subservicio_nombre,
                        sub.costo as subservicio_costo,
                        
                        -- Veterinario
                        u.id_usuario,
                        CONCAT(u.nombres, ' ', u.apellidos) as veterinario_nombre
                        
                    FROM agendamiento a
                    INNER JOIN propietario pr ON a.id_propietario = pr.id_propietario
                    INNER JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                    
                    WHERE a.id_propietario = :id_propietario";

            // Aplicar filtros
            $params = ['id_propietario' => $id_propietario];

            if (!empty($filtros['estado'])) {
                $sql .= " AND a.estado = :estado";
                $params['estado'] = $filtros['estado'];
            }

            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(a.fecha_hora) >= :fecha_inicio";
                $params['fecha_inicio'] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(a.fecha_hora) <= :fecha_fin";
                $params['fecha_fin'] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['id_paciente'])) {
                $sql .= " AND a.id_paciente = :id_paciente";
                $params['id_paciente'] = $filtros['id_paciente'];
            }

            $sql .= " ORDER BY a.fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::listarCitasPropietario -> " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RFS 35: MODIFICAR CITA
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Modifica las fechas de una cita
     * 
     * @param int $id_agendamiento - ID de la cita
     * @param string $fecha_hora - Nueva fecha y hora de inicio
     * @param string $fecha_hora_fin - Nueva fecha y hora de fin
     * @return bool - true si se actualizó correctamente
     */
    public function modificarFechasCita($id_agendamiento, $fecha_hora, $fecha_hora_fin)
    {
        try {
            $sql = "UPDATE agendamiento 
                    SET fecha_hora = :fecha_hora,
                        fecha_hora_fin = :fecha_hora_fin
                    WHERE id_agendamiento = :id_agendamiento
                    AND estado = 'Pendiente'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora', $fecha_hora, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_hora_fin', $fecha_hora_fin, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::modificarFechasCita -> " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RFS 36: CANCELAR CITA
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Valida si una cita puede ser cancelada
     * 
     * @param int $id_agendamiento - ID de la cita
     * @return array - ['valido' => bool, 'mensaje' => string, 'estado_actual' => string]
     */
    public function validarEstadoCita($id_agendamiento)
    {
        try {
            $sql = "SELECT id_agendamiento, estado, fecha_hora
                    FROM agendamiento
                    WHERE id_agendamiento = :id_agendamiento
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->execute();

            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cita) {
                return [
                    'valido' => false,
                    'mensaje' => 'La cita no existe',
                    'estado_actual' => null
                ];
            }

            if (strtotime($cita['fecha_hora']) <= time()) {
                return [
                    'valido' => false,
                    'mensaje' => 'No se puede cancelar una cita ya iniciada o pasada',
                    'estado_actual' => $cita['estado']
                ];
            }

            return [
                'valido' => true,
                'mensaje' => 'La cita puede ser cancelada',
                'estado_actual' => $cita['estado']
            ];
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::validarEstadoCita -> " . $e->getMessage());
            return [
                'valido' => false,
                'mensaje' => 'Error al validar la cita',
                'estado_actual' => null
            ];
        }
    }

    /**
     * Registra el motivo de cancelación
     * 
     * @param int $id_agendamiento - ID de la cita
     * @param string $motivo - Motivo de cancelación
     * @param int $id_usuario - ID del usuario que cancela
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function registrarMotivoCancelacion($id_agendamiento, $motivo, $id_usuario = null)
    {
        try {
            $sql = "UPDATE agendamiento 
                    SET motivo_cancelacion = :motivo,
                        fecha_cancelacion = NOW()";

            if ($id_usuario !== null) {
                $sql .= ", usuario_cancelo = :id_usuario";
            }

            $sql .= " WHERE id_agendamiento = :id_agendamiento";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);

            if ($id_usuario !== null) {
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }

            $resultado = $stmt->execute();

            return [
                'exito' => $resultado,
                'mensaje' => $resultado ? 'Motivo registrado' : 'Error al registrar motivo'
            ];
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::registrarMotivoCancelacion -> " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error del sistema'
            ];
        }
    }

    /**
     * Actualiza el estado de la cita a Cancelada
     * 
     * @param int $id_agendamiento - ID de la cita
     * @return array - ['exito' => bool, 'mensaje' => string]
     */
    public function actualizarEstadoCancelada($id_agendamiento)
    {
        try {
            $sql = "UPDATE agendamiento 
                    SET estado = 'Cancelada'
                    WHERE id_agendamiento = :id_agendamiento
                    AND estado = 'Pendiente'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado = $stmt->execute();

            return [
                'exito' => $resultado && $stmt->rowCount() > 0,
                'mensaje' => $resultado ? 'Estado actualizado' : 'No se pudo actualizar'
            ];
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::actualizarEstadoCancelada -> " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error del sistema'
            ];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  FUNCIONES DE VALIDACIÓN
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Verifica que una mascota pertenezca a un propietario
     * 
     * @param int $id_paciente - ID de la mascota
     * @param int $id_propietario - ID del propietario
     * @return bool - true si la mascota pertenece al propietario
     */
    public function verificarMascotaPropietario($id_paciente, $id_propietario)
    {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM paciente
                    WHERE id_paciente = :id_paciente
                    AND id_propietario = :id_propietario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::verificarMascotaPropietario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica que una cita pertenezca a un propietario
     * 
     * @param int $id_agendamiento - ID de la cita
     * @param int $id_propietario - ID del propietario
     * @return bool - true si la cita pertenece al propietario
     */
    public function verificarCitaPropietario($id_agendamiento, $id_propietario)
    {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM agendamiento
                    WHERE id_agendamiento = :id_agendamiento
                    AND id_propietario = :id_propietario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::verificarCitaPropietario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica disponibilidad de horario
     * 
     * @param string $fecha_inicio - Fecha y hora de inicio
     * @param string $fecha_fin - Fecha y hora de fin
     * @param int|null $id_agendamiento - ID de cita a excluir (para modificaciones)
     * @return bool - true si está disponible
     */
    public function verificarDisponibilidad($fecha_inicio, $fecha_fin, $id_agendamiento = null)
    {
        try {
            $sql = "SELECT COUNT(*) as conflictos
                    FROM agendamiento
                    WHERE estado NOT IN ('Cancelada', 'Realizada')
                    AND (
                        (fecha_hora < :fecha_fin AND fecha_hora_fin > :fecha_inicio)
                    )";

            if ($id_agendamiento) {
                $sql .= " AND id_agendamiento != :id_agendamiento";
            }

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);

            if ($id_agendamiento) {
                $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            }

            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['conflictos'] == 0;
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::verificarDisponibilidad -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene citas que causan conflicto de horario
     * 
     * @param string $fecha_inicio - Fecha y hora de inicio
     * @param string $fecha_fin - Fecha y hora de fin
     * @param int|null $id_agendamiento - ID de cita a excluir
     * @return array - Array de citas en conflicto
     */
    public function obtenerCitasConflicto($fecha_inicio, $fecha_fin, $id_agendamiento = null)
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.tipo,
                        pac.nombre as mascota
                    FROM agendamiento a
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    WHERE a.estado NOT IN ('Cancelada', 'Realizada')
                    AND (
                        (a.fecha_hora < :fecha_fin AND a.fecha_hora_fin > :fecha_inicio)
                    )";

            if ($id_agendamiento) {
                $sql .= " AND a.id_agendamiento != :id_agendamiento";
            }

            $sql .= " ORDER BY a.fecha_hora ASC LIMIT 5";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);

            if ($id_agendamiento) {
                $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerCitasConflicto -> " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  FUNCIONES AUXILIARES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Obtiene detalles completos de una cita
     * 
     * @param int $id_agendamiento - ID de la cita
     * @return array|null - Datos de la cita o null
     */
    public function obtenerDetallesCita($id_agendamiento)
    {
        try {
            $sql = "SELECT 
                        a.*,
                        CONCAT(pr.nombres, ' ', pr.apellidos) as nombre_propietario,
                        u.email as email_propietario,
                        pac.nombre as nombre_mascota,
                        pac.especie as mascota_especie,
                        pac.raza as mascota_raza,
                        s.nombre as servicio_nombre,
                        sub.nombre as subservicio_nombre,
                        CONCAT(vet.nombres, ' ', vet.apellidos) as veterinario_nombre
                    FROM agendamiento a
                    LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                    LEFT JOIN usuario u ON pr.id_usuario = u.id_usuario
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    LEFT JOIN usuario vet ON a.id_usuario = vet.id_usuario
                    WHERE a.id_agendamiento = :id_agendamiento
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerDetallesCita -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene datos para notificación de cancelación
     * 
     * @param int $id_agendamiento - ID de la cita
     * @return array|null - Datos para la notificación
     */
    public function obtenerDatosNotificacionCancelacion($id_agendamiento)
    {
        try {
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_cancelacion,
                        a.motivo_cancelacion,
                        a.tipo as tipo_servicio,
                        CONCAT(pr.nombres, ' ', pr.apellidos) as nombre_completo_propietario,
                        u.email as email_propietario,
                        pac.nombre as nombre_mascota
                    FROM agendamiento a
                    LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                    LEFT JOIN usuario u ON pr.id_usuario = u.id_usuario
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    WHERE a.id_agendamiento = :id_agendamiento
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerDatosNotificacionCancelacion -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene servicios disponibles
     * 
     * @param int|null $id_veterinaria - ID de la veterinaria
     * @return array - Array de servicios
     */
    public function obtenerServicios($id_veterinaria = null)
    {
        try {
            $sql = "SELECT id_servicio, nombre, descripcion
                    FROM servicio
                    WHERE estado = 'Activo'";

            if ($id_veterinaria) {
                $sql .= " AND id_veterinaria = :id_veterinaria";
            }

            $sql .= " ORDER BY nombre ASC";

            $stmt = $this->conexion->prepare($sql);

            if ($id_veterinaria) {
                $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerServicios -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene subservicios de un servicio
     * 
     * @param int $id_servicio - ID del servicio
     * @return array - Array de subservicios
     */
    public function obtenerSubserviciosPorServicio($id_servicio)
    {
        try {
            $sql = "SELECT id_subservicio, nombre, descripcion, costo
                    FROM subservicios
                    WHERE id_servicio = :id_servicio
                    AND estado = 'Activo'
                    ORDER BY nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerSubserviciosPorServicio -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene mascotas de un propietario
     * 
     * @param int $id_propietario - ID del propietario
     * @return array - Array de mascotas
     */
    public function obtenerMascotasPropietario($id_propietario)
    {
        try {
            $sql = "SELECT 
                        id_paciente,
                        nombre,
                        especie,
                        raza,
                        edad_numero,
                        edad_unidad,
                        sexo,
                        img_mascota
                    FROM paciente
                    WHERE id_propietario = :id_propietario
                    ORDER BY nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::obtenerMascotasPropietario -> " . $e->getMessage());
            return [];
        }
    }
}
