<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo HistorialCompras
 * Gestiona el registro y consulta del historial de compras de clientes
 * RFS 55: Registro de Clientes - Historial de Compras
 */
class HistorialCompras
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /* ===============================
        REGISTRAR COMPRA
    =============================== */
    
    /**
     * Registra una nueva compra en el historial
     * @param array $data Array con: id_propietario, id_veterinaria, monto, descripcion, tipo_compra, referencia_mercadopago
     * @return int|false ID de compra o false si falla
     */
    public function registrarCompra($data)
    {
        try {
            if (empty($data['id_propietario']) || empty($data['id_veterinaria']) || empty($data['monto'])) {
                error_log("❌ Error en HistorialCompras::registrarCompra - Datos incompletos");
                return false;
            }

            $this->conexion->beginTransaction();

            $sql = "INSERT INTO historial_compras 
                    (id_propietario, id_veterinaria, monto, descripcion, tipo_compra, referencia_mercadopago, estado)
                    VALUES 
                    (:id_propietario, :id_veterinaria, :monto, :descripcion, :tipo_compra, :referencia_mercadopago, 'completado')";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_propietario', $data['id_propietario'], PDO::PARAM_INT);
            $query->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
            $query->bindParam(':monto', $data['monto']);
            $query->bindParam(':descripcion', $data['descripcion'] ?? null);
            $query->bindParam(':tipo_compra', $data['tipo_compra'] ?? 'venta');
            $query->bindParam(':referencia_mercadopago', $data['referencia_mercadopago'] ?? null);

            if (!$query->execute()) {
                $this->conexion->rollBack();
                error_log("❌ Error al insertar compra en historial");
                return false;
            }

            $id_compra = $this->conexion->lastInsertId();
            $this->conexion->commit();

            error_log("✅ Compra registrada. ID: " . $id_compra . " - Propietario: " . $data['id_propietario']);
            return $id_compra;

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("❌ Error en HistorialCompras::registrarCompra → " . $e->getMessage());
            return false;
        }
    }


    /* ===============================
        OBTENER HISTORIAL
    =============================== */
    
    /**
     * Obtiene el historial de compras de un cliente
     * 
     * @param int $id_propietario
     * @param int $id_veterinaria (opcional)
     * @param int $limite (default: 50)
     * @return array
     */
    public function obtenerHistorialCliente($id_propietario, $id_veterinaria = null, $limite = 50)
    {
        try {
            $sql = "SELECT * FROM historial_compras 
                    WHERE id_propietario = :id_propietario";

            if ($id_veterinaria) {
                $sql .= " AND id_veterinaria = :id_veterinaria";
            }

            $sql .= " ORDER BY fecha_compra DESC LIMIT " . intval($limite);

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);

            if ($id_veterinaria) {
                $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en HistorialCompras::obtenerHistorialCliente → " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
        ESTADÍSTICAS
    =============================== */
    
    /**
     * Obtiene estadísticas de ventas por veterinaria
     */
    public function obtenerEstadisticasVeterinaria($id_veterinaria)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_compras,
                        SUM(monto) as monto_total,
                        AVG(monto) as monto_promedio,
                        COUNT(DISTINCT id_propietario) as clientes_unicos
                    FROM historial_compras
                    WHERE id_veterinaria = :id_veterinaria
                    AND estado = 'completado'";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $query->execute();

            return $query->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en HistorialCompras::obtenerEstadisticasVeterinaria → " . $e->getMessage());
            return null;
        }
    }


    /* ===============================
        UTILIDADES PRIVADAS
    =============================== */
    
    private function actualizarResumenCompras($id_propietario, $id_veterinaria, $monto)
    {
        try {
            $sql = "INSERT INTO resumen_compras_propietario 
                    (id_propietario, id_veterinaria, total_compras, numero_compras, ultima_compra)
                    VALUES 
                    (:id_propietario, :id_veterinaria, :monto, 1, NOW())
                    ON DUPLICATE KEY UPDATE
                        total_compras = total_compras + :monto,
                        numero_compras = numero_compras + 1,
                        ultima_compra = NOW()";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $query->bindParam(':monto', $monto);

            return $query->execute();

        } catch (PDOException $e) {
            error_log("Error al actualizar resumen: " . $e->getMessage());
            return false;
        }
    }

    private function existeProcedimiento($nombre_procedimiento)
    {
        try {
            $sql = "SELECT COUNT(*) as existe FROM information_schema.ROUTINES 
                    WHERE ROUTINE_NAME = :nombre 
                    AND ROUTINE_SCHEMA = DATABASE()";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':nombre', $nombre_procedimiento);
            $query->execute();

            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            return $resultado['existe'] > 0;

        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
