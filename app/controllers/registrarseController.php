<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Registrarse.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ob_start();
$GLOBALS['registroErrorMostrado'] = false;

//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        registrarseVeterinaria();
        break;

    default:
        http_response_code(405);
        echo "Metodo no encontrado";
        break;
}

// =========================================
//  FUNCIONES CRUD
// =========================================

function guardarDatosRegistroAnterior(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $currentStep = isset($_POST['current_step']) ? (int) $_POST['current_step'] : 0;
    if ($currentStep > 1) {
        $currentStep = 1;
    }

    $_SESSION['registro_old'] = [
        'nit' => trim((string) ($_POST['nit'] ?? '')),
        'nombreVeterinaria' => trim((string) ($_POST['nombreVeterinaria'] ?? '')),
        'direccionVeterinaria' => trim((string) ($_POST['direccionVeterinaria'] ?? '')),
        'ciudad' => trim((string) ($_POST['ciudad'] ?? '')),
        'telefonoVeterinaria' => trim((string) ($_POST['telefonoVeterinaria'] ?? '')),
        'emailVeterinaria' => trim((string) ($_POST['emailVeterinaria'] ?? '')),
        'tipo_documento' => trim((string) ($_POST['tipo_documento'] ?? '')),
        'numero_documento' => trim((string) ($_POST['numero_documento'] ?? '')),
        'nombres' => trim((string) ($_POST['nombres'] ?? '')),
        'apellidos' => trim((string) ($_POST['apellidos'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'telefono' => trim((string) ($_POST['telefono'] ?? '')),
        'direccion' => trim((string) ($_POST['direccion'] ?? '')),
        'plan' => trim((string) ($_POST['plan'] ?? '')),
        'current_step' => $currentStep,
    ];
}

function limpiarDatosRegistroAnterior(): void
{
    unset($_SESSION['registro_old']);
}

function mostrarErrorRegistro(string $titulo, string $mensaje): void
{
    if (!empty($GLOBALS['registroErrorMostrado'])) {
        exit();
    }

    $GLOBALS['registroErrorMostrado'] = true;
    guardarDatosRegistroAnterior();

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    mostrarSweetAlert('error', $titulo, $mensaje, BASE_URL . '/registro');
}

set_exception_handler(function (Throwable $e): void {
    error_log('Excepción no controlada en registrarseController: ' . $e->getMessage());
    mostrarErrorRegistro('Error en el registro', 'Ocurrió un error inesperado. Intenta nuevamente.');
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function (): void {
    $error = error_get_last();

    if ($error === null) {
        return;
    }

    $erroresFatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    if (in_array($error['type'], $erroresFatales, true)) {
        error_log('Error fatal en registrarseController: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']);
        mostrarErrorRegistro('Error en el registro', 'Ocurrió un error interno al procesar el formulario. Intenta nuevamente.');
    }
});

// FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
function registrarseVeterinaria()
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
    $estado = 'pendiente';
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
        mostrarErrorRegistro('Campos vacíos', 'Por favor complete todos los campos');
    }

    // Validamos y procesamos la foto de la veterinaria si se ha enviado
    if (!empty($_FILES['fotoVeterinaria']['name'])) {

        $file = $_FILES['fotoVeterinaria'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];
        // Validar extensión y tamaño
        if (!in_array($ext, $permitidas)) {
            mostrarErrorRegistro('Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
        }
        // Validar tamaño
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarErrorRegistro('Archivo no válido', 'La foto supera las 2MB');
        }
        // Generar un nombre único para la imagen
        $fotoVeterinaria = uniqid('veterinaria_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/veterinaria/' . $fotoVeterinaria;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarErrorRegistro('Error al subir el archivo', 'No se pudo guardar el logo de la veterinaria.');
        }
    }

    // Imagen de perfil
    if (!empty($_FILES['img_perfil']['name'])) {

        $file = $_FILES['img_perfil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];
        // Validar extensión y tamaño
        if (!in_array($ext, $permitidas)) {
            mostrarErrorRegistro('Extensión no permitida', 'Solo archivos PNG, JPEG, JPG');
        }
        // Validar tamaño
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarErrorRegistro('Archivo no válido', 'La foto supera las 2MB');
        }
        // Generar un nombre único para la imagen
        $img_perfil = uniqid('user_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/' . $img_perfil;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarErrorRegistro('Error al subir el archivo', 'No se pudo guardar la foto del representante legal.');
        }
    }

    // Creamos el objeto de la clase VeterinariaRegistrarse
    $objVeterinaria = new VeterinariaRegistrarse();

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
        'nivel_acceso' => $nivel_acceso,
        'plan' => $_POST['plan'] ?? '1'
    ];

    try {
        // Llamamos a la funcion registrarse del modelo VeterinariaRegistrarse
        $resultado = $objVeterinaria->registrarse($data);
        $idSuscripcion = is_array($resultado)
            ? (int) ($resultado['id_suscripcion'] ?? 0)
            : (int) $resultado;

        // Verificamos el resultado y redirigimos a la pasarela de pago
        if ($idSuscripcion > 0) {
            $rutaPago = BASE_URL . '/pasarela-pago?origen=suscripcion&id_suscripcion=' . urlencode((string) $idSuscripcion);
            limpiarDatosRegistroAnterior();

            mostrarSweetAlert(
                'success',
                'Registro completado',
                'La compañía fue registrada correctamente. A continuación serás redirigido a la pasarela de pago para activar tu suscripción.',
                $rutaPago
            );
        }

        mostrarErrorRegistro('Error en el registro', 'No se pudo registrar la compañía. Intenta nuevamente.');
    } catch (Throwable $e) {
        error_log('Error en registrarseVeterinaria: ' . $e->getMessage());
        mostrarErrorRegistro('Error en el registro', $e->getMessage());
    }

    exit();
}
