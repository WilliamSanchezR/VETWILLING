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
define('SMTP_USER', 'vetwillingsoporte@gmail.com');
define('SMTP_PASS', 'zbfpnwrnuwykjedn'); // APP PASSWORD de Gmail
define('SMTP_FROM_EMAIL', 'vetwillingsoporte@gmail.com');
define('SMTP_FROM_NAME', 'VetWilling - Sistema de Gestión Veterinaria');

// Configuración de notificaciones automáticas
define('HORAS_ANTICIPACION_RECORDATORIO', 24);
define('MAX_REINTENTOS_NOTIFICACION', 3);
define('INTERVALO_REINTENTO_NOTIFICACION', 30);

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN: INICIALIZAR MAILER CON CONFIGURACIÓN CENTRALIZADA
// ARCHIVO: mailer_helper.php
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Inicializa una instancia de PHPMailer con configuración SMTP centralizada
 * 
 * @return PHPMailer Instancia configurada de PHPMailer
 */
function mailer_init()
{
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
