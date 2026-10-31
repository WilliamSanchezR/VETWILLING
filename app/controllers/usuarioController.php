<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/usuario.php';

// Estados de cuenta que acepta el administrador (usuario.estado)
const USUARIO_ESTADOS_CUENTA = ['activo', 'inactivo', 'bloqueado'];

// Acciones POST que admite cada ruta que incluye este controlador. Las rutas de contraseña
// solo cambian la contraseña; crear cuentas es solo /admin/guardar-usuario (rol 1).
const USUARIO_ACCIONES_POST = [
    '/admin/guardar-usuario'             => ['registrar'],
    '/admin/actualizar-usuario'          => ['actualizar'],
    '/admin/eliminar-usuario'            => ['eliminar'],
    '/admin/cambiar-foto'                => ['cambiar-foto'],
    '/admin/actualizar-contrasena'       => ['actualizar-constrasena'],
    '/veterinario/actualizar-constrasena' => ['vet-constrasena'],
    '/cliente/actualizar-contrasena'     => ['modificar-contrasena'],
];


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = (string) ($_POST['accion'] ?? '');
        acceso_exigir_accion_de_ruta($accion, USUARIO_ACCIONES_POST);
        if ($accion === 'actualizar') {
            actualizarUsuario();
        } else if ($accion  === 'actualizar-constrasena') {
            cambioContrasena();
        } else if ($accion  === 'modificar-contrasena') {
            modiContrasena();
        } else if ($accion  === 'vet-constrasena') {
            vetContrasena();
        } else if ($accion === 'cambiar-foto') {
            actualizarFotoPerfil();
        } else if ($accion === 'eliminar') {
            eliminarUsuario();
        } else if ($accion === 'registrar') {
            registrarUsuario();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarUsuario(); // solo POST: responde 405
        } elseif ($accion === 'listarAdmin') {
            ListaUsuariosAdmin();
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

// FUNCION PARA REGISTRAR UN NUEVO USUARIO
// Solo el administrador crea cuentas, de cualquier rol existente.
function registrarUsuario()
{
    acceso_exigir_rol([1]);

    // Capturamos los datos enviados por el formulario
    $email = $_POST['email'] ?? '';
    $estado = 'activo';
    $id_rol = $_POST['rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $password = !empty($numero_documento) ? $numero_documento : '123';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? null;
    $nivel_acceso = 'Completo';
    $img_perfil = null;

    // Validamos que los campos no esten vacios
    if (
        empty($email) || empty($password) || empty($estado) || empty($id_rol) ||
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos) || empty($telefono) || empty($direccion)
    ) {
        // Mostrar alerta de error si hay campos vacíos
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }
    if (!in_array(acceso_entero_positivo($id_rol), ACCESO_ROLES_VALIDOS, true)) {
        mostrarSweetAlert('error', 'Rol no válido', 'Seleccione un rol de la lista');
        exit();
    }

    // Validamos que si el rol es veterinario, se seleccione una veterinaria
    if ($id_rol == '4' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }

    // Imagen de perfil
    if (!empty($_FILES['img_perfil']['name'])) {

        $file = $_FILES['img_perfil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];
        // Validar extensión y tamaño
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
            exit();
        }
        // Validar tamaño
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error', 'La foto supera las 2MB');
            exit();
        }
        // Generar un nombre único para la imagen
        $img_perfil = uniqid('user_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $img_perfil;
        move_uploaded_file($file['tmp_name'], $destino);
    }
    // Creamos el objeto de la clase Usuario
    $objUsuario = new Usuario();
    // Preparamos los datos para el registro
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
        'direccion' => $direccion,
        'img_perfil' => $img_perfil,
        'id_veterinaria' => $id_veterinaria,
        'nivel_acceso' => $nivel_acceso,
    ];
    // Registramos el usuario
    $resultado = $objUsuario->registrar($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Usuario registrado',
            'El usuario ha sido creado correctamente',
            BASE_URL . '/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar el usuario');
    }

    exit();
}

// FUNCION PARA LISTAR LOS USUARIOS REGISTRADOS
function listarUsuarios()
{
    $resultado = new Usuario();
    return $resultado->listar();
}

function ListarTodosUsuarios()
{
    $resultado = new Usuario();
    return $resultado->ListarTodosUsuarios();
}

// FUNCION PARA CONSULTAR UN USUARIO POR ID
function consultarUsuarioId($id)
{
    $objUsuario = new Usuario();
    return $objUsuario->consultarUsuario($id);
}

// FUNCION PARA CONSULTAR UN USUARIO POR ID para ticket
function consultarUsuarioTicketId($id)
{
    $objUsuario = new Usuario();
    return $objUsuario->consultarUsuarioTicket($id);
}

// FUNCION PARA ACTUALIZAR LOS DATOS DEL USUARIO
// Solo el administrador: este controlador también se incluye desde rutas de otros roles.
function actualizarUsuario()
{
    $actor = acceso_exigir_rol([1]);
    $id_usuario = $_POST['id_usuario'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_rol = $_POST['id_rol'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? null;
    $direccion = $_POST['direccion'] ?? null;
    $estado = mb_strtolower(trim((string) ($_POST['estado'] ?? 'activo')));

    if (
        empty($id_usuario) || empty($tipo_documento) || empty($numero_documento) ||
        empty($nombres) || empty($apellidos) || empty($telefono) || empty($email) ||
        empty($id_rol) || empty($direccion)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete todos los campos');
        exit();
    }

    if (!in_array($estado, USUARIO_ESTADOS_CUENTA, true)) {
        mostrarSweetAlert('error', 'Estado no válido', 'Seleccione un estado de la lista');
        exit();
    }

    $objUsuario = new Usuario();
    $cuenta = $objUsuario->consultarCuenta((int) acceso_entero_positivo($id_usuario));
    if (!$cuenta) {
        acceso_denegar('404', 'usuario inexistente');
    }
    // El rol de la cuenta sale de la base, no del formulario
    $id_rol = (string) $cuenta['id_rol'];

    if ($id_rol == '4' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }

    $cambioEstado = $objUsuario->cambiarEstadoCuenta((int) $cuenta['id_usuario'], $estado, (int) $actor['id_usuario']);
    if ($cambioEstado !== 'ok') {
        mostrarSweetAlert('error', 'Estado no permitido', mensajeCambioEstadoCuenta($cambioEstado));
        exit();
    }

    $data = [
        'id_usuario' => $cuenta['id_usuario'],
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono,
        'email' => $email,
        'id_rol' => $id_rol,
        'id_veterinaria' => $id_veterinaria,
        'direccion' => $direccion
    ];

    $resultado = $objUsuario->actualizarUsuario($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Usuario actualizado',
            'Los datos han sido actualizados correctamente',
            BASE_URL . '/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar el usuario');
    }

    exit();
}

// FUNCION PARA INHABILITAR UN USUARIO (estado de la cuenta)
// Solo el administrador, por POST con token CSRF. Las sesiones de la cuenta dejan de valer en su
// siguiente petición (acceso_usuario() exige estado activo).
function eliminarUsuario()
{
    $actor = acceso_exigir_accion_post([1]);
    $id = acceso_entero_positivo($_POST['id'] ?? null);

    $objUsuario = new Usuario();
    $resultado = $id === null ? 'no_existe' : $objUsuario->cambiarEstadoCuenta($id, 'inactivo', (int) $actor['id_usuario']);

    if ($resultado === 'no_existe') {
        acceso_denegar('404', 'usuario inexistente');
    }
    if ($resultado === 'ok') {
        mostrarSweetAlert(
            'success',
            'Usuario Inhabilitado',
            'El usuario ha sido Inhabilitado',
            BASE_URL . '/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'No permitido', mensajeCambioEstadoCuenta($resultado), BASE_URL . '/admin/listar-usuarios');
    }

    exit();
}

function mensajeCambioEstadoCuenta(string $resultado): string
{
    return match ($resultado) {
        'propia'       => 'No puedes inhabilitar tu propia cuenta.',
        'ultimo_admin' => 'No se puede inhabilitar al último administrador activo.',
        default        => 'No se pudo cambiar el estado de la cuenta.',
    };
}

// FUNCION PARA CAMBIAR LA CONTRASEÑA DEL USUARIO
function cambioContrasena()
{
    $id_usuario = $_POST['id_usuario'] ?? '';
    $passwordActual = $_POST['contrasena-actual'] ?? '';
    $PasswordNuevo = $_POST['nueva-contrasena'] ?? '';
    $PasswordConfir = $_POST['confi-contrasena'] ?? '';

    if (
        empty($id_usuario) || empty($passwordActual) || empty($PasswordNuevo) ||
        empty($PasswordConfir)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete todos los campos');
        exit();
    }

    if ($PasswordConfir !== $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La confirmación de contraseña no es igual a la constraseña nueva.');
        exit();
    }

    if ($passwordActual === $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La nueva contraseña no puede ser igual a la constraseña actual.');
        exit();
    }

    $objUsuario = new Usuario();

    $data = [
        'id_usuario' => $id_usuario,
        'password_actual' => $passwordActual,
        'nuevo_password' => $PasswordNuevo
    ];

    $resultado = $objUsuario->actualizarContrasena($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Contraseña actualizada',
            'La contraseña han sido actualizada correctamente',
            BASE_URL . '/admin/perfil-administrador'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la contraseña');
    }

    exit();
}
function vetContrasena()
{
    $id_usuario = $_POST['id_usuario'] ?? '';
    $passwordActual = $_POST['contrasena-actual'] ?? '';
    $PasswordNuevo = $_POST['nueva-contrasena'] ?? '';
    $PasswordConfir = $_POST['confi-contrasena'] ?? '';

    if (
        empty($id_usuario) || empty($passwordActual) || empty($PasswordNuevo) ||
        empty($PasswordConfir)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete todos los campos');
        exit();
    }

    if ($PasswordConfir !== $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La confirmación de contraseña no es igual a la constraseña nueva.');
        exit();
    }

    if ($passwordActual === $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La nueva contraseña no puede ser igual a la constraseña actual.');
        exit();
    }

    $objUsuario = new Usuario();

    $data = [
        'id_usuario' => $id_usuario,
        'password_actual' => $passwordActual,
        'nuevo_password' => $PasswordNuevo
    ];

    $resultado = $objUsuario->actualizarContrasena($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Contraseña actualizada',
            'La contraseña han sido actualizada correctamente',
            BASE_URL . '/veterinario/consultar-perfil'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la contraseña');
    }

    exit();
}

function modiContrasena()
{
    $id_usuario = $_POST['id_usuario'] ?? '';
    $passwordActual = $_POST['contrasena-actual'] ?? '';
    $PasswordNuevo = $_POST['nueva-contrasena'] ?? '';
    $PasswordConfir = $_POST['confi-contrasena'] ?? '';

    if (
        empty($id_usuario) || empty($passwordActual) || empty($PasswordNuevo) ||
        empty($PasswordConfir)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete todos los campos');
        exit();
    }

    if ($PasswordConfir !== $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La confirmación de contraseña no es igual a la constraseña nueva.');
        exit();
    }

    if ($passwordActual === $PasswordNuevo) {
        mostrarSweetAlert('error', 'Constraseña Invalida', 'La nueva contraseña no puede ser igual a la constraseña actual.');
        exit();
    }

    $objUsuario = new Usuario();

    $data = [
        'id_usuario' => $id_usuario,
        'password_actual' => $passwordActual,
        'nuevo_password' => $PasswordNuevo
    ];

    $resultado = $objUsuario->actualizarContrasena($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Contraseña actualizada',
            'La contraseña han sido actualizada correctamente',
            BASE_URL . '/login'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la contraseña');
    }

    exit();
}

// FUNCION PARA CAMBIAR LA FOTO DE PERFIL 
function actualizarFotoPerfil()
{
    $id_usuario = $_POST['id_usuario'] ?? '';

    if (empty($id_usuario)) {
        mostrarSweetAlert('error', 'Error', 'ID de usuario no proporcionado');
        exit();
    }

    if (!empty($_FILES['img_perfil']['name'])) {

        $file = $_FILES['img_perfil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];
        // Validar extensión y tamaño
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
            exit();
        }
        // Validar tamaño
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error', 'La foto supera las 2MB');
            exit();
        }
        // Generar un nombre único para la imagen
        $img_perfil = uniqid('user_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $img_perfil;
        move_uploaded_file($file['tmp_name'], $destino);

        // Actualizar en la base de datos
        $objUsuario = new Usuario();
        $data = [
            'id_usuario' => $id_usuario,
            'img_perfil' => $img_perfil
        ];
        $resultado = $objUsuario->actualizarFotoPerfil($data);

        if ($resultado) {
            mostrarSweetAlert(
                'success',
                'Imagen actualizada',
                'La imagen de perfil ha sido actualizada correctamente',
                BASE_URL . '/admin/perfil-administrador'
            );
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la imagen de perfil');
        }
    } else {
        mostrarSweetAlert('error', 'Error', 'No se ha seleccionado ninguna imagen');
    }

    exit();
}

function ListaUsuariosAdmin()
{
    $objUsuario = new Usuario();
    $listaUsuarios = $objUsuario->ListaUsuariosAdmin();
    // Retornar la lista de usuarios en formato JSON
    header('Content-Type: application/json');
    header('Status: 200 OK');
    echo json_encode($listaUsuarios);
    exit();
}
