<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/usuario.php';

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
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
        // Esta variable captura la accion de eliminar
        // Se declara la variable accion para capturar la accion del boton eliminar 
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarUsuario($_GET['id']);
        } else if (isset($_GET['id'])) {
            // Esta funcion llena la funcion de de editar un solo veterinario
            $id = $_GET['id'];
            consultarUsuarioId($id);
        } else {
            // Esta funcio llena toda la tabla de veterinarios
            listarUsuarios();
        }
        break;

    //Estas lineas se usarian si se trabajara con apis resful
    case 'PUT':
        // actualizarUsuario();
        break;

    // case 'DELETE':
    //     eliminarUsuario();
    //     break;

    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

//FUNCIONES CRUD

function registrarUsuario()
{
    //Capturamosen variables los datos enviados desde el formulario a travez de el metodo
    //POST y el  nombre de los campos

    $email = $_POST['email'] ?? '';
    $password = '123';
    $estado = 'activo';
    $id_rol = $_POST['rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? '';
    $img_perfil = '';
    $nivel_acceso = 'Completo';

    //VALIDAMOS LOS CAMPOS QUE SON OBLIGATORIOS
    if (
        empty($email) || empty($password) || empty($estado) || empty($id_rol) || empty($tipo_documento) || empty($numero_documento) || empty($nombres) || empty($apellidos)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    if ($id_rol == '3' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }
    echo ($id_veterinaria);


    //Instanciamos la clase
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

    //Enviamos la data al metodo "registrar()" de la clase instanciada anteriormente "Usuario()"
    // Y esperamos una respuesta booleana del modelo en resultado
    $resultado = $objUsuario->registrar($data);

    //Si la respuesta del modelo es verdadera confirmamos el registro y redireccionamos 
    //si es falsa notificamos y redireccionamos
    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Registro de Usuario exitoso',
            'Se ha creado un nuevo usuario',
            '/vetwilling/admin/registro-usuario'
        );
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el usuario. Intente nuevamente');
    }

    exit();
}

// Funcion para listar los usuarios
function listarUsuarios()
{
    $resultado = new Usuario();
    $usuarios = $resultado->listar();

    return $usuarios;
}

function consultarUsuarioId($id)
{

    $objUsuario = new Usuario();
    $usuario = $objUsuario->consultarUsuario($id);

    return $usuario;
}

function actualizarUsuario()
{
    //Capturamosen variables los datos enviados desde el formulario a travez de el metodo
    //POST y el  nombre de los campos
    $id_veterinaria = $_POST['veterinaria'] ?? '';
    $id_usuario = $_POST['id_usuario'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_rol = $_POST['id_rol'] ?? '';


    //VALIDAMOS LOS CAMPOS QUE SON OBLIGATORIOS
    if (empty($id_usuario) || empty($numero_documento) || empty($tipo_documento) || empty($nombres) || empty($apellidos) || empty($telefono) || empty($email) || empty($id_rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
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
        'id_rol' => $id_rol,
        'id_veterinaria' => $id_veterinaria
    ];

    $resultado = $objUsuario->actualizarUsuario($data);

    //Si la respuesta del modelo es verdadera confirmamos el registro y redireccionamos 
    //si es falsa notificamos y redireccionamos
    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Registro de Usuario exitoso',
            'Se ha creado un nuevo usuario',
            '/vetwilling/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el usuario. Intente nuevamente');
    }

    exit();
}

function eliminarUsuario($id)
{
    $objUsuario = new Usuario();
    $resultado = $objUsuario->elimimarUsuario($id);

    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Eliminación del Usuario exitoso',
            'Se ha eliminado el usuario',
            '/vetwilling/admin/listar-usuarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error al Eliminar', 'No se pudo Eliminar el usuario. Intente nuevamente');
    }

    exit();
}
