<?php
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';
require_once __DIR__ . '/../models/Profesional.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';
require_once __DIR__ . '/../helpers/email_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarProfesional();
        } else if ($accion === 'eliminar') {
            eliminarProfesional();
        } else if ($accion === 'eliminarEspProfesional') {
            eliminarEspecialidadDeProfesional();
        } else if ($accion === 'eliminarServProfesional') {
            eliminarServicioDeProfesional();
        } else {
            // Llamar a la función para guardar el profesional
            registrarProfesional();
        }
        break;
    case 'GET':
        // Las eliminaciones solo se aceptan por POST con token CSRF
        $accion = $_GET['action'] ?? '';
        if (in_array($accion, ['eliminar', 'eliminarEspProfesional', 'eliminarServProfesional'], true)) {
            acceso_denegar('405', 'eliminación por GET');
        }
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
    $estado = 'Activo';
    $id_rol = $_POST['rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $password = $numero_documento !== '' ? $numero_documento : '123';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    // El representante registra profesionales solo en su clínica
    acceso_exigir_rol([4]);
    $id_veterinaria = acceso_exigir_clinica();
    $nivel_acceso = 'Completo';
    $img_perfil = null;
    $img_firma = null;
    $listaEspecialidades = $_POST['especialidades'] ?? '';
    $listaServicios = $_POST['servicios'] ?? '';
   
    // Validamos que los campos no esten vacios
    if (
        empty($email) || empty($password) || empty($estado) || empty($id_rol) ||
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos) || empty($telefono) || empty($direccion) || empty($id_veterinaria)
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
        'registro_medico' => $_POST['registro_medico'] ?? '',
        'servicios' => $listaServicios
    ];

    // Registramos el usuario
    $resultado = $objProfesional->registrarProfesional($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        $emailEnviado = false;
        if (validarFormatoEmail($email)) {
            $emailEnviado = enviarBienvenidaProfesional([
                'email' => $email,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'numero_documento' => $numero_documento,
                'registro_medico' => $_POST['registro_medico'] ?? ''
            ]);
        }

        mostrarSweetAlert(
            'success',
            'Profesional registrado',
            $emailEnviado
                ? 'El profesional ha sido creado correctamente y el correo fue enviado.'
                : 'El profesional ha sido creado correctamente, pero no se pudo enviar el correo.',
            BASE_URL . '/representante/listar-profesionales'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar el profesional');
    }

    exit();
}

function enviarBienvenidaProfesional(array $datos): bool
{
    try {
        $mail = mailer_init();

        $email = $datos['email'] ?? '';
        $nombreCompleto = trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? ''));
        $numeroDocumento = (string) ($datos['numero_documento'] ?? '');
        $registroMedico = (string) ($datos['registro_medico'] ?? '');

        $mail->setFrom(SMTP_FROM_EMAIL, 'VetWilling - Sistema de Gestion Veterinaria');
        $mail->addAddress($email, $nombreCompleto);
        $mail->isHTML(true);
        $mail->Subject = 'Registro profesional en VetWilling';

        $nombreSeguro = htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8');
        $emailSeguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $documentoSeguro = htmlspecialchars($numeroDocumento, ENT_QUOTES, 'UTF-8');
        $registroSeguro = htmlspecialchars($registroMedico, ENT_QUOTES, 'UTF-8');
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        $registroHtml = $registroSeguro !== ''
            ? "<p><strong>Registro medico:</strong> {$registroSeguro}</p>"
            : "";

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 640px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0a932c; color: white; padding: 24px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 28px; border: 1px solid #ddd; }
                .card { background-color: #ffffff; padding: 18px; margin: 18px 0; border-left: 4px solid #0a932c; border-radius: 6px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .btn { background-color: #0a932c; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>VetWilling</h1>
                    <p>Registro de profesional</p>
                </div>
                <div class='content'>
                    <h2>Hola {$nombreSeguro},</h2>
                    <p>Tu cuenta profesional ha sido registrada exitosamente en VetWilling.</p>
                    <div class='card'>
                        <h3>Credenciales de acceso</h3>
                        <p><strong>Correo:</strong> {$emailSeguro}</p>
                        <p><strong>Contrasena inicial:</strong> {$documentoSeguro}</p>
                        <p>Recuerda cambiar la contrasena en el primer ingreso.</p>
                        {$registroHtml}
                    </div>
                    <p>Puedes iniciar sesion desde el siguiente enlace:</p>
                    <a class='btn' href='{$baseUrl}'>Acceder al sistema</a>
                </div>
                <div class='footer'>
                    <p>Este es un correo automatico, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 VetWilling</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Hola {$nombreCompleto},\n\n"
            . "Tu cuenta profesional fue registrada en VetWilling.\n"
            . "Correo: {$email}\n"
            . "Contrasena inicial: {$numeroDocumento}\n"
            . "Recuerda cambiar la contrasena en el primer ingreso.\n"
            . ($registroMedico !== '' ? "Registro medico: {$registroMedico}\n" : "")
            . "\nAccede desde: {$baseUrl}\n\n"
            . "VetWilling";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar bienvenida profesional: " . $e->getMessage());
        return false;
    }
}

// FUNCION PARA LISTAR LOS USUARIOS REGISTRADOS
function listarUsuarios($id_veterinaria)
{
    $resultado = new Profesional();
    return $resultado->listar($id_veterinaria);
}

function consultarProfesional($id)
{
    $objProfesional = new Profesional();
    return $objProfesional->consultarPorId($id);
}

function listarEspecialidadesPorProfesional($id, $idVeterinaria)
{
    $objProfesional = new Profesional();
    return $objProfesional->listarEspecialidadesPorProfesional($id, $idVeterinaria);
}

function listarServiciosPorProfesional($id, $idVeterinaria)
{
    $objProfesional = new Profesional();
    return $objProfesional->listarServiciosPorProfesional($id, $idVeterinaria);
}

// Vínculo del profesional (id_usuario) con la clínica de la sesión; si no existe, 404
function exigirProfesionalDeMiClinica($idUsuario): array
{
    $idVeterinaria = acceso_exigir_clinica();
    $id = acceso_entero_positivo($idUsuario);
    $vinculo = $id === null ? null : (new Profesional())->consultarVinculo($id, $idVeterinaria);
    if (!$vinculo) {
        acceso_denegar('404', 'profesional sin vínculo con la clínica de la sesión', ['GET', 'HEAD', 'POST']);
    }
    return $vinculo + ['id_usuario' => $id, 'id_veterinaria' => $idVeterinaria];
}

function eliminarEspecialidadDeProfesional()
{
    acceso_exigir_accion_post([4]);
    $vinculo = exigirProfesionalDeMiClinica($_POST['id_profesional'] ?? null);
    $id_profesional = $vinculo['id_usuario'];
    $id_especialidad = acceso_entero_positivo($_POST['id_especialidad'] ?? null);

    $objProfesional = new Profesional();
    $resultado = $id_especialidad !== null
        && $objProfesional->deleteEspProfesional($id_especialidad, $id_profesional, $vinculo['id_veterinaria']);
    if (!$resultado) {
        acceso_denegar('404', 'especialidad de otro profesional o de otra clínica');
    }

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Especialidad Eliminada',
            'La especialidad ha sido eliminada correctamente',
            BASE_URL . '/representante/editar-profesional?id=' . $id_profesional
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la especialidad');
    }

    exit();
}

function eliminarServicioDeProfesional()
{
    acceso_exigir_accion_post([4]);
    $vinculo = exigirProfesionalDeMiClinica($_POST['id_usuario'] ?? null);
    $id_usario = $vinculo['id_usuario'];
    $id_servicio = acceso_entero_positivo($_POST['id_servicio'] ?? null);

    $objProfesional = new Profesional();
    $resultado = $id_servicio !== null
        && $objProfesional->deleteServProfesional($id_servicio, (int) $vinculo['id_profesional'], $vinculo['id_veterinaria']);
    if (!$resultado) {
        acceso_denegar('404', 'servicio de otro profesional o de otra clínica');
    }

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Servicio Eliminado',
            'El servicio ha sido eliminado correctamente',
            BASE_URL . '/representante/editar-profesional?id=' . $id_usario
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el servicio');
    }

    exit();
}

// El representante edita solo profesionales vinculados a su clínica (otra clínica → 404).
// No cambia el correo de acceso (solo el administrador) ni el estado de la cuenta: el campo
// "estado" es el del vínculo con su clínica (P3).
function actualizarProfesional()
{
    acceso_exigir_rol([4]);
    $vinculo = exigirProfesionalDeMiClinica($_POST['id_usuario'] ?? null);

    // Obtener los datos del formulario
    $id_profesional = $vinculo['id_profesional'];
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $listaEspecialidades = $_POST['especialidades'] ?? '';
    $registro_medico = $_POST['registro_medico'] ?? '';
    $id_rol = $_POST['rol'] ?? '';
    $estado = ucfirst(mb_strtolower(trim((string) ($_POST['estado'] ?? ''))));
    $id_usuario = $vinculo['id_usuario'];
    $img_perfil = $_POST['img_perfil'] ?? null;
    $img_firma = $_POST['img_firma'] ?? null;
    $id_veterinaria = $vinculo['id_veterinaria'];
    $listaServicios = $_POST['servicios'] ?? '';

    // Validamos que los campos no esten vacios
    if (
        empty($tipo_documento) || empty($numero_documento) || empty($nombres) ||
        empty($apellidos) || empty($telefono) || empty($direccion) || empty($id_rol)
    ) {
        // Mostrar alerta de error si hay campos vacíos
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos');
        exit();
    }

    if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
        mostrarSweetAlert('error', 'Estado no válido', 'Seleccione si el profesional está activo o inactivo en la clínica');
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
        'id_profesional' => $id_profesional,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'especialidades' => $listaEspecialidades,
        'registro_medico' => $registro_medico,
        'id_rol' => $id_rol,
        'estado_vinculo' => $estado,
        'id_prof_veterinaria' => $vinculo['id_prof_veterinaria'],
        'id_usuario' => $id_usuario,
        'img_perfil' => $img_perfil,
        'img_firma' => $img_firma,
        'id_veterinaria' => $id_veterinaria,
        'servicios' => $listaServicios
    ];

    // Actualizamos el profesional
    $resultado = $objProfesional->actualizarProfesional($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Profesional actualizado',
            'El profesional ha sido actualizado correctamente',
            BASE_URL . '/representante/listar-profesionales'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar el profesional');
    }

    exit();
}

// Desvincula al profesional de la clínica del representante (P3): solo cambia el vínculo
// (profesional_veterinaria.estado), nunca el estado global de la cuenta. POST con token CSRF.
function eliminarProfesional()
{
    acceso_exigir_accion_post([4]);
    $idVeterinaria = acceso_exigir_clinica();
    $id = acceso_entero_positivo($_POST['id'] ?? null);

    $objProfesional = new Profesional();
    if ($id === null || !$objProfesional->eliminarProfesional($id, $idVeterinaria)) {
        acceso_denegar('404', 'vínculo inexistente o de otra clínica');
    }

    mostrarSweetAlert(
        'success',
        'Profesional desvinculado',
        'El profesional ya no está vinculado a tu clínica. Su cuenta no cambia.',
        BASE_URL . '/representante/listar-profesionales'
    );
    exit();
}
