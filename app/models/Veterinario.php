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
                        INDEX idx_hcp_paciente (id_paciente),
                        INDEX idx_hcp_profesional (id_usuario_profesional),
                        INDEX idx_hcp_fecha (fecha_atencion)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->conexion->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::asegurarTablaHistorialClinico - " . $e->getMessage());
            return false;
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
                        h.fecha_atencion,
                        h.motivo_consulta,
                        h.diagnostico,
                        h.tratamientos_aplicados,
                        h.medicacion_recetada,
                        h.observaciones_adicionales,
                        h.version_registro,
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
                $sqlExiste = "SELECT version_registro
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

                $nuevaVersion = ((int) $actual['version_registro']) + 1;

                $sqlUpdate = "UPDATE historial_clinico_paciente
                              SET fecha_atencion = :fecha_atencion,
                                  motivo_consulta = :motivo_consulta,
                                  diagnostico = :diagnostico,
                                  tratamientos_aplicados = :tratamientos_aplicados,
                                  medicacion_recetada = :medicacion_recetada,
                                  observaciones_adicionales = :observaciones_adicionales,
                                  version_registro = :version_registro
                              WHERE id_historial = :id_historial
                                AND id_usuario_profesional = :id_usuario
                                AND id_paciente = :id_paciente";

                $stmtUpdate = $this->conexion->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':fecha_atencion', $fechaAtencion, PDO::PARAM_STR);
                $stmtUpdate->bindParam(':motivo_consulta', $data['motivo_consulta'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':diagnostico', $data['diagnostico'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':tratamientos_aplicados', $data['tratamientos_aplicados'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':medicacion_recetada', $data['medicacion_recetada'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':observaciones_adicionales', $data['observaciones_adicionales'], PDO::PARAM_STR);
                $stmtUpdate->bindParam(':version_registro', $nuevaVersion, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':id_historial', $idHistorial, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);

                if (!$stmtUpdate->execute()) {
                    return false;
                }

                return [
                    'id_historial' => $idHistorial,
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

            return [
                'id_historial' => (int) $this->conexion->lastInsertId(),
                'id_paciente' => $idPaciente,
                'version_registro' => $version,
            ];
        } catch (PDOException $e) {
            error_log("Error en Veterinario::guardarHistorialClinicoPorProfesional - " . $e->getMessage());
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
}
