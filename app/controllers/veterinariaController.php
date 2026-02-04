<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/veterinaria.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarVeterinaria();
        } else {
            registrarVeterinaria();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $accion = $_GET['action'] ?? '';
        if ($accion === 'eliminar') {
            eliminarVeterinaria($_GET['id']);
        }
        break;

    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

// =========================================
//  FUNCIONES CRUD
// =========================================

// FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
function registrarVeterinaria()
{
    // Capturamos los datos enviados por el formulario
    $nit = $_POST['nit'] ?? '';
    $nombreVeterinaria = $_POST['nombreVeterinaria'] ?? '';
    $direccionVeterinaria = $_POST['direccionVeterinaria'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefonoVeterinaria = $_POST['telefonoVeterinaria'] ?? '';
    $emailVeterinaria = $_POST['emailVeterinaria'] ?? '';
    $fotoVeterinaria = null;


    // Validamos los campos recibidos de representante legal
    $email = $_POST['email'] ?? '';
    $password = $_POST['numero_documento'] ?? '123';
    $estado = 'activo';
    $id_rol = '4'; // Rol de representante legal
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $nivel_acceso = 'Completo';
    $img_perfil = null;


    // Validamos que los campos no esten vacios
    if (
        empty($nit) || empty($nombreVeterinaria) || empty($direccionVeterinaria) || empty($ciudad) || empty($telefonoVeterinaria) ||
        empty($emailVeterinaria) ||
        empty($email) || empty($password) || empty($estado) || empty($id_rol) ||
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos) || empty($telefono) || empty($direccion)
    ) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    // Validamos y procesamos la foto de la veterinaria si se ha enviado
    if (!empty($_FILES['fotoVeterinaria']['name'])) {

        $file = $_FILES['fotoVeterinaria'];
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
        $fotoVeterinaria = uniqid('veterinaria_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/veterinaria/' . $fotoVeterinaria;
        move_uploaded_file($file['tmp_name'], $destino);
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

    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();

    // Preparamos los datos para el registro
    $data = [
        'nit' => $nit,
        'nombreVeterinaria' => $nombreVeterinaria,
        'direccionVeterinaria' => $direccionVeterinaria,
        'ciudad' => $ciudad,
        'telefonoVeterinaria' => $telefonoVeterinaria,
        'emailVeterinaria' => $emailVeterinaria,
        'fotoVeterinaria' => $fotoVeterinaria,
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
        'nivel_acceso' => $nivel_acceso
    ];
    // Llamamos a la funcion registrar del modelo Veterinaria
    $resultado = $objVeterinaria->registrar($data);

    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria registrada',
            'La veterinaria ha sido creada correctamente',
            '/vetwilling/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar la veterinaria');
    }

    exit();
}

// FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS
function listarVeterinariasRegistradas()
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinarias = $veterinariaModel->listar();
    return $veterinarias;
}

// fUNCION PARA CONSULTAR UNA VETERINARIA POR ID
function consultarVeterinariasRegistradas($id)
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinaria = $veterinariaModel->consultarVeterinariasRegistradas($id);
    return $veterinaria;
}

// consultar veterinarias por arry de ids
function consultarVeterinariasPorArray($idsArray)
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinarias = $veterinariaModel->consultarVeterinariasPorArray($idsArray);
    return $veterinarias;
}

// FUNCION PARA ACTUALIZAR LOS DATOS DE LA VETERINARIA
function actualizarVeterinaria()
{
    // Capturamos los datos enviados por el formulario
    $foto = $_POST['foto_actual'] ?? null;
    $nit = $_POST['nit'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = $_POST['estado'] ?? 'Activo';


    // Validamos que los campos no esten vacios
    if (
        empty($nit) || empty($nombre) || empty($direccion) || empty($ciudad) || empty($telefono) ||
        empty($email) || empty($estado)
    ) {
        // Mostrar un mensaje de error si algún campo está vacío
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }


    // Validamos y procesamos la foto de la veterinaria si se ha enviado
    if (!empty($_FILES['foto']['name'])) {
        $file = $_FILES['foto'];
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
        $foto = uniqid('veterinaria_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/veterinaria/' . $foto;
        move_uploaded_file($file['tmp_name'], $destino);
    }
    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();
    // Preparamos los datos para la actualización
    $data = [
        'nit' => $nit,
        'nombre' => $nombre,
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'telefono' => $telefono,
        'email' => $email,
        'estado' => $estado,
        'foto' => $foto,
        'id_veterinaria' => $_POST['id_veterinaria'] ?? ''
    ];
    // Llamamos a la funcion actualizar del modelo Veterinaria
    $resultado = $objVeterinaria->actualizar($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria actualizada',
            'Los datos han sido actualizados correctamente',
            '/vetwilling/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la veterinaria');
    }

    exit();
}

// FUNCION PARA ELIMINAR UNA VETERINARIA
function eliminarVeterinaria($id)
{
    // Creamos el objeto de la clase Veterinaria
    $objVeterinaria = new Veterinaria();
    // Llamamos a la funcion eliminar del modelo Veterinaria
    $resultado = $objVeterinaria->eliminar($id);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria eliminada',
            'La veterinaria ha sido eliminada correctamente',
            '/vetwilling/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la veterinaria');
    }

    exit();
}

function consultarVeterinariaPorId($id)
{
    // Creamos el objeto de la clase Veterinaria
    $veterinariaModel = new Veterinaria();
    $veterinaria = $veterinariaModel->consultarVeterinariasRegistradas($id);
    return $veterinaria;
}
