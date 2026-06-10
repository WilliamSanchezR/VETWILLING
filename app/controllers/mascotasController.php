<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Mascotas.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarMascota();
        } elseif ($accion === 'eliminar') {
            // ✅ RFS 29: Eliminar con motivo (requiere POST)
            eliminarMascota($_POST['id'] ?? $_POST['id_paciente'] ?? null);
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

    // Campos clínicos opcionales del formulario de registro
    $peso_registro = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;

    $data = [
        'id_propietario' => $id_propietario,
        'nombre'         => $nombre,
        'especie'        => $especie,
        'raza'           => $raza,
        'edad_numero'    => $edad_numero,
        'edad_unidad'    => $edad_unidad,
        'sexo'           => $sexo,
        'peso'           => $peso_registro,
        'estado_salud'   => 'Bueno',   // Subtarea 6: ficha clínica inicial
        'img_mascota'    => $img_mascota
    ];

    error_log("✅ Datos a registrar mascota: " . print_r($data, true));

    $resultado = $objMascota->registrar($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Mascota registrada',
            '¡' . htmlspecialchars($nombre) . ' ha sido registrada correctamente!',
            BASE_URL . '/cliente/mascotas'
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
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validar sesión activa
    if (!isset($_SESSION['user']['id_usuario'])) {
        mostrarSweetAlert('error', 'Sesión no válida', 'Por favor inicia sesión');
        exit();
    }

    $id_usuario = $_SESSION['user']['id_usuario'];
    $id_rol     = $_SESSION['user']['id_rol'] ?? 0;

    $id          = $_POST['id_mascota'] ?? null;
    $nombre      = trim($_POST['nombre'] ?? '');
    $especie     = trim($_POST['especie'] ?? '');
    $raza        = trim($_POST['raza'] ?? '');
    $edad_numero = (int)($_POST['edad_numero'] ?? 0);
    $edad_unidad = $_POST['edad_unidad'] ?? '';
    $sexo        = $_POST['sexo'] ?? null;

    // Campos clínicos (opcionales)
    $peso                         = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
    $estado_salud                 = $_POST['estado_salud'] ?? null;
    $fecha_ultima_desparasitacion = !empty($_POST['fecha_ultima_desparasitacion'])
                                    ? $_POST['fecha_ultima_desparasitacion']
                                    : null;

    // Validar estado_salud contra valores permitidos
    $estados_validos = ['Bueno', 'Regular', 'Delicado'];
    if ($estado_salud !== null && !in_array($estado_salud, $estados_validos, true)) {
        $estado_salud = null;
    }

    // Validar campos obligatorios
    if (!$id || !$nombre || !$especie || !$raza || $edad_numero <= 0 || !$edad_unidad || !$sexo) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete los campos obligatorios');
        exit();
    }

    require_once __DIR__ . '/../../config/database.php';

    // ── VALIDACIÓN DE PROPIEDAD ────────────────────────────────────────
    // Roles 1=Admin, 2=Vet pueden editar cualquier mascota.
    // Rol 3=Propietario solo puede editar sus propias mascotas.
    if ($id_rol == 3) {
        try {
            $db = new conexion();
            $conexion = $db->getConexion();

            // Obtener el id_propietario del usuario en sesión
            $sqlProp = "SELECT id_propietario FROM propietario WHERE id_usuario = :id_usuario";
            $stmtProp = $conexion->prepare($sqlProp);
            $stmtProp->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtProp->execute();
            $propietario = $stmtProp->fetch(PDO::FETCH_ASSOC);

            if (!$propietario) {
                mostrarSweetAlert('error', 'Perfil incompleto', 'Tu usuario no tiene perfil de propietario');
                exit();
            }

            // Obtener el id_propietario de la mascota a editar
            $sqlMasc = "SELECT id_propietario FROM paciente WHERE id_paciente = :id_paciente AND estado = 'Activo'";
            $stmtMasc = $conexion->prepare($sqlMasc);
            $stmtMasc->bindParam(':id_paciente', $id, PDO::PARAM_INT);
            $stmtMasc->execute();
            $mascotaActual = $stmtMasc->fetch(PDO::FETCH_ASSOC);

            if (!$mascotaActual) {
                mostrarSweetAlert('error', 'No encontrada', 'La mascota no existe o ya fue eliminada');
                exit();
            }

            if ((int)$mascotaActual['id_propietario'] !== (int)$propietario['id_propietario']) {
                error_log("❌ Intento de edición no autorizado: usuario $id_usuario sobre mascota $id");
                mostrarSweetAlert('error', 'Sin permiso', 'No puedes editar una mascota que no te pertenece');
                exit();
            }
        } catch (PDOException $e) {
            error_log('❌ Error validando propiedad mascota: ' . $e->getMessage());
            mostrarSweetAlert('error', 'Error', 'No se pudo verificar la propiedad de la mascota');
            exit();
        }
    } elseif ($id_rol != 1 && $id_rol != 2) {
        mostrarSweetAlert('error', 'Sin permiso', 'No tienes permisos para editar mascotas');
        exit();
    }
    // ── FIN VALIDACIÓN DE PROPIEDAD ────────────────────────────────────

    $imagen = null;

    // Procesar imagen si se subió una nueva
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
        'id_paciente'                  => $id,
        'nombre'                       => $nombre,
        'especie'                      => $especie,
        'raza'                         => $raza,
        'edad_numero'                  => $edad_numero,
        'edad_unidad'                  => $edad_unidad,
        'sexo'                         => $sexo,
        'peso'                         => $peso,
        'estado_salud'                 => $estado_salud,
        'fecha_ultima_desparasitacion' => $fecha_ultima_desparasitacion,
        'img_mascota'                  => $imagen,
        'id_usuario'                   => $id_usuario,
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

// ✅ FUNCIÓN ELIMINAR MEJORADA - RFS 29
// Incluye: validación de permisos, verificación de dependencias, motivo y auditoría
function eliminarMascota($id)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../models/Mascotas.php';
    require_once __DIR__ . '/../models/AuditoriaEliminacion.php';
    require_once __DIR__ . '/../../config/database.php';

    try {
        // 1. OBTENER DATOS DE LA SESIÓN
        if (!isset($_SESSION['user'])) {
            mostrarSweetAlert('error', 'Sesión no válida', 'Por favor inicia sesión');
            exit();
        }

        $id_usuario = $_SESSION['user']['id_usuario'];
        $id_rol = $_SESSION['user']['id_rol'] ?? 0;  // id_rol: 1=Admin, 2=Vet, 3=Propietario, 4=Representante
        $nombre_usuario = $_SESSION['user']['nombre'] ?? $_SESSION['user']['nombres'] ?? 'Desconocido';

        // 2. OBTENER DATOS DE LA MASCOTA
        $objMascota = new Mascota();
        $mascota = $objMascota->consultar($id);

        if (!$mascota) {
            mostrarSweetAlert('error', 'No encontrada', 'La mascota no existe o ya fue eliminada');
            exit();
        }

        // 3. VALIDACIÓN DE PERMISOS POR ROL (usando id_rol: 1=Admin, 2=Vet, 3=Propietario, 4=Representante)
        $tienePermiso = false;
        $rol_texto = '';

        if ($id_rol == 3) {
            // id_rol = 3 = Propietario (Cliente)
            // El propietario solo puede eliminar sus propias mascotas
            $db = new conexion();
            $conexion = $db->getConexion();

            $sqlVerificaPropiedad = "SELECT id_propietario FROM propietario WHERE id_usuario = :id_usuario";
            $stmtProp = $conexion->prepare($sqlVerificaPropiedad);
            $stmtProp->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtProp->execute();
            $propietario = $stmtProp->fetch(PDO::FETCH_ASSOC);

            error_log("🔍 Validando propietario - ID Usuario: $id_usuario, ID Propietario Usuario: " . ($propietario['id_propietario'] ?? 'null') . ", ID Propietario Mascota: " . $mascota['id_propietario']);

            if ($propietario && (int)$mascota['id_propietario'] === (int)$propietario['id_propietario']) {
                $tienePermiso = true;
                $rol_texto = 'Propietario';
                error_log("✅ Propietario validado correctamente");
            } else {
                error_log("❌ Propietario no coincide");
            }
        } elseif ($id_rol == 2) {
            // id_rol = 2 = Veterinario
            // El veterinario solo puede eliminar mascotas que tiene asignadas
            $db = new conexion();
            $conexion = $db->getConexion();

            $sqlVerificaVeterinario = "SELECT COUNT(*) as total FROM paciente_profesional_asignacion 
                                       WHERE id_paciente = :id_paciente 
                                       AND id_usuario = :id_usuario 
                                       AND estado = 'Activo'";
            $stmtVet = $conexion->prepare($sqlVerificaVeterinario);
            $stmtVet->bindParam(':id_paciente', $id, PDO::PARAM_INT);
            $stmtVet->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtVet->execute();
            $resultVet = $stmtVet->fetch(PDO::FETCH_ASSOC);

            if ($resultVet && $resultVet['total'] > 0) {
                $tienePermiso = true;
                $rol_texto = 'Veterinario';
            }
        } elseif ($id_rol == 1) {
            // id_rol = 1 = Administrador
            // Administrador tiene permiso total
            $tienePermiso = true;
            $rol_texto = 'Administrador';
        } elseif ($id_rol == 4) {
            // id_rol = 4 = Representante (se trata igual que propietario)
            $db = new conexion();
            $conexion = $db->getConexion();

            $sqlVerificaPropiedad = "SELECT id_propietario FROM propietario WHERE id_usuario = :id_usuario";
            $stmtProp = $conexion->prepare($sqlVerificaPropiedad);
            $stmtProp->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtProp->execute();
            $propietario = $stmtProp->fetch(PDO::FETCH_ASSOC);

            if ($propietario && (int)$mascota['id_propietario'] === (int)$propietario['id_propietario']) {
                $tienePermiso = true;
                $rol_texto = 'Representante';
            }
        }

        // Si no tiene permiso, rechazar
        if (!$tienePermiso) {
            mostrarSweetAlert(
                'error',
                'Permiso denegado',
                'No tienes permisos para eliminar esta mascota. Solo el propietario o el veterinario asignado pueden hacerlo.'
            );
            exit();
        }

        // 4. VERIFICAR DEPENDENCIAS ACTIVAS
        $dependencias = $objMascota->verificarDependencias($id);

        if ($dependencias['tiene_dependencias']) {
            mostrarSweetAlert(
                'error',
                'No se puede eliminar',
                $dependencias['mensaje'] . ' Debes resolver estas pendencias primero.'
            );
            exit();
        }

        // 5. OBTENER MOTIVO DE ELIMINACIÓN (si viene en POST)
        $motivo = $_POST['motivo'] ?? $_POST['motivo_eliminacion'] ?? '';

        if (empty($motivo)) {
            mostrarSweetAlert(
                'error',
                'Motivo requerido',
                'Por favor especifica un motivo para la eliminación'
            );
            exit();
        }

        // 6. PREPARAR DATOS DE AUDITORÍA
        $datosAuditoria = [
            'id_usuario' => $id_usuario,
            'nombre_usuario' => $nombre_usuario,
            'rol_usuario' => $rol_texto,
            'motivo_eliminacion' => $motivo,
            'citas_canceladas' => $dependencias['citas_pendientes'],
            'tratamientos_cancelados' => $dependencias['tratamientos_activos']
        ];

        // 7. ELIMINAR LA MASCOTA Y REGISTRAR AUDITORÍA
        $respuesta = $objMascota->eliminar($id, $datosAuditoria);

        // 8. RESPONDER AL USUARIO
        if ($respuesta) {
            mostrarSweetAlert(
                'success',
                'Mascota inactivada',
                'La mascota fue marcada como inactiva correctamente. El registro y su historial médico se conservan en la auditoría.',
                '/vetwilling/cliente/mascotas'
            );
        } else {
            mostrarSweetAlert(
                'error',
                'Error al eliminar',
                'No se pudo eliminar la mascota. Intenta nuevamente'
            );
        }
    } catch (Exception $e) {
        error_log("❌ Error en eliminarMascota: " . $e->getMessage());
        mostrarSweetAlert(
            'error',
            'Error del sistema',
            'Ocurrió un error. Por favor intenta más tarde.'
        );
    }

    exit();
}
