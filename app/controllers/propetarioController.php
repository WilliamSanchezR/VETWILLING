<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Propietario.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarPropietario();
        }
        break;

    case 'GET':

        $accion = $_GET['accion'] ?? '';

        // --- ELIMINAR ---
        if ($accion === 'eliminar' && isset($_GET['id'])) {
            eliminarPropietario($_GET['id']);
            exit();
        }

        // --- CONSULTAR UN PROPIETARIO ---
        if ($accion === 'consultar' && isset($_GET['id'])) {
            $data = consultarPropietarioId($_GET['id']);
            echo json_encode($data);
            exit();
        }

        // --- SI HAY ID PERO SIN ACCION, CONSULTA DIRECTA ---
        if (isset($_GET['id'])) {
            echo json_encode(consultarPropietarioId($_GET['id']));
            exit();
        }

        // --- LISTAR TODOS ---
        echo json_encode(listarPropietarios());
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
    $obj = new Propietario();
    return $obj->consultarPropietario($id);
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
        mostrarSweetAlert('success', 'Actualización exitosa', 'Datos actualizados correctamente', '/vetwilling/Cliente/perfil');
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
