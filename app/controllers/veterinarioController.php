<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Veterinario.php';

// Creamos una variable METHOD para que capture las peticiones al servidor

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarVeterinario();
        } else {
            registrarVeterinario();
        }

        break;

    case 'GET':

        // Se declara la variable accion para capturar la accion del boton eliminar 
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {

            // Esta funcion elimina el veterinario con el metodo GET

            eliminarVeterinario($_GET['id']);
        }

        if (isset($_GET['id'])) {

            // Esta  funcion llena el formulari de editar a un veterinario

            listarVeterinario($_GET['id']);
        } else {

            // estafuncion llena la tabla de instructores

            mostrarVeterinarios();
        }

        break;

    // EL PATO DEL INSTRUCTOR SE EQUIVOCO, PATO.
    // case 'PUT':
    //     actualizarVeterinario();
    //     break;

    // case 'DELETE':
    //     eliminarVeterinario();
    //     break;

    default:
        http_response_code(405);
        echo "Metodo no permitido";
        break;
}

// Funciones del crud

function registrarVeterinario()
{

    // capturamos el id del usuario que inicia secion para guardarlo solo si es necesario

    session_start();
    $id_veterinaria = $_SESSION['user']['id_usuario'];


    // capturamos en variables los datos desde el formulario a travez del metodo POST y los name de los campos

    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $numero_licencia_profesional = $_POST['numero_licencia_profesional'] ?? ''; // AGREGADO
    $id_rol = '2'; // Rol de veterinario
    $password = '123'; // Considera usar un password temporal o generado
    $estado = 'activo';
    $id_veterinaria = $id_veterinaria['user']['id_veterinaria'] ?? '';
    $ruta_img = $_POST['img_perfil'] ?? '';

    // Validamos los campos que son obligatorios
    if (
        empty($numero_documento) || empty($nombres) || empty($apellidos) ||
        empty($tipo_documento) || empty($telefono) || empty($email)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completar todos los campos');
        exit();
    }

    // POO - instanciamos la clase

    // Logica para cargar imagenes

    // Lógica para cargar imágenes
    $ruta_img = null;

    // Validamos si se envió o no la foto desde el formulario
    if (!empty($_FILES['img_perfil']['name'])) {
        $file = $_FILES['img_perfil'];

        // Obtenemos la extensión del archivo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Definimos las extensiones permitidas
        $permitidas = ['png', 'jpg', 'jpeg'];

        // Validamos que la extensión esté dentro de las permitidas
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Recuerda que solo soporta archivos png, jpeg y jpg');
            exit();
        }

        // Validamos el tamaño o el peso MAX 2 MB
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar la foto', 'El tamaño de la foto supera los 2 MB');
            exit();
        }

        // Definimos el nombre del archivo y le concatenamos la extensión
        $ruta_img = uniqid('user_') . '.' . $ext;

        // Definimos el destino donde moveremos el archivo
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $ruta_img;

        // Movemos el archivo al destino
        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error', 'No se pudo guardar la imagen');
            exit();
        }
    } else {
        // Imagen por default
        $ruta_img = 'foto_default.jpg';
    }

    // Instanciamos la clase Veterinario
    $objVeterinario = new Veterinario();

    // Preparamos los datos con TODOS los campos necesarios
    $data = [
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'telefono' => $telefono,
        'email' => $email,
        'id_rol' => $id_rol,
        'password' => $password, // Cambiado de password_hash a password
        'estado' => $estado,
        'id_veterinaria' => $id_veterinaria,
        'img_perfil' => $ruta_img,
        'numero_licencia_profesional' => $numero_licencia_profesional // AGREGADO
    ];

    // Enviamos la data al método registrar
    $resultado = $objVeterinario->registrar($data);

    // Validamos el resultado
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Registro del veterinario exitoso', 'Se ha creado un nuevo veterinario en la veterinaria', '/vetwilling/veterinario/registrar-veterinario');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el veterinario. Intenta nuevamente');
    }
    exit();
}

function mostrarVeterinarios()
{

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id_veterinaria = $_SESSION['user'];


    $resultado = new Veterinario();
    $veterinarios = $resultado->listar($id_veterinaria);

    return $veterinarios;
}

function listarVeterinario($id)
{

    $objVeterinario = new Veterinario();
    $veterinario = $objVeterinario->listarVeterinario($id);

    return $veterinario;
}

function actualizarVeterinario()
{
    $id_usuario = $_POST['id_usuario'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $id_rol = $_POST['id_rol'] ?? '2';

    // Validación
    if (
        empty($numero_documento) ||
        empty($nombres) ||
        empty($apellidos) ||
        empty($tipo_documento) ||
        empty($telefono) ||
        empty($email)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completar todos los campos');
        exit();
    }

    // Enviar solo los campos que el modelo utiliza realmente
    $objVeterinario = new Veterinario();
    $data = [
        'id_usuario' => $id_usuario,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'telefono' => $telefono,
        'email' => $email,
        'id_rol' => $id_rol,
        'estado' => $estado
    ];

    $resultado = $objVeterinario->actualizar($data);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Actualización exitosa', 'El veterinario ha sido actualizado', '/vetwilling/veterinario/consultar-veterinario');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el veterinario');
    }
    exit();
}

function eliminarVeterinario($id)
{

    $objVeterinario = new Veterinario();
    $respuesta = $objVeterinario->eliminar($id);

    if ($respuesta === true) {
        mostrarSweetAlert('success', 'Eliminacion del veterinario exitosa', 'Se ha eliminado el veterinario de la veterinaria', '/vetwilling/veterinario/registrar-veterinario');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'No se pudo eliminar el veterinario. Intenta nuevamente');
    }
    exit();
}
