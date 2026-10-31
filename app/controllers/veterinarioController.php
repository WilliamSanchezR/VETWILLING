<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Veterinario.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';

// Creamos una variable METHOD para que capture las peticiones al servidor

// Acciones POST que admite cada ruta que incluye este controlador (las demás → 400).
// /veterinario/guardar-veterinario está deshabilitada: el veterinario no crea cuentas.
const VETERINARIO_ACCIONES_POST = [
    '/veterinario/actualizar-veterinario' => ['actualizar'],
    '/veterinario/cambiar-foto'           => ['cambiar-foto'],
];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':

        $accion = (string) ($_POST['accion'] ?? '');
        acceso_exigir_accion_de_ruta($accion, VETERINARIO_ACCIONES_POST);

        if ($accion === 'actualizar') {
            actualizarVeterinario();
        } else if ($accion === 'cambiar-foto') {
            actualizarFotoPerfil();
        }

        break;

    case 'GET':

        // Se declara la variable accion para capturar la accion del boton eliminar 
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {

            // Inhabilitar una cuenta no se hace por GET (este controlador se incluye desde vistas)

            acceso_denegar('405', 'inhabilitar veterinario por GET');
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



// Sin ruta activa (/veterinario/guardar-veterinario está deshabilitada). Si se vuelve a usar:
// solo el administrador crea cuentas.
function registrarVeterinario()
{
    acceso_exigir_rol([1]);
    
    session_start();

    // Capturamos en variables los datos desde el formulario
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_rol = '2';
    $password_hash = '123456';
    $estado = 'activo';
    $id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? '';
    $numero_licencia_profesional = $_POST['numero_licencia_profesional'] ?? '';
    
    // ❌ ELIMINA ESTA LÍNEA: $ruta_img = $_POST['img_perfil'] ?? '';

    // Validamos los campos que son obligatorios
    if (empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($telefono) || empty($email)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completar todos los campos');
        exit();
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        mostrarSweetAlert('error', 'Email inválido', 'Por favor ingresa un correo válido');
        exit();
    }

    // Validar que exista la sesión de veterinaria
    if (empty($id_veterinaria)) {
        mostrarSweetAlert('error', 'Error de sesión', 'No se encontró la veterinaria asociada');
        exit();
    }

    // Lógica para cargar imágenes
    $ruta_img = 'foto_default.jpg'; // ✅ Valor por defecto

    // Validamos si se subió realmente un archivo
    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['img_perfil'];

        // Obtenemos la extensión del archivo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Definimos las extensiones permitidas 
        $permitidas = ['png', 'jpg', 'jpeg', 'webp'];

        // Validamos que la extensión de las imágenes esté dentro de las permitidas
        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Recuerda que solo soporta archivos png, jpeg, jpg y webp');
            exit();
        }

        // Validamos el tamaño o el peso MAX 2 MB
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar la foto', 'El tamaño de la foto supera los 2 MB');
            exit();
        }

        // Definimos el nombre del archivo y le concatenamos la extensión
        $ruta_img = uniqid('vet_') . '.' . $ext;

        // Definimos el destino donde moveremos el archivo
        $destino = BASE_PATH . '/public/uploads/veterinarios/' . $ruta_img;

        // Verificar que el directorio exista
        if (!is_dir(BASE_PATH . '/public/uploads/veterinarios/')) {
            mkdir(BASE_PATH . '/public/uploads/veterinarios/', 0755, true);
        }

        // Movemos el archivo al destino
        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar la imagen');
            exit();
        }
    }

    // POO - instanciamos la clase
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
        'id_veterinaria' => $id_veterinaria,
        'img_perfil' => $ruta_img,
        'numero_licencia_profesional' => $numero_licencia_profesional
    ];

    // Enviamos la data al método registrar
    try {
        $resultado = $objVeterinario->registrar($data);

        // Si la respuesta del modelo es verdadera confirmamos el registro
        if ($resultado === true) {
            mostrarSweetAlert('success', 'Registro del veterinario exitoso', 'Se ha creado un nuevo veterinario en la veterinaria', BASE_URL . '/veterinario/consultar-veterinarios');
        } else {
            mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el veterinario. Intenta nuevamente');
        }
    } catch (Exception $e) {
        error_log("EXCEPCIÓN EN REGISTRO: " . $e->getMessage());
        mostrarSweetAlert('error', 'Error al registrar', 'Error: ' . $e->getMessage());
    }
    
    exit();
}

function mostrarVeterinarios()
{

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $id_veterinaria = $_SESSION['user']['id_veterinaria'];

    // session_start();

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

// Veterinario que el usuario de la sesión puede ver y editar; si no, 404.
// El veterinario solo a sí mismo; el representante, los veterinarios de su clínica (criterio
// provisional mientras D1 esté pendiente).
function exigirVeterinarioEnAlcance($idUsuario): array
{
    $usuario = acceso_exigir_rol([2, 4]);
    $id = acceso_entero_positivo($idUsuario);
    $veterinario = $id === null ? null : (new Veterinario())->listarVeterinario($id);
    $enAlcance = $veterinario && ((int) $usuario['id_rol'] === 2
        ? $id === (int) $usuario['id_usuario']
        : (int) $veterinario['id_veterinaria'] === acceso_exigir_clinica());
    if (!$enAlcance) {
        acceso_denegar('404', 'veterinario fuera del alcance de la sesión', ['GET', 'HEAD', 'POST']);
    }
    return $veterinario;
}

// Actualiza el perfil del veterinario. El correo de acceso y el estado de la cuenta no cambian
// por esta vía: solo el administrador los cambia.
function actualizarVeterinario()
{
    $veterinario = exigirVeterinarioEnAlcance($_POST['id_usuario'] ?? null);

    // capturamos en variables los datos desde el formulario a travez del metodo POST y los name de los campos

    $id_usuario = (int) $veterinario['id_usuario'];
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $veterinario['email'];
    $id_rol = '2';
    $password_hash = '123';
    $estado = $veterinario['estado'];
    $tipo_usuario = 'Veterinario';
    $id_veterinaria = $veterinario['id_veterinaria'];

    // Validamos los caampos que son obligatorios

    if (empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($telefono)) {
        mostrarSweetAlert('error', 'Campos vacios', 'Por favor completar todos los campos');
        exit();
    }

    // POO - instanciamos la clase

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
        'password_hash' => $password_hash,
        'estado' => $estado,
        'tipo_usuario' => $tipo_usuario,
        'id_veterinaria' => $id_veterinaria,
        'numero_licencia_profesional' => $veterinario['numero_licencia_profesional']
    ];

    // Enviamos la data al metodo (registrar) de la clase instanciada anteriormente (Veterinario) y esperamos una respuesta booleana del modelo en resultados

    $resultado = $objVeterinario->actualizar($data);

    // Si la respuesta del modelo es verdadera confirmamos el registro y redireccionameos, si es falsa notificamos y redireccionamos

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Actualizacion del veterinario exitoso', 'Se ha actualizado el veterinario', BASE_URL . '/veterinario/consultar-veterinarios');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el veterinario. Intenta nuevamente');
    }
    exit();
}

// Sin llamadores vivos (/veterinario/eliminar-veterinario está deshabilitada). Si se vuelve a
// usar: solo el administrador, por POST con token CSRF.
function eliminarVeterinario($id)
{
    acceso_exigir_accion_post([1]);

    $objVeterinario = new Veterinario();
    $respuesta = $objVeterinario->eliminar($id);

    if ($respuesta === true) {
        mostrarSweetAlert('success',
            'Veterinario Inhabilitado',
            'El veterinario ha sido Inhabilitado', BASE_URL . '/veterinario/consultar-veterinarios');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'No se pudo eliminar el veterinario. Intenta nuevamente');
    }
    exit();
}


// FUNCION PARA CAMBIAR LA FOTO DE PERFIL 
function actualizarFotoPerfil()
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
        $objVeterinario = new Veterinario();
        $veterinarioActual = $objVeterinario->listarVeterinario($id_usuario);
        $fotoAnterior = $veterinarioActual['img_perfil'] ?? null;
        
        // Generar nombre único
        $img_perfil = uniqid('vet_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/profesionales/' . $img_perfil;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $destino)) {
            
            // Actualizar en base de datos
            $data = [
                'id_usuario' => $id_usuario,
                'img_perfil' => $img_perfil
            ];
            
            $resultado = $objVeterinario->actualizarFotoPerfil($data);
            
            if ($resultado) {
                // Eliminar foto anterior si existe y no es la por defecto
                if ($fotoAnterior && $fotoAnterior !== 'foto_default.jpg') {
                    $rutaAnterior = BASE_PATH . '/public/uploads/profesionales/' . $fotoAnterior;
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
                    BASE_URL . '/veterinario/consultar-perfil'
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