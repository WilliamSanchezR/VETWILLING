<?php

/**
 * ═══════════════════════════════════════════════════════════════════
 *  MODELO DE SEGUIMIENTOS DE PACIENTES
 *  Descripción: Maneja todas las operaciones de BD para seguimientos
 *  Versión: 2.0 - Usa las nuevas tablas de seguimientos
 * ═══════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Notificacion.php';

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
            // Consulta directa sobre seguimientos_paciente para incluir TODOS los estados
            // (la vista solo devuelve activos/pausados, lo que rompe el filtro "Completados")
            $sql = "SELECT
                        sp.id_seguimiento,
                        sp.id_paciente,
                        sp.id_usuario_profesional,
                        sp.id_asignacion,
                        sp.id_veterinaria,
                        sp.tipo_seguimiento,
                        sp.motivo,
                        sp.diagnostico_principal,
                        sp.objetivo_tratamiento,
                        sp.tratamiento_actual,
                        sp.observaciones_generales,
                        sp.prioridad,
                        sp.prioridad          AS prioridad_calculada,
                        sp.estado,
                        sp.progreso_porcentaje,
                        sp.proxima_revision,
                        sp.alerta_critica,
                        p.nombre              AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        p.img_mascota,
                        prop.id_propietario,
                        prop.nombres          AS propietario_nombres,
                        prop.apellidos        AS propietario_apellidos,
                        prop.telefono         AS propietario_telefono,
                        (SELECT COUNT(*)
                         FROM agendamiento a
                         WHERE a.id_paciente = sp.id_paciente
                           AND a.id_usuario  = sp.id_usuario_profesional) AS total_citas_realizadas,
                        (SELECT fecha_hora
                         FROM agendamiento
                         WHERE id_paciente = sp.id_paciente
                           AND id_usuario  = sp.id_usuario_profesional
                           AND fecha_hora  < NOW()
                         ORDER BY fecha_hora DESC LIMIT 1) AS ultima_cita,
                        (SELECT fecha_hora
                         FROM agendamiento
                         WHERE id_paciente = sp.id_paciente
                           AND id_usuario  = sp.id_usuario_profesional
                           AND fecha_hora  > NOW()
                           AND estado     != 'Cancelada'
                         ORDER BY fecha_hora ASC LIMIT 1)  AS proxima_cita,
                        (SELECT observaciones
                         FROM agendamiento
                         WHERE id_paciente    = sp.id_paciente
                           AND id_usuario     = sp.id_usuario_profesional
                           AND observaciones IS NOT NULL
                           AND observaciones != ''
                         ORDER BY fecha_hora DESC LIMIT 1)  AS ultimo_diagnostico,
                        CASE WHEN sp.proxima_revision IS NOT NULL
                              AND sp.proxima_revision <= NOW() THEN 1 ELSE 0
                        END AS requiere_atencion,
                        DATEDIFF(CURDATE(),
                            IFNULL((SELECT MAX(fecha_hora)
                                    FROM agendamiento
                                    WHERE id_paciente = sp.id_paciente
                                      AND id_usuario  = sp.id_usuario_profesional), CURDATE())
                        ) AS dias_sin_cita
                    FROM seguimientos_paciente sp
                    JOIN paciente   p    ON sp.id_paciente    = p.id_paciente
                    JOIN propietario prop ON p.id_propietario = prop.id_propietario
                    WHERE sp.id_usuario_profesional = :id_usuario";

            if ($id_veterinaria) {
                $sql .= " AND sp.id_veterinaria = :id_veterinaria";
            }

            $sql .= " ORDER BY
                        FIELD(sp.prioridad, 'critica', 'alta', 'normal', 'baja'),
                        requiere_atencion DESC,
                        dias_sin_cita DESC,
                        sp.proxima_revision ASC";

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
                        prop.id_usuario AS propietario_usuario_id,
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

    public function registrarActualizacionClinica(int $id_seguimiento, array $datos, int $id_usuario): array
    {
        $estadoSalud = $this->normalizarEstadoSalud($datos['estado_salud'] ?? '');
        $observacion = trim((string) ($datos['observacion'] ?? ''));
        $diagnostico = trim((string) ($datos['diagnostico'] ?? ''));
        $tratamiento = trim((string) ($datos['tratamiento'] ?? ''));
        $dosisTratamiento = trim((string) ($datos['dosis_tratamiento'] ?? ''));
        $fechaFinTratamiento = trim((string) ($datos['fecha_fin_tratamiento'] ?? ''));

        if ($estadoSalud === null) {
            throw new InvalidArgumentException('Estado de salud inválido.');
        }

        if ($observacion === '') {
            throw new InvalidArgumentException('La observación clínica es obligatoria.');
        }

        $seguimiento = $this->obtenerSeguimientoPorId($id_seguimiento, $id_usuario);
        if (!$seguimiento) {
            throw new RuntimeException('Seguimiento no encontrado o sin permisos para actualizarlo.');
        }

        $descripcion = $this->construirResumenActualizacion($estadoSalud, $observacion, $diagnostico, $tratamiento, $dosisTratamiento);
        $progreso = $this->calcularProgresoPorEstado($estadoSalud, $seguimiento['progreso_porcentaje'] ?? null);

        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE seguimientos_paciente
                    SET observaciones_generales = :observacion,
                        diagnostico_principal = :diagnostico,
                        objetivo_tratamiento = :tratamiento,
                        progreso_porcentaje = :progreso,
                        updated_by = :id_usuario,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id_seguimiento = :id_seguimiento
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':observacion' => $observacion,
                ':diagnostico' => $diagnostico !== '' ? $diagnostico : ($seguimiento['diagnostico_principal'] ?? null),
                ':tratamiento' => $tratamiento !== '' ? $tratamiento : ($seguimiento['objetivo_tratamiento'] ?? null),
                ':progreso' => $progreso,
                ':id_usuario' => $id_usuario,
                ':id_seguimiento' => $id_seguimiento,
            ]);

            $this->registrarActividad([
                'id_seguimiento' => $id_seguimiento,
                'tipo_actividad' => 'actualizacion_estado',
                'titulo' => 'Actualización del estado del paciente',
                'descripcion' => $descripcion,
                'resultado' => 'Estado reportado: ' . ucfirst($estadoSalud),
                'categoria' => 'clinico',
                'estado' => 'completada',
                'importancia' => $estadoSalud === 'empeoramiento' ? 'alta' : 'media',
                'registrado_por' => $id_usuario,
            ]);

            $this->asegurarTablaHistorialClinico();
            $this->insertarConsultaClinica((int) $seguimiento['id_paciente'], $id_usuario, $estadoSalud, $diagnostico, $descripcion);
            $this->insertarNotaClinica((int) $seguimiento['id_paciente'], $id_usuario, $descripcion);
            $historialId = $this->insertarHistorialClinico((int) $seguimiento['id_paciente'], $id_usuario, $estadoSalud, $diagnostico, $tratamiento, $dosisTratamiento, $descripcion);

            $tratamientoId = null;
            if ($tratamiento !== '') {
                $tratamientoId = $this->insertarTratamientoClinico((int) $seguimiento['id_paciente'], $id_usuario, $tratamiento, $dosisTratamiento, $observacion, $fechaFinTratamiento);
            }

            $notificacionesCreadas = $this->crearNotificacionesSeguimiento(
                $seguimiento,
                'Actualización clínica registrada',
                sprintf('Se actualizó el estado de %s a "%s". %s', $seguimiento['paciente_nombre'], $estadoSalud, $observacion),
                $id_usuario
            );

            $this->conexion->commit();

            return [
                'id_seguimiento' => $id_seguimiento,
                'estado_salud' => $estadoSalud,
                'historial_registrado' => $historialId !== null,
                'tratamiento_registrado' => $tratamientoId !== null,
                'notificaciones_creadas' => $notificacionesCreadas,
            ];
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log('Error en Seguimientos::registrarActualizacionClinica - ' . $e->getMessage());
            throw new RuntimeException('No se pudo registrar la actualización clínica.');
        }
    }

    public function enviarNotificacionSeguimiento(int $id_seguimiento, int $id_usuario, string $mensaje): int
    {
        $seguimiento = $this->obtenerSeguimientoPorId($id_seguimiento, $id_usuario);
        if (!$seguimiento) {
            throw new RuntimeException('Seguimiento no encontrado.');
        }

        return $this->crearNotificacionesSeguimiento(
            $seguimiento,
            'Recordatorio de seguimiento',
            trim($mensaje) !== '' ? trim($mensaje) : 'Se ha generado un recordatorio clínico para el seguimiento del paciente.',
            $id_usuario
        );
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
                        estado,
                        fecha_inicio,
                        proxima_revision,
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
                        :estado,
                        :fecha_inicio,
                        :proxima_revision,
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
                ':estado' => $datos['estado'] ?? 'activo',
                ':fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                ':proxima_revision' => $datos['proxima_revision'] ?? null,
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
     * Agrega una medicación a un seguimiento
     */
    public function agregarMedicacion($id_seguimiento, $medicamento, $dosis, $id_usuario_prescriptor = null)
    {
        try {
            $sql = "INSERT INTO seguimiento_medicaciones (
                        id_seguimiento,
                        medicamento,
                        dosis,
                        estado,
                        fecha_inicio,
                        prescrito_por
                    ) VALUES (
                        :id_seguimiento,
                        :medicamento,
                        :dosis,
                        'activo',
                        CURRENT_DATE,
                        :prescrito_por
                    )";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':id_seguimiento' => $id_seguimiento,
                ':medicamento' => $medicamento,
                ':dosis' => $dosis,
                ':prescrito_por' => $id_usuario_prescriptor
            ]);

            return $resultado ? $this->conexion->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error en Seguimientos::agregarMedicacion - " . $e->getMessage());
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

        if ($seguimiento['estado'] === 'completado') {
            return 'completado';
        }

        if ($seguimiento['estado'] === 'activo') {
            return 'activo';
        }

        return 'normal';
    }

    private function normalizarEstadoSalud(string $estado): ?string
    {
        $estado = strtolower(trim($estado));
        $mapa = [
            'mejoria' => 'mejoria',
            'mejoría' => 'mejoria',
            'estable' => 'estable',
            'empeoramiento' => 'empeoramiento',
        ];

        return $mapa[$estado] ?? null;
    }

    private function calcularProgresoPorEstado(string $estadoSalud, $progresoActual): int
    {
        $progresoActual = (int) ($progresoActual ?? 0);
        $mapa = [
            'mejoria' => max($progresoActual, 75),
            'estable' => max($progresoActual, 50),
            'empeoramiento' => min($progresoActual > 0 ? $progresoActual : 25, 25),
        ];

        return $mapa[$estadoSalud] ?? max($progresoActual, 25);
    }

    private function construirResumenActualizacion(string $estadoSalud, string $observacion, string $diagnostico, string $tratamiento, string $dosisTratamiento): string
    {
        $partes = [
            'Estado de salud: ' . ucfirst($estadoSalud),
            'Observación: ' . $observacion,
        ];

        if ($diagnostico !== '') {
            $partes[] = 'Diagnóstico: ' . $diagnostico;
        }

        if ($tratamiento !== '') {
            $resumenTratamiento = 'Tratamiento/medicación: ' . $tratamiento;
            if ($dosisTratamiento !== '') {
                $resumenTratamiento .= ' (' . $dosisTratamiento . ')';
            }
            $partes[] = $resumenTratamiento;
        }

        return implode(' | ', $partes);
    }

    private function asegurarTablaHistorialClinico(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS historial_clinico_paciente (
                    id_historial INT AUTO_INCREMENT PRIMARY KEY,
                    id_historial_base INT NULL,
                    id_paciente INT NOT NULL,
                    id_usuario_profesional INT NOT NULL,
                    fecha_atencion DATETIME NOT NULL,
                    motivo_consulta VARCHAR(255) NOT NULL,
                    diagnostico TEXT NULL,
                    tratamientos_aplicados TEXT NULL,
                    medicacion_recetada TEXT NULL,
                    observaciones_adicionales TEXT NULL,
                    version_registro INT NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_hcp_base (id_historial_base),
                    INDEX idx_hcp_paciente (id_paciente),
                    INDEX idx_hcp_profesional (id_usuario_profesional),
                    INDEX idx_hcp_fecha (fecha_atencion),
                    CONSTRAINT fk_hcp_paciente FOREIGN KEY (id_paciente) REFERENCES paciente (id_paciente) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    CONSTRAINT fk_hcp_usuario FOREIGN KEY (id_usuario_profesional) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT,
                    CONSTRAINT fk_hcp_historial_base FOREIGN KEY (id_historial_base) REFERENCES historial_clinico_paciente (id_historial) ON DELETE RESTRICT ON UPDATE RESTRICT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conexion->exec($sql);
    }

    private function insertarConsultaClinica(int $id_paciente, int $id_usuario, string $estadoSalud, string $diagnostico, string $descripcion): void
    {
        $sql = "INSERT INTO paciente_consulta_clinica (
                    id_paciente,
                    id_usuario_profesional,
                    fecha_consulta,
                    motivo,
                    diagnostico,
                    observaciones
                ) VALUES (
                    :id_paciente,
                    :id_usuario,
                    CURRENT_TIMESTAMP,
                    :motivo,
                    :diagnostico,
                    :observaciones
                )";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $id_paciente,
            ':id_usuario' => $id_usuario,
            ':motivo' => 'Actualización de seguimiento clínico',
            ':diagnostico' => $diagnostico !== '' ? $diagnostico : 'Estado clínico reportado: ' . ucfirst($estadoSalud),
            ':observaciones' => $descripcion,
        ]);
    }

    private function insertarNotaClinica(int $id_paciente, int $id_usuario, string $descripcion): void
    {
        $sql = "INSERT INTO paciente_nota_clinica (id_paciente, id_usuario_profesional, nota)
                VALUES (:id_paciente, :id_usuario, :nota)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $id_paciente,
            ':id_usuario' => $id_usuario,
            ':nota' => $descripcion,
        ]);
    }

    private function insertarHistorialClinico(int $id_paciente, int $id_usuario, string $estadoSalud, string $diagnostico, string $tratamiento, string $dosisTratamiento, string $descripcion): ?int
    {
        $sql = "INSERT INTO historial_clinico_paciente (
                    id_paciente,
                    id_usuario_profesional,
                    fecha_atencion,
                    motivo_consulta,
                    diagnostico,
                    tratamientos_aplicados,
                    medicacion_recetada,
                    observaciones_adicionales
                ) VALUES (
                    :id_paciente,
                    :id_usuario,
                    CURRENT_TIMESTAMP,
                    :motivo_consulta,
                    :diagnostico,
                    :tratamientos_aplicados,
                    :medicacion_recetada,
                    :observaciones_adicionales
                )";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $id_paciente,
            ':id_usuario' => $id_usuario,
            ':motivo_consulta' => 'Actualización de estado del seguimiento',
            ':diagnostico' => $diagnostico !== '' ? $diagnostico : 'Estado clínico: ' . ucfirst($estadoSalud),
            ':tratamientos_aplicados' => $tratamiento !== '' ? $tratamiento : null,
            ':medicacion_recetada' => $tratamiento !== '' ? trim($tratamiento . ' ' . ($dosisTratamiento !== '' ? '(' . $dosisTratamiento . ')' : '')) : null,
            ':observaciones_adicionales' => $descripcion,
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    private function insertarTratamientoClinico(int $id_paciente, int $id_usuario, string $tratamiento, string $dosisTratamiento, string $observacion, string $fechaFinTratamiento): ?int
    {
        $sql = "INSERT INTO paciente_tratamiento (
                    id_paciente,
                    id_usuario_profesional,
                    medicamento,
                    dosis,
                    fecha_inicio,
                    fecha_fin,
                    estado,
                    observaciones
                ) VALUES (
                    :id_paciente,
                    :id_usuario,
                    :medicamento,
                    :dosis,
                    CURDATE(),
                    :fecha_fin,
                    'Activo',
                    :observaciones
                )";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':medicamento', $tratamiento, PDO::PARAM_STR);
        $stmt->bindValue(':dosis', $dosisTratamiento !== '' ? $dosisTratamiento : 'Según indicación veterinaria', PDO::PARAM_STR);
        if ($fechaFinTratamiento !== '') {
            $stmt->bindValue(':fecha_fin', $fechaFinTratamiento, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':fecha_fin', null, PDO::PARAM_NULL);
        }
        $stmt->bindValue(':observaciones', $observacion, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->conexion->lastInsertId();
    }

    private function crearNotificacionesSeguimiento(array $seguimiento, string $titulo, string $mensaje, int $id_usuario): int
    {
        require_once BASE_PATH . '/app/helpers/notification/notificacion_helper.php';

        $creadas    = 0;
        $idPaciente = (int) $seguimiento['id_paciente'];

        if (!empty($seguimiento['propietario_usuario_id'])) {
            if (!notificar('tratamiento', $titulo, $mensaje, (int) $seguimiento['propietario_usuario_id'], $idPaciente)) {
                throw new RuntimeException('No se pudo notificar al propietario.');
            }
            $creadas++;
        }

        if (!notificar('tratamiento', $titulo, $mensaje, $id_usuario, $idPaciente)) {
            throw new RuntimeException('No se pudo registrar la notificación del profesional.');
        }

        return $creadas + 1;
    }
}
