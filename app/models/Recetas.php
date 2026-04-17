<?php

require_once __DIR__ . '/../../config/database.php';

class Recetas
{
    private PDO $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    public function obtenerEstadisticas(int $idUsuario): array
    {
        try {
            if (!$this->existeTablaHistorial()) {
                return [
                    'total_recetas' => 0,
                    'pacientes_atendidos' => 0,
                    'emitidas_hoy' => 0,
                    'impresiones_hoy' => 0,
                ];
            }

            $sql = "SELECT
                        COUNT(*) AS total_recetas,
                        COUNT(DISTINCT h.id_paciente) AS pacientes_atendidos,
                        SUM(CASE WHEN DATE(h.fecha_atencion) = CURDATE() THEN 1 ELSE 0 END) AS emitidas_hoy
                    FROM historial_clinico_paciente h
                    WHERE h.id_usuario_profesional = :id_usuario
                    AND (
                        NULLIF(TRIM(COALESCE(h.medicacion_recetada, '')), '') IS NOT NULL
                        OR NULLIF(TRIM(COALESCE(h.tratamientos_aplicados, '')), '') IS NOT NULL
                    )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total_recetas' => (int) ($row['total_recetas'] ?? 0),
                'pacientes_atendidos' => (int) ($row['pacientes_atendidos'] ?? 0),
                'emitidas_hoy' => (int) ($row['emitidas_hoy'] ?? 0),
                // Aun no hay trazabilidad de impresiones en BD.
                'impresiones_hoy' => 0,
            ];
        } catch (PDOException $e) {
            error_log('Error en Recetas::obtenerEstadisticas - ' . $e->getMessage());
            return [
                'total_recetas' => 0,
                'pacientes_atendidos' => 0,
                'emitidas_hoy' => 0,
                'impresiones_hoy' => 0,
            ];
        }
    }

    public function obtenerPacientesAsignados(int $idUsuario): array
    {
        try {
            $sql = "SELECT
                        p.id_paciente,
                        p.nombre,
                        p.especie,
                        p.raza,
                        p.sexo,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS nombrePropietario,
                        COALESCE(pr.numero_documento, '') AS documento,
                        COALESCE(pr.telefono, '') AS telefono,
                        COALESCE(
                            DATE_FORMAT(MAX(a.fecha_hora), '%Y-%m-%d'),
                            DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                        ) AS fecha
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    LEFT JOIN agendamiento a
                        ON a.id_paciente = p.id_paciente
                        AND a.id_usuario = ppa.id_usuario_profesional
                    WHERE ppa.id_usuario_profesional = :id_usuario
                    AND ppa.estado = 'Activo'
                    AND ppa.fecha_fin IS NULL
                    GROUP BY
                        p.id_paciente,
                        p.nombre,
                        p.especie,
                        p.raza,
                        p.sexo,
                        pr.nombres,
                        pr.apellidos,
                        pr.numero_documento,
                        pr.telefono
                    ORDER BY p.nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Recetas::obtenerPacientesAsignados - ' . $e->getMessage());
            return [];
        }
    }

    private function existeTablaHistorial(): bool
    {
        $sql = "SELECT COUNT(*)
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'historial_clinico_paciente'";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return ((int) $stmt->fetchColumn()) > 0;
    }
}
