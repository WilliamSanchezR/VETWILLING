<?php

require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =========================================
//  FUNCIONES DE ENVIO DE EMAIL
// =========================================

// FUNCION PARA ENVIAR NOTIFICACION DE CITA CREADA AL PROPIETARIO
function enviarNotificacionCitaCreada($datosCita)
{
    // Verificar si el envio de emails esta habilitado
    if (!EMAIL_ENABLED) {
        error_log("Envio de emails deshabilitado en configuracion");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuracion del servidor SMTP
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Destinatarios
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($datosCita['email_propietario'], $datosCita['nombre_propietario']);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Cita - VetWilling';

        $fechaCita = date('d/m/Y', strtotime($datosCita['fecha_hora']));
        $horaCita = date('H:i', strtotime($datosCita['fecha_hora']));

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007832; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .cita-info { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #007832; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .btn { background-color: #007832; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                h2 { color: #007832; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🐾 VetWilling</h1>
                    <p>Sistema de Gestión Veterinaria</p>
                </div>
                <div class='content'>
                    <h2>¡Hola {$datosCita['nombre_propietario']}!</h2>
                    <p>Tu cita ha sido <strong>registrada exitosamente</strong> en nuestro sistema.</p>
                    
                    <div class='cita-info'>
                        <h3>📋 Detalles de la Cita</h3>
                        <p><strong>Mascota:</strong> {$datosCita['nombre_mascota']}</p>
                        <p><strong>Servicio:</strong> {$datosCita['tipo_servicio']}</p>
                        <p><strong>Fecha:</strong> {$fechaCita}</p>
                        <p><strong>Hora:</strong> {$horaCita}</p>
                        <p><strong>Estado:</strong> <span style='color: #ffc107;'>{$datosCita['estado']}</span></p>
                    </div>
                    
                    <p><strong>Importante:</strong> Por favor llega 10 minutos antes de tu cita.</p>
                    <p>Si necesitas cancelar o reprogramar, contáctanos con al menos 24 horas de anticipación.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 VetWilling - Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Hola {$datosCita['nombre_propietario']},\n\n"
            . "Tu cita ha sido registrada:\n"
            . "Mascota: {$datosCita['nombre_mascota']}\n"
            . "Servicio: {$datosCita['tipo_servicio']}\n"
            . "Fecha: {$fechaCita} a las {$horaCita}\n\n"
            . "VetWilling - Sistema de Gestión Veterinaria";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email de confirmacion: {$mail->ErrorInfo}");
        return false;
    }
}

// FUNCION PARA ENVIAR RECORDATORIO DE CITA (24 HORAS ANTES)
function enviarRecordatorioCita($datosCita)
{
    // Verificar si el envio de emails esta habilitado
    if (!EMAIL_ENABLED) {
        error_log("Envio de emails deshabilitado en configuracion");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuracion del servidor SMTP
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($datosCita['email_propietario'], $datosCita['nombre_propietario']);

        $mail->isHTML(true);
        $mail->Subject = 'Recordatorio: Cita Mañana - VetWilling';

        $fechaCita = date('d/m/Y', strtotime($datosCita['fecha_hora']));
        $horaCita = date('H:i', strtotime($datosCita['fecha_hora']));

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ffc107; color: #333; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .reminder { background-color: #fff3cd; padding: 20px; margin: 20px 0; border-left: 4px solid #ffc107; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⏰ Recordatorio de Cita</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola {$datosCita['nombre_propietario']}!</h2>
                    <p>Te recordamos que tienes una cita <strong>mañana</strong> en VetWilling.</p>
                    
                    <div class='reminder'>
                        <h3>📋 Tu Cita</h3>
                        <p><strong>Mascota:</strong> {$datosCita['nombre_mascota']}</p>
                        <p><strong>Servicio:</strong> {$datosCita['tipo_servicio']}</p>
                        <p><strong>Fecha:</strong> {$fechaCita}</p>
                        <p><strong>Hora:</strong> {$horaCita}</p>
                    </div>
                    
                    <p>Recuerda llegar 10 minutos antes de tu cita.</p>
                    <p>¡Te esperamos! 🐾</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 VetWilling</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar recordatorio: {$mail->ErrorInfo}");
        return false;
    }
}
// FUNCION PARA ENVIAR NOTIFICACION DE CITA CANCELADA
function enviarNotificacionCitaCancelada($datosCancelacion)
{
    // Verificar si el envio de emails esta habilitado
    if (!EMAIL_ENABLED) {
        error_log("Envio de emails deshabilitado en configuracion");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuracion del servidor SMTP
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Destinatarios
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($datosCancelacion['email_propietario'], $datosCancelacion['nombre_propietario']);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Cancelación de Cita - VetWilling';

        $fechaCita = date('d/m/Y', strtotime($datosCancelacion['fecha_hora']));
        $horaCita = date('H:i', strtotime($datosCancelacion['fecha_hora']));
        $fechaCancelacion = date('d/m/Y H:i', strtotime($datosCancelacion['fecha_cancelacion']));

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .cancelacion-info { background-color: #f8d7da; padding: 20px; margin: 20px 0; border-left: 4px solid #dc3545; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .motivo-box { background-color: white; padding: 15px; margin: 15px 0; border: 1px solid #e0e0e0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>❌ Cita Cancelada</h1>
                </div>
                <div class='content'>
                    <h2>Hola {$datosCancelacion['nombre_propietario']},</h2>
                    <p>Te informamos que tu cita ha sido <strong style='color: #dc3545;'>cancelada</strong> exitosamente.</p>
                    
                    <div class='cancelacion-info'>
                        <h3>📋 Información de la Cita Cancelada</h3>
                        <p><strong>Mascota:</strong> {$datosCancelacion['nombre_mascota']}</p>
                        <p><strong>Servicio:</strong> {$datosCancelacion['tipo_servicio']}</p>
                        <p><strong>Fecha Original:</strong> {$fechaCita}</p>
                        <p><strong>Hora Original:</strong> {$horaCita}</p>
                        <p><strong>Fecha de Cancelación:</strong> {$fechaCancelacion}</p>
                    </div>

                    <div class='motivo-box'>
                        <h3>💬 Motivo de la Cancelación</h3>
                        <p>{$datosCancelacion['motivo_cancelacion']}</p>
                    </div>
                    
                    <p>Si deseas <strong>reagendar</strong> esta cita o tienes alguna pregunta, no dudes en ponerte en contacto con nosotros.</p>
                    <p>¡Esperamos verte pronto! 🐾</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 VetWilling - Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Hola {$datosCancelacion['nombre_propietario']},\n\n"
            . "Tu cita ha sido cancelada:\n"
            . "Mascota: {$datosCancelacion['nombre_mascota']}\n"
            . "Servicio: {$datosCancelacion['tipo_servicio']}\n"
            . "Fecha Original: {$fechaCita} a las {$horaCita}\n"
            . "Motivo: {$datosCancelacion['motivo_cancelacion']}\n\n"
            . "Si deseas reagendar, contáctanos.\n\n"
            . "VetWilling - Sistema de Gestión Veterinaria";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar notificacion de cancelacion: {$mail->ErrorInfo}");
        return false;
    }
}

// =========================================
//  RFS 37: FUNCIONES DE VALIDACION DE EMAIL
// =========================================

/**
 * VALIDAR QUE EL EMAIL TENGA UN FORMATO VALIDO Y SEGURO
 * 
 * Verifica que:
 * - El campo no este vacio
 * - Tenga formato de email valido
 * - No contenga caracteres peligrosos
 * - El dominio tenga estructura correcta
 * 
 * @param string $email Email a validar
 * @return bool True si el email es valido, False en caso contrario
 */
function validarFormatoEmail($email)
{
    // Verificar que no este vacio
    if (empty($email)) {
        error_log("Validacion email: Campo vacio");
        return false;
    }

    // Eliminar espacios en blanco al inicio y final
    $email = trim($email);

    // Validar formato basico de email usando filtro de PHP
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Validacion email: Formato invalido - {$email}");
        return false;
    }

    // Validar que no contenga caracteres peligrosos (inyeccion de codigo)
    if (preg_match('/[<>"\'\(\)]/', $email)) {
        error_log("Validacion email: Contiene caracteres no permitidos - {$email}");
        return false;
    }

    // Validar longitud razonable (maximo 255 caracteres segun RFC)
    if (strlen($email) > 255) {
        error_log("Validacion email: Demasiado largo - {$email}");
        return false;
    }

    // Separar usuario y dominio
    $partes = explode('@', $email);
    if (count($partes) !== 2) {
        error_log("Validacion email: Estructura incorrecta - {$email}");
        return false;
    }

    $usuario = $partes[0];
    $dominio = $partes[1];

    // Validar que el usuario no este vacio y no sea demasiado corto
    if (strlen($usuario) < 1) {
        error_log("Validacion email: Usuario vacio - {$email}");
        return false;
    }

    // Validar que el dominio tenga al menos un punto
    if (strpos($dominio, '.') === false) {
        error_log("Validacion email: Dominio sin TLD - {$email}");
        return false;
    }

    // Validar que el dominio no termine con punto
    if (substr($dominio, -1) === '.') {
        error_log("Validacion email: Dominio termina con punto - {$email}");
        return false;
    }

    return true;
}

/**
 * VALIDAR QUE EL DOMINIO DEL EMAIL EXISTA (VERIFICACION DNS)
 * 
 * Verifica que el dominio del email tenga registros MX o A en DNS
 * Esto confirma que el dominio puede recibir emails
 * 
 * @param string $email Email completo a verificar
 * @return bool True si el dominio existe y puede recibir emails
 */
function validarDominioEmail($email)
{
    // Primero validar el formato
    if (!validarFormatoEmail($email)) {
        return false;
    }

    // Extraer el dominio del email
    $partes = explode('@', $email);
    $dominio = $partes[1];

    // Verificar registros MX (Mail Exchange) del dominio
    // Los registros MX indican que el dominio puede recibir emails
    if (checkdnsrr($dominio, 'MX')) {
        return true;
    }

    // Si no tiene MX, verificar registro A (direccion IP)
    // Algunos servidores usan el registro A para emails
    if (checkdnsrr($dominio, 'A')) {
        error_log("Validacion DNS: Dominio sin MX pero con registro A - {$dominio}");
        return true;
    }

    error_log("Validacion DNS: Dominio no existe o no puede recibir emails - {$dominio}");
    return false;
}

/**
 * DETECTAR SI EL EMAIL ES TEMPORAL O DESECHABLE
 * 
 * Verifica contra una lista de dominios conocidos de emails temporales
 * Estos servicios se usan para emails de un solo uso y no son confiables
 * 
 * @param string $email Email a verificar
 * @return bool True si es temporal/desechable, False si es legitimo
 */
function esEmailTemporal($email)
{
    // Lista de dominios temporales mas comunes
    $dominiosTemporales = [
        'tempmail.com',
        'guerrillamail.com',
        'mailinator.com',
        'maildrop.cc',
        'throwaway.email',
        '10minutemail.com',
        'temp-mail.org',
        'yopmail.com',
        'fakeinbox.com',
        'trashmail.com',
        'getnada.com',
        'mohmal.com',
        'mintemail.com',
        'sharklasers.com',
        'guerrillamail.info',
        'grr.la',
        'guerrillamail.biz',
        'guerrillamail.de',
        'spam4.me',
        'emailondeck.com'
    ];

    // Extraer dominio del email
    $partes = explode('@', $email);
    if (count($partes) !== 2) {
        return false;
    }

    $dominio = strtolower($partes[1]);

    // Verificar si el dominio esta en la lista de temporales
    if (in_array($dominio, $dominiosTemporales)) {
        error_log("Email temporal detectado: {$email}");
        return true;
    }

    return false;
}

/**
 * VALIDACION COMPLETA DE EMAIL (FUNCION PRINCIPAL)
 * 
 * Ejecuta todas las validaciones en orden:
 * 1. Formato correcto
 * 2. Dominio existe (DNS)
 * 3. No es email temporal
 * 
 * Esta es la funcion que debe usarse antes de enviar notificaciones
 * 
 * @param string $email Email a validar completamente
 * @param bool $verificarDNS Si debe verificar DNS (puede ser lento)
 * @return array Array con 'valido' (bool) y 'mensaje' (string)
 */
function validarEmailCompleto($email, $verificarDNS = false)
{
    // PASO 1: Validar formato
    if (!validarFormatoEmail($email)) {
        return [
            'valido' => false,
            'mensaje' => 'Formato de email invalido'
        ];
    }

    // PASO 2: Verificar que no sea email temporal
    if (esEmailTemporal($email)) {
        return [
            'valido' => false,
            'mensaje' => 'Email temporal no permitido'
        ];
    }

    // PASO 3: Verificar DNS (opcional, puede ser lento)
    if ($verificarDNS) {
        if (!validarDominioEmail($email)) {
            return [
                'valido' => false,
                'mensaje' => 'Dominio no existe o no puede recibir emails'
            ];
        }
    }

    // Si paso todas las validaciones
    return [
        'valido' => true,
        'mensaje' => 'Email valido'
    ];
}

/**
 * SANITIZAR EMAIL PARA USO SEGURO
 * 
 * Limpia el email de caracteres peligrosos y espacios
 * Debe usarse antes de guardar en base de datos o enviar
 * 
 * @param string $email Email a limpiar
 * @return string Email limpio y seguro
 */
function sanitizarEmail($email)
{
    // Eliminar espacios en blanco
    $email = trim($email);

    // Convertir a minusculas (emails no son case-sensitive)
    $email = strtolower($email);

    // Filtrar usando funciones de PHP
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    return $email;
}
