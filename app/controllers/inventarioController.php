<?php

// ── Dependencias ──────────────────────────────────────────────────────────────
// Verificamos que el usuario tenga sesión activa y sea Representante (id_rol = 4)
require_once __DIR__ . '/../helpers/session_representante.php';

// Importamos el helper de alertas visuales (SweetAlert2)
require_once __DIR__ . '/../helpers/alert_helpers.php';

// Importamos el modelo de inventario
require_once __DIR__ . '/../models/Inventario.php';

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

    // Categorías permitidas (deben coincidir con el <select> del formulario)
    $categoriasValidas = ['medicamento', 'alimento', 'accesorio', 'insumo', 'otro'];

    // ── 2. Validaciones backend estrictas (RFS 44) ──────────────────────────────

    // Nombre obligatorio
    if ($nombre === '') {
        mostrarSweetAlert('error', 'Campo obligatorio', 'El nombre del producto es obligatorio.', $urlRegistro);
        exit();
    }

    // Veterinaria de sesión válida
    if ($id_veterinaria <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se pudo identificar la veterinaria. Vuelve a iniciar sesión.', $urlRegistro);
        exit();
    }

    // Cantidad debe ser mayor a cero (no se permite stock inicial en 0)
    if ($cantidad <= 0) {
        mostrarSweetAlert('error', 'Cantidad inválida', 'La cantidad inicial debe ser mayor a cero.', $urlRegistro);
        exit();
    }

    // Categoría obligatoria y dentro de la lista permitida
    if ($categoria === '' || !in_array($categoria, $categoriasValidas, true)) {
        mostrarSweetAlert('error', 'Categoría inválida', 'Debes seleccionar una categoría válida para el producto.', $urlRegistro);
        exit();
    }

    // Proveedor obligatorio
    if ($proveedor === '') {
        mostrarSweetAlert('error', 'Campo obligatorio', 'El proveedor o laboratorio es obligatorio.', $urlRegistro);
        exit();
    }

    // Fecha de vencimiento obligatoria, con formato correcto y no pasada
    if ($fecha_venc === '') {
        mostrarSweetAlert('error', 'Campo obligatorio', 'La fecha de vencimiento es obligatoria.', $urlRegistro);
        exit();
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha_venc);
    $erroresFecha = DateTime::getLastErrors();

    // createFromFormat devuelve false si el formato no es válido (ej: 2024-13-40)
    if ($fechaObj === false || ($erroresFecha['warning_count'] ?? 0) > 0 || ($erroresFecha['error_count'] ?? 0) > 0) {
        mostrarSweetAlert('error', 'Fecha inválida', 'La fecha de vencimiento no tiene un formato válido (AAAA-MM-DD).', $urlRegistro);
        exit();
    }

    // Comparar solo la fecha (sin hora) contra hoy
    $hoy = new DateTime('today');
    if ($fechaObj < $hoy) {
        mostrarSweetAlert('error', 'Fecha vencida', 'La fecha de vencimiento no puede ser anterior a hoy.', $urlRegistro);
        exit();
    }

    // Precio numérico y no negativo
    if (!is_numeric($precio) || (float) $precio < 0) {
        mostrarSweetAlert('error', 'Precio inválido', 'El precio debe ser un número mayor o igual a cero.', $urlRegistro);
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
    $nombre       = trim(   $_POST['nombre']            ?? '');
    $descripcion  = trim(   $_POST['descripcion']       ?? '');
    $precio       = trim(   $_POST['precio']            ?? '0');
    $fecha_venc   = trim(   $_POST['fecha_vencimiento'] ?? '');
    $cantidad     = (int)  ($_POST['cantidad']          ?? 0);
    $categoria    = trim(   $_POST['categoria']         ?? '');
    $numero_lote  = trim(   $_POST['numero_lote']       ?? '');
    $stock_minimo = (int)  ($_POST['stock_minimo']      ?? 5);

    // ── 3. Validar que los IDs y campos obligatorios sean correctos ───────────
    if ($id_inventario <= 0 || $id_producto <= 0 || empty($nombre)) {
        mostrarSweetAlert(
            'error',
            'Datos inválidos',
            'No se pudo identificar el producto a editar.',
            BASE_URL . '/representante/inventario'
        );
        exit();
    }

    if (!is_numeric($precio) || (float) $precio < 0) {
        mostrarSweetAlert(
            'error',
            'Precio inválido',
            'El precio debe ser un número mayor o igual a cero.',
            BASE_URL . '/representante/editar-producto?id=' . $id_inventario
        );
        exit();
    }

    // ── 4. Llamar al modelo para actualizar las dos tablas ────────────────────
    $modelInv = new Inventario();

    $datosLote = [
        'cantidad'     => $cantidad,
        'categoria'    => $categoria,
        'numero_lote'  => $numero_lote,
        'stock_minimo' => $stock_minimo,
    ];

    $datosProducto = [
        'nombre'            => $nombre,
        'descripcion'       => $descripcion,
        'precio'            => (float) $precio,
        'fecha_vencimiento' => $fecha_venc,
    ];

    $exitoLote     = $modelInv->actualizarLote($id_inventario, $datosLote);
    $exitoProducto = $modelInv->actualizarProducto($id_producto, $datosProducto);

    // ── 5. Responder según el resultado ───────────────────────────────────────
    if ($exitoLote && $exitoProducto) {
        mostrarSweetAlert(
            'success',
            '¡Producto actualizado!',
            'Los cambios se guardaron correctamente.',
            BASE_URL . '/representante/inventario'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al actualizar',
            'No se pudieron guardar todos los cambios. Intenta de nuevo.',
            BASE_URL . '/representante/editar-producto?id=' . $id_inventario
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
    // ── 1. Capturar el ID del lote a eliminar ─────────────────────────────────
    $id_inventario  = (int) ($_POST['id_inventario']  ?? 0);

    // Tomamos la veterinaria de la sesión para evitar que alguien manipule
    // el formulario e intente eliminar lotes de otra clínica
    $id_veterinaria = (int) ($_SESSION['user']['id_veterinaria'] ?? 0);

    // ── 2. Validar que llegaron IDs válidos ───────────────────────────────────
    if ($id_inventario <= 0 || $id_veterinaria <= 0) {
        mostrarSweetAlert(
            'error',
            'Solicitud inválida',
            'No se pudo identificar el producto a eliminar.',
            BASE_URL . '/representante/inventario'
        );
        exit();
    }

    // ── 3. Ejecutar el soft-delete en el modelo ───────────────────────────────
    $modelInv  = new Inventario();
    $resultado = $modelInv->eliminarLote($id_inventario, $id_veterinaria);

    // ── 4. Responder con alerta y redirigir a la lista ────────────────────────
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Producto eliminado',
            'El producto fue retirado del inventario correctamente.',
            BASE_URL . '/representante/inventario'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al eliminar',
            'No se encontró el producto o no tienes permiso para eliminarlo.',
            BASE_URL . '/representante/inventario'
        );
    }
    exit();
}
