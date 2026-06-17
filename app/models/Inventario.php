<?php

// Importamos la clase de conexión a la base de datos
require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo Inventario
 *
 * Maneja todas las operaciones con las tablas 'inventario' y 'producto'.
 * Patrón igual al de Laboratorio.php: la clase crea su propia conexión.
 *
 * Cubre:
 *   RFS 44 — Registro de productos     (crearLote, crearProducto)
 *   RFS 45 — Gestión de stock          (aumentarStock, reducirStock)
 *   RFS 46 — Alertas de inventario     (obtenerLotesStockBajo, contarAlertasStock)
 *   RFS 47 — Modificación              (actualizarLote, actualizarProducto)
 *   RFS 48 — Reportes y análisis       (obtenerProductosProximosAVencer, obtenerResumen)
 *   RFS 49 — Eliminación               (eliminarLote — soft-delete)
 */
class Inventario
{
    // Guardamos la conexión PDO para usarla en todos los métodos
    private PDO $conexion;

    /**
     * El constructor se llama automáticamente al hacer: new Inventario()
     * Aquí creamos la conexión a la base de datos una sola vez.
     */
    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Expone la conexión PDO para que el controlador pueda
     * abrir transacciones (beginTransaction / commit / rollBack).
     */
    public function obtenerConexion(): PDO
    {
        return $this->conexion;
    }

    // ============================================================
    // GRUPO A — CREAR (RFS 44)
    // Insertar un lote nuevo y su producto en dos pasos:
    //   Paso 1: crearLote()     → inserta en tabla 'inventario'
    //   Paso 2: crearProducto()  → inserta en tabla 'producto'
    // El controlador envuelve ambos en una transacción única.
    // ============================================================

    /**
     * Inserta un nuevo lote en la tabla 'inventario'.
     * Devuelve el ID generado para usarlo en crearProducto().
     *
     * @param array $datos  Debe contener:
     *                      - id_veterinaria         (int)
     *                      - cantidad               (int)
     *                      - categoria              (string)
     *                      - numero_lote            (string)
     *                      - stock_minimo           (int)
     *                      - detalle_almacenamiento (string)
     * @return int           ID del lote creado
     * @throws PDOException  Si la inserción falla (el controlador hace rollBack)
     */
    public function crearLote(array $datos): int
    {
        // Sentencia preparada: los :nombre se reemplazan de forma segura
        $sql = "INSERT INTO inventario
                    (id_veterinaria, cantidad, categoria, numero_lote, stock_minimo, detalle_almacenamiento)
                VALUES
                    (:id_veterinaria, :cantidad, :categoria, :numero_lote, :stock_minimo, :detalle_almacenamiento)";

        $stmt = $this->conexion->prepare($sql);

        // Guardamos en variables locales porque bindParam exige referencias
        $idVeterinaria         = (int) $datos['id_veterinaria'];
        $cantidad              = (int) $datos['cantidad'];
        $categoria             = $datos['categoria'];
        $numeroLote            = $datos['numero_lote'];
        $stockMinimo           = (int) $datos['stock_minimo'];
        $detalleAlmacenamiento = $datos['detalle_almacenamiento'];

        $stmt->bindParam(':id_veterinaria',         $idVeterinaria,         PDO::PARAM_INT);
        $stmt->bindParam(':cantidad',               $cantidad,              PDO::PARAM_INT);
        $stmt->bindParam(':categoria',              $categoria,             PDO::PARAM_STR);
        $stmt->bindParam(':numero_lote',           $numeroLote,            PDO::PARAM_STR);
        $stmt->bindParam(':stock_minimo',          $stockMinimo,           PDO::PARAM_INT);
        $stmt->bindParam(':detalle_almacenamiento', $detalleAlmacenamiento, PDO::PARAM_STR);

        $stmt->execute();

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Inserta el producto descriptivo asociado a un lote existente.
     * Se llama justo después de crearLote() dentro de la misma transacción.
     *
     * @param array $datos  Debe contener:
     *                      - id_inventario     (int)
     *                      - nombre            (string)
     *                      - descripcion       (string)
     *                      - proveedor         (string)
     *                      - precio            (float)
     *                      - fecha_vencimiento (string 'Y-m-d')
     *                      - imagen            (string|null)
     * @return bool
     * @throws PDOException  Si la inserción falla (el controlador hace rollBack)
     */
    public function crearProducto(array $datos): bool
    {
        $sql = "INSERT INTO producto
                    (id_inventario, nombre, descripcion, proveedor, precio, precio_venta, costo_mayorista, fecha_vencimiento, imagen)
                VALUES
                    (:id_inventario, :nombre, :descripcion, :proveedor, :precio, :precio_venta, :costo_mayorista, :fecha_vencimiento, :imagen)";

        $stmt = $this->conexion->prepare($sql);

        // Precio de venta y costo mayorista: si no vienen, usamos el precio base
        $precioVenta    = isset($datos['precio_venta'])    && $datos['precio_venta']    !== ''
            ? $datos['precio_venta']
            : $datos['precio'];
        $costoMayorista = isset($datos['costo_mayorista']) && $datos['costo_mayorista'] !== ''
            ? $datos['costo_mayorista']
            : $datos['precio'];

        $imagen = !empty($datos['imagen']) ? $datos['imagen'] : null;

        $idInventario    = (int) $datos['id_inventario'];
        $nombre          = $datos['nombre'];
        $descripcion     = $datos['descripcion'];
        $proveedor       = $datos['proveedor'];
        $precio          = $datos['precio'];
        $fechaVencimiento = $datos['fecha_vencimiento'];

        $stmt->bindParam(':id_inventario',     $idInventario,     PDO::PARAM_INT);
        $stmt->bindParam(':nombre',            $nombre,           PDO::PARAM_STR);
        $stmt->bindParam(':descripcion',       $descripcion,      PDO::PARAM_STR);
        $stmt->bindParam(':proveedor',         $proveedor,        PDO::PARAM_STR);
        $stmt->bindParam(':precio',            $precio);
        $stmt->bindParam(':precio_venta',      $precioVenta);
        $stmt->bindParam(':costo_mayorista',   $costoMayorista);
        $stmt->bindParam(':fecha_vencimiento', $fechaVencimiento, PDO::PARAM_STR);
        $stmt->bindParam(':imagen',            $imagen,           PDO::PARAM_STR);

        return $stmt->execute();
    }

    // ============================================================
    // GRUPO B — LEER (RFS 44)
    // Consultas para la lista principal y para el formulario de edición.
    // ============================================================

    /**
     * Lista todos los lotes activos de una veterinaria
     * junto con los datos descriptivos de su producto.
     *
     * Esta es la query principal que alimenta listaInventario.php.
     * El JOIN combina las dos tablas usando id_inventario como puente.
     *
     * @param int $id_veterinaria  ID de la veterinaria de la sesión
     * @return array               Arreglo de filas; vacío si no hay datos
     */
    public function listarInventario(int $id_veterinaria): array
    {
        try {
            $sql = "SELECT
                        i.id_inventario,
                        i.cantidad,
                        i.categoria,
                        i.numero_lote,
                        i.stock_minimo,
                        -- Alerta visual: 1 si el stock está en nivel crítico, 0 si está bien
                        IF(i.cantidad <= i.stock_minimo, 1, 0) AS alerta_stock,
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.precio,
                        p.fecha_vencimiento,
                        p.imagen,
                        -- Días restantes hasta vencimiento (negativo = ya venció)
                        DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_para_vencer
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                      AND i.estado = 1
                    ORDER BY i.creado_en DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            // fetchAll devuelve todas las filas como un arreglo de arreglos asociativos
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Inventario::listarInventario - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca un lote por su ID y devuelve todos sus campos
     * combinados con los campos de su producto (JOIN).
     * Usado por el formulario de edición para pre-llenar los campos.
     *
     * @param int $id_inventario  ID del lote a editar
     * @return array|false        Fila con los datos, o false si no existe
     */
    public function obtenerLotePorId(int $id_inventario)
    {
        try {
            $sql = "SELECT
                        i.id_inventario,
                        i.id_veterinaria,
                        i.cantidad,
                        i.categoria,
                        i.numero_lote,
                        i.stock_minimo,
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.precio,
                        p.precio_venta,
                        p.costo_mayorista,
                        p.fecha_vencimiento,
                        p.imagen
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE i.id_inventario = :id_inventario
                      AND i.estado = 1
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->execute();

            // fetch devuelve una sola fila como arreglo asociativo, o false si no hay
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Error en Inventario::obtenerLotePorId - ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // GRUPO C — ACTUALIZAR (RFS 47)
    // Se actualiza el lote (tabla inventario) Y el producto
    // (tabla producto) en métodos separados.
    // El controlador llama a los dos en secuencia.
    // ============================================================

    /**
     * Actualiza los campos del lote: cantidad, categoría,
     * número de lote y stock mínimo.
     *
     * @param int   $id_inventario  ID del lote a modificar
     * @param array $datos          Campos nuevos del lote
     * @return bool                 true si se actualizó, false si falló
     */
    public function actualizarLote(int $id_inventario, array $datos): bool
    {
        try {
            $sql = "UPDATE inventario
                    SET cantidad       = :cantidad,
                        categoria      = :categoria,
                        numero_lote    = :numero_lote,
                        stock_minimo   = :stock_minimo
                    WHERE id_inventario = :id_inventario
                      AND estado = 1";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':cantidad',      $datos['cantidad'],     PDO::PARAM_INT);
            $stmt->bindParam(':categoria',     $datos['categoria'],    PDO::PARAM_STR);
            $stmt->bindParam(':numero_lote',   $datos['numero_lote'],  PDO::PARAM_STR);
            $stmt->bindParam(':stock_minimo',  $datos['stock_minimo'], PDO::PARAM_INT);
            $stmt->bindParam(':id_inventario', $id_inventario,         PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log('Error en Inventario::actualizarLote - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los campos descriptivos del producto:
     * nombre, descripción, precio y fecha de vencimiento.
     *
     * @param int   $id_producto  ID del producto a modificar
     * @param array $datos        Campos nuevos del producto
     * @return bool
     */
    public function actualizarProducto(int $id_producto, array $datos): bool
    {
        try {
            $sql = "UPDATE producto
                    SET nombre            = :nombre,
                        descripcion       = :descripcion,
                        precio            = :precio,
                        precio_venta      = :precio_venta,
                        costo_mayorista   = :costo_mayorista,
                        fecha_vencimiento = :fecha_vencimiento
                    WHERE id_producto = :id_producto";

            $stmt = $this->conexion->prepare($sql);

            $fechaVenc      = !empty($datos['fecha_vencimiento']) ? $datos['fecha_vencimiento'] : null;
            $precioVenta    = isset($datos['precio_venta'])    && $datos['precio_venta']    !== '' ? $datos['precio_venta']    : $datos['precio'];
            $costoMayorista = isset($datos['costo_mayorista']) && $datos['costo_mayorista'] !== '' ? $datos['costo_mayorista'] : $datos['precio'];

            $stmt->bindParam(':nombre',            $datos['nombre'],      PDO::PARAM_STR);
            $stmt->bindParam(':descripcion',       $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':precio',            $datos['precio']);
            $stmt->bindParam(':precio_venta',      $precioVenta);
            $stmt->bindParam(':costo_mayorista',   $costoMayorista);
            $stmt->bindParam(':fecha_vencimiento', $fechaVenc,            PDO::PARAM_STR);
            $stmt->bindParam(':id_producto',       $id_producto,          PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log('Error en Inventario::actualizarProducto - ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // GRUPO D — ELIMINAR (soft-delete) (RFS 49)
    // No borramos el registro físicamente.
    // Solo cambiamos estado = 0 en la tabla 'inventario'.
    // La FK ON DELETE CASCADE eliminaría el producto si borráramos
    // físicamente, pero con soft-delete conservamos todo el historial.
    // ============================================================

    /**
     * Marca el lote como eliminado (estado = 0).
     * El producto asociado queda oculto porque listarInventario()
     * filtra con WHERE estado = 1.
     *
     * @param int $id_inventario   ID del lote a eliminar
     * @param int $id_veterinaria  Seguridad extra: solo borra si pertenece a esta veterinaria
     * @return bool
     */
    public function eliminarLote(int $id_inventario, int $id_veterinaria): bool
    {
        try {
            // El filtro id_veterinaria impide que un representante elimine
            // lotes de otra veterinaria manipulando el ID en el formulario
            $sql = "UPDATE inventario
                    SET estado = 0
                    WHERE id_inventario  = :id_inventario
                      AND id_veterinaria = :id_veterinaria";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario',  $id_inventario,  PDO::PARAM_INT);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);

            $stmt->execute();

            // rowCount() devuelve cuántas filas se modificaron
            // Si es 0, el ID no existía o no pertenecía a esta veterinaria
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log('Error en Inventario::eliminarLote - ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // GRUPO E — ALERTAS DE STOCK BAJO (RFS 46)
    // Detecta lotes cuyo stock actual está en nivel crítico.
    // ============================================================

    /**
     * Devuelve los lotes donde cantidad <= stock_minimo.
     * Alimenta el panel de alertas del dashboard del representante.
     *
     * @param int $id_veterinaria
     * @return array
     */
    public function obtenerLotesStockBajo(int $id_veterinaria): array
    {
        try {
            $sql = "SELECT
                        i.id_inventario,
                        i.cantidad,
                        i.stock_minimo,
                        i.categoria,
                        i.numero_lote,
                        p.nombre,
                        p.precio
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                      AND i.estado = 1
                      AND i.cantidad <= i.stock_minimo
                    ORDER BY i.cantidad ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Inventario::obtenerLotesStockBajo - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta cuántos lotes están por debajo del mínimo.
     * Usado como badge/contador en el menú lateral.
     *
     * @param int $id_veterinaria
     * @return int
     */
    public function contarAlertasStock(int $id_veterinaria): int
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM inventario
                    WHERE id_veterinaria = :id_veterinaria
                      AND estado = 1
                      AND cantidad <= stock_minimo";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($row['total'] ?? 0);

        } catch (PDOException $e) {
            error_log('Error en Inventario::contarAlertasStock - ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // GRUPO F — REPORTES Y ANÁLISIS (RFS 48)
    // Consultas para el módulo de reportes del representante.
    // Nota: Reportes.php ya tiene métodos similares para el admin
    // global. Estos son exclusivos del representante de su propia
    // veterinaria y usan id_veterinaria directo (no via usuario).
    // ============================================================

    /**
     * Devuelve lotes con fecha_vencimiento dentro de los próximos $dias días.
     * Incluye también los ya vencidos (dias_para_vencer negativo).
     *
     * @param int $id_veterinaria
     * @param int $dias           Horizonte de alerta (default 30)
     * @return array
     */
    public function obtenerProductosProximosAVencer(int $id_veterinaria, int $dias = 30): array
    {
        try {
            $sql = "SELECT
                        p.nombre,
                        p.descripcion,
                        p.fecha_vencimiento,
                        DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_para_vencer,
                        i.cantidad,
                        i.categoria,
                        i.numero_lote,
                        i.id_inventario
                    FROM producto p
                    INNER JOIN inventario i ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                      AND i.estado = 1
                      AND p.fecha_vencimiento IS NOT NULL
                      AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    ORDER BY p.fecha_vencimiento ASC
                    LIMIT 50";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->bindParam(':dias',           $dias,           PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log('Error en Inventario::obtenerProductosProximosAVencer - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen general del inventario de la veterinaria:
     * total de productos, vigentes, por vencer y vencidos.
     * Misma estructura de claves que usa Reportes.php para consistencia.
     *
     * @param int $id_veterinaria
     * @return array
     */
    public function obtenerResumen(int $id_veterinaria): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) AS total_productos,
                        SUM(CASE WHEN p.fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) AS vencidos,
                        SUM(CASE WHEN p.fecha_vencimiento BETWEEN CURDATE()
                                      AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS por_vencer,
                        SUM(CASE WHEN p.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                      OR p.fecha_vencimiento IS NULL THEN 1 ELSE 0 END) AS vigentes,
                        SUM(COALESCE(i.cantidad, 0)) AS cantidad_total
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                      AND i.estado = 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total_productos' => (int) ($row['total_productos'] ?? 0),
                'vencidos'        => (int) ($row['vencidos']        ?? 0),
                'por_vencer'      => (int) ($row['por_vencer']      ?? 0),
                'vigentes'        => (int) ($row['vigentes']        ?? 0),
                'cantidad_total'  => (int) ($row['cantidad_total']  ?? 0),
            ];

        } catch (PDOException $e) {
            error_log('Error en Inventario::obtenerResumen - ' . $e->getMessage());
            return [
                'total_productos' => 0, 'vencidos' => 0,
                'por_vencer' => 0, 'vigentes' => 0, 'cantidad_total' => 0,
            ];
        }
    }

    // ============================================================
    // GRUPO G — MOVIMIENTOS DE STOCK (Paso 2)
    // Métodos para decrementar/incrementar stock y validar disponibilidad.
    // ============================================================

    /**
     * Disminuye la cantidad de un lote (inventario).
     * Se usa cuando se registra una venta.
     *
     * @param int    $id_inventario ID del lote a reducir
     * @param int    $cantidad      Cantidad a restar
     * @param string $motivo        Razón de la salida (ej: 'venta', 'ajuste')
     * @param int    $id_usuario    ID del usuario que registra la operación
     * @return bool                 true si se actualizó, false si falló
     */
    public function decrementarStock(int $id_inventario, int $cantidad, string $motivo = 'venta', ?int $id_usuario = null): bool
    {
        try {
            // Actualizar cantidad en inventario
            $sql = "UPDATE inventario SET cantidad = cantidad - :cantidad WHERE id_inventario = :id_inventario AND cantidad >= :cantidad";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);

            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error en Inventario::decrementarStock - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aumenta la cantidad de un lote (inventario).
     * Se usa cuando se registra una entrada de stock.
     *
     * @param int    $id_inventario ID del lote a aumentar
     * @param int    $cantidad      Cantidad a sumar
     * @param string $motivo        Razón de la entrada (ej: 'compra', 'devolución')
     * @param int    $id_usuario    ID del usuario que registra la operación
     * @return bool                 true si se actualizó, false si falló
     */
    public function incrementarStock(int $id_inventario, int $cantidad, string $motivo = 'compra', ?int $id_usuario = null): bool
    {
        try {
            // Actualizar cantidad en inventario
            $sql = "UPDATE inventario SET cantidad = cantidad + :cantidad WHERE id_inventario = :id_inventario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);

            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error en Inventario::incrementarStock - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida si hay stock suficiente en un lote.
     * Se usa antes de confirmar una venta.
     *
     * @param int $id_inventario ID del lote a verificar
     * @param int $cantidad      Cantidad requerida
     * @return bool              true si hay stock suficiente, false si no
     */
    public function validarDisponibilidad(int $id_inventario, int $cantidad): bool
    {
        try {
            $sql = "SELECT cantidad FROM inventario WHERE id_inventario = :id_inventario AND estado = 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_inventario', $id_inventario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return false; // Lote no existe o está inactivo
            }

            return (int) $row['cantidad'] >= $cantidad;
        } catch (PDOException $e) {
            error_log('Error en Inventario::validarDisponibilidad - ' . $e->getMessage());
            return false;
        }
    }
}
