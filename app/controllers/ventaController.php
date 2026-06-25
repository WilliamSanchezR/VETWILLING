<?php

// ── Dependencias ──────────────────────────────────────────────────────────────
require_once __DIR__ . '/../helpers/session_representante.php';
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/MovimientoStock.php';

// ── Detectar método HTTP ─────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'registrar') {
            registrarVenta();
        }
        break;
    case 'GET':
        // Reservado para futuras APIs JSON
        break;
    default:
        http_response_code(405);
        echo 'Método no permitido';
        break;
}

// =============================================================================
// FUNCIÓN: registrarVenta
// Registra una venta completa con validaciones y actualización de stock.
// Usa transacción BD para garantizar integridad de datos.
// =============================================================================
function registrarVenta(): void
{
    $urlVenta = BASE_URL . '/representante/registro-venta';

    // ── 1. Capturar datos de la venta ────────────────────────────────────────
    $id_veterinaria = (int) ($_SESSION['user']['id_veterinaria'] ?? 0);
    $id_cliente = !empty($_POST['id_cliente']) ? (int) $_POST['id_cliente'] : null;
    $descuento = (float) ($_POST['descuento'] ?? 0);
    $impuesto = (float) ($_POST['impuesto'] ?? 0);

    // ── 2. Capturar items de la venta ─────────────────────────────────────────
    $items = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $id_inventario = (int) ($item['id_inventario'] ?? 0);
            $cantidad = (int) ($item['cantidad'] ?? 0);
            $precio_unitario = (float) ($item['precio_unitario'] ?? 0);

            if ($id_inventario > 0 && $cantidad > 0 && $precio_unitario > 0) {
                $items[] = [
                    'id_inventario' => $id_inventario,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_unitario
                ];
            }
        }
    }

    // ── 3. Validaciones ───────────────────────────────────────────────────────
    if ($id_veterinaria <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se pudo identificar la veterinaria.', $urlVenta);
        exit();
    }

    if (empty($items)) {
        mostrarSweetAlert('error', 'Carrito vacío', 'Debes agregar al menos un producto a la venta.', $urlVenta);
        exit();
    }

    if ($descuento < 0 || $impuesto < 0) {
        mostrarSweetAlert('error', 'Valores inválidos', 'Descuento e impuesto no pueden ser negativos.', $urlVenta);
        exit();
    }

    // ── 4. Validar productos y stock ───────────────────────────────────────────
    $modelVenta = new Venta();
    $modelInv = new Inventario();
    $id_usuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);

    foreach ($items as $item) {
        // Validar que el producto exista
        if (!$modelVenta->validarProducto($item['id_inventario'])) {
            mostrarSweetAlert('error', 'Producto inválido', 'Uno de los productos no existe o está inactivo.', $urlVenta);
            exit();
        }

        // Validar stock disponible
        if (!$modelInv->validarDisponibilidad($item['id_inventario'], $item['cantidad'])) {
            mostrarSweetAlert('error', 'Stock insuficiente', 'No hay stock suficiente para uno de los productos.', $urlVenta);
            exit();
        }
    }

    // Validar cliente si se proporcionó
    if ($id_cliente > 0 && !$modelVenta->validarCliente($id_cliente)) {
        mostrarSweetAlert('error', 'Cliente inválido', 'El cliente seleccionado no existe.', $urlVenta);
        exit();
    }

    // ── 5. Procesar venta con transacción ─────────────────────────────────────
    $datosVenta = [
        'id_veterinaria' => $id_veterinaria,
        'id_cliente' => $id_cliente,
        'descuento' => $descuento,
        'impuesto' => $impuesto
    ];

    $pdo = $modelVenta->obtenerConexion();

    try {
        $pdo->beginTransaction();

        // Registrar venta y items
        $resultado = $modelVenta->registrarVenta($datosVenta, $items, $id_usuario);

        if (!$resultado['exito']) {
            throw new RuntimeException($resultado['mensaje']);
        }

        $idVenta = $resultado['id_venta'];

        // Actualizar inventario y registrar movimientos de stock
        $modelMov = new MovimientoStock();

        foreach ($items as $item) {
            // Decrementar stock
            if (!$modelInv->decrementarStock($item['id_inventario'], $item['cantidad'], 'venta', $id_usuario)) {
                throw new RuntimeException('Error al actualizar stock del producto.');
            }

            // Registrar movimiento de stock
            $modelMov->registrarMovimiento(
                $item['id_inventario'],
                'salida',
                $item['cantidad'],
                'Venta #' . $idVenta,
                $id_usuario
            );
        }

        $pdo->commit();

        mostrarSweetAlert(
            'success',
            '¡Venta registrada!',
            'La venta se procesó correctamente y el inventario fue actualizado.',
            BASE_URL . '/representante/inventario'
        );

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error en registrarVenta - ' . $e->getMessage());

        mostrarSweetAlert(
            'error',
            'Error al procesar venta',
            'No se pudo completar la venta. Verifica los datos e intenta de nuevo.',
            $urlVenta
        );
    }

    exit();
}
