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

    // capturamos en variables los datos desde el formulario a travez del metodo POST y los name de los campos

    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_rol = '2';
    $password_hash = '123456';
    $estado = 'activo';
    $tipo_usuario = '2';
    $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? '';
    $ruta_img = $_POST['img_perfil'] ?? '';

    // Validamos los caampos que son obligatorios

    if (empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($telefono) || empty($email)) {
        mostrarSweetAlert('error', 'Campos vacios', 'Por favor completar todos los campos');
        exit();
    }

    // capturamos el id del usuario que inicia secion para guardarlo solo si es necesario

    session_start();
    $id_veterinaria = $_SESSION['user']['id_veterinaria'];

    // POO - instanciamos la clase

    // Logica para cargar imagenes

    $ruta_img = null;

    // validamos si se envio o no la foto desde el formulario
    // * si el usuario no registro una foto, dejar una foto definida *

    if (!empty($_FILES['img_perfil'])) {

        $file = $_FILES['img_perfil'];

        // *Obtenemos el la extencion del archivo

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // *Definimos las extenciones permitidas 

        $permitidas = ['png', 'jpg', 'jpeg'];

        // *validamos que la extencion de las imagenes esta dentro de las permitidas

        if (!in_array($ext, $permitidas)) {

            mostrarSweetAlert('error', 'Extencion no permitida', 'Recuerda que solo soporta archivos png, jpeg y jpg');
            exit();
        }

        // * Validamos el tamaño o el peso MAX 2 mb

        if ($file['size'] > 2 * 1024 * 1024) {

            mostrarSweetAlert('erro', 'Error al cargar la foto', 'El tamaño de la foto supera las 2 MB');
            exit();
        }

        // *Definimos el nombre del archivo y le concatenamos la extencion
        $ruta_img = uniqid('user_') . '.' . $ext;

        // *Definimos el destino donde moveremos el archivo
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $ruta_img;

        // *Movemos el archivo al destino
        move_uploaded_file($file['tmp_name'], $destino);
    } else {
        // *agregar la logica de la imagen por default

        $ruta_img = 'foto_default.jpg';
    }

    $objVeterinario = new Veterinario();
    $data = [
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'telefono' => $telefono,
        'email' => $email,
        'id_rol' => $id_rol,
        'password_hash' => $password_hash,
        'estado' => $estado,
        'tipo_usuario' => $tipo_usuario,
        'id_veterinaria' => $id_veterinaria,
        'img_perfil' => $ruta_img
    ];

    // Enviamos la data al metodo (registrar) de la clase instanciada anteriormente (Veterinario) y esperamos una respuesta booleana del modelo en resultados

    $resultado = $objVeterinario->registrar($data);

    // Si la respuesta del modelo es verdadera confirmamos el registro y redireccionameos, si es falsa notificamos y redireccionamos

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Registro del veterinario exitoso', 'Se ha creado un nuevo veterinario en la veterinaria', '/vetwilling/veterinario/registrar-veterinario');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el veterinario. Intenta nuevamente');
    }
    exit();
}

function mostrarVeterinarios()
{

    // session_start();
    $id_veterinaria = $_SESSION['user'];

    // echo $id_veterinaria;

    $resultado = new Veterinario();
    $veterinario = $resultado->listar($id_veterinaria);

    return $veterinario;
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
