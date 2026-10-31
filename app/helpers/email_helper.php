<?php

require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/mailer_helper.php'; // Usar la configuración centralizada

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =========================================
//  FUNCIONES DE ENVIO DE EMAIL
// =========================================

// FUNCION PARA ENVIAR NOTIFICACION DE CITA CREADA AL PROPIETARIO
function enviarNotificacionCitaCreada($datosCita)
{
    return enviarCorreoCita('cita', $datosCita, [
        'titulo'          => 'Tu cita fue registrada',
        'asunto_defecto'  => 'Confirmación de Cita - VetWilling',
        'mensaje_defecto' => '<p>Hola <strong>{nombre_propietario}</strong>, tu cita para <strong>{nombre_mascota}</strong> fue registrada el <strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong>.</p>',
        'nota'            => 'Si necesitas reprogramarla, ingresa a tu cuenta o comunícate con la veterinaria.',
        'acento'          => 'verde',
    ]);
}

// FUNCION PARA ENVIAR RECORDATORIO DE CITA (24 HORAS ANTES)
function enviarRecordatorioCita($datosCita)
{
    return enviarCorreoCita('recordatorio', $datosCita, [
        'titulo'          => 'Recordatorio de tu cita',
        'asunto_defecto'  => 'Recordatorio de Cita - VetWilling',
        'mensaje_defecto' => '<p>Hola <strong>{nombre_propietario}</strong>, te recordamos que tienes una cita mañana (<strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong>) para <strong>{nombre_mascota}</strong>.</p>',
        'nota'            => 'Recuerda llegar 10 minutos antes. ¡Te esperamos!',
        'acento'          => 'ambar',
    ]);
}

// FUNCION PARA ENVIAR NOTIFICACION DE CITA CANCELADA
function enviarNotificacionCitaCancelada($datosCancelacion)
{
    return enviarCorreoCita('cancelacion', $datosCancelacion, [
        'titulo'          => 'Tu cita fue cancelada',
        'asunto_defecto'  => 'Cancelación de Cita - VetWilling',
        'mensaje_defecto' => '<p>Hola <strong>{nombre_propietario}</strong>, lamentamos informarte que la cita de <strong>{nombre_mascota}</strong> del <strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong> fue cancelada.</p>',
        'nota'            => 'Puedes agendar una nueva cita desde tu cuenta cuando lo necesites.',
        'acento'          => 'rojo',
    ]);
}

/**
 * Arma y envía un correo de cita usando la plantilla de Medios de envío.
 *
 * El asunto y el mensaje principal salen de la plantilla del tipo ('cita', 'recordatorio',
 * 'cancelacion') si está activa; si no, se usan los textos por defecto. El mensaje va dentro
 * del diseño de marca (app/views/emails/notificacion.php) con el recuadro de datos de la cita.
 *
 * @return bool true si se envió
 */
function enviarCorreoCita(string $tipo, array $datos, array $conf): bool
{
    require_once __DIR__ . '/notificacion_helper.php';
    require_once __DIR__ . '/../models/CanalEnvio.php';

    try {
        $timestamp = strtotime($datos['fecha_hora'] ?? 'now');
        $vars = [
            'nombre_propietario' => $datos['nombre_propietario'] ?? '',
            'nombre_mascota'     => $datos['nombre_mascota'] ?? 'tu mascota',
            'tipo_servicio'      => $datos['tipo_servicio'] ?? '',
            'fecha_cita'         => date('d/m/Y', $timestamp),
            'hora_cita'          => date('H:i', $timestamp),
            'estado'             => $datos['estado'] ?? '',
            'motivo_cancelacion' => $datos['motivo_cancelacion'] ?? '',
        ];

        // Plantilla editable en Medios de envío (null si no existe o está inactiva)
        $plantilla = renderizarPlantillaCanal('email', $tipo, $vars);

        $asunto  = ($plantilla['asunto'] ?? '') ?: CanalEnvio::renderizar($conf['asunto_defecto'], $vars);
        $mensaje = ($plantilla['cuerpo_html'] ?? '') ?: CanalEnvio::renderizar($conf['mensaje_defecto'], $vars);
        $texto   = ($plantilla['cuerpo_texto'] ?? '') ?: trim(html_entity_decode(strip_tags($mensaje), ENT_QUOTES, 'UTF-8'));

        $detalles = [
            'Mascota'  => $vars['nombre_mascota'],
            'Servicio' => $vars['tipo_servicio'],
            'Fecha'    => $vars['fecha_cita'],
            'Hora'     => $vars['hora_cita'],
        ];
        if ($tipo === 'cita') {
            $detalles['Estado'] = $vars['estado'];
        }
        if ($tipo === 'cancelacion') {
            $detalles['Motivo'] = $vars['motivo_cancelacion'];
            if (!empty($datos['fecha_cancelacion'])) {
                $detalles['Cancelada el'] = date('d/m/Y H:i', strtotime($datos['fecha_cancelacion']));
            }
        }

        // Renderizar el diseño de marca
        $titulo = $conf['titulo'];
        $nota   = $conf['nota'];
        $acento = $conf['acento'];
        ob_start();
        require __DIR__ . '/../views/emails/notificacion.php';
        $html = ob_get_clean();

        $mail = mailer_init();
        $mail->addAddress($datos['email_propietario'], $datos['nombre_propietario'] ?? '');
        $mail->Subject = $asunto;
        $mail->Body    = $html;
        $mail->AltBody = $texto;

        return mailer_enviar($mail, $tipo, ['id_agendamiento' => $datos['id_agendamiento'] ?? null]);
    } catch (Throwable $e) {
        error_log("Error al enviar correo de {$tipo}: " . $e->getMessage());
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
