<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Propietario.php';
require_once __DIR__ . '/../helpers/session_all.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarPropietario();
        } else if ($accion === 'cambiar-foto') {
            fotoPerfil();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        // --- ELIMINAR ---
        if ($accion === 'eliminar' && isset($_GET['id'])) {
            eliminarPropietario($_GET['id']);
            exit();
        }

        if ($accion === 'listar-veterinaria') {
            listarPropietariosVeterinaria($_GET['id']);
            exit();
        }

        // --- CONSULTAR UN PROPIETARIO ---
        if ($accion === 'consultar' && isset($_GET['id'])) {
            $data = consultarPropietarioId($_GET['id']);
            echo json_encode($data);
            exit();
        }

        if ($accion === 'listar-veterinaria') {
            $id_veterinaria = $_GET['id_veterinaria'] ?? '';

            exit();
        }

        // --- SI HAY ID PERO SIN ACCION, CONSULTA DIRECTA ---
        if (isset($_GET['id'])) {
            echo json_encode(consultarPropietarioId($_GET['id']));
            exit();
        }

        echo "No se ha especificado una acción válida o falta el ID para la consulta/eliminación";

        // --- LISTAR TODOS ---
        echo json_encode(listarPropietarios());
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? '';
        if ($id) {
            eliminarPropietarioAJAX($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID de propietario no proporcionado']);
        }

        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}


// ==================== CRUD ====================

function listarPropietarios()
{
    $resultado = new Propietario();
    return $resultado->listar();
}


function consultarPropietarioId($id)
{
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        return [
            'status' => 'error',
            'message' => 'Sesión no válida',
            'code' => 'UNAUTHORIZED'
        ];
    }
    
    $id_usuario_sesion = $_SESSION['user']['id_usuario'];
    $id_rol_sesion = (int)$_SESSION['user']['id_rol'];
    
    $obj = new Propietario();
    $propietario = $obj->consultarPropietario($id);
    
    if (!$propietario) {
        http_response_code(404);
        return [
            'status' => 'error',
            'message' => 'El propietario solicitado no existe',
            'code' => 'NOT_FOUND'
        ];
    }
    
    if ($propietario['estado'] !== 'Activo') {
        http_response_code(403);
        return [
            'status' => 'error',
            'message' => 'Este propietario está inactivo y no puede ser consultado',
            'code' => 'INACTIVE_OWNER'
        ];
    }

    
    if ($id_rol_sesion === 3) {
        // Cliente: solo puede ver su propio perfil
        if ((int)$id !== $id_usuario_sesion) {
            http_response_code(403);
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para consultar este propietario',
                'code' => 'FORBIDDEN'
            ];
        }
    } elseif ($id_rol_sesion === 4) {
        // Representante: debe verificar que el propietario pertenece a su veterinaria
        $id_veterinaria_propietario = $propietario['id_veterinaria'] ?? null;
        $id_veterinaria_sesion = $_SESSION['user']['id_veterinaria'] ?? null;
        
        if ($id_veterinaria_propietario !== $id_veterinaria_sesion) {
            http_response_code(403);
            return [
                'status' => 'error',
                'message' => 'Este propietario no pertenece a tu veterinaria',
                'code' => 'FORBIDDEN'
            ];
        }
    } elseif ($id_rol_sesion !== 1) {
        // Otros roles que no tienen permisos (ej: Veterinario)
        http_response_code(403);
        return [
            'status' => 'error',
            'message' => 'Tu rol no tiene permisos para consultar propietarios',
            'code' => 'FORBIDDEN'
        ];
    }
    
    return [
        'status' => 'success',
        'data' => $propietario
    ];
}

function actualizarPropietario()
{
    $id_propietario   = $_POST['id_propietario'] ?? '';
    $tipo_documento   = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres          = $_POST['nombres'] ?? '';
    $apellidos        = $_POST['apellidos'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $direccion        = $_POST['direccion'] ?? '';
    $email            = $_POST['email'] ?? '';
    $id_veterinaria   = $_POST['id_veterinaria'] ?? '';

    if (empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($telefono) || empty($email)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor, completar todos los campos');
        exit();
    }

    $img_perfil = null;
    $actualizarImagen = false;

    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === 0) {
        $ruta = __DIR__ . "/../../public/uploads/usuarios/";
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES['img_perfil']['name']);
        $destino = $ruta . $nombreArchivo;

        if (move_uploaded_file($_FILES['img_perfil']['tmp_name'], $destino)) {
            $img_perfil = $nombreArchivo;
            $actualizarImagen = true;
        }
    }

    $prop = new Propietario();
    $data = [
        'id_propietario'   => $id_propietario,
        'tipo_documento'   => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres'          => $nombres,
        'apellidos'        => $apellidos,
        'telefono'         => $telefono,
        'direccion'        => $direccion,
        'email'            => $email,
        'id_veterinaria'   => $id_veterinaria,
        'img_perfil'       => $img_perfil
    ];

    $resultado = $prop->actualizar($data, $actualizarImagen);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Actualización exitosa', 'Datos actualizados correctamente', '/vetwilling/cliente/perfil');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el propietario. Intenta nuevamente');
    }
    exit();
}

function eliminarPropietario($id)
{
    $obj = new Propietario();
    $resultado = $obj->eliminar($id);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Propietario inhabilitado',
            'El propietario ha sido inhabilitado correctamente',
            '/vetwilling/admin/listar-propietarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar');
    }

    exit();
}


// FUNCION PARA CAMBIAR LA FOTO DE PERFIL 
function fotoPerfil()
{
    session_start();

    $id_usuario = $_POST['id_usuario'] ?? '';

    if (empty($id_usuario)) {
        mostrarSweetAlert('error', 'Error', 'ID de usuario no proporcionado');
        exit();
    }

    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['img_perfil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'webp'];

        // Validar extensión
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG o WEBP');
            exit();
        }

        // Validar tamaño (2MB máximo)
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Archivo muy grande', 'La foto no debe superar los 2MB');
            exit();
        }

        // Obtener la foto anterior para eliminarla
        $objVeterinario = new Propietario();
        $veterinarioActual = $objVeterinario->consultarPropietario($id_usuario);
        $fotoAnterior = $veterinarioActual['img_perfil'] ?? null;

        // Generar nombre único
        $img_perfil = uniqid('vet_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $img_perfil;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $destino)) {

            // Actualizar en base de datos
            $data = [
                'id_usuario' => $id_usuario,
                'img_perfil' => $img_perfil
            ];

            $resultado = $objVeterinario->fotoPerfil($data);

            if ($resultado) {
                // Eliminar foto anterior si existe y no es la por defecto
                if ($fotoAnterior && $fotoAnterior !== 'foto_default.jpg') {
                    $rutaAnterior = BASE_PATH . '/public/uploads/usuarios/' . $fotoAnterior;
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                // Actualizar sesión
                $_SESSION['user']['img_perfil'] = $img_perfil;

                mostrarSweetAlert(
                    'success',
                    '¡Foto actualizada!',
                    'Tu foto de perfil se ha actualizado correctamente',
                    '/vetwilling/cliente/perfil'
                );
            } else {
                mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la imagen en la base de datos');
            }
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo guardar la imagen');
        }
    } else {
        mostrarSweetAlert('error', 'Error', 'No se ha seleccionado ninguna imagen');
    }

    exit();
}

// Función para listar propietarios asociados a una veterinaria
function listarPropietariosVeterinaria($id_veterinaria)
{

    try { // Creamos una instancia del modelo Servicio
        $obj = new Propietario();
        $lista = $obj->listarPropietariosVeterinaria($id_veterinaria);

        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'propietarios' => $lista,
        ]);
    } catch (Exception $e) {
        // ┌─ RETORNAR ERROR    
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los horarios del servicio: ' . $e->getMessage()
        ]);
    }
}

function eliminarPropietarioAJAX($id)
{
    try {
        $obj = new Propietario();
        $resultado = $obj->eliminar($id);


        if ($resultado) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Propietario inhabilitado correctamente',
            ]);
        } else {
            header('Content-Type: application/json');
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo eliminar el propietario',
            ]);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al eliminar el propietario: ' . $e->getMessage()
        ]);
    }

    exit();
}

// ==================== FIN CRUD ====================
