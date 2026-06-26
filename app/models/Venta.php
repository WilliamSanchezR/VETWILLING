<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo Venta
 *
 * Maneja el registro de ventas y sus detalles (items).
 * Reutiliza métodos de Inventario para actualización de stock.
 */
class Venta
{
    private PDO $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Expone la conexión PDO para transacciones desde el controlador.
     */
    public function obtenerConexion(): PDO
    {
        return $this->conexion;
    }

    /**
     * Registra una venta completa con sus items en una transacción.
     * 
     * @param array $datosVenta Datos de la venta (id_cliente, descuento, impuesto)
     * @param array $items Arreglo de items vendidos (id_inventario, cantidad, precio_unitario)
     * @param int $id_usuario ID del usuario que registra la venta
     * @return array Resultado con id_venta generado o error
     */
    public function registrarVenta(array $datosVenta, array $items, int $id_usuario): array
    {
        try {
            $pdo = $this->conexion;
            $pdo->beginTransaction();

            // Calcular totales
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['cantidad'] * $item['precio_unitario'];
            }

            $descuento = (float) ($datosVenta['descuento'] ?? 0);
            $impuesto = (float) ($datosVenta['impuesto'] ?? 0);
            $total = $subtotal - $descuento + $impuesto;

            // Insertar venta principal
            $sqlVenta = "INSERT INTO venta 
                        (id_veterinaria, id_cliente, id_usuario, subtotal, descuento, impuesto, total, fecha_venta)
                        VALUES 
                        (:id_veterinaria, :id_cliente, :id_usuario, :subtotal, :descuento, :impuesto, :total, NOW())";

            $stmtVenta = $pdo->prepare($sqlVenta);
            $stmtVenta->bindParam(':id_veterinaria', $datosVenta['id_veterinaria'], PDO::PARAM_INT);
            $stmtVenta->bindParam(':id_cliente', $datosVenta['id_cliente'], PDO::PARAM_INT);
            $stmtVenta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtVenta->bindParam(':subtotal', $subtotal);
            $stmtVenta->bindParam(':descuento', $descuento);
            $stmtVenta->bindParam(':impuesto', $impuesto);
            $stmtVenta->bindParam(':total', $total);
            $stmtVenta->execute();

            $idVenta = (int) $pdo->lastInsertId();

            // Insertar items de la venta
            $sqlItem = "INSERT INTO detalle_venta 
                        (id_venta, id_inventario, cantidad, precio_unitario, subtotal)
                        VALUES 
                        (:id_venta, :id_inventario, :cantidad, :precio_unitario, :subtotal)";

            $stmtItem = $pdo->prepare($sqlItem);

            foreach ($items as $item) {
                $subtotalItem = $item['cantidad'] * $item['precio_unitario'];
                $stmtItem->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
                $stmtItem->bindParam(':id_inventario', $item['id_inventario'], PDO::PARAM_INT);
                $stmtItem->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtItem->bindParam(':precio_unitario', $item['precio_unitario']);
                $stmtItem->bindParam(':subtotal', $subtotalItem);
                $stmtItem->execute();
            }

            $pdo->commit();

            return ['exito' => true, 'id_venta' => $idVenta, 'mensaje' => 'Venta registrada correctamente'];

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en Venta::registrarVenta - ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al registrar la venta'];
        }
    }

    /**
     * Valida que un producto exista y esté activo.
     */
    public function validarProducto(int $id_inventario): bool
    {
        try {
            $sql = "SELECT id_inventario FROM inventario 
                    WHERE id_inventario = :id_inventario AND estado = 1 LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log('Error en Venta::validarProducto - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida que un cliente exista (si se proporciona).
     */
    public function validarCliente(?int $id_cliente): bool
    {
        if ($id_cliente === null || $id_cliente <= 0) {
            return true; // Cliente opcional
        }

        try {
            $sql = "SELECT id_propietario FROM propietario 
                    WHERE id_propietario = :id_cliente LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log('Error en Venta::validarCliente - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el precio de venta actual de un producto.
     */
    public function obtenerPrecioProducto(int $id_inventario): ?float
    {
        try {
            $sql = "SELECT precio_venta FROM producto 
                    WHERE id_inventario = :id_inventario LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (float) $row['precio_venta'] : null;
        } catch (PDOException $e) {
            error_log('Error en Venta::obtenerPrecioProducto - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista ventas de una veterinaria con filtros opcionales.
     */
    public function listarVentas(int $id_veterinaria, ?string $fecha_inicio = null, ?string $fecha_fin = null, ?int $id_cliente = null, ?string $producto = null, ?string $estado = 'todos'): array
    {
        try {
            $sql = "SELECT v.id_venta, v.id_cliente, v.subtotal, v.descuento, v.impuesto, v.total, 
                           v.fecha_venta, u.nombre as usuario, p.nombre as cliente
                    FROM venta v
                    LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
                    LEFT JOIN propietario p ON v.id_cliente = p.id_propietario
                    WHERE v.id_veterinaria = :id_veterinaria";

            $params = [':id_veterinaria' => $id_veterinaria];

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if ($id_cliente !== null && $id_cliente > 0) {
                $sql .= " AND v.id_cliente = :id_cliente";
                $params[':id_cliente'] = $id_cliente;
            }

            if (!empty($producto)) {
                // Sub-query: venta must have at least one item whose product name matches
                $sql .= " AND v.id_venta IN (
                            SELECT dv.id_venta FROM detalle_venta dv
                            INNER JOIN producto pr ON dv.id_inventario = pr.id_inventario
                            WHERE pr.nombre LIKE :producto)";
                $params[':producto'] = '%' . $producto . '%';
            }

            if (!empty($estado) && $estado !== 'todos') {
                $sql .= " AND COALESCE(v.estado, 'completada') = :estado";
                $params[':estado'] = $estado;
            }

            $sql .= " ORDER BY v.fecha_venta DESC";

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Venta::listarVentas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen agregado de ventas para el período (totales y conteo).
     */
    public function obtenerResumenVentas(int $id_veterinaria, ?string $fecha_inicio = null, ?string $fecha_fin = null, ?int $id_cliente = null, ?string $producto = null, ?string $estado = 'todos'): array
    {
        try {
            $sql = "SELECT COUNT(*) AS total_ventas,
                           COALESCE(SUM(v.total), 0) AS monto_total,
                           COALESCE(SUM(v.descuento), 0) AS total_descuentos,
                           COALESCE(SUM(v.impuesto), 0) AS total_impuestos,
                           COALESCE(AVG(v.total), 0) AS promedio_venta
                    FROM venta v
                    WHERE v.id_veterinaria = :id_veterinaria";

            $params = [':id_veterinaria' => $id_veterinaria];

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Venta::obtenerResumenVentas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista de propietarios que han realizado compras en esta veterinaria.
     */
    public function listarClientesConVentas(int $id_veterinaria): array
    {
        try {
            $sql = "SELECT DISTINCT p.id_propietario, p.nombre
                    FROM venta v
                    INNER JOIN propietario p ON v.id_cliente = p.id_propietario
                    WHERE v.id_veterinaria = :id_veterinaria
                    ORDER BY p.nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Venta::listarClientesConVentas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene detalles de una venta específica con sus items.
     */
    public function obtenerVentaPorId(int $id_venta): ?array
    {
        try {
            $sql = "SELECT v.*, u.nombre as usuario, p.nombre as cliente
                    FROM venta v
                    LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
                    LEFT JOIN propietario p ON v.id_cliente = p.id_propietario
                    WHERE v.id_venta = :id_venta LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
            $stmt->execute();

            $venta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                return null;
            }

            // Obtener items
            $sqlItems = "SELECT vi.*, prod.nombre as producto, i.numero_lote
                         FROM detalle_venta vi
                         INNER JOIN inventario i ON vi.id_inventario = i.id_inventario
                         INNER JOIN producto prod ON i.id_inventario = prod.id_inventario
                         WHERE vi.id_venta = :id_venta";

            $stmtItems = $this->conexion->prepare($sqlItems);
            $stmtItems->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
            $stmtItems->execute();

            $venta['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            return $venta;

        } catch (PDOException $e) {
            error_log('Error en Venta::obtenerVentaPorId - ' . $e->getMessage());
            return null;
        }
    }
}
