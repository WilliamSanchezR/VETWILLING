<?php

/**
 * ============================================================
 * MODELO: AuditoriaEliminacion
 * Descripción: Gestión de auditoría de eliminación de mascotas
 * RFS 29: Eliminación de registros del animal
 * ============================================================
 */

require_once __DIR__ . '/../../config/database.php';

class AuditoriaEliminacion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * REGISTRAR ELIMINACIÓN EN AUDITORÍA
     * Crea un registro de cada eliminación de mascota
     */
    public function registrarEliminacion($data)
    {
        try {
            // Validar datos requeridos
            if (empty($data['id_paciente']) || empty($data['id_usuario'])) {
                error_log("❌ AuditoriaEliminacion: Datos incompletos");
                return false;
            }

            $sql = "INSERT INTO auditoria_eliminacion_mascotas 
                    (id_paciente, nombre_mascota, id_usuario, nombre_usuario, rol_usuario, 
                     motivo_eliminacion, citas_canceladas, tratamientos_cancelados, estado)
                    VALUES 
                    (:id_paciente, :nombre_mascota, :id_usuario, :nombre_usuario, :rol_usuario, 
                     :motivo_eliminacion, :citas_canceladas, :tratamientos_cancelados, 'completado')";

            $stmt = $this->conexion->prepare($sql);

            // Bindear parámetros
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_mascota', $data['nombre_mascota'] ?? '', PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_usuario', $data['nombre_usuario'] ?? '', PDO::PARAM_STR);
            $stmt->bindParam(':rol_usuario', $data['rol_usuario'] ?? '', PDO::PARAM_STR);
            $stmt->bindParam(':motivo_eliminacion', $data['motivo_eliminacion'] ?? '', PDO::PARAM_STR);
            $stmt->bindParam(':citas_canceladas', $data['citas_canceladas'] ?? 0, PDO::PARAM_INT);
            $stmt->bindParam(':tratamientos_cancelados', $data['tratamientos_cancelados'] ?? 0, PDO::PARAM_INT);

            $resultado = $stmt->execute();

            if ($resultado) {
                $id_auditoria = $this->conexion->lastInsertId();
                error_log("✅ Auditoría registrada ID: " . $id_auditoria);
                return $id_auditoria;
            }

            return false;
        } catch (PDOException $e) {
            error_log("❌ Error AuditoriaEliminacion::registrarEliminacion → " . $e->getMessage());
            return false;
        }
    }

    /**
     * OBTENER HISTORIAL DE ELIMINACIONES
     */
    public function obtenerHistorial($id_paciente = null, $limite = 50)
    {
        try {
            $sql = "SELECT * FROM auditoria_eliminacion_mascotas 
                    WHERE 1=1";
            $params = [];

            if (!empty($id_paciente)) {
                $sql .= " AND id_paciente = :id_paciente";
                $params['id_paciente'] = $id_paciente;
            }

            $sql .= " ORDER BY fecha_eliminacion DESC LIMIT :limite";
            $params['limite'] = $limite;

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam(':' . $key, $params[$key], $type);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error AuditoriaEliminacion::obtenerHistorial → " . $e->getMessage());
            return [];
        }
    }

    /**
     * OBTENER AUDITORÍA POR ID
     */
    public function obtenerAuditoria($id_auditoria)
    {
        try {
            $sql = "SELECT * FROM auditoria_eliminacion_mascotas WHERE id_auditoria = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_auditoria, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error AuditoriaEliminacion::obtenerAuditoria → " . $e->getMessage());
            return null;
        }
    }

    /**
     * CONTAR ELIMINACIONES POR USUARIO
     */
    public function contarEliminacionesPorUsuario($id_usuario)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM auditoria_eliminacion_mascotas 
                    WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("❌ Error AuditoriaEliminacion::contarEliminacionesPorUsuario → " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ESTADÍSTICAS DE ELIMINACIONES
     */
    public function obtenerEstadisticas()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_eliminaciones,
                        SUM(citas_canceladas) as total_citas_canceladas,
                        SUM(tratamientos_cancelados) as total_tratamientos_cancelados,
                        COUNT(DISTINCT id_usuario) as usuarios_que_eliminaron
                    FROM auditoria_eliminacion_mascotas";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error AuditoriaEliminacion::obtenerEstadisticas → " . $e->getMessage());
            return null;
        }
    }
}
