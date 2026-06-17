<?php

class MovimientoStock {
    private $conexion;

    public function __construct() {
        require_once dirname(__DIR__) . '/../config/database.php';
        $bd = new Conexion();
        $this->conexion = $bd->getConexion();
    }

    // Registrar un nuevo movimiento (entrada, salida o ajuste)
    public function registrarMovimiento($id_inventario, $tipo, $cantidad, $motivo = null, $id_usuario = null) {
        try {
            // Tipos permitidos: entrada, salida, ajuste
            $tiposValidos = ['entrada', 'salida', 'ajuste'];
            if (!in_array($tipo, $tiposValidos)) {
                throw new Exception("Tipo de movimiento no válido");
            }

            $sql = "INSERT INTO movimiento_stock (id_inventario, tipo, cantidad, motivo, id_usuario, fecha_movimiento)
                    VALUES (:id_inventario, :tipo, :cantidad, :motivo, :id_usuario, NOW())";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':id_usuario', $id_usuario);

            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // Obtener historial de movimientos de un inventario (lote)
    public function obtenerHistorialPorLote($id_inventario) {
        try {
            $sql = "SELECT ms.id_movimiento, ms.id_inventario, ms.tipo, ms.cantidad, ms.motivo, 
                           ms.fecha_movimiento, u.nombre as usuario
                    FROM movimiento_stock ms
                    LEFT JOIN usuario u ON ms.id_usuario = u.id_usuario
                    WHERE ms.id_inventario = :id_inventario
                    ORDER BY ms.fecha_movimiento DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Obtener historial de movimientos por veterinaria
    public function obtenerHistorialPorVeterinaria($id_veterinaria, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $sql = "SELECT ms.id_movimiento, ms.id_inventario, p.nombre as producto, ms.tipo, 
                           ms.cantidad, ms.motivo, ms.fecha_movimiento, u.nombre as usuario
                    FROM movimiento_stock ms
                    INNER JOIN inventario i ON ms.id_inventario = i.id_inventario
                    INNER JOIN producto p ON i.id_inventario = p.id_inventario
                    LEFT JOIN usuario u ON ms.id_usuario = u.id_usuario
                    WHERE i.id_veterinaria = :id_veterinaria";

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND DATE(ms.fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin";
            }

            $sql .= " ORDER BY ms.fecha_movimiento DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria);

            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Calcular cantidad actual de un inventario (lote) sumando todos los movimientos
    public function calcularCantidadActual($id_inventario) {
        try {
            $sql = "SELECT SUM(CASE 
                            WHEN tipo = 'entrada' THEN cantidad
                            WHEN tipo = 'salida' THEN -cantidad
                            WHEN tipo = 'ajuste' THEN cantidad
                        END) as cantidad_total
                    FROM movimiento_stock
                    WHERE id_inventario = :id_inventario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['cantidad_total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    // Obtener resumen de movimientos (entrada, salida, ajuste) en un período
    public function obtenerResumenPeriodo($id_veterinaria, $fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        ms.tipo,
                        COUNT(*) as total_movimientos,
                        SUM(ms.cantidad) as cantidad_total
                    FROM movimiento_stock ms
                    INNER JOIN inventario i ON ms.id_inventario = i.id_inventario
                    INNER JOIN producto p ON i.id_inventario = p.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                    AND DATE(ms.fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY ms.tipo";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Validar si hay stock suficiente para una operación
    public function validarDisponibilidad($id_inventario, $cantidad_requerida) {
        try {
            $cantidad_actual = $this->calcularCantidadActual($id_inventario);
            return $cantidad_actual >= $cantidad_requerida;
        } catch (Exception $e) {
            return false;
        }
    }

    // Eliminar un movimiento (para correcciones)
    public function eliminarMovimiento($id_movimiento) {
        try {
            $sql = "DELETE FROM movimiento_stock WHERE id_movimiento = :id_movimiento";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_movimiento', $id_movimiento);

            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
