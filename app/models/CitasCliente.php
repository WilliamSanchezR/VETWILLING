<?php

/**
 * ═══════════════════════════════════════════════════════════════════
 *  MODELO DE CITAS PARA CLIENTES/PROPIETARIOS - ✅ CORREGIDO
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
     * ✅ CORREGIDO: Manejo adecuado de valores NULL
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

            // ✅ CORRECCIÓN: Usar bindValue para campos que pueden ser NULL
            
            // id_propietario - puede ser NULL
            if (isset($datos['id_propietario']) && $datos['id_propietario'] !== null) {
                $stmt->bindValue(':id_propietario', (int)$datos['id_propietario'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_propietario', null, PDO::PARAM_NULL);
            }

            // Campos requeridos (NOT NULL)
            $stmt->bindValue(':id_paciente', (int)$datos['id_paciente'], PDO::PARAM_INT);
            $stmt->bindValue(':id_servicio', (int)$datos['id_servicio'], PDO::PARAM_INT);
            $stmt->bindValue(':id_subservicio', (int)$datos['id_subservicio'], PDO::PARAM_INT);
            $stmt->bindValue(':id_especialidad', (int)$datos['id_especialidad'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_hora', $datos['fecha_hora'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_hora_fin', $datos['fecha_hora_fin'], PDO::PARAM_STR);
            $stmt->bindValue(':estado', $datos['estado'], PDO::PARAM_STR);

            // observaciones - puede ser NULL
            if (isset($datos['observaciones']) && $datos['observaciones'] !== null && $datos['observaciones'] !== '') {
                $stmt->bindValue(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':observaciones', null, PDO::PARAM_NULL);
            }

            // id_usuario - puede ser NULL (veterinario aún no asignado)
            if (isset($datos['id_usuario']) && $datos['id_usuario'] !== null) {
                $stmt->bindValue(':id_usuario', (int)$datos['id_usuario'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_usuario', null, PDO::PARAM_NULL);
            }

            $resultado = $stmt->execute();

            if ($resultado) {
                return $this->conexion->lastInsertId();
            }

            return false;
            
        } catch (PDOException $e) {
            error_log("❌ Error en CitasCliente::crearCita -> " . $e->getMessage());
            // Registro local para depuración accesible desde el proyecto
            $dir = __DIR__ . '/../logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file = $dir . '/citas_error.log';
            $ts = date('Y-m-d H:i:s');
            $info = isset($e->errorInfo) ? print_r($e->errorInfo, true) : 'sin errorInfo';
            $entry = "[{$ts}] CitasCliente::crearCita EXCEPTION: " . $e->getMessage() . " | errorInfo: " . $info . "\n";
            @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    /**
     * Lista todas las citas de un propietario con filtros opcionales
     * 
     * @param int $id_propietario - ID del propietario
     * @param array $filtros - Filtros opcionales
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
                        
                        pr.id_propietario,
                        CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_nombre,
                        pr.telefono as propietario_telefono,
                        
                        pac.id_paciente,
                        pac.nombre as mascota_nombre,
                        pac.especie as mascota_especie,
                        pac.raza as mascota_raza,
                        
                        s.id_servicio,
                        s.nombre as servicio_nombre,
                        
                        sub.id_subservicio,
                        sub.nombre as subservicio_nombre,
                        sub.costo as subservicio_costo,
                        
                        a.id_usuario,
                        COALESCE(CONCAT(v.nombres, ' ', v.apellidos), 'No asignado') as veterinario_nombre
                        
                    FROM agendamiento a
                    INNER JOIN propietario pr ON a.id_propietario = pr.id_propietario
                    INNER JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    LEFT JOIN veterinario v ON a.id_usuario = v.id_usuario
                    
                    WHERE a.id_propietario = :id_propietario";

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
            // Registro local accesible en el proyecto
            $dir = __DIR__ . '/../logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file = $dir . '/citas_error.log';
            $ts = date('Y-m-d H:i:s');
            $entry = "[{$ts}] CitasCliente::listarCitasPropietario EXCEPTION: " . $e->getMessage() . " | SQL: " . (isset($sql) ? $sql : 'no_sql') . "\n";
            @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            return [];
        }
    }

    /**
     * Modifica las fechas de una cita
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

    /**
     * Valida si una cita puede ser cancelada
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

    /**
     * Verifica que una mascota pertenezca a un propietario
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

    /**
     * Obtiene detalles completos de una cita
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