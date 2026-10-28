<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../config/config.php';

// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE CORREO ELECTRÓNICO
// ═══════════════════════════════════════════════════════════════════════════
// Configuración SMTP para Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPT', 'smtps');
define('SMTP_DEBUG', 0); // 0 = Sin debug, 1 = debug básico, 2 = debug verboso
// Las credenciales se leen de .env (nunca se suben al repositorio)
define('SMTP_USER', env_value('SMTP_USER', 'vetwillingsoporte@gmail.com'));
define('SMTP_PASS', env_value('SMTP_PASS', '')); // APP PASSWORD de Gmail
define('SMTP_FROM_EMAIL', env_value('SMTP_FROM_EMAIL', SMTP_USER));
define('SMTP_FROM_NAME', 'VetWilling - Sistema de Gestión Veterinaria');

// Transporte de correo: 'brevo' (API HTTP, puerto 443) o 'smtp' (Gmail).
// Si hay BREVO_API_KEY en .env se usa Brevo, salvo que MAIL_DRIVER=smtp.
define('BREVO_API_KEY', env_value('BREVO_API_KEY', ''));
define('MAIL_DRIVER', env_value('MAIL_DRIVER', BREVO_API_KEY !== '' ? 'brevo' : 'smtp'));

// Configuración de notificaciones automáticas
define('HORAS_ANTICIPACION_RECORDATORIO', 24);
define('MAX_REINTENTOS_NOTIFICACION', 3);
define('INTERVALO_REINTENTO_NOTIFICACION', 30);

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN: INICIALIZAR MAILER CON CONFIGURACIÓN CENTRALIZADA
// ARCHIVO: mailer_helper.php
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Inicializa una instancia de PHPMailer con configuración centralizada.
 * Con MAIL_DRIVER=brevo devuelve un BrevoMailer (misma API, envío por HTTPS).
 *
 * @return PHPMailer Instancia configurada de PHPMailer
 */
function mailer_init()
{
    if (MAIL_DRIVER === 'brevo') {
        require_once __DIR__ . '/BrevoMailer.php';
        $mail = new BrevoMailer(BREVO_API_KEY, true);
        $mail->CharSet = "UTF-8";
        $mail->isHTML(true);
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        return $mail;
    }

    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);

    // Configuración de servidor SMTP
    $mail->SMTPDebug = SMTP_DEBUG;
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usa ENCRYPTION_SMTPS
    $mail->Port = SMTP_PORT;
    
    // Configuración de idioma y encoding
    $mail->CharSet = "UTF-8";
    $mail->isHTML(true);
    
    // Email remitente
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

    return $mail;
}

/**
 * Inicializa PHPMailer con parámetros cargados desde canal_envio_config en BD.
 * Si el canal no está habilitado o no existe, lanza RuntimeException.
 *
 * @return PHPMailer
 * @throws RuntimeException
 */
function mailer_init_from_db(): PHPMailer
{
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../models/CanalEnvio.php';

    $model = new CanalEnvio();

    if (!$model->estaHabilitado('email')) {
        throw new RuntimeException('El canal de correo electrónico está deshabilitado.');
    }

    $cfg = $model->obtenerPorCodigo('email');
    if (!$cfg) {
        throw new RuntimeException('No se encontró configuración SMTP en la base de datos.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug  = SMTP_DEBUG;
    $mail->Host       = $cfg['smtp_host']       ?: SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = $cfg['smtp_usuario']     ?: SMTP_USER;
    $mail->Password   = $cfg['smtp_password']    ?: SMTP_PASS;
    $mail->SMTPSecure = ($cfg['smtp_encriptacion'] === 'tls')
                            ? PHPMailer::ENCRYPTION_STARTTLS
                            : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = (int)($cfg['smtp_port'] ?: SMTP_PORT);
    $mail->CharSet    = 'UTF-8';
    $mail->isHTML(true);
    $mail->setFrom(
        $cfg['smtp_remitente']        ?: SMTP_FROM_EMAIL,
        $cfg['smtp_nombre_remitente'] ?: SMTP_FROM_NAME
    );

    return $mail;
}
