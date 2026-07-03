<?php

// Importamos las dependencias

require_once __DIR__ . '/../../config/database.php';

class Veterinario
{

    private $conexion;

    public function __construct()
    {

        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function contarPacientesPorVeterinario($id_usuario)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM paciente_profesional_asignacion ppa
                    WHERE ppa.id_usuario_profesional = :id_usuario
                    AND ppa.estado = 'Activo'
                    AND ppa.fecha_fin IS NULL";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarPacientesPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                    AND DATE(a.fecha_hora) = :fecha";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarPacientesHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT a.id_paciente)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                    AND DATE(a.fecha_hora) = :fecha";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarPacientesHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasPendientesHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                    AND DATE(a.fecha_hora) = :fecha
                    AND UPPER(a.estado) = 'PENDIENTE'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasPendientesHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasSemanaPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                    AND YEARWEEK(a.fecha_hora, 1) = YEARWEEK(:fecha, 1)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasSemanaPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerCitasHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            // Debug
            error_log("=== QUERY CITAS HOY ===");
            error_log("Buscando citas para id_usuario: " . $id_usuario . " en fecha: " . $fecha);

            // Primero consultamos TODAS las citas del usuario para ver qué fechas hay
            $sqlDebug = "SELECT id_agendamiento, fecha_hora, DATE(fecha_hora) as fecha_solo, id_usuario 
                        FROM agendamiento 
                        WHERE id_usuario = :id_usuario 
                        ORDER BY fecha_hora DESC LIMIT 10";
            $stmtDebug = $this->conexion->prepare($sqlDebug);
            $stmtDebug->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtDebug->execute();
            $todasCitas = $stmtDebug->fetchAll(PDO::FETCH_ASSOC);
            error_log("TODAS las citas del usuario (últimas 10): " . print_r($todasCitas, true));

            $sql = "SELECT 
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.tipo,
                        a.estado,
                        a.observaciones,
                        p.id_paciente,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        p.img_mascota,
                        prop.id_propietario,
                        prop.nombres AS propietario_nombres,
                        prop.apellidos AS propietario_apellidos,
                        prop.telefono AS propietario_telefono,
                                                u.email AS propietario_email
                    FROM agendamiento a
                    LEFT JOIN paciente p ON a.id_paciente = p.id_paciente
                    LEFT JOIN propietario prop ON p.id_propietario = prop.id_propietario
                                        LEFT JOIN usuario u ON prop.id_usuario = u.id_usuario
                    WHERE a.id_usuario = :id_usuario
                    AND DATE(a.fecha_hora) = :fecha
                    ORDER BY a.fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Registros encontrados para fecha " . $fecha . ": " . count($resultado));
            error_log("========================");

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerCitasHoyPorVeterinario - " . $e->getMessage());
            return [];
        }
    }

    public function listarCitasPendientesParaAtencion($id_usuario): array
    {
        try {
            $sql = "SELECT
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.tipo,
                        a.estado,
                        a.observaciones AS observaciones_cita,
                        p.id_paciente,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        TRIM(CONCAT(COALESCE(pr.nombres,''), ' ', COALESCE(pr.apellidos,''))) AS propietario_nombre
                    FROM agendamiento a
                    INNER JOIN paciente p ON p.id_paciente = a.id_paciente
                    LEFT JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    WHERE a.id_usuario = :id_usuario
                    AND UPPER(a.estado) IN ('PENDIENTE', 'CONFIRMADA')
                    AND DATE(a.fecha_hora) >= CURDATE()
                    ORDER BY a.fecha_hora ASC
                    LIMIT 50";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarCitasPendientesParaAtencion - " . $e->getMessage());
            return [];
        }
    }

    public function listarCitasVistaVeterinario(int $id_usuario): array
    {
        try {
            $sql = "SELECT
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.tipo,
                        a.estado,
                        a.observaciones,
                        p.id_paciente,
                        p.nombre  AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.img_mascota,
                        TRIM(CONCAT(COALESCE(prop.nombres,''), ' ', COALESCE(prop.apellidos,'')))
                            AS propietario_nombre,
                        prop.telefono AS propietario_telefono
                    FROM agendamiento a
                    INNER JOIN paciente p    ON p.id_paciente   = a.id_paciente
                    LEFT  JOIN propietario prop ON prop.id_propietario = p.id_propietario
                    WHERE a.id_usuario = :id_usuario
                      AND (
                          (UPPER(a.estado) IN ('PENDIENTE','CONFIRMADA') AND DATE(a.fecha_hora) >= CURDATE())
                          OR
                          (UPPER(a.estado) = 'COMPLETADA' AND DATE(a.fecha_hora) >= DATE_SUB(CURDATE(), INTERVAL 60 DAY))
                      )
                    ORDER BY a.fecha_hora ASC
                    LIMIT 150";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarCitasVistaVeterinario - " . $e->getMessage());
            return [];
        }
    }

    public function contarCitasPorEstadoVeterinario(int $id_usuario): array
    {
        try {
            $sql = "SELECT UPPER(estado) AS estado, COUNT(*) AS total
                    FROM agendamiento
                    WHERE id_usuario = :id_usuario
                    GROUP BY UPPER(estado)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $counts = ['PENDIENTE' => 0, 'CONFIRMADA' => 0, 'COMPLETADA' => 0, 'CANCELADA' => 0];
            foreach ($rows as $r) {
                $key = $r['estado'];
                if (array_key_exists($key, $counts)) {
                    $counts[$key] = (int) $r['total'];
                }
            }
            return $counts;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasPorEstadoVeterinario - " . $e->getMessage());
            return ['PENDIENTE' => 0, 'CONFIRMADA' => 0, 'COMPLETADA' => 0, 'CANCELADA' => 0];
        }
    }

    public function actualizarEstadoCitaVeterinario(int $id_agendamiento, string $nuevo_estado, int $id_usuario, ?string $motivo_cancelacion = null): bool
    {
        try {
            $sql = "UPDATE agendamiento
                    SET estado = :estado" . ($motivo_cancelacion !== null ? ",
                        observaciones = TRIM(CONCAT(COALESCE(observaciones, ''), ' | Motivo de cancelación: ', :motivo))" : "") . "
                    WHERE id_agendamiento = :id_agendamiento
                      AND id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado',          $nuevo_estado,   PDO::PARAM_STR);
            $stmt->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',      $id_usuario,     PDO::PARAM_INT);
            if ($motivo_cancelacion !== null) {
                $stmt->bindParam(':motivo', $motivo_cancelacion, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarEstadoCitaVeterinario - " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPacientesPorVeterinario($id_usuario)
    {
        try {
            $sql = "SELECT 
                                                p.id_paciente,
                                                p.nombre AS paciente_nombre,
                                                p.especie,
                                                p.raza,
                                                p.edad_numero,
                                                p.edad_unidad,
                                                p.sexo,
                                                CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario_nombre,
                                                (
                                                        SELECT MAX(a.fecha_hora)
                                                        FROM agendamiento a
                                                        WHERE a.id_usuario = :id_usuario
                                                            AND a.id_paciente = p.id_paciente
                                                ) AS ultima_visita,
                                                (
                                                        SELECT a2.estado
                                                        FROM agendamiento a2
                                                        WHERE a2.id_usuario = :id_usuario
                                                            AND a2.id_paciente = p.id_paciente
                                                        ORDER BY a2.fecha_hora DESC, a2.id_agendamiento DESC
                                                        LIMIT 1
                                                ) AS estado_ultima_cita
                                        FROM paciente_profesional_asignacion ppa
                                        INNER JOIN paciente p ON ppa.id_paciente = p.id_paciente
                                        INNER JOIN propietario pr ON p.id_propietario = pr.id_propietario
                                        WHERE ppa.id_usuario_profesional = :id_usuario
                                            AND ppa.estado = 'Activo'
                                            AND ppa.fecha_fin IS NULL
                                        ORDER BY ultima_visita DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerPacientesPorVeterinario - " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetallePacientePorVeterinario($id_usuario, $id_paciente)
    {
        try {
            $sql = "SELECT
                        p.id_paciente,
                        p.nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        p.img_mascota,
                        p.id_propietario,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario_nombre,
                        pr.telefono AS propietario_telefono,
                        u.email AS propietario_email
                    FROM paciente p
                    INNER JOIN paciente_profesional_asignacion ppa ON ppa.id_paciente = p.id_paciente
                    INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    LEFT JOIN usuario u ON u.id_usuario = pr.id_usuario
                    WHERE p.id_paciente = :id_paciente
                    AND ppa.id_usuario_profesional = :id_usuario
                    AND ppa.estado = 'Activo'
                    AND ppa.fecha_fin IS NULL
                    ORDER BY ppa.id_asignacion DESC
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
            return $paciente ?: null;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerDetallePacientePorVeterinario - " . $e->getMessage());
            return null;
        }
    }

    public function obtenerHistorialPacientePorVeterinario($id_usuario, $id_paciente, $limite = 10)
    {
        try {
            $limite = max(1, (int) $limite);

            $sql = "SELECT
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.tipo,
                        a.estado,
                        a.observaciones,
                        s.nombre AS servicio,
                        ss.nombre AS subservicio
                    FROM agendamiento a
                    LEFT JOIN servicio s ON s.id_servicio = a.id_servicio
                    LEFT JOIN subservicio ss ON ss.id_subservicio = a.id_subservicio
                    WHERE a.id_usuario = :id_usuario
                    AND a.id_paciente = :id_paciente
                    ORDER BY a.fecha_hora DESC, a.id_agendamiento DESC
                    LIMIT $limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerHistorialPacientePorVeterinario - " . $e->getMessage());
            return [];
        }
    }

    public function actualizarDatosPacientePorVeterinario($id_usuario, array $data)
    {
        try {
            $sqlValida = "SELECT COUNT(*)
                        FROM paciente_profesional_asignacion
                        WHERE id_paciente = :id_paciente
                            AND id_usuario_profesional = :id_usuario
                            AND estado = 'Activo'
                            AND fecha_fin IS NULL";

            $stmtValida = $this->conexion->prepare($sqlValida);
            $stmtValida->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmtValida->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtValida->execute();

            if ((int) $stmtValida->fetchColumn() === 0) {
                return false;
            }

            $sql = "UPDATE paciente
                    SET nombre = :nombre,
                        especie = :especie,
                        raza = :raza,
                        edad_numero = :edad_numero,
                        edad_unidad = :edad_unidad,
                        sexo = :sexo
                    WHERE id_paciente = :id_paciente";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':especie', $data['especie'], PDO::PARAM_STR);
            $stmt->bindParam(':raza', $data['raza'], PDO::PARAM_STR);
            $stmt->bindParam(':edad_numero', $data['edad_numero'], PDO::PARAM_INT);
            $stmt->bindParam(':edad_unidad', $data['edad_unidad'], PDO::PARAM_STR);
            $stmt->bindParam(':sexo', $data['sexo'], PDO::PARAM_STR);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarDatosPacientePorVeterinario - " . $e->getMessage());
            return false;
        }
    }

    public function desactivarPacientePorVeterinario($id_usuario, $id_paciente)
    {
        try {
            $sql = "UPDATE paciente_profesional_asignacion
                    SET estado = 'Inactivo',
                        fecha_fin = COALESCE(fecha_fin, CURRENT_TIMESTAMP),
                        motivo_cambio = 'Desactivado desde dashboard veterinario'
                    WHERE id_paciente = :id_paciente
                    AND id_usuario_profesional = :id_usuario
                    AND estado = 'Activo'
                    AND fecha_fin IS NULL";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::desactivarPacientePorVeterinario - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarTablaHistorialClinico(): bool
    {
        try {
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
            $this->asegurarColumnasHistorialClinico();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaHistorialClinico - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarColumnasHistorialClinico(): void
    {
        try {
            $sqlExisteColumna = "SELECT COUNT(*)
                                FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = DATABASE()
                                AND TABLE_NAME = 'historial_clinico_paciente'
                                AND COLUMN_NAME = 'id_historial_base'";

            $stmtExiste = $this->conexion->prepare($sqlExisteColumna);
            $stmtExiste->execute();
            $existeColumna = ((int) $stmtExiste->fetchColumn()) > 0;

            if (!$existeColumna) {
                $this->conexion->exec("ALTER TABLE historial_clinico_paciente ADD COLUMN id_historial_base INT NULL AFTER id_historial");
            }

            $sqlExisteIndice = "SELECT COUNT(*)
                            FROM information_schema.STATISTICS
                            WHERE TABLE_SCHEMA = DATABASE()
                                AND TABLE_NAME = 'historial_clinico_paciente'
                                AND INDEX_NAME = 'idx_hcp_base'";

            $stmtIndice = $this->conexion->prepare($sqlExisteIndice);
            $stmtIndice->execute();
            $existeIndice = ((int) $stmtIndice->fetchColumn()) > 0;

            if (!$existeIndice) {
                $this->conexion->exec("ALTER TABLE historial_clinico_paciente ADD INDEX idx_hcp_base (id_historial_base)");
            }
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarColumnasHistorialClinico - " . $e->getMessage());
        }
    }

    private function profesionalTienePacienteActivo($id_usuario, $id_paciente): bool
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM paciente_profesional_asignacion
                    WHERE id_usuario_profesional = :id_usuario
                    AND id_paciente = :id_paciente
                    AND estado = 'Activo'
                    AND fecha_fin IS NULL";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();

            return ((int) $stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::profesionalTienePacienteActivo - " . $e->getMessage());
            return false;
        }
    }

    public function listarHistorialesClinicosPorProfesional($id_usuario, array $filtros = []): array
    {
        try {
            if (!$this->asegurarTablaHistorialClinico()) {
                return [];
            }

            $sql = "SELECT
                        p.id_paciente,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        h.id_historial,
                        h.id_historial_base,
                        h.fecha_atencion,
                        h.motivo_consulta,
                        h.diagnostico,
                        h.tratamientos_aplicados,
                        h.medicacion_recetada,
                        h.observaciones_adicionales,
                        h.version_registro,
                        h.updated_at,
                        COALESCE(
                            NULLIF(TRIM(CONCAT(v.nombres, ' ', v.apellidos)), ''),
                            NULLIF(TRIM(CONCAT(prf.nombres, ' ', prf.apellidos)), ''),
                            u.email,
                            'Profesional'
                        ) AS veterinario_responsable
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    LEFT JOIN historial_clinico_paciente h ON h.id_historial = (
                        SELECT h2.id_historial
                        FROM historial_clinico_paciente h2
                        WHERE h2.id_paciente = p.id_paciente
                        AND h2.id_usuario_profesional = ppa.id_usuario_profesional
                        ORDER BY h2.fecha_atencion DESC, h2.id_historial DESC
                        LIMIT 1
                    )
                    LEFT JOIN usuario u ON u.id_usuario = ppa.id_usuario_profesional
                    LEFT JOIN veterinario v ON v.id_usuario = ppa.id_usuario_profesional
                    LEFT JOIN profesional prf ON prf.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.id_usuario_profesional = :id_usuario
                    AND ppa.estado = 'Activo'
                    AND ppa.fecha_fin IS NULL";

            $params = [':id_usuario' => $id_usuario];

            if (!empty($filtros['paciente'])) {
                $sql .= " AND p.nombre LIKE :paciente";
                $params[':paciente'] = '%' . $filtros['paciente'] . '%';
            }

            if (!empty($filtros['fecha'])) {
                $sql .= " AND DATE(h.fecha_atencion) = :fecha";
                $params[':fecha'] = $filtros['fecha'];
            }

            if (!empty($filtros['veterinario'])) {
                $sql .= " AND COALESCE(
                                NULLIF(TRIM(CONCAT(v.nombres, ' ', v.apellidos)), ''),
                                NULLIF(TRIM(CONCAT(prf.nombres, ' ', prf.apellidos)), ''),
                                u.email,
                                'Profesional'
                            ) = :veterinario";
                $params[':veterinario'] = $filtros['veterinario'];
            }

            $sql .= " ORDER BY h.fecha_atencion DESC, h.id_historial DESC, p.nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $row['acceso'] = 'Autorizado';
            }

            return $rows;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarHistorialesClinicosPorProfesional - " . $e->getMessage());
            return [];
        }
    }

    public function listarVersionesHistorialClinicoPorProfesional($id_usuario, $id_historial): array
    {
        try {
            if (!$this->asegurarTablaHistorialClinico()) {
                return [];
            }

            $sqlBase = "SELECT id_historial, id_historial_base, id_paciente
                        FROM historial_clinico_paciente
                        WHERE id_historial = :id_historial
                        AND id_usuario_profesional = :id_usuario
                        LIMIT 1";

            $stmtBase = $this->conexion->prepare($sqlBase);
            $stmtBase->bindParam(':id_historial', $id_historial, PDO::PARAM_INT);
            $stmtBase->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtBase->execute();

            $base = $stmtBase->fetch(PDO::FETCH_ASSOC);
            if (!$base) {
                return [];
            }

            $idBase = (int) ($base['id_historial_base'] ?? 0);
            if ($idBase <= 0) {
                $idBase = (int) $base['id_historial'];
            }

            $idPaciente = (int) $base['id_paciente'];

            $sql = "SELECT
                        h.id_historial,
                        h.id_historial_base,
                        h.id_paciente,
                        h.fecha_atencion,
                        h.motivo_consulta,
                        h.diagnostico,
                        h.tratamientos_aplicados,
                        h.medicacion_recetada,
                        h.observaciones_adicionales,
                        h.version_registro,
                        h.updated_at,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        COALESCE(
                            NULLIF(TRIM(CONCAT(v.nombres, ' ', v.apellidos)), ''),
                            NULLIF(TRIM(CONCAT(prf.nombres, ' ', prf.apellidos)), ''),
                            u.email,
                            'Profesional'
                        ) AS veterinario_responsable
                    FROM historial_clinico_paciente h
                    INNER JOIN paciente p ON p.id_paciente = h.id_paciente
                    LEFT JOIN usuario u ON u.id_usuario = h.id_usuario_profesional
                    LEFT JOIN veterinario v ON v.id_usuario = h.id_usuario_profesional
                    LEFT JOIN profesional prf ON prf.id_usuario = h.id_usuario_profesional
                    WHERE h.id_usuario_profesional = :id_usuario
                    AND h.id_paciente = :id_paciente
                    AND COALESCE(h.id_historial_base, h.id_historial) = :id_base
                    ORDER BY h.version_registro DESC, h.id_historial DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_base', $idBase, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarVersionesHistorialClinicoPorProfesional - " . $e->getMessage());
            return [];
        }
    }

    public function guardarHistorialClinicoPorProfesional($id_usuario, array $data)
    {
        try {
            if (!$this->asegurarTablaHistorialClinico()) {
                return false;
            }

            $idPaciente = (int) ($data['id_paciente'] ?? 0);
            $idHistorial = (int) ($data['id_historial'] ?? 0);

            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $fechaAtencion = trim((string) ($data['fecha_atencion'] ?? ''));
            if ($fechaAtencion === '') {
                $fechaAtencion = date('Y-m-d H:i:s');
            } elseif (strlen($fechaAtencion) === 10) {
                $fechaAtencion .= ' 00:00:00';
            }

            if ($idHistorial > 0) {
                                $sqlExiste = "SELECT id_historial, id_historial_base, version_registro
                            FROM historial_clinico_paciente
                            WHERE id_historial = :id_historial
                                AND id_usuario_profesional = :id_usuario
                                AND id_paciente = :id_paciente
                            LIMIT 1";

                $stmtExiste = $this->conexion->prepare($sqlExiste);
                $stmtExiste->bindParam(':id_historial', $idHistorial, PDO::PARAM_INT);
                $stmtExiste->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmtExiste->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
                $stmtExiste->execute();

                $actual = $stmtExiste->fetch(PDO::FETCH_ASSOC);
                if (!$actual) {
                    return false;
                }

                $idBase = (int) ($actual['id_historial_base'] ?? 0);
                if ($idBase <= 0) {
                    $idBase = (int) $actual['id_historial'];
                }

                $sqlVersion = "SELECT COALESCE(MAX(version_registro), 0) + 1
                            FROM historial_clinico_paciente
                            WHERE id_paciente = :id_paciente
                                AND id_usuario_profesional = :id_usuario
                                AND COALESCE(id_historial_base, id_historial) = :id_base";

                $stmtVersion = $this->conexion->prepare($sqlVersion);
                $stmtVersion->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
                $stmtVersion->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmtVersion->bindParam(':id_base', $idBase, PDO::PARAM_INT);
                $stmtVersion->execute();

                $nuevaVersion = (int) $stmtVersion->fetchColumn();
                if ($nuevaVersion <= 0) {
                    $nuevaVersion = ((int) $actual['version_registro']) + 1;
                }

                $sqlInsertVersion = "INSERT INTO historial_clinico_paciente (
                                        id_historial_base,
                                        id_paciente,
                                        id_usuario_profesional,
                                        fecha_atencion,
                                        motivo_consulta,
                                        diagnostico,
                                        tratamientos_aplicados,
                                        medicacion_recetada,
                                        observaciones_adicionales,
                                        version_registro
                                    ) VALUES (
                                        :id_historial_base,
                                        :id_paciente,
                                        :id_usuario,
                                        :fecha_atencion,
                                        :motivo_consulta,
                                        :diagnostico,
                                        :tratamientos_aplicados,
                                        :medicacion_recetada,
                                        :observaciones_adicionales,
                                        :version_registro
                                    )";

                $stmtInsertVersion = $this->conexion->prepare($sqlInsertVersion);
                $stmtInsertVersion->bindParam(':id_historial_base', $idBase, PDO::PARAM_INT);
                $stmtInsertVersion->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
                $stmtInsertVersion->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmtInsertVersion->bindParam(':fecha_atencion', $fechaAtencion, PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':motivo_consulta', $data['motivo_consulta'], PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':diagnostico', $data['diagnostico'], PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':tratamientos_aplicados', $data['tratamientos_aplicados'], PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':medicacion_recetada', $data['medicacion_recetada'], PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':observaciones_adicionales', $data['observaciones_adicionales'], PDO::PARAM_STR);
                $stmtInsertVersion->bindParam(':version_registro', $nuevaVersion, PDO::PARAM_INT);

                if (!$stmtInsertVersion->execute()) {
                    return false;
                }

                $nuevoIdHistorial = (int) $this->conexion->lastInsertId();

                return [
                    'id_historial' => $nuevoIdHistorial,
                    'id_historial_base' => $idBase,
                    'id_paciente' => $idPaciente,
                    'version_registro' => $nuevaVersion,
                ];
            }

            $sqlVersion = "SELECT COALESCE(MAX(version_registro), 0) + 1
                        FROM historial_clinico_paciente
                        WHERE id_paciente = :id_paciente
                            AND id_usuario_profesional = :id_usuario";

            $stmtVersion = $this->conexion->prepare($sqlVersion);
            $stmtVersion->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmtVersion->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtVersion->execute();
            $version = (int) $stmtVersion->fetchColumn();
            if ($version <= 0) {
                $version = 1;
            }

            $sqlInsert = "INSERT INTO historial_clinico_paciente (
                            id_historial_base,
                            id_paciente,
                            id_usuario_profesional,
                            fecha_atencion,
                            motivo_consulta,
                            diagnostico,
                            tratamientos_aplicados,
                            medicacion_recetada,
                            observaciones_adicionales,
                            version_registro
                        ) VALUES (
                            NULL,
                            :id_paciente,
                            :id_usuario,
                            :fecha_atencion,
                            :motivo_consulta,
                            :diagnostico,
                            :tratamientos_aplicados,
                            :medicacion_recetada,
                            :observaciones_adicionales,
                            :version_registro
                        )";

            $stmtInsert = $this->conexion->prepare($sqlInsert);
            $stmtInsert->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtInsert->bindParam(':fecha_atencion', $fechaAtencion, PDO::PARAM_STR);
            $stmtInsert->bindParam(':motivo_consulta', $data['motivo_consulta'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':diagnostico', $data['diagnostico'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':tratamientos_aplicados', $data['tratamientos_aplicados'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':medicacion_recetada', $data['medicacion_recetada'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':observaciones_adicionales', $data['observaciones_adicionales'], PDO::PARAM_STR);
            $stmtInsert->bindParam(':version_registro', $version, PDO::PARAM_INT);

            if (!$stmtInsert->execute()) {
                return false;
            }

            $nuevoIdHistorial = (int) $this->conexion->lastInsertId();

            $sqlSetBase = "UPDATE historial_clinico_paciente
                        SET id_historial_base = :id_base
                        WHERE id_historial = :id_historial
                            AND id_usuario_profesional = :id_usuario";
            $stmtSetBase = $this->conexion->prepare($sqlSetBase);
            $stmtSetBase->bindParam(':id_base', $nuevoIdHistorial, PDO::PARAM_INT);
            $stmtSetBase->bindParam(':id_historial', $nuevoIdHistorial, PDO::PARAM_INT);
            $stmtSetBase->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtSetBase->execute();

            return [
                'id_historial' => $nuevoIdHistorial,
                'id_historial_base' => $nuevoIdHistorial,
                'id_paciente' => $idPaciente,
                'version_registro' => $version,
            ];
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarHistorialClinicoPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarTablaVacunasPaciente(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS paciente_vacuna (
                        id_vacuna INT AUTO_INCREMENT PRIMARY KEY,
                        id_paciente INT NOT NULL,
                        id_usuario_profesional INT NOT NULL,
                        tipo_vacuna VARCHAR(150) NOT NULL,
                        dosis VARCHAR(100) NOT NULL,
                        fecha_aplicacion DATE NOT NULL,
                        observaciones TEXT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_pv_paciente (id_paciente),
                        INDEX idx_pv_profesional (id_usuario_profesional),
                        CONSTRAINT fk_pv_paciente FOREIGN KEY (id_paciente) REFERENCES paciente (id_paciente) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT fk_pv_usuario FOREIGN KEY (id_usuario_profesional) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaVacunasPaciente - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarTablaTratamientosPaciente(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS paciente_tratamiento (
                        id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
                        id_paciente INT NOT NULL,
                        id_usuario_profesional INT NOT NULL,
                        medicamento VARCHAR(150) NOT NULL,
                        dosis VARCHAR(100) NOT NULL,
                        fecha_inicio DATE NOT NULL,
                        fecha_fin DATE NULL,
                        estado VARCHAR(60) NOT NULL DEFAULT 'Activo',
                        observaciones TEXT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_pt_paciente (id_paciente),
                        INDEX idx_pt_profesional (id_usuario_profesional),
                        CONSTRAINT fk_pt_paciente FOREIGN KEY (id_paciente) REFERENCES paciente (id_paciente) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT fk_pt_usuario FOREIGN KEY (id_usuario_profesional) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaTratamientosPaciente - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarTablaConsultasPaciente(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS paciente_consulta_clinica (
                        id_consulta INT AUTO_INCREMENT PRIMARY KEY,
                        id_paciente INT NOT NULL,
                        id_usuario_profesional INT NOT NULL,
                        fecha_consulta DATETIME NOT NULL,
                        motivo VARCHAR(255) NOT NULL,
                        diagnostico TEXT NULL,
                        observaciones TEXT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_pcc_paciente (id_paciente),
                        INDEX idx_pcc_profesional (id_usuario_profesional),
                        CONSTRAINT fk_pcc_paciente FOREIGN KEY (id_paciente) REFERENCES paciente (id_paciente) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT fk_pcc_usuario FOREIGN KEY (id_usuario_profesional) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaConsultasPaciente - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarTablaNotasPaciente(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS paciente_nota_clinica (
                        id_nota INT AUTO_INCREMENT PRIMARY KEY,
                        id_paciente INT NOT NULL,
                        id_usuario_profesional INT NOT NULL,
                        nota TEXT NOT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_pnc_paciente (id_paciente),
                        INDEX idx_pnc_profesional (id_usuario_profesional),
                        CONSTRAINT fk_pnc_paciente FOREIGN KEY (id_paciente) REFERENCES paciente (id_paciente) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT fk_pnc_usuario FOREIGN KEY (id_usuario_profesional) REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaNotasPaciente - " . $e->getMessage());
            return false;
        }
    }

    private function asegurarEstructuraRfs30(): bool
    {
        return $this->asegurarTablaHistorialClinico()
            && $this->asegurarTablaVacunasPaciente()
            && $this->asegurarTablaTratamientosPaciente()
            && $this->asegurarTablaConsultasPaciente()
            && $this->asegurarTablaNotasPaciente();
    }

    public function obtenerFichaClinicaCompletaPorProfesional($id_usuario, $id_paciente)
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, $id_paciente)) {
                return false;
            }

            $paciente = $this->obtenerDetallePacientePorVeterinario($id_usuario, $id_paciente);
            if (!$paciente) {
                return false;
            }

            return [
                'paciente' => $paciente,
                'historial' => $this->obtenerHistorialPacientePorVeterinario($id_usuario, $id_paciente, 20),
                'vacunas' => $this->listarVacunasPorProfesionalPaciente($id_usuario, $id_paciente),
                'tratamientos' => $this->listarTratamientosPorProfesionalPaciente($id_usuario, $id_paciente),
                'consultas' => $this->listarConsultasPorProfesionalPaciente($id_usuario, $id_paciente),
                'notas' => $this->listarNotasClinicasPorProfesionalPaciente($id_usuario, $id_paciente),
            ];
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerFichaClinicaCompletaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function listarVacunasPorProfesionalPaciente($id_usuario, $id_paciente): array
    {
        try {
            $sql = "SELECT id_vacuna, tipo_vacuna, dosis, fecha_aplicacion, observaciones, created_at, updated_at
                    FROM paciente_vacuna
                    WHERE id_usuario_profesional = :id_usuario
                      AND id_paciente = :id_paciente
                    ORDER BY fecha_aplicacion DESC, id_vacuna DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarVacunasPorProfesionalPaciente - " . $e->getMessage());
            return [];
        }
    }

    public function listarTratamientosPorProfesionalPaciente($id_usuario, $id_paciente): array
    {
        try {
            $sql = "SELECT id_tratamiento, medicamento, dosis, fecha_inicio, fecha_fin, estado, observaciones, created_at, updated_at
                    FROM paciente_tratamiento
                    WHERE id_usuario_profesional = :id_usuario
                      AND id_paciente = :id_paciente
                    ORDER BY fecha_inicio DESC, id_tratamiento DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarTratamientosPorProfesionalPaciente - " . $e->getMessage());
            return [];
        }
    }

    public function listarConsultasPorProfesionalPaciente($id_usuario, $id_paciente): array
    {
        try {
            $sql = "SELECT id_consulta, fecha_consulta, motivo, diagnostico, observaciones, created_at, updated_at
                    FROM paciente_consulta_clinica
                    WHERE id_usuario_profesional = :id_usuario
                      AND id_paciente = :id_paciente
                    ORDER BY fecha_consulta DESC, id_consulta DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarConsultasPorProfesionalPaciente - " . $e->getMessage());
            return [];
        }
    }

    public function listarNotasClinicasPorProfesionalPaciente($id_usuario, $id_paciente): array
    {
        try {
            $sql = "SELECT id_nota, nota, created_at, updated_at
                    FROM paciente_nota_clinica
                    WHERE id_usuario_profesional = :id_usuario
                      AND id_paciente = :id_paciente
                    ORDER BY created_at DESC, id_nota DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarNotasClinicasPorProfesionalPaciente - " . $e->getMessage());
            return [];
        }
    }

    public function guardarVacunaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $data['id_paciente'])) {
                return false;
            }

            $sql = "INSERT INTO paciente_vacuna (id_paciente, id_usuario_profesional, tipo_vacuna, dosis, fecha_aplicacion, observaciones)
                    VALUES (:id_paciente, :id_usuario, :tipo_vacuna, :dosis, :fecha_aplicacion, :observaciones)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_vacuna', $data['tipo_vacuna'], PDO::PARAM_STR);
            $stmt->bindParam(':dosis', $data['dosis'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_aplicacion', $data['fecha_aplicacion'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarVacunaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function guardarTratamientoPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $data['id_paciente'])) {
                return false;
            }

            $sql = "INSERT INTO paciente_tratamiento (
                        id_paciente, id_usuario_profesional, medicamento, dosis, fecha_inicio, fecha_fin, estado, observaciones
                    ) VALUES (
                        :id_paciente, :id_usuario, :medicamento, :dosis, :fecha_inicio, :fecha_fin, :estado, :observaciones
                    )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':medicamento', $data['medicamento'], PDO::PARAM_STR);
            $stmt->bindParam(':dosis', $data['dosis'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio'], PDO::PARAM_STR);

            if (!empty($data['fecha_fin'])) {
                $stmt->bindParam(':fecha_fin', $data['fecha_fin'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':fecha_fin', null, PDO::PARAM_NULL);
            }

            $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarTratamientoPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function guardarConsultaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $data['id_paciente'])) {
                return false;
            }

            $fechaConsulta = trim((string) ($data['fecha_consulta'] ?? ''));
            if ($fechaConsulta !== '' && strlen($fechaConsulta) === 10) {
                $fechaConsulta .= ' 00:00:00';
            }

            $sql = "INSERT INTO paciente_consulta_clinica (
                        id_paciente, id_usuario_profesional, fecha_consulta, motivo, diagnostico, observaciones
                    ) VALUES (
                        :id_paciente, :id_usuario, :fecha_consulta, :motivo, :diagnostico, :observaciones
                    )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_consulta', $fechaConsulta, PDO::PARAM_STR);
            $stmt->bindParam(':motivo', $data['motivo'], PDO::PARAM_STR);
            $stmt->bindParam(':diagnostico', $data['diagnostico'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarConsultaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function guardarNotaClinicaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $data['id_paciente'])) {
                return false;
            }

            $sql = "INSERT INTO paciente_nota_clinica (id_paciente, id_usuario_profesional, nota)
                    VALUES (:id_paciente, :id_usuario, :nota)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':nota', $data['nota'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarNotaClinicaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function actualizarVacunaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            $idPaciente = (int) ($data['id_paciente'] ?? 0);
            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $sql = "UPDATE paciente_vacuna
                    SET tipo_vacuna = :tipo_vacuna,
                        dosis = :dosis,
                        fecha_aplicacion = :fecha_aplicacion,
                        observaciones = :observaciones
                    WHERE id_vacuna = :id_vacuna
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo_vacuna', $data['tipo_vacuna'], PDO::PARAM_STR);
            $stmt->bindParam(':dosis', $data['dosis'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_aplicacion', $data['fecha_aplicacion'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(':id_vacuna', $data['id_vacuna'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarVacunaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function eliminarVacunaPorProfesional($id_usuario, $id_paciente, $id_vacuna): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $id_paciente)) {
                return false;
            }

            $sql = "DELETE FROM paciente_vacuna
                    WHERE id_vacuna = :id_vacuna
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_vacuna', $id_vacuna, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::eliminarVacunaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function actualizarTratamientoPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            $idPaciente = (int) ($data['id_paciente'] ?? 0);
            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $sql = "UPDATE paciente_tratamiento
                    SET medicamento = :medicamento,
                        dosis = :dosis,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin = :fecha_fin,
                        estado = :estado,
                        observaciones = :observaciones
                    WHERE id_tratamiento = :id_tratamiento
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':medicamento', $data['medicamento'], PDO::PARAM_STR);
            $stmt->bindParam(':dosis', $data['dosis'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio'], PDO::PARAM_STR);

            if (!empty($data['fecha_fin'])) {
                $stmt->bindParam(':fecha_fin', $data['fecha_fin'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':fecha_fin', null, PDO::PARAM_NULL);
            }

            $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(':id_tratamiento', $data['id_tratamiento'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarTratamientoPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function eliminarTratamientoPorProfesional($id_usuario, $id_paciente, $id_tratamiento): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $id_paciente)) {
                return false;
            }

            $sql = "DELETE FROM paciente_tratamiento
                    WHERE id_tratamiento = :id_tratamiento
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_tratamiento', $id_tratamiento, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::eliminarTratamientoPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function actualizarConsultaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            $idPaciente = (int) ($data['id_paciente'] ?? 0);
            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $fechaConsulta = trim((string) ($data['fecha_consulta'] ?? ''));
            if ($fechaConsulta !== '' && strlen($fechaConsulta) === 10) {
                $fechaConsulta .= ' 00:00:00';
            }

            $sql = "UPDATE paciente_consulta_clinica
                    SET fecha_consulta = :fecha_consulta,
                        motivo = :motivo,
                        diagnostico = :diagnostico,
                        observaciones = :observaciones
                    WHERE id_consulta = :id_consulta
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_consulta', $fechaConsulta, PDO::PARAM_STR);
            $stmt->bindParam(':motivo', $data['motivo'], PDO::PARAM_STR);
            $stmt->bindParam(':diagnostico', $data['diagnostico'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $data['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(':id_consulta', $data['id_consulta'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarConsultaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function eliminarConsultaPorProfesional($id_usuario, $id_paciente, $id_consulta): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $id_paciente)) {
                return false;
            }

            $sql = "DELETE FROM paciente_consulta_clinica
                    WHERE id_consulta = :id_consulta
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_consulta', $id_consulta, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::eliminarConsultaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function actualizarNotaClinicaPorProfesional($id_usuario, array $data): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            $idPaciente = (int) ($data['id_paciente'] ?? 0);
            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $sql = "UPDATE paciente_nota_clinica
                    SET nota = :nota
                    WHERE id_nota = :id_nota
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nota', $data['nota'], PDO::PARAM_STR);
            $stmt->bindParam(':id_nota', $data['id_nota'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarNotaClinicaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function eliminarNotaClinicaPorProfesional($id_usuario, $id_paciente, $id_nota): bool
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            if (!$this->profesionalTienePacienteActivo($id_usuario, (int) $id_paciente)) {
                return false;
            }

            $sql = "DELETE FROM paciente_nota_clinica
                    WHERE id_nota = :id_nota
                      AND id_paciente = :id_paciente
                      AND id_usuario_profesional = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::eliminarNotaClinicaPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function obtenerFichaClinicaPdfPorProfesional($id_usuario, $id_paciente)
    {
        try {
            if (!$this->asegurarEstructuraRfs30()) {
                return false;
            }

            $idPaciente = (int) $id_paciente;
            if ($idPaciente <= 0 || !$this->profesionalTienePacienteActivo($id_usuario, $idPaciente)) {
                return false;
            }

            $paciente = $this->obtenerDetallePacientePorVeterinario($id_usuario, $idPaciente);
            if (!$paciente) {
                return false;
            }

            $sqlHistorialClinico = "SELECT
                                        id_historial,
                                        fecha_atencion,
                                        motivo_consulta,
                                        diagnostico,
                                        tratamientos_aplicados,
                                        medicacion_recetada,
                                        observaciones_adicionales,
                                        version_registro,
                                        created_at,
                                        updated_at
                                    FROM historial_clinico_paciente
                                    WHERE id_usuario_profesional = :id_usuario
                                      AND id_paciente = :id_paciente
                                    ORDER BY fecha_atencion DESC, id_historial DESC";

            $stmtHistorial = $this->conexion->prepare($sqlHistorialClinico);
            $stmtHistorial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtHistorial->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmtHistorial->execute();
            $historialClinico = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

            return [
                'paciente' => $paciente,
                'historial_clinico' => $historialClinico,
                'vacunas' => $this->listarVacunasPorProfesionalPaciente($id_usuario, $idPaciente),
                'tratamientos' => $this->listarTratamientosPorProfesionalPaciente($id_usuario, $idPaciente),
                'consultas' => $this->listarConsultasPorProfesionalPaciente($id_usuario, $idPaciente),
                'notas' => $this->listarNotasClinicasPorProfesionalPaciente($id_usuario, $idPaciente),
            ];
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerFichaClinicaPdfPorProfesional - " . $e->getMessage());
            return false;
        }
    }

    public function registrar($data)
    {
        try {
            $this->conexion->beginTransaction();

            // INSERT USUARIO
            $sqlUsuario = "INSERT INTO usuario (email, password_hash, estado, id_rol)
                    VALUES (:email, :password_hash, :estado, :id_rol)";
            $stmtUsuario = $this->conexion->prepare($sqlUsuario);

            $passwordHash = password_hash($data['password_hash'], PASSWORD_DEFAULT);

            $stmtUsuario->execute([
                ':email' => $data['email'],
                ':password_hash' => $passwordHash,
                ':estado' => $data['estado'],
                ':id_rol' => 2
            ]);

            $id_usuario = $this->conexion->lastInsertId();

            // INSERT VETERINARIO
            $sqlVet = "INSERT INTO veterinario 
            (id_usuario, tipo_documento, numero_documento, nombres, apellidos,
            telefono, img_perfil, numero_licencia_profesional, id_veterinaria, fecha_contratacion)
            VALUES
            (:id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos,
            :telefono, :img_perfil, :numero_licencia_profesional, :id_veterinaria, :fecha_contratacion)";

            $stmtVet = $this->conexion->prepare($sqlVet);

            $stmtVet->execute([
                ':id_usuario' => $id_usuario,
                ':tipo_documento' => $data['tipo_documento'],
                ':numero_documento' => $data['numero_documento'],
                ':nombres' => $data['nombres'],
                ':apellidos' => $data['apellidos'],
                ':telefono' => $data['telefono'],
                ':img_perfil' => $data['img_perfil'],
                ':numero_licencia_profesional' => $data['numero_licencia_profesional'],
                ':id_veterinaria' => $data['id_veterinaria'],
                ':fecha_contratacion' => date('Y-m-d')
            ]);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("ERROR REGISTRAR VETERINARIO: " . $e->getMessage());
            return false;
        }
    }



    public function listar($id_veterinaria)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.fecha_contratacion,  r.nombre AS nombre_rol, vet.nombre AS nombre_veterinaria FROM usuario u  INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE v.id_veterinaria = :id_veterinaria  AND u.id_rol = 2 ORDER BY v.nombres ASC, v.apellidos ASC";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listar - " . $e->getMessage());
            return [];
        }
    }

    public function listarVeterinario($id)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, u.id_rol, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, r.nombre AS nombre_rol, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.id_veterinaria, v.fecha_contratacion, vet.nombre AS nombre_veterinaria FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE u.id_usuario = :id  LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listarVeterinario - " . $e->getMessage());
            return false;
        }
    }


    public function actualizar($data)
    {
        try {
            // Iniciamos una transacción
            $this->conexion->beginTransaction();

            // 1. Actualizamos la tabla usuario
            $actualizarUsuario = "UPDATE usuario SET email = :email, estado = :estado WHERE id_usuario = :id_usuario";

            $resultadoUsuario = $this->conexion->prepare($actualizarUsuario);
            $resultadoUsuario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoUsuario->bindParam(':email', $data['email']);
            $resultadoUsuario->bindParam(':estado', $data['estado']);
            $resultadoUsuario->execute();

            // 2. Actualizamos la tabla veterinario
            $actualizarVeterinario = "UPDATE veterinario  SET tipo_documento = :tipo_documento,  numero_documento = :numero_documento,  nombres = :nombres,  apellidos = :apellidos,  telefono = :telefono, numero_licencia_profesional = :numero_licencia_profesional WHERE id_usuario = :id_usuario";

            $resultadoVeterinario = $this->conexion->prepare($actualizarVeterinario);
            $resultadoVeterinario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoVeterinario->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultadoVeterinario->bindParam(':numero_documento', $data['numero_documento']);
            $resultadoVeterinario->bindParam(':nombres', $data['nombres']);
            $resultadoVeterinario->bindParam(':apellidos', $data['apellidos']);
            $resultadoVeterinario->bindParam(':telefono', $data['telefono']);
            $resultadoVeterinario->bindParam(':numero_licencia_profesional', $data['numero_licencia_profesional']);
            $resultadoVeterinario->execute();

            // Confirmamos la transacción
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            // Si hay error, revertimos los cambios
            $this->conexion->rollBack();
            error_log("Error en veterinario::actualizar - " . $e->getMessage());
            return false;
        }
    }


    public function actualizarFotoPerfil($data)
    {
        // Actualizamos la foto de perfil del usuario
        try {
            $sql = "UPDATE profesional SET img_perfil = :img_perfil
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':img_perfil', $data['img_perfil']);

            $ok = $stmt->execute();
            // Verificamos si la actualización fue exitosa
            if ($ok) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarFotoPerfil -> " . $e->getMessage());
            return false;
        }
    }




    public function eliminar($id)
    {
        try {
            // Actualizamos el estado del usuario a 'inactivo'
            $actualizar = "UPDATE usuario SET
                        estado = :estado
                        WHERE id_usuario = :id_usuario";
            // Preparar y ejecutar la consulta
            $resultado = $this->conexion->prepare($actualizar);

            $resultado->bindValue(':estado', 'inactivo');
            $resultado->bindValue(':id_usuario', $id);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::eliminar - " . $e->getMessage());
            return false;
        }
    }

    // Método adicional para actualizar la imagen de perfil
    public function actualizarImagen($id_usuario, $img_perfil)
    {
        try {
            $actualizar = "UPDATE veterinario
                        SET img_perfil = :img_perfil
                        WHERE id_usuario = :id_usuario";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $resultado->bindParam(':img_perfil', $img_perfil);
            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::actualizarImagen - " . $e->getMessage());
            return false;
        }
    }

    // =========================================
    //  DIRECTORIO DE VETERINARIOS - Issue #240
    // =========================================

    private function asegurarColumnaEstadoDirectorio(): void
    {
        try {
            $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'profesional'
                    AND COLUMN_NAME = 'estado_directorio'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() === 0) {
                $this->conexion->exec(
                    "ALTER TABLE profesional ADD COLUMN estado_directorio
                     ENUM('Activo','En consulta','De vacaciones','No disponible')
                     NOT NULL DEFAULT 'Activo' AFTER direccion"
                );
            }
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarColumnaEstadoDirectorio - " . $e->getMessage());
        }
    }

    private function asegurarTablaResenias(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS resenia_profesional (
                        id_resenia INT AUTO_INCREMENT PRIMARY KEY,
                        id_usuario_profesional INT NOT NULL,
                        id_usuario_autor INT NOT NULL,
                        calificacion TINYINT NOT NULL,
                        comentario TEXT NULL,
                        estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_resp_profesional (id_usuario_profesional),
                        INDEX idx_resp_autor (id_usuario_autor),
                        CONSTRAINT fk_resp_profesional FOREIGN KEY (id_usuario_profesional)
                            REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT fk_resp_autor FOREIGN KEY (id_usuario_autor)
                            REFERENCES usuario (id_usuario) ON DELETE RESTRICT ON UPDATE RESTRICT,
                        CONSTRAINT chk_resp_calificacion CHECK (calificacion BETWEEN 1 AND 5)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaResenias - " . $e->getMessage());
            return false;
        }
    }

    public function listarDirectorioProfesionales(?int $id_veterinaria = null, array $filtros = []): array
    {
        try {
            $this->asegurarColumnaEstadoDirectorio();
            $this->asegurarTablaResenias();

            $sql = "SELECT
                        p.id_profesional,
                        p.id_usuario,
                        p.nombres,
                        p.apellidos,
                        p.registro_medico,
                        p.telefono,
                        p.img_perfil,
                        p.direccion,
                        p.estado_directorio,
                        us.email,
                        v.id_veterinaria,
                        v.nombre AS veterinaria,
                        GROUP_CONCAT(DISTINCT esp.nombre ORDER BY esp.nombre SEPARATOR ', ') AS especialidades,
                        ROUND(AVG(r.calificacion), 1) AS calificacion_promedio,
                        COUNT(DISTINCT r.id_resenia) AS total_resenias
                    FROM profesional_veterinaria pv
                    INNER JOIN profesional p ON p.id_profesional = pv.id_profesional
                    INNER JOIN usuario us ON p.id_usuario = us.id_usuario
                    INNER JOIN veterinaria v ON pv.id_veterinaria = v.id_veterinaria
                    LEFT JOIN profesional_especialidad pe ON p.id_usuario = pe.id_usuario
                        AND pe.estado = 'Activo' AND pe.id_veterinaria = pv.id_veterinaria
                    LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad
                    LEFT JOIN resenia_profesional r ON r.id_usuario_profesional = p.id_usuario
                        AND r.estado = 'Activo'
                    WHERE pv.estado = 'Activo' AND us.estado = 'activo'";

            $params = [];

            if ($id_veterinaria !== null) {
                $sql .= " AND pv.id_veterinaria = :id_veterinaria";
                $params[':id_veterinaria'] = $id_veterinaria;
            }

            if (!empty($filtros['disponibilidad'])) {
                $sql .= " AND p.estado_directorio = :disponibilidad";
                $params[':disponibilidad'] = $filtros['disponibilidad'];
            }

            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda2
                           OR CONCAT(p.nombres, ' ', p.apellidos) LIKE :busqueda3)";
                $params[':busqueda']  = '%' . $filtros['busqueda'] . '%';
                $params[':busqueda2'] = '%' . $filtros['busqueda'] . '%';
                $params[':busqueda3'] = '%' . $filtros['busqueda'] . '%';
            }

            $sql .= " GROUP BY p.id_profesional, p.id_usuario, pv.id_veterinaria";

            if (!empty($filtros['especialidad'])) {
                $sql .= " HAVING especialidades LIKE :especialidad";
                $params[':especialidad'] = '%' . $filtros['especialidad'] . '%';
            }

            $sql .= " ORDER BY calificacion_promedio DESC, p.nombres ASC";

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarDirectorioProfesionales - " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPerfilDirectorio(int $id_usuario): ?array
    {
        try {
            $this->asegurarColumnaEstadoDirectorio();
            $this->asegurarTablaResenias();

            $sql = "SELECT
                        p.id_profesional,
                        p.id_usuario,
                        p.nombres,
                        p.apellidos,
                        p.registro_medico,
                        p.telefono,
                        p.img_perfil,
                        p.direccion,
                        p.estado_directorio,
                        us.email,
                        v.id_veterinaria,
                        v.nombre AS veterinaria,
                        GROUP_CONCAT(DISTINCT esp.nombre ORDER BY esp.nombre SEPARATOR ', ') AS especialidades,
                        ROUND(AVG(r.calificacion), 1) AS calificacion_promedio,
                        COUNT(DISTINCT r.id_resenia) AS total_resenias
                    FROM profesional p
                    INNER JOIN usuario us ON p.id_usuario = us.id_usuario
                    INNER JOIN profesional_veterinaria pv ON p.id_profesional = pv.id_profesional
                        AND pv.estado = 'Activo'
                    INNER JOIN veterinaria v ON pv.id_veterinaria = v.id_veterinaria
                    LEFT JOIN profesional_especialidad pe ON p.id_usuario = pe.id_usuario
                        AND pe.estado = 'Activo' AND pe.id_veterinaria = pv.id_veterinaria
                    LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad
                    LEFT JOIN resenia_profesional r ON r.id_usuario_profesional = p.id_usuario
                        AND r.estado = 'Activo'
                    WHERE p.id_usuario = :id_usuario
                    GROUP BY p.id_profesional, v.id_veterinaria
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$perfil) {
                return null;
            }

            $sqlHorarios = "SELECT
                                du.dia_semana,
                                CASE du.dia_semana
                                    WHEN 1 THEN 'Lunes'   WHEN 2 THEN 'Martes'
                                    WHEN 3 THEN 'Miércoles' WHEN 4 THEN 'Jueves'
                                    WHEN 5 THEN 'Viernes' WHEN 6 THEN 'Sábado'
                                    WHEN 7 THEN 'Domingo'
                                END AS dia,
                                du.hora_inicio,
                                du.hora_fin,
                                esp.nombre AS especialidad
                            FROM disponibilidad_usuario du
                            LEFT JOIN especialidad esp ON du.id_especialidad = esp.id_especialidad
                            WHERE du.id_usuario = :id_usuario AND du.estado = 'Activo'
                            ORDER BY du.dia_semana ASC, du.hora_inicio ASC";

            $stmtH = $this->conexion->prepare($sqlHorarios);
            $stmtH->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtH->execute();
            $perfil['horarios'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            return $perfil;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerPerfilDirectorio - " . $e->getMessage());
            return null;
        }
    }

    public function actualizarEstadoDirectorio(int $id_usuario, string $estado): bool
    {
        $estados_validos = ['Activo', 'En consulta', 'De vacaciones', 'No disponible'];
        if (!in_array($estado, $estados_validos, true)) {
            return false;
        }
        try {
            $this->asegurarColumnaEstadoDirectorio();
            $sql = "UPDATE profesional SET estado_directorio = :estado WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::actualizarEstadoDirectorio - " . $e->getMessage());
            return false;
        }
    }

    public function agregarResenia(array $data): bool
    {
        if (!$this->asegurarTablaResenias()) {
            return false;
        }
        $calificacion = (int) ($data['calificacion'] ?? 0);
        if ($calificacion < 1 || $calificacion > 5) {
            return false;
        }
        try {
            $sql = "INSERT INTO resenia_profesional
                        (id_usuario_profesional, id_usuario_autor, calificacion, comentario)
                    VALUES (:id_usuario_profesional, :id_usuario_autor, :calificacion, :comentario)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario_profesional', $data['id_usuario_profesional'], PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario_autor',       $data['id_usuario_autor'],       PDO::PARAM_INT);
            $stmt->bindParam(':calificacion',           $calificacion,                   PDO::PARAM_INT);
            $stmt->bindParam(':comentario',             $data['comentario'],             PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::agregarResenia - " . $e->getMessage());
            return false;
        }
    }

    public function listarResenias(int $id_usuario): array
    {
        if (!$this->asegurarTablaResenias()) {
            return [];
        }
        try {
            $sql = "SELECT
                        r.id_resenia,
                        r.calificacion,
                        r.comentario,
                        r.created_at,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS autor_nombre
                    FROM resenia_profesional r
                    LEFT JOIN propietario pr ON r.id_usuario_autor = pr.id_usuario
                    WHERE r.id_usuario_profesional = :id_usuario AND r.estado = 'Activo'
                    ORDER BY r.created_at DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinario::listarResenias - " . $e->getMessage());
            return [];
        }
    }
}
