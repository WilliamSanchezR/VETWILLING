<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/mascotas.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarMascota();
        } else {
            registrarMascota();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        // ✅ ELIMINAR POR GET (como tu ejemplo de veterinario)
        if ($accion === 'eliminar') {
            eliminarMascota($_GET['id']);
        } else if (isset($_GET['id'])) {
            consultarMascotaId($_GET['id']);
        } else {
            listarMascotas();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// =================================================================
// ✔ FUNCIONES CRUD MASCOTAS
// =================================================================

function registrarMascota()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nombre = $_POST['nombre'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raza = $_POST['raza'] ?? '';

    // ✅ CAPTURAR EDAD CON UNIDAD
    $edad_numero = (int)($_POST['edad_numero'] ?? 0);
    $edad_unidad = $_POST['edad_unidad'] ?? '';

    $sexo = $_POST['sexo'] ?? '';
    $img_mascota = null;

    // Validar campos obligatorios
    if (empty($nombre) || empty($especie) || empty($raza) || $edad_numero <= 0 || empty($edad_unidad) || empty($sexo)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    if (!isset($_SESSION['user']['id_usuario'])) {
        mostrarSweetAlert('error', 'Error de sesión', 'No se pudo identificar al propietario');
        exit();
    }

    $id_usuario = $_SESSION['user']['id_usuario'];

    require_once __DIR__ . '/../../config/database.php';

    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT id_propietario FROM propietario WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $propietario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$propietario || !isset($propietario['id_propietario'])) {
            error_log("❌ Usuario $id_usuario no tiene registro en tabla propietario");
            mostrarSweetAlert(
                'error',
                'Perfil incompleto',
                'Tu usuario no tiene un perfil de propietario. Contacta al administrador.'
            );
            exit();
        }

        $id_propietario = $propietario['id_propietario'];
        error_log("✅ ID Usuario: $id_usuario → ID Propietario: $id_propietario");
    } catch (PDOException $e) {
        error_log("❌ Error al buscar propietario: " . $e->getMessage());
        mostrarSweetAlert(
            'error',
            'Error de base de datos',
            'No se pudo verificar tu perfil. Intenta nuevamente.'
        );
        exit();
    }

    // Validar imagen
    if (isset($_FILES['img_mascota']) && $_FILES['img_mascota']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['img_mascota'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
            exit();
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error', 'La foto supera los 5MB');
            exit();
        }

        $img_mascota = uniqid('pet_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/mascotas/' . $img_mascota;

        if (!is_dir(BASE_PATH . '/public/uploads/mascotas/')) {
            mkdir(BASE_PATH . '/public/uploads/mascotas/', 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            error_log("Error al mover archivo a: $destino");
            mostrarSweetAlert('error', 'Error', 'No se pudo subir la foto');
            exit();
        }
    } else {
        $img_mascota = 'default_pet.jpg';
    }

    $objMascota = new Mascota();

    $data = [
        'id_propietario' => $id_propietario,
        'nombre'         => $nombre,
        'especie'        => $especie,
        'raza'           => $raza,
        'edad_numero'    => $edad_numero,          // ✅ Número: 4
        'edad_unidad'    => $edad_unidad,          // ✅ Unidad: "años"
        'sexo'           => $sexo,
        'img_mascota'    => $img_mascota
    ];

    error_log("✅ Datos a registrar mascota: " . print_r($data, true));

    $resultado = $objMascota->registrar($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Mascota registrada',
            'La mascota ha sido registrada correctamente',
            BASE_URL . '/cliente/perfil'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar la mascota. Verifica la conexión a la base de datos.');
    }

    exit();
}

// ✅ FUNCIÓN AUXILIAR PARA FORMATEAR EDAD
function formatearEdad($edad_numero, $edad_unidad)
{
    if (empty($edad_numero) || empty($edad_unidad)) {
        return "No especificada";
    }
    return "$edad_numero $edad_unidad";
}

// ✅ FUNCIÓN LISTAR MASCOTAS MEJORADA
// Ahora acepta un parámetro opcional para facilitar el uso en PDFs
function listarMascotas($id_propietario = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Si se pasa un ID de propietario, usarlo directamente
    if ($id_propietario !== null) {
        $obj = new Mascota();
        return $obj->listarPorPropietario($id_propietario);
    }

    // Si no, buscar el propietario del usuario en sesión
    if (!isset($_SESSION['user']['id_usuario'])) {
        return [];
    }

    $id_usuario = $_SESSION['user']['id_usuario'];

    require_once __DIR__ . '/../../config/database.php';

    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT id_propietario FROM propietario WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $propietario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$propietario || !isset($propietario['id_propietario'])) {
            error_log("❌ Usuario $id_usuario no tiene registro en tabla propietario");
            return [];
        }

        $id_propietario = $propietario['id_propietario'];

        $obj = new Mascota();
        return $obj->listarPorPropietario($id_propietario);
    } catch (PDOException $e) {
        error_log("❌ Error al listar mascotas: " . $e->getMessage());
        return [];
    }
}

function consultarMascotaId($id)
{
    $obj = new Mascota();
    return $obj->consultar($id);
}

function actualizarMascota()
{
    $id = $_POST['id_mascota'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $raza = trim($_POST['raza'] ?? '');

    // ✅ CAPTURAR EDAD CON UNIDAD
    $edad_numero = (int)($_POST['edad_numero'] ?? 0);
    $edad_unidad = $_POST['edad_unidad'] ?? '';

    $sexo = $_POST['sexo'] ?? null;

    // Validar campos obligatorios
    if (!$id || !$nombre || !$especie || !$raza || $edad_numero <= 0 || !$edad_unidad || !$sexo) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete los campos obligatorios');
        exit();
    }

    $imagen = null;

    // Validar y procesar imagen si se subió una nueva
    if (!empty($_FILES['img_mascota']['name'])) {
        $file = $_FILES['img_mascota'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo JPG, JPEG o PNG');
            exit();
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Archivo muy grande', 'Máx. 5MB');
            exit();
        }

        $imagen = uniqid('pet_') . '.' . $ext;
        $destino = BASE_PATH . "/public/uploads/mascotas/$imagen";

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            error_log("Error al mover archivo a: $destino");
            mostrarSweetAlert('error', 'Error', 'No se pudo subir la foto');
            exit();
        }
    }

    $data = [
        'id_paciente'  => $id,
        'nombre'       => $nombre,
        'especie'      => $especie,
        'raza'         => $raza,
        'edad_numero'  => $edad_numero,      // ✅ Número
        'edad_unidad'  => $edad_unidad,      // ✅ Unidad
        'sexo'         => $sexo,
        'img_mascota'  => $imagen
    ];

    $mascota = new Mascota();
    $resultado = $mascota->actualizar($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Mascota actualizada',
            'La información fue actualizada correctamente',
            BASE_URL . '/cliente/mascotas'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la mascota');
    }

    exit();
}

// ✅ FUNCIÓN ELIMINAR CON GET (igual que tu ejemplo de veterinario)
function eliminarMascota($id)
{


    // Crear instancia y eliminar
    $objMascota = new Mascota();
    $respuesta = $objMascota->eliminar($id);

    // Responder según resultado
    if ($respuesta) {
        mostrarSweetAlert(
            'success',
            'Eliminación exitosa',
            'La mascota ha sido eliminada correctamente',
            '/vetwilling/cliente/mascotas'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al eliminar',
            'No se pudo eliminar la mascota. Intenta nuevamente'
        );
    }

    exit();
}
