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
            // actualizarUsuario();
        } else {
            registrarUsuario();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        if (isset($_GET['id'])) {
            // Esta funcion llena la funcion de de editar un solo veterinario
            $id = $_GET['id'];
            consultarUsuarioId($id);
        } else {
            // Esta funcio llena toda la tabla de veterinarios
            listarUsuarios();
        }
        break;

    //Estas lineas se usarian si se trabajara con apis resful
    // case 'PUT':
    //     actualizarUsuario();
    //     break;

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
    echo($id_veterinaria);


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
    $id_usuario = $_POST['id_usuario'] ?? '';
    $id_veterinaria = $_POST['veterinaria'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $id_rol = $_POST['rol'] ?? '';

    //VALIDAMOS LOS CAMPOS QUE SON OBLIGATORIOS
    if (empty($id_usuario) || empty($numero_documento) || empty($tipo_documento) || empty($nombres) || empty($apellidos) || empty($telefono) || empty($email) || empty($id_rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    if ($id_rol == '3' && empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Veterinaria requerida', 'Debe seleccionar una veterinaria');
        exit();
    }
    echo($id_veterinaria);

    // $objUsuario = new Usuario();
    // $data = [
    //     'id_usuario' => $id_usuario,
    //     'tipo_documento' => $tipo_documento,
    //     'numero_documento' => $numero_documento,
    //     'nombres' => $nombres,
    //     'apellidos' => $apellidos,
    //     'telefono' => $telefono,
    //     'email' => $email,
    //     'estado' => $estado,
    //     'id_rol' => $id_rol,
    //     'id_veterinaria' => $id_veterinaria
    // ];

    // $resultado = $objUsuario->registrar($data);

    // //Si la respuesta del modelo es verdadera confirmamos el registro y redireccionamos 
    // //si es falsa notificamos y redireccionamos
    // if ($resultado === true) {
    //     mostrarSweetAlert(
    //         'success',
    //         'Registro de Usuario exitoso',
    //         'Se ha creado un nuevo usuario',
    //         '/vetwilling/admin/registro-usuario'
    //     );
    // } else {
    //     mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el usuario. Intente nuevamente');
    // }

    exit();
}

function eliminarUsuario($id) {}
    



//     //Instanciamos la clase
//     $objUsuario = new Usuario();

//     //Invocamos el metodo listar() de la clase Usuario
//     return $objUsuario->listar();
// }

//         $listar = "SELECT id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, email, estado, nombre_rol, nombre_veterinaria
//             FROM usuario u
//             LEFT JOIN rol r ON u.id_rol = r.id_rol
//             LEFT JOIN veterinaria v ON u.id_veterinaria = v.id_veterinaria";

//             $resultado = $this->conexion->prepare($listar);
//             $resultado->execute();
//             return $resultado->fetchAll(PDO::FETCH_ASSOC);
//         } catch (PDOException $e) {
//             error_log("Error en el usuario::listar " . $e->getMessage());
//             return [];
//         }
//     }
// class Usuario
// {
//     private $conexion;

//     public function __construct()
//     {
//         // Conexión a la base de datos
//         $host = 'localhost';
//         $db = 'vetwilling';
//         $user = 'root';
