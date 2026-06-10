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
// Registra un lote nuevo y su producto en dos pasos.
// Primero inserta en 'inventario', luego en 'producto' usando el ID devuelto.
// =============================================================================
function guardarProducto(): void
{
    // ── 1. Capturar y limpiar los datos del formulario ────────────────────────
    // (int) convierte el valor a entero de forma segura
    // trim() elimina espacios en blanco al inicio y final de cada campo de texto
    $id_veterinaria = (int)   ($_POST['id_veterinaria']   ?? 0);
    $nombre         = trim(    $_POST['nombre']            ?? '');
    $descripcion    = trim(    $_POST['descripcion']       ?? '');
    $precio         = trim(    $_POST['precio']            ?? '0');
    $fecha_venc     = trim(    $_POST['fecha_vencimiento'] ?? '');
    $cantidad       = (int)   ($_POST['cantidad']          ?? 0);
    $categoria      = trim(    $_POST['categoria']         ?? '');
    $numero_lote    = trim(    $_POST['numero_lote']       ?? '');
    $stock_minimo   = (int)   ($_POST['stock_minimo']      ?? 5);

    // ── 2. Validar campos obligatorios ────────────────────────────────────────
    if (empty($nombre) || $id_veterinaria <= 0 || $cantidad < 0) {
        mostrarSweetAlert(
            'error',
            'Campos incompletos',
            'El nombre del producto, la veterinaria y la cantidad son obligatorios.',
            BASE_URL . '/representante/registro-producto'
        );
        exit();
    }

    // ── 3. Validar que el precio sea un número positivo ───────────────────────
    if (!is_numeric($precio) || (float) $precio < 0) {
        mostrarSweetAlert(
            'error',
            'Precio inválido',
            'El precio debe ser un número mayor o igual a cero.',
            BASE_URL . '/representante/registro-producto'
        );
        exit();
    }

    // ── 4. Instanciar el modelo y ejecutar los dos INSERTs ────────────────────
    $modelInv = new Inventario();

    // Datos del lote (tabla inventario)
    $datosLote = [
        'id_veterinaria' => $id_veterinaria,
        'cantidad'       => $cantidad,
        'categoria'      => $categoria,
        'numero_lote'    => $numero_lote,
        'stock_minimo'   => $stock_minimo,
    ];

    // Insertamos el lote y obtenemos su ID recién generado
    $idLote = $modelInv->crearLote($datosLote);

    if ($idLote === false) {
        mostrarSweetAlert(
            'error',
            'Error al registrar',
            'No se pudo guardar el lote. Intenta de nuevo.',
            BASE_URL . '/representante/registro-producto'
        );
        exit();
    }

    // Datos del producto (tabla producto), usando el ID del lote recién creado
    $datosProducto = [
        'id_inventario'    => $idLote,
        'nombre'           => $nombre,
        'descripcion'      => $descripcion,
        'precio'           => (float) $precio,
        'fecha_vencimiento'=> $fecha_venc,
        'imagen'           => null, // La subida de imagen se implementa en fases siguientes
    ];

    $exitoProducto = $modelInv->crearProducto($datosProducto);

    // ── 5. Responder según el resultado ───────────────────────────────────────
    if ($exitoProducto) {
        mostrarSweetAlert(
            'success',
            '¡Producto registrado!',
            'El producto se agregó correctamente al inventario.',
            BASE_URL . '/representante/inventario'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al registrar',
            'El lote se guardó pero el producto no pudo registrarse. Contacta al administrador.',
            BASE_URL . '/representante/inventario'
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
