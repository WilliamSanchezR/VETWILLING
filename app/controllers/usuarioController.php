<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/usuario.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarUsuario();
        } else {
            registrarUsuario();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarUsuario($_GET['id']);
        } else if (isset($_GET['id'])) {
            consultarUsuarioId($_GET['id']);
        } else {
            listarUsuarios();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}


// =========================================
//  FUNCIONES CRUD
// =========================================

function registrarUsuario()
{
    $email = $_POST['email'] ?? '';
    $password = '123';
    $estado = 'activo';
    $id_rol = $_POST['rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? null;
    $nivel_acceso = 'Completo';
    $img_perfil = null;

    if (
        empty($email) || empty($password) || empty($estado) || empty($id_rol) ||
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    if ($id_rol == '3' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }

    // IMAGEN DEL PERFIL
    if (!empty($_FILES['img_perfil']['name'])) {

        $file = $_FILES['img_perfil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error', 'La foto supera las 2MB');
            exit();
        }

        $img_perfil = uniqid('user_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $img_perfil;
        move_uploaded_file($file['tmp_name'], $destino);
    } else {
        $img_perfil = 'foto_default.jpg';
    }

    $objUsuario = new Usuario();

    $data = [
        'email' => $email,
        'password' => $password,
        'estado' => $estado,
        'id_rol' => $id_rol,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono,
        'img_perfil' => $img_perfil,
        'id_veterinaria' => $id_veterinaria,
        'nivel_acceso' => $nivel_acceso,
    ];

    $resultado = $objUsuario->registrar($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Usuario registrado',
            'El usuario ha sido creado correctamente',
            '/vetwilling/admin/registro-usuario'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar el usuario');
    }

    exit();
}

function listarUsuarios()
{
    $resultado = new Usuario();
    return $resultado->listar();
}

function consultarUsuarioId($id)
{
    $objUsuario = new Usuario();
    return $objUsuario->consultarUsuario($id);
}

function actualizarUsuario()
{
    $id_usuario = $_POST['id_usuario'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_rol = $_POST['id_rol'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? null;
    $estado = $_POST['estado'] ?? 'activo';

    if (
        empty($id_usuario) || empty($tipo_documento) || empty($numero_documento) ||
        empty($nombres) || empty($apellidos) || empty($telefono) || empty($email) ||
        empty($id_rol)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete todos los campos');
        exit();
    }

    if ($id_rol == '3' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }

    $objUsuario = new Usuario();

    $data = [
        'id_usuario' => $id_usuario,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono,
        'email' => $email,
        'estado' => $estado,
        'id_rol' => $id_rol,
        'id_veterinaria' => $id_veterinaria
    ];

    $resultado = $objUsuario->actualizarUsuario($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Usuario actualizado',
            'Los datos han sido actualizados correctamente',
            '/vetwilling/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar el usuario');
    }

    exit();
}

function eliminarUsuario($id)
{
    $objUsuario = new Usuario();
    $resultado = $objUsuario->eliminarUsuario($id);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Usuario Inhabilitado',
            'El usuario ha sido Inhabilitado',
            '/vetwilling/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el usuario');
    }

    exit();
}
