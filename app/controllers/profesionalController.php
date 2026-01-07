<?php
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Profesional.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Llamar a la función para guardar el profesional
        registrarProfesional();
        break;
    case 'GET':
        
        break;
    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

function registrarProfesional()
{
    // Obtener los datos del formulario
    $email = $_POST['email'] ?? '';
    $password = '123';
    $estado = 'Activo';
    $id_rol = $_POST['rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $id_veterinaria = $_POST['id_veterinaria'] ?? null;
    $nivel_acceso = 'Completo';
    $img_perfil = null;
    $img_firma = null;
    $listaEspecialidades = $_POST['especialidades'] ?? '';

    // Validamos que los campos no esten vacios
    if (
        empty($email) || empty($password) || empty($estado) || empty($id_rol) ||
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos) || empty($telefono) || empty($direccion) || empty($id_veterinaria) ||
        empty($listaEspecialidades)
    ) {
        // Mostrar alerta de error si hay campos vacíos
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
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
        $destino = BASE_PATH . '/public/uploads/profesionales/' . $img_perfil;
        move_uploaded_file($file['tmp_name'], $destino);
    }

    // Imagen de firma
    if (!empty($_FILES['firma']['name'])) {
        $file = $_FILES['firma'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];
        // Validar extensión y tamaño
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
            exit();
        }
        // Validar tamaño
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error', 'La firma supera las 2MB');
            exit();
        }
        // Generar un nombre único para la imagen
        $img_firma = uniqid('firma_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/firmas/' . $img_firma;
        move_uploaded_file($file['tmp_name'], $destino);
     }

     $objProfesional = new Profesional();
     
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
        'img_firma' => $img_firma,
        'especialidades' => $listaEspecialidades,
        'id_veterinaria' => $id_veterinaria,
        'nivel_acceso' => $nivel_acceso,
        'registro_medico' => $_POST['registro_medico'] ?? ''
    ];

    // Registramos el usuario
    $resultado = $objProfesional->registrarProfesional($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Profesional registrado',
            'El profesional ha sido creado correctamente',
            '/vetwilling/representante/registro-profesionales'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar el profesional');
    }

    exit();
}

// FUNCION PARA LISTAR LOS USUARIOS REGISTRADOS
function listarUsuarios($id_veterinaria)
{
    $resultado = new Profesional();
    return $resultado->listar($id_veterinaria);
}
