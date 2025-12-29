<?php

// =========================================
// CONFIGURACION DE EMAIL SMTP
// =========================================

// INSTRUCCIONES PARA GMAIL:
// 1. Activar verificacion en 2 pasos en tu cuenta de Gmail
// 2. Ir a: https://myaccount.google.com/apppasswords
// 3. Generar una "Contraseña de aplicación" para "Correo"
// 4. Usar esa contraseña en EMAIL_PASSWORD (no tu contraseña normal)

define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 587);
define('EMAIL_USERNAME', 'tu-email@gmail.com');  // Cambiar por tu email real
define('EMAIL_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Cambiar por contraseña de aplicación
define('EMAIL_FROM', 'noreply@vetwilling.com');
define('EMAIL_FROM_NAME', 'VetWilling - Sistema de Citas');

// Habilitar o deshabilitar envio de emails (util para desarrollo)
define('EMAIL_ENABLED', false); // Cambiar a true cuando configures el SMTP

// =========================================
// CONFIGURACION DE NOTIFICACIONES
// =========================================

// Enviar recordatorio X horas antes de la cita
define('RECORDATORIO_HORAS_ANTES', 24);

// Email del administrador para recibir notificaciones
define('EMAIL_ADMIN', 'admin@vetwilling.com');
