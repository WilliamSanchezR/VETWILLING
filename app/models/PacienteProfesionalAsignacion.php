<?php

require_once __DIR__ . '/../../config/database.php';

class PacienteProfesionalAsignacion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    public function tablaExiste()
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'paciente_profesional_asignacion'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::tablaExiste -> ' . $e->getMessage());
            return false;
        }
    }

    public function registrarAsignacionInicial($id_paciente, $id_usuario_profesional, $id_usuario_asigno = null, $motivo = 'Asignación inicial')
    {
        try {
            if (!$this->tablaExiste()) {
                return true;
            }

            $sql = "INSERT INTO paciente_profesional_asignacion (
                        id_paciente,
                        id_usuario_profesional,
                        id_usuario_asigno,
                        fecha_inicio,
                        fecha_fin,
                        motivo_cambio,
                        estado
                    ) VALUES (
                        :id_paciente,
                        :id_usuario_profesional,
                        :id_usuario_asigno,
                        CURRENT_TIMESTAMP,
                        NULL,
                        :motivo_cambio,
                        'Activo'
                    )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario_profesional', $id_usuario_profesional, PDO::PARAM_INT);

            if ($id_usuario_asigno !== null) {
                $stmt->bindParam(':id_usuario_asigno', $id_usuario_asigno, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_usuario_asigno', null, PDO::PARAM_NULL);
            }

            $stmt->bindParam(':motivo_cambio', $motivo, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::registrarAsignacionInicial -> ' . $e->getMessage());
            return false;
        }
    }

    public function reasignarPaciente($id_paciente, $id_usuario_profesional_nuevo, $id_usuario_asigno = null, $motivo = 'Reasignación manual', $observacion = null)
    {
        try {
            if (!$this->tablaExiste()) {
                return true;
            }

            $this->conexion->beginTransaction();

            $sqlCerrar = "UPDATE paciente_profesional_asignacion
                         SET estado = 'Finalizado',
                             fecha_fin = CURRENT_TIMESTAMP,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id_paciente = :id_paciente
                           AND estado = 'Activo'
                           AND fecha_fin IS NULL";

            $stmtCerrar = $this->conexion->prepare($sqlCerrar);
            $stmtCerrar->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmtCerrar->execute();

            $sqlAbrir = "INSERT INTO paciente_profesional_asignacion (
                            id_paciente,
                            id_usuario_profesional,
                            id_usuario_asigno,
                            fecha_inicio,
                            fecha_fin,
                            motivo_cambio,
                            observacion,
                            estado
                         ) VALUES (
                            :id_paciente,
                            :id_usuario_profesional,
                            :id_usuario_asigno,
                            CURRENT_TIMESTAMP,
                            NULL,
                            :motivo_cambio,
                            :observacion,
                            'Activo'
                         )";

            $stmtAbrir = $this->conexion->prepare($sqlAbrir);
            $stmtAbrir->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmtAbrir->bindParam(':id_usuario_profesional', $id_usuario_profesional_nuevo, PDO::PARAM_INT);

            if ($id_usuario_asigno !== null) {
                $stmtAbrir->bindParam(':id_usuario_asigno', $id_usuario_asigno, PDO::PARAM_INT);
            } else {
                $stmtAbrir->bindValue(':id_usuario_asigno', null, PDO::PARAM_NULL);
            }

            $stmtAbrir->bindParam(':motivo_cambio', $motivo, PDO::PARAM_STR);

            if ($observacion !== null) {
                $stmtAbrir->bindParam(':observacion', $observacion, PDO::PARAM_STR);
            } else {
                $stmtAbrir->bindValue(':observacion', null, PDO::PARAM_NULL);
            }

            $ok = $stmtAbrir->execute();
            if (!$ok) {
                $this->conexion->rollBack();
                return false;
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('Error en PacienteProfesionalAsignacion::reasignarPaciente -> ' . $e->getMessage());
            return false;
        }
    }

    public function asegurarAsignacionActiva($id_paciente, $id_usuario_profesional, $id_usuario_asigno = null, $motivo = 'Asignación automática')
    {
        try {
            if (!$this->tablaExiste()) {
                return true;
            }

            $sqlActiva = "SELECT id_asignacion, id_usuario_profesional
                          FROM paciente_profesional_asignacion
                          WHERE id_paciente = :id_paciente
                            AND estado = 'Activo'
                            AND fecha_fin IS NULL
                          ORDER BY fecha_inicio DESC, id_asignacion DESC
                          LIMIT 1";

            $stmtActiva = $this->conexion->prepare($sqlActiva);
            $stmtActiva->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmtActiva->execute();
            $activa = $stmtActiva->fetch(PDO::FETCH_ASSOC);

            if (!$activa) {
                return $this->registrarAsignacionInicial($id_paciente, $id_usuario_profesional, $id_usuario_asigno, $motivo);
            }

            if ((int) $activa['id_usuario_profesional'] === (int) $id_usuario_profesional) {
                return true;
            }

            return $this->reasignarPaciente($id_paciente, $id_usuario_profesional, $id_usuario_asigno, $motivo, 'Actualizado por evento de agendamiento');
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::asegurarAsignacionActiva -> ' . $e->getMessage());
            return false;
        }
    }

    public function listarPacientesActivosPorProfesional($id_usuario_profesional)
    {
        try {
            if (!$this->tablaExiste()) {
                return [];
            }

            $sql = "SELECT
                        ppa.id_asignacion,
                        ppa.id_paciente,
                        ppa.id_usuario_profesional,
                        ppa.fecha_inicio,
                        ppa.motivo_cambio,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        prop.id_propietario,
                        CONCAT(prop.nombres, ' ', prop.apellidos) AS propietario_nombre,
                        u_prof.email AS profesional_email
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario prop ON prop.id_propietario = p.id_propietario
                    INNER JOIN usuario u_prof ON u_prof.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.id_usuario_profesional = :id_usuario_profesional
                      AND ppa.estado = 'Activo'
                      AND ppa.fecha_fin IS NULL
                    ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario_profesional', $id_usuario_profesional, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::listarPacientesActivosPorProfesional -> ' . $e->getMessage());
            return [];
        }
    }

    public function listarPacientesActivosGlobal($id_veterinaria = null)
    {
        try {
            if (!$this->tablaExiste()) {
                return [];
            }

            $sql = "SELECT
                        ppa.id_asignacion,
                        ppa.id_paciente,
                        ppa.id_usuario_profesional,
                        ppa.fecha_inicio,
                        ppa.motivo_cambio,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        prop.id_propietario,
                        prop.id_veterinaria,
                        CONCAT(prop.nombres, ' ', prop.apellidos) AS propietario_nombre,
                        COALESCE(prof.nombres, '') AS profesional_nombres,
                        COALESCE(prof.apellidos, '') AS profesional_apellidos,
                        u_prof.email AS profesional_email
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario prop ON prop.id_propietario = p.id_propietario
                    INNER JOIN usuario u_prof ON u_prof.id_usuario = ppa.id_usuario_profesional
                    LEFT JOIN profesional prof ON prof.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.estado = 'Activo'
                      AND ppa.fecha_fin IS NULL";

            if ($id_veterinaria !== null) {
                $sql .= " AND prop.id_veterinaria = :id_veterinaria";
            }

            $sql .= " ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC";

            $stmt = $this->conexion->prepare($sql);
            if ($id_veterinaria !== null) {
                $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::listarPacientesActivosGlobal -> ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerAsignacionActivaPorPaciente($id_paciente)
    {
        try {
            if (!$this->tablaExiste()) {
                return null;
            }

            $sql = "SELECT
                        ppa.*,
                        p.nombre AS paciente_nombre,
                        COALESCE(prof.nombres, '') AS profesional_nombres,
                        COALESCE(prof.apellidos, '') AS profesional_apellidos,
                        u_prof.email AS profesional_email
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN usuario u_prof ON u_prof.id_usuario = ppa.id_usuario_profesional
                    LEFT JOIN profesional prof ON prof.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.id_paciente = :id_paciente
                      AND ppa.estado = 'Activo'
                      AND ppa.fecha_fin IS NULL
                    ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::obtenerAsignacionActivaPorPaciente -> ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerHistorialPorPaciente($id_paciente)
    {
        try {
            if (!$this->tablaExiste()) {
                return [];
            }

            $sql = "SELECT
                        ppa.id_asignacion,
                        ppa.id_paciente,
                        ppa.id_usuario_profesional,
                        ppa.id_usuario_asigno,
                        ppa.fecha_inicio,
                        ppa.fecha_fin,
                        ppa.estado,
                        ppa.motivo_cambio,
                        ppa.observacion,
                        p.nombre AS paciente_nombre,
                        COALESCE(prof.nombres, '') AS profesional_nombres,
                        COALESCE(prof.apellidos, '') AS profesional_apellidos,
                        u_prof.email AS profesional_email
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN usuario u_prof ON u_prof.id_usuario = ppa.id_usuario_profesional
                    LEFT JOIN profesional prof ON prof.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.id_paciente = :id_paciente
                    ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::obtenerHistorialPorPaciente -> ' . $e->getMessage());
            return [];
        }
    }

    public function profesionalTienePacienteActivo($id_usuario_profesional, $id_paciente)
    {
        try {
            if (!$this->tablaExiste()) {
                return false;
            }

            $sql = "SELECT COUNT(*)
                    FROM paciente_profesional_asignacion
                    WHERE id_usuario_profesional = :id_usuario_profesional
                      AND id_paciente = :id_paciente
                      AND estado = 'Activo'
                      AND fecha_fin IS NULL";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario_profesional', $id_usuario_profesional, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            return ((int) $stmt->fetchColumn() > 0);
        } catch (PDOException $e) {
            error_log('Error en PacienteProfesionalAsignacion::profesionalTienePacienteActivo -> ' . $e->getMessage());
            return false;
        }
    }
}
