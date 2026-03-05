<?php

/**
 * ═══════════════════════════════════════════════════════════════════
 *  MODELO DE SEGUIMIENTOS DE PACIENTES
 *  Descripción: Maneja todas las operaciones de BD para seguimientos
 *  Versión: 2.0 - Usa las nuevas tablas de seguimientos
 * ═══════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../../config/database.php';

class Seguimientos
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene todos los seguimientos activos de un veterinario usando la nueva estructura
     * 
     * @param int $id_usuario ID del veterinario/profesional
     * @param int $id_veterinaria ID de la veterinaria (opcional)
     * @return array Lista de seguimientos con toda la información
     */
    public function obtenerSeguimientosPorProfesional($id_usuario, $id_veterinaria = null)
    {
        try {
            // Usar la vista optimizada
            $sql = "SELECT 
                        s.*,
                        -- Última cita desde agendamiento
                        (SELECT fecha_hora 
                         FROM agendamiento 
                         WHERE id_paciente = s.id_paciente 
                         AND id_usuario = s.id_usuario_profesional
                         AND fecha_hora < NOW()
                         ORDER BY fecha_hora DESC 
                         LIMIT 1) AS ultima_cita,
                        
                        -- Próxima cita programada
                        (SELECT fecha_hora 
                         FROM agendamiento 
                         WHERE id_paciente = s.id_paciente 
                         AND id_usuario = s.id_usuario_profesional
                         AND fecha_hora > NOW()
                         AND estado != 'Cancelada'
                         ORDER BY fecha_hora ASC 
                         LIMIT 1) AS proxima_cita,
                        
                        -- Último diagnóstico / observaciones (agendamiento usa 'observaciones')
                        (SELECT observaciones 
                         FROM agendamiento 
                         WHERE id_paciente = s.id_paciente 
                         AND id_usuario = s.id_usuario_profesional
                         AND observaciones IS NOT NULL
                         AND observaciones != ''
                         ORDER BY fecha_hora DESC 
                         LIMIT 1) AS ultimo_diagnostico
                        
                    FROM vista_seguimientos_activos s
                    WHERE s.id_usuario_profesional = :id_usuario";
            
            if ($id_veterinaria) {
                // La vista `vista_seguimientos_activos` no expone siempre `id_veterinaria`.
                // Usar subconsulta EXISTS para filtrar por veterinaria en la tabla principal.
                $sql .= " AND EXISTS (SELECT 1 FROM seguimientos_paciente sp WHERE sp.id_seguimiento = s.id_seguimiento AND sp.id_veterinaria = :id_veterinaria)";
            }
            
            $sql .= " ORDER BY 
                        FIELD(s.prioridad_calculada, 'critica', 'alta', 'normal', 'baja'),
                        s.requiere_atencion DESC,
                        s.dias_sin_cita DESC,
                        s.proxima_revision ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            if ($id_veterinaria) {
                $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enriquecer con datos adicionales
            foreach ($seguimientos as &$seg) {
                // Formatear imagen
                if (!empty($seg['img_mascota'])) {
                    $seg['img_mascota'] = BASE_URL . '/public/uploads/mascotas/' . $seg['img_mascota'];
                } else {
                    $seg['img_mascota'] = BASE_URL . '/public/assets/global/img/default-pet.png';
                }
                
                // Estado visual para frontend
                $seg['estado_seguimiento'] = $this->determinarEstadoVisual($seg);
            }

            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerSeguimientosPorProfesional - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un seguimiento específico por ID
     */
    public function obtenerSeguimientoPorId($id_seguimiento, $id_usuario)
    {
        try {
            $sql = "SELECT 
                        s.*,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        p.img_mascota,
                        p.id_veterinaria,
                        prop.id_propietario,
                        prop.nombres AS propietario_nombres,
                        prop.apellidos AS propietario_apellidos,
                        prop.telefono AS propietario_telefono,
                        prop.email AS propietario_email
                    FROM seguimientos_paciente s
                    INNER JOIN paciente p ON s.id_paciente = p.id_paciente
                    INNER JOIN propietario prop ON p.id_propietario = prop.id_propietario
                    WHERE s.id_seguimiento = :id_seguimiento
                    AND s.id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerSeguimientoPorId - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo seguimiento desde una asignación
     */
    public function crearSeguimiento($datos, $id_usuario_creador)
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "INSERT INTO seguimientos_paciente (
                        id_paciente,
                        id_usuario_profesional,
                        id_asignacion,
                        id_veterinaria,
                        tipo_seguimiento,
                        motivo,
                        diagnostico_principal,
                        objetivo_tratamiento,
                        prioridad,
                        created_by
                    ) VALUES (
                        :id_paciente,
                        :id_usuario_profesional,
                        :id_asignacion,
                        :id_veterinaria,
                        :tipo_seguimiento,
                        :motivo,
                        :diagnostico_principal,
                        :objetivo_tratamiento,
                        :prioridad,
                        :created_by
                    )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':id_paciente' => $datos['id_paciente'],
                ':id_usuario_profesional' => $datos['id_usuario_profesional'],
                ':id_asignacion' => $datos['id_asignacion'],
                ':id_veterinaria' => $datos['id_veterinaria'],
                ':tipo_seguimiento' => $datos['tipo_seguimiento'] ?? 'tratamiento-cronico',
                ':motivo' => $datos['motivo'],
                ':diagnostico_principal' => $datos['diagnostico_principal'] ?? null,
                ':objetivo_tratamiento' => $datos['objetivo_tratamiento'] ?? null,
                ':prioridad' => $datos['prioridad'] ?? 'normal',
                ':created_by' => $id_usuario_creador
            ]);

            $id_seguimiento = $this->conexion->lastInsertId();

            // Registrar actividad de creación
            $this->registrarActividad([
                'id_seguimiento' => $id_seguimiento,
                'tipo_actividad' => 'cambio_estado',
                'titulo' => 'Seguimiento creado',
                'descripcion' => 'Se inició el seguimiento del paciente',
                'categoria' => 'sistema',
                'estado' => 'completada',
                'importancia' => 'alta',
                'registrado_por' => $id_usuario_creador
            ]);

            $this->conexion->commit();
            return $id_seguimiento;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Seguimientos::crearSeguimiento - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza las observaciones de un seguimiento
     */
    public function actualizarObservaciones($id_seguimiento, $observacion, $id_usuario)
    {
        try {
            $sql = "UPDATE seguimientos_paciente 
                    SET observaciones_generales = :observacion,
                        updated_by = :id_usuario,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id_seguimiento = :id_seguimiento
                    AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':observacion', $observacion, PDO::PARAM_STR);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            $result = $stmt->execute();

            if ($result) {
                // Registrar actividad
                $this->registrarActividad([
                    'id_seguimiento' => $id_seguimiento,
                    'tipo_actividad' => 'observacion_registrada',
                    'titulo' => 'Observación actualizada',
                    'descripcion' => $observacion,
                    'categoria' => 'clinico',
                    'estado' => 'completada',
                    'registrado_por' => $id_usuario
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::actualizarObservaciones - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finaliza un seguimiento
     */
    public function finalizarSeguimiento($id_seguimiento, $id_usuario)
    {
        try {
            $sql = "UPDATE seguimientos_paciente 
                    SET estado = 'completado',
                        fecha_fin_real = CURRENT_TIMESTAMP,
                        progreso_porcentaje = 100,
                        updated_by = :id_usuario,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id_seguimiento = :id_seguimiento
                    AND id_usuario_profesional = :id_usuario
                    AND estado IN ('activo', 'pausado')";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            $result = $stmt->execute();

            if ($result) {
                // Registrar actividad
                $this->registrarActividad([
                    'id_seguimiento' => $id_seguimiento,
                    'tipo_actividad' => 'cambio_estado',
                    'titulo' => 'Seguimiento finalizado',
                    'descripcion' => 'El seguimiento ha sido completado exitosamente',
                    'categoria' => 'administrativo',
                    'estado' => 'completada',
                    'importancia' => 'alta',
                    'registrado_por' => $id_usuario
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::finalizarSeguimiento - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra una nueva actividad en el seguimiento
     */
    public function registrarActividad($datos)
    {
        try {
            $sql = "INSERT INTO seguimiento_actividades (
                        id_seguimiento,
                        tipo_actividad,
                        categoria,
                        titulo,
                        descripcion,
                        resultado,
                        estado,
                        importancia,
                        fecha_realizada,
                        id_cita,
                        id_tratamiento,
                        registrado_por
                    ) VALUES (
                        :id_seguimiento,
                        :tipo_actividad,
                        :categoria,
                        :titulo,
                        :descripcion,
                        :resultado,
                        :estado,
                        :importancia,
                        CURRENT_TIMESTAMP,
                        :id_cita,
                        :id_tratamiento,
                        :registrado_por
                    )";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':id_seguimiento' => $datos['id_seguimiento'],
                ':tipo_actividad' => $datos['tipo_actividad'],
                ':categoria' => $datos['categoria'] ?? 'clinico',
                ':titulo' => $datos['titulo'],
                ':descripcion' => $datos['descripcion'] ?? null,
                ':resultado' => $datos['resultado'] ?? null,
                ':estado' => $datos['estado'] ?? 'completada',
                ':importancia' => $datos['importancia'] ?? 'media',
                ':id_cita' => $datos['id_cita'] ?? null,
                ':id_tratamiento' => $datos['id_tratamiento'] ?? null,
                ':registrado_por' => $datos['registrado_por']
            ]);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::registrarActividad - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el timeline de actividades de un seguimiento
     */
    public function obtenerActividades($id_seguimiento, $limite = 50)
    {
        try {
            $sql = "SELECT 
                        a.*,
                        u.nombres AS registrado_nombre,
                        u.apellidos AS registrado_apellido
                    FROM seguimiento_actividades a
                    LEFT JOIN usuario u ON a.registrado_por = u.id_usuario
                    WHERE a.id_seguimiento = :id_seguimiento
                    ORDER BY a.created_at DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerActividades - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene medicaciones activas de un seguimiento
     */
    public function obtenerMedicaciones($id_seguimiento)
    {
        try {
            $sql = "SELECT 
                        m.*,
                        u.nombres AS prescrito_nombre,
                        u.apellidos AS prescrito_apellido
                    FROM seguimiento_medicaciones m
                    LEFT JOIN usuario u ON m.prescrito_por = u.id_usuario
                    WHERE m.id_seguimiento = :id_seguimiento
                    AND m.estado IN ('activo', 'completado')
                    ORDER BY m.estado ASC, m.proxima_dosis ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerMedicaciones - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene alertas pendientes de un seguimiento
     */
    public function obtenerAlertas($id_seguimiento)
    {
        try {
            $sql = "SELECT * 
                    FROM seguimiento_alertas
                    WHERE id_seguimiento = :id_seguimiento
                    AND estado IN ('pendiente', 'enviada')
                    ORDER BY prioridad DESC, fecha_programada ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_seguimiento', $id_seguimiento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerAlertas - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de seguimientos para un veterinario
     */
    public function obtenerEstadisticas($id_usuario, $id_veterinaria = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_activos,
                        SUM(CASE WHEN prioridad_calculada = 'critica' THEN 1 ELSE 0 END) as criticos,
                        SUM(CASE WHEN requiere_atencion = TRUE THEN 1 ELSE 0 END) as requieren_atencion,
                        SUM(CASE WHEN proxima_revision IS NOT NULL 
                                  AND DATE(proxima_revision) = CURDATE() 
                            THEN 1 ELSE 0 END) as revisiones_hoy,
                        SUM(CASE WHEN dias_sin_cita > 7 THEN 1 ELSE 0 END) as sin_atencion_7dias
                    FROM seguimientos_paciente
                    WHERE id_usuario_profesional = :id_usuario
                    AND estado IN ('activo', 'pausado')";
            
            if ($id_veterinaria) {
                $sql .= " AND id_veterinaria = :id_veterinaria";
            }

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            if ($id_veterinaria) {
                $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }
            
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::obtenerEstadisticas - " . $e->getMessage());
            return [
                'total_activos' => 0,
                'criticos' => 0,
                'requieren_atencion' => 0,
                'revisiones_hoy' => 0,
                'sin_atencion_7dias' => 0
            ];
        }
    }

    /**
     * Determina el estado visual de un seguimiento para el frontend
     */
    private function determinarEstadoVisual($seguimiento)
    {
        if ($seguimiento['alerta_critica'] || $seguimiento['prioridad_calculada'] === 'critica') {
            return 'critico';
        }

        if ($seguimiento['requiere_atencion']) {
            return 'requiere-atencion';
        }

        if ($seguimiento['proxima_cita']) {
            $dias_hasta_cita = (strtotime($seguimiento['proxima_cita']) - time()) / (60 * 60 * 24);
            if ($dias_hasta_cita <= 1) {
                return 'cita-proxima';
            }
            return 'programado';
        }

        if ($seguimiento['estado'] === 'activo') {
            return 'activo';
        }

        return 'normal';
    }
}
