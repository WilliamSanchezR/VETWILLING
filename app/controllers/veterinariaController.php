<?php

//Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/veterinaria.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';
require_once __DIR__ . '/../helpers/email_helper.php';


//CAPTUTRAMOS EN UNA VARIABLE EL METODO O SOLICITUD HECHA AL SERVIDOR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar') {
            actualizarVeterinaria();
        } else if ($accion === 'actualizarInfo') {
            actualizarInfoVeterinaria();
        } else {
            registrarVeterinaria();
        }
        break;

    case 'GET':
        // Esta variable captura la accion de eliminar
        $action = $_GET['action'] ?? '';
        if ($action === 'eliminar') {
            eliminarVeterinaria($_GET['id']);
        }  elseif ($action === 'obtener_horarios') {
            $id_veterinaria = $_GET['id'] ?? '';
            consultarHorariosVeterinaria($id_veterinaria);
        } elseif ($action === 'eliminar_horarios') {
            $idHorario = $_GET['id'] ?? '';
            eliminarHorariosPorVeterinaria($idHorario);
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
    $password = $nit !== '' ? $nit : ($_POST['numero_documento'] ?? '123');
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
        $emailEnviado = false;
        if (validarFormatoEmail($emailVeterinaria)) {
            $emailEnviado = enviarBienvenidaVeterinaria([
                'email' => $emailVeterinaria,
                'razon_social' => $nombreVeterinaria,
                'nit' => $nit,
                'representante' => trim($nombres . ' ' . $apellidos)
            ]);
        }

        mostrarSweetAlert(
            'success',
            'Veterinaria registrada',
            $emailEnviado
                ? 'La veterinaria ha sido creada correctamente y el correo fue enviado.'
                : 'La veterinaria ha sido creada correctamente, pero no se pudo enviar el correo.',
            BASE_URL . '/admin/listar-veterinarias'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar la veterinaria');
    }

    exit();
}

function enviarBienvenidaVeterinaria(array $datos): bool
{
    try {
        $mail = mailer_init();

        $email = $datos['email'] ?? '';
        $razonSocial = $datos['razon_social'] ?? '';
        $nit = (string) ($datos['nit'] ?? '');
        $representante = $datos['representante'] ?? '';

        $mail->setFrom(SMTP_FROM_EMAIL, 'VetWilling - Sistema de Gestion Veterinaria');
        $mail->addAddress($email, $razonSocial);
        $mail->isHTML(true);
        $mail->Subject = 'Registro exitoso de su veterinaria en VetWilling';

        $razonSeguro = htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8');
        $representanteSeguro = htmlspecialchars($representante, ENT_QUOTES, 'UTF-8');
        $emailSeguro = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $nitSeguro = htmlspecialchars($nit, ENT_QUOTES, 'UTF-8');
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

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
                    <p>Registro de Veterinaria</p>
                </div>
                <div class='content'>
                    <h2>Estimado equipo de {$razonSeguro},</h2>
                    <p>Su veterinaria ha sido registrada exitosamente en nuestro sistema.</p>
                    <div class='card'>
                        <h3>Datos de acceso</h3>
                        <p><strong>Correo:</strong> {$emailSeguro}</p>
                        <p><strong>Contrasena inicial:</strong> {$nitSeguro}</p>
                        <p>Por seguridad, recuerde cambiar la contrasena en el primer ingreso.</p>
                    </div>
                    <p><strong>Representante legal:</strong> {$representanteSeguro}</p>
                    <p>Puede iniciar sesion desde el siguiente enlace:</p>
                    <a class='btn' href='{$baseUrl}'>Acceder al sistema</a>
                </div>
                <div class='footer'>
                    <p>Este es un correo automatico, por favor no responda a este mensaje.</p>
                    <p>&copy; 2025 VetWilling</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Estimado equipo de {$razonSocial},\n\n"
            . "Su veterinaria fue registrada exitosamente en VetWilling.\n"
            . "Correo: {$email}\n"
            . "Contrasena inicial: {$nit}\n"
            . "Recuerde cambiar la contrasena en el primer ingreso.\n"
            . "Representante legal: {$representante}\n\n"
            . "Acceda desde: {$baseUrl}\n\n"
            . "VetWilling";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar bienvenida veterinaria: " . $e->getMessage());
        return false;
    }
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
    /// Datos de representante legal
    $id_usuario = $_POST['id_usuario'] ?? '';
    $id_rol = $_POST['id_rol'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email_user = $_POST['email_user'] ?? '';
    $telefono_user = $_POST['telefono_user'] ?? '';
    $direccion_user = $_POST['direccion_user'] ?? '';
    $estado_user = $_POST['estado_user'] ?? '';


    // Validamos que los campos no esten vacios
    if (
        empty($nit) || empty($nombre) || empty($direccion) || empty($ciudad) || empty($telefono) ||
        empty($email) || empty($estado) || empty($id_usuario) || empty($tipo_documento) || empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($telefono_user) || empty($email_user) || empty($id_rol) || empty($direccion_user)
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
        'id_veterinaria' => $_POST['id_veterinaria'] ?? '',
        'id_usuario' => $id_usuario,
        'id_rol' => $id_rol,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'email_user' => $email_user,
        'telefono_user' => $telefono_user,
        'direccion_user' => $direccion_user,
        'estado_user' => $estado_user
    ];
    // Llamamos a la funcion actualizar del modelo Veterinaria
    $resultado = $objVeterinaria->actualizar($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria actualizada',
            'Los datos han sido actualizados correctamente',
            BASE_URL . '/admin/listar-veterinarias'
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
            BASE_URL . '/admin/listar-veterinarias'
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

function actualizarInfoVeterinaria()
{
    // Capturamos los datos enviados por el formulario
    $foto = $_POST['foto_actual'] ?? null;
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $horarios = $_POST['horarios'] ?? '';
    $id_veterinaria = $_POST['id_veterinaria'] ?? '';


    // Validamos que los campos no esten vacios
    if (
        empty($direccion) || empty($ciudad) || empty($telefono) || empty($id_veterinaria)
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
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'telefono' => $telefono,
        'foto' => $foto,
        'id_veterinaria' => $id_veterinaria,
        'horarios' => $horarios
    ];
    // Llamamos a la funcion actualizar del modelo Veterinaria
    $resultado = $objVeterinaria->actualizarInformacionVeterinaria($data);
    // Verificamos el resultado y mostramos una alerta
    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Veterinaria actualizada',
            'Los datos han sido actualizados correctamente',
            BASE_URL . '/representante/veterinaria_info'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la veterinaria');
    }
    exit();
}

function consultarHorariosVeterinaria($id_veterinaria)
{
     try {
        
        $veterinariaModel = new Veterinaria();

        // Llamamos al método para obtener los horarios del servicio
        $horarios = $veterinariaModel->consultarHorariosVeterinaria($id_veterinaria);

        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'horarios' => $horarios,
        ]);
    } catch (Exception $e) {
        // ┌─ RETORNAR ERROR    
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los horarios de la veterinaria: ' . $e->getMessage()
        ]);
    }
}

function eliminarHorariosPorVeterinaria($idHorario)
{
        $veterinariaModel = new Veterinaria();

    // Llamamos al método para eliminar los horarios del servicio
    $resultado = $veterinariaModel->eliminarHorariosPorVeterinaria($idHorario);

    if ($resultado) {
        // ┌─ RETORNAR RESULTADO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Horarios eliminados correctamente'
        ]);
    } else {
        // ┌─ RETORNAR ERROR    
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudieron eliminar los horarios'
        ]);
    }
    exit();

}