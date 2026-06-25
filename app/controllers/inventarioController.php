<?php

// ── Dependencias ──────────────────────────────────────────────────────────────
// Verificamos que el usuario tenga sesión activa y sea Representante (id_rol = 4)
require_once __DIR__ . '/../helpers/session_representante.php';

// Importamos el helper de alertas visuales (SweetAlert2)
require_once __DIR__ . '/../helpers/alert_helpers.php';

// Importamos el modelo de inventario
require_once __DIR__ . '/../models/Inventario.php';

// Importamos el modelo de movimientos de stock (Paso 4)
require_once __DIR__ . '/../models/MovimientoStock.php';

// Días sin movimientos requeridos para permitir eliminación (Issue #245)
const DIAS_BLOQUEO_ELIMINACION = 30;

// Categorías permitidas (deben coincidir con el <select> del formulario)
const CATEGORIAS_INVENTARIO = ['medicamento', 'alimento', 'accesorio', 'insumo', 'otro'];

// ── Detectar qué método HTTP usó el navegador (GET o POST) ───────────────────
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── POST: el usuario envió un formulario ──────────────────────────────────
    case 'POST':
        // Leemos el campo oculto 'accion' del formulario para saber qué hacer
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'guardar') {
            guardarProducto();
        } elseif ($accion === 'actualizar') {
            actualizarProducto();
        } elseif ($accion === 'eliminar') {
            eliminarProducto();
        }
        break;

    // ── GET: el usuario accedió a una URL con parámetros ─────────────────────
    case 'GET':
        // Las rutas de vista son gestionadas directamente por index.php
        // Este bloque se reserva para futuras APIs JSON del módulo
        break;

    default:
        http_response_code(405);
        echo 'Método no permitido';
        break;
}


// =============================================================================
// FUNCIÓN AUXILIAR: validarStockDisponible  (Paso 4)
// Valida si hay stock suficiente para procesar una operación.
// Se reutiliza en actualizarProducto() y futuras funciones de venta.
// =============================================================================
function validarStockDisponible(int $id_inventario, int $cantidad_requerida): bool
{
    $modelInv = new Inventario();
    return $modelInv->validarDisponibilidad($id_inventario, $cantidad_requerida);
}

// =============================================================================
// FUNCIÓN AUXILIAR: obtenerStockActual  (Paso 4)
// Retorna la cantidad actual de un lote usando los movimientos.
// Útil para mostrar avisos al usuario si está bajando stock.
// =============================================================================
function obtenerStockActual(int $id_inventario): int
{
    $modelMov = new MovimientoStock();
    return $modelMov->calcularCantidadActual($id_inventario);
}

// =============================================================================
// FUNCIÓN AUXILIAR: procesarVenta  (Paso 4 - Para uso futuro en RFS 50)
// Valida y decrementa stock para una venta.
// Se usará en ventasController.php cuando se implemente RFS 50.
// Retorna: ['exito' => bool, 'mensaje' => string]
// =============================================================================
function procesarVenta(int $id_inventario, int $cantidad, int $id_usuario = null): array
{
    if (!validarStockDisponible($id_inventario, $cantidad)) {
        return ['exito' => false, 'mensaje' => 'Stock insuficiente'];
    }
    $modelInv = new Inventario();
    $resultado = $modelInv->decrementarStock($id_inventario, $cantidad, 'venta', $id_usuario);
    if ($resultado) {
        $modelMov = new MovimientoStock();
        $modelMov->registrarMovimiento($id_inventario, 'salida', $cantidad, 'venta', $id_usuario);
        return ['exito' => true, 'mensaje' => 'Venta procesada'];
    }
    return ['exito' => false, 'mensaje' => 'Error al procesar'];
}

// =============================================================================
// VALIDACIONES COMPARTIDAS (Issue #245)
// Centraliza reglas para bloquear editar/eliminar con datos inconsistentes.
// =============================================================================

/**
 * Valida campos de producto enviados por formulario (crear/editar).
 *
 * @return string[] Lista de mensajes de error; vacía si todo es válido
 */
function validarCamposProducto(array $datos, bool $permitirFechaVencida = false): array
{
    $errores = [];

    $nombre     = trim($datos['nombre'] ?? '');
    $proveedor  = trim($datos['proveedor'] ?? '');
    $categoria  = trim($datos['categoria'] ?? '');
    $precio     = trim((string) ($datos['precio'] ?? ''));
    $fechaVenc  = trim($datos['fecha_vencimiento'] ?? '');
    $cantidad   = (int) ($datos['cantidad'] ?? 0);
    $stockMin   = (int) ($datos['stock_minimo'] ?? 0);

    if ($nombre === '') {
        $errores[] = 'El nombre del producto es obligatorio.';
    }

    if ($proveedor === '') {
        $errores[] = 'El proveedor o laboratorio es obligatorio.';
    }

    if ($categoria === '' || !in_array($categoria, CATEGORIAS_INVENTARIO, true)) {
        $errores[] = 'Debes seleccionar una categoría válida para el producto.';
    }

    if ($precio === '' || !is_numeric($precio) || (float) $precio < 0) {
        $errores[] = 'El precio debe ser un número mayor o igual a cero.';
    }

    if ($fechaVenc === '') {
        $errores[] = 'La fecha de vencimiento es obligatoria.';
    } else {
        $fechaObj     = DateTime::createFromFormat('Y-m-d', $fechaVenc);
        $erroresFecha = DateTime::getLastErrors();

        if ($fechaObj === false
            || ($erroresFecha['warning_count'] ?? 0) > 0
            || ($erroresFecha['error_count'] ?? 0) > 0
        ) {
            $errores[] = 'La fecha de vencimiento no tiene un formato válido (AAAA-MM-DD).';
        } elseif (!$permitirFechaVencida) {
            $hoy = new DateTime('today');
            if ($fechaObj < $hoy) {
                $errores[] = 'La fecha de vencimiento no puede ser anterior a hoy.';
            }
        }
    }

    if ($cantidad <= 0) {
        $errores[] = 'La cantidad debe ser mayor a cero.';
    }

    if ($stockMin < 0) {
        $errores[] = 'El stock mínimo no puede ser negativo.';
    }

    if ($cantidad > 0 && $stockMin > 0 && $cantidad < $stockMin) {
        $errores[] = 'La cantidad no puede ser menor al stock mínimo (' . $stockMin . ' unidades).';
    }

    return $errores;
}

/**
 * Valida que un lote activo en BD tenga datos consistentes antes de operar.
 *
 * @return string[] Lista de mensajes de error; vacía si el registro es íntegro
 */
function validarIntegridadLote(array $lote): array
{
    return validarCamposProducto([
        'nombre'            => $lote['nombre'] ?? '',
        'proveedor'         => $lote['proveedor'] ?? '',
        'categoria'         => $lote['categoria'] ?? '',
        'precio'            => $lote['precio'] ?? '',
        'fecha_vencimiento' => $lote['fecha_vencimiento'] ?? '',
        'cantidad'          => $lote['cantidad'] ?? 0,
        'stock_minimo'      => $lote['stock_minimo'] ?? 0,
    ], true);
}

/**
 * Resuelve un lote operable: existe, pertenece a la veterinaria y pasa integridad.
 *
 * @return array{lote: array|null, errores: string[]}
 */
function resolverLoteOperable(
    Inventario $modelInv,
    int $id_inventario,
    int $id_veterinaria,
    ?int $id_producto = null
): array {
    if ($id_inventario <= 0 || $id_veterinaria <= 0) {
        return ['lote' => null, 'errores' => ['No se pudo identificar el producto.']];
    }

    $lote = $modelInv->obtenerLoteActivoDeVeterinaria($id_inventario, $id_veterinaria);

    if (!$lote) {
        return ['lote' => null, 'errores' => ['El producto no existe, ya fue eliminado o no pertenece a tu veterinaria.']];
    }

    if ($id_producto !== null && (int) $lote['id_producto'] !== $id_producto) {
        return ['lote' => null, 'errores' => ['Los identificadores del producto no coinciden.']];
    }

    $erroresIntegridad = validarIntegridadLote($lote);
    if (!empty($erroresIntegridad)) {
        return ['lote' => null, 'errores' => $erroresIntegridad];
    }

    return ['lote' => $lote, 'errores' => []];
}

// =============================================================================
// FUNCIÓN: guardarProducto  (RFS 44)
// Registra lote + producto dentro de una transacción BD.
// Si falla cualquier INSERT, se revierte todo (sin lotes huérfanos).
// =============================================================================
function guardarProducto(): void
{
    // URL de retorno cuando hay error de validación
    $urlRegistro = BASE_URL . '/representante/registro-producto';

    // ── 1. Capturar y limpiar los datos del formulario ────────────────────────
    $id_veterinaria         = (int) ($_POST['id_veterinaria']         ?? 0);
    $nombre                 = trim($_POST['nombre']                 ?? '');
    $descripcion            = trim($_POST['descripcion']            ?? '');
    $proveedor              = trim($_POST['proveedor']                ?? '');
    $precio                 = trim($_POST['precio']                   ?? '0');
    $fecha_venc             = trim($_POST['fecha_vencimiento']        ?? '');
    $cantidad               = (int) ($_POST['cantidad']                ?? 0);
    $categoria              = trim($_POST['categoria']                ?? '');
    $numero_lote            = trim($_POST['numero_lote']              ?? '');
    $stock_minimo           = (int) ($_POST['stock_minimo']           ?? 5);
    $detalle_almacenamiento = trim($_POST['detalle_almacenamiento']   ?? '');

    // ── 2. Validaciones backend estrictas (RFS 44) ──────────────────────────────

    $erroresValidacion = validarCamposProducto([
        'nombre'            => $nombre,
        'proveedor'         => $proveedor,
        'categoria'         => $categoria,
        'precio'            => $precio,
        'fecha_vencimiento' => $fecha_venc,
        'cantidad'          => $cantidad,
        'stock_minimo'      => $stock_minimo,
    ]);

    if (!empty($erroresValidacion)) {
        mostrarSweetAlert('error', 'Datos inválidos', implode(' ', $erroresValidacion), $urlRegistro);
        exit();
    }

    // Veterinaria de sesión válida
    if ($id_veterinaria <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se pudo identificar la veterinaria. Vuelve a iniciar sesión.', $urlRegistro);
        exit();
    }

    // ── 3. Armar los arreglos que consumirá el modelo ───────────────────────────
    $datosLote = [
        'id_veterinaria'         => $id_veterinaria,
        'cantidad'               => $cantidad,
        'categoria'              => $categoria,
        'numero_lote'            => $numero_lote,
        'stock_minimo'           => $stock_minimo,
        'detalle_almacenamiento' => $detalle_almacenamiento,
    ];

    $datosProducto = [
        'nombre'            => $nombre,
        'descripcion'       => $descripcion,
        'proveedor'         => $proveedor,
        'precio'            => (float) $precio,
        'fecha_vencimiento' => $fecha_venc,
        'imagen'            => null,
    ];

    // ── 4. Transacción BD: lote + producto o ninguno ───────────────────────────
    $modelInv = new Inventario();
    $pdo      = $modelInv->obtenerConexion();

    try {
        // Iniciamos la transacción: los INSERT quedan pendientes hasta commit()
        $pdo->beginTransaction();

        // Paso A: insertar el lote en 'inventario'
        $idLote = $modelInv->crearLote($datosLote);

        // Paso B: insertar el producto en 'producto' usando el ID del lote
        $datosProducto['id_inventario'] = $idLote;
        $exitoProducto = $modelInv->crearProducto($datosProducto);

        if (!$exitoProducto) {
            throw new RuntimeException('No se pudo insertar el producto.');
        }

        // Si llegamos aquí, ambos INSERT fueron bien → confirmamos cambios
        $pdo->commit();

        // Generar alertas automáticas después de registrar producto
        $id_usuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
        if ($id_usuario > 0) {
            $modelInv->generarAlertasInventario($id_veterinaria, $id_usuario);
        }

        mostrarSweetAlert(
            'success',
            '¡Producto registrado!',
            'El producto se agregó correctamente al inventario.',
            BASE_URL . '/representante/inventario'
        );

    } catch (Throwable $e) {
        // Cualquier error revierte lote y producto (evita registros huérfanos)
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error en guardarProducto - ' . $e->getMessage());

        mostrarSweetAlert(
            'error',
            'Error al registrar',
            'No se pudo guardar el producto. Verifica los datos e intenta de nuevo.',
            $urlRegistro
        );
    }

    exit();
}


// =============================================================================
// FUNCIÓN: actualizarProducto  (RFS 47)
// Recibe los datos del formulario de edición y actualiza
// las dos tablas: inventario (lote) y producto (datos descriptivos).
// =============================================================================
function actualizarProducto(): void
{
    // ── 1. Capturar los IDs que identifican qué registro editar ───────────────
    $id_inventario  = (int) ($_POST['id_inventario'] ?? 0);
    $id_producto    = (int) ($_POST['id_producto']   ?? 0);
    // id_veterinaria de sesión: más seguro que confiar en el campo del formulario
    $id_veterinaria = (int) ($_SESSION['user']['id_veterinaria'] ?? 0);

    // ── 2. Capturar los campos editables del formulario ───────────────────────
    $nombre                 = trim(   $_POST['nombre']                    ?? '');
    $descripcion            = trim(   $_POST['descripcion']               ?? '');
    $proveedor              = trim(   $_POST['proveedor']                 ?? '');
    $precio                 = trim(   $_POST['precio']                    ?? '0');
    $fecha_venc             = trim(   $_POST['fecha_vencimiento']        ?? '');
    $cantidad               = (int)  ($_POST['cantidad']                  ?? 0);
    $categoria              = trim(   $_POST['categoria']                 ?? '');
    $numero_lote            = trim(   $_POST['numero_lote']               ?? '');
    $stock_minimo           = (int)  ($_POST['stock_minimo']              ?? 5);
    $detalle_almacenamiento = trim($_POST['detalle_almacenamiento']   ?? '');

    $urlEditar = BASE_URL . '/representante/editar-producto?id=' . $id_inventario;

    // ── 3. Verificar lote activo, propiedad e integridad antes de escribir ────
    $modelInv = new Inventario();
    $resolucion = resolverLoteOperable($modelInv, $id_inventario, $id_veterinaria, $id_producto);

    if (!empty($resolucion['errores'])) {
        mostrarSweetAlert(
            'error',
            'Operación no permitida',
            implode(' ', $resolucion['errores']),
            BASE_URL . '/representante/inventario'
        );
        exit();
    }

    // ── 4. Validar campos enviados por el formulario ──────────────────────────
    $erroresValidacion = validarCamposProducto([
        'nombre'            => $nombre,
        'proveedor'         => $proveedor,
        'categoria'         => $categoria,
        'precio'            => $precio,
        'fecha_vencimiento' => $fecha_venc,
        'cantidad'          => $cantidad,
        'stock_minimo'      => $stock_minimo,
    ], true);

    if (!empty($erroresValidacion)) {
        mostrarSweetAlert('error', 'Datos inválidos', implode(' ', $erroresValidacion), $urlEditar);
        exit();
    }

    // ── 5. Actualizar en transacción (lote + producto + movimiento opcional) ───
    $modelMov = new MovimientoStock();
    $pdo      = $modelInv->obtenerConexion();
    $cantidadAnterior = obtenerStockActual($id_inventario);

    $datosLote = [
        'cantidad'               => $cantidad,
        'categoria'              => $categoria,
        'numero_lote'            => $numero_lote,
        'stock_minimo'           => $stock_minimo,
        'detalle_almacenamiento' => $detalle_almacenamiento,
    ];

    $datosProducto = [
        'nombre'            => $nombre,
        'descripcion'       => $descripcion,
        'proveedor'         => $proveedor,
        'precio'            => (float) $precio,
        'fecha_vencimiento' => $fecha_venc,
    ];

    try {
        $pdo->beginTransaction();

        $exitoLote     = $modelInv->actualizarLote($id_inventario, $datosLote);
        $exitoProducto = $modelInv->actualizarProducto($id_producto, $datosProducto);

        if (!$exitoLote || !$exitoProducto) {
            throw new RuntimeException('No se pudieron guardar todos los cambios.');
        }

        if ($cantidad !== $cantidadAnterior) {
            $diferencia         = $cantidad - $cantidadAnterior;
            $tipoMovimiento     = $diferencia > 0 ? 'entrada' : 'salida';
            $cantidadMovimiento = abs($diferencia);

            $movimientoOk = $modelMov->registrarMovimiento(
                $id_inventario,
                $tipoMovimiento,
                $cantidadMovimiento,
                'Ajuste por edición de producto',
                $_SESSION['user']['id_usuario'] ?? null
            );

            if (!$movimientoOk) {
                throw new RuntimeException('No se pudo registrar el movimiento de stock.');
            }
        }

        $pdo->commit();

        $id_usuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
        if ($id_usuario > 0) {
            $modelInv->generarAlertasInventario($id_veterinaria, $id_usuario);
        }

        mostrarSweetAlert(
            'success',
            '¡Producto actualizado!',
            'Los cambios se guardaron correctamente.',
            BASE_URL . '/representante/inventario'
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error en actualizarProducto - ' . $e->getMessage());

        mostrarSweetAlert(
            'error',
            'Error al actualizar',
            'No se pudieron guardar todos los cambios. Intenta de nuevo.',
            $urlEditar
        );
    }
    exit();
}


// =============================================================================
// FUNCIÓN: eliminarProducto  (RFS 49)
// Recibe el ID del lote y lo marca como eliminado (estado = 0).
// No se borra físicamente: el historial queda intacto en la BD.
// =============================================================================
function eliminarProducto(): void
{
    $urlInventario  = BASE_URL . '/representante/inventario';
    $id_inventario  = (int) ($_POST['id_inventario'] ?? 0);
    $id_veterinaria = (int) ($_SESSION['user']['id_veterinaria'] ?? 0);

    $modelInv = new Inventario();
    $resolucion = resolverLoteOperable($modelInv, $id_inventario, $id_veterinaria);

    if (!empty($resolucion['errores'])) {
        mostrarSweetAlert(
            'error',
            'Operación no permitida',
            implode(' ', $resolucion['errores']),
            $urlInventario
        );
        exit();
    }

    // Bloquear si hubo movimientos recientes (protege trazabilidad en reportes)
    $modelMov = new MovimientoStock();
    if ($modelMov->tieneMovimientosRecientes($id_inventario, DIAS_BLOQUEO_ELIMINACION)) {
        mostrarSweetAlert(
            'error',
            'Eliminación bloqueada',
            'Este producto tiene movimientos de stock en los últimos '
                . DIAS_BLOQUEO_ELIMINACION
                . ' días. No puede eliminarse para preservar la trazabilidad.',
            $urlInventario
        );
        exit();
    }

    // Soft-delete: estado = 0; registros en producto y movimiento_stock permanecen
    $resultado = $modelInv->eliminarLote($id_inventario, $id_veterinaria);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Producto eliminado',
            'El producto fue retirado del inventario correctamente.',
            $urlInventario
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al eliminar',
            'No se pudo completar la eliminación. Intenta de nuevo.',
            $urlInventario
        );
    }
    exit();
}
