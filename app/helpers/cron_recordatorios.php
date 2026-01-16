<?php

// =========================================
// RFS 37: SISTEMA DE RECORDATORIOS DE CITAS AUTOMATICOS
// Script para enviar notificaciones de recordatorio a usuarios
// Este script debe ejecutarse mediante un cron job cada hora
// 
// Ejemplo crontab: 0 * * * * /usr/bin/php /opt/lampp/htdocs/vetwilling/app/helpers/cron_recordatorios.php
// =========================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/email_config.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/notificacion_helper.php';
require_once __DIR__ . '/../models/Eventos.php';

// =========================================
// CONFIGURACION Y VARIABLES GLOBALES
// =========================================
$horasAntes = RECORDATORIO_HORAS_ANTES; // Definido en email_config.php
$fechaInicio = date('Y-m-d H:i:s', strtotime("+{$horasAntes} hours"));
$fechaFin = date('Y-m-d H:i:s', strtotime("+{$horasAntes} hours +1 hour"));

// Contadores para reporte
$totalCitas = 0;
$notificacionesExitosas = 0;
$notificacionesFallidas = 0;
$emailsInvalidos = 0;
$sinPreferencia = 0;

// =========================================
// INICIO DEL PROCESO
// =========================================
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  RFS 37: SISTEMA DE RECORDATORIOS AUTOMATICOS                 ║\n";
echo "║  Inicio: " . date('Y-m-d H:i:s') . "                              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "→ Buscando citas entre {$fechaInicio} y {$fechaFin}\n";
echo "→ Configurado para enviar {$horasAntes} horas antes de la cita\n\n";

try {
    // =========================================
    // INICIALIZAR MODELO DE EVENTOS
    // =========================================
    $eventosModel = new Eventos();

    // =========================================
    // OBTENER CITAS QUE NECESITAN RECORDATORIO
    // =========================================
    $citas = $eventosModel->obtenerCitasParaRecordatorio($fechaInicio, $fechaFin);
    $totalCitas = count($citas);

    echo "→ Se encontraron {$totalCitas} citas pendientes de recordatorio\n\n";

    if ($totalCitas === 0) {
        echo "✓ No hay citas para procesar en este momento\n";
        exit(0);
    }

    echo "════════════════════════════════════════════════════════════════\n";
    echo "PROCESANDO CITAS\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // =========================================
    // PROCESAR CADA CITA INDIVIDUALMENTE
    // =========================================
    foreach ($citas as $index => $cita) {
        $numeroCita = $index + 1;
        echo "[{$numeroCita}/{$totalCitas}] Procesando cita ID: {$cita['id_agendamiento']}\n";
        echo "    → Propietario: {$cita['nombre_propietario']}\n";
        echo "    → Email: {$cita['email_propietario']}\n";
        echo "    → Mascota: {$cita['nombre_mascota']}\n";
        echo "    → Fecha cita: " . date('d/m/Y H:i', strtotime($cita['fecha_hora'])) . "\n";

        // ┌─────────────────────────────────────────────────────────────┐
        // │ PASO 1: VERIFICAR PREFERENCIAS DE NOTIFICACION             │
        // └─────────────────────────────────────────────────────────────┘
        if ($cita['preferencia_notificacion'] === 'ninguno') {
            echo "    ⊘ Usuario no desea recibir notificaciones\n";
            echo "    ⊗ OMITIDO\n\n";
            $sinPreferencia++;
            continue;
        }

        // ┌─────────────────────────────────────────────────────────────┐
        // │ PASO 2: VALIDAR EMAIL DEL PROPIETARIO                      │
        // └─────────────────────────────────────────────────────────────┘
        $emailSanitizado = sanitizarEmail($cita['email_propietario']);
        $validacionEmail = validarEmailCompleto($emailSanitizado, false);

        if (!$validacionEmail['valido']) {
            echo "    ✗ Email invalido: {$validacionEmail['mensaje']}\n";

            // Registrar intento fallido por email invalido
            registrarNotificacionEnviada([
                'id_agendamiento' => $cita['id_agendamiento'],
                'medio_notificacion' => 'email',
                'destinatario' => $cita['email_propietario'],
                'estado_envio' => 'fallido',
                'mensaje_error' => "Email invalido: {$validacionEmail['mensaje']}",
                'intentos_envio' => 1
            ]);

            echo "    ⊗ OMITIDO - Email invalido\n\n";
            $emailsInvalidos++;
            continue;
        }

        echo "    ✓ Email validado correctamente\n";

        // ┌─────────────────────────────────────────────────────────────┐
        // │ PASO 3: VERIFICAR SI YA SE ENVIO NOTIFICACION              │
        // └─────────────────────────────────────────────────────────────┘
        if (existeNotificacionExitosa($cita['id_agendamiento'], 'email')) {
            echo "    ⊘ Ya existe notificacion exitosa para esta cita\n";
            echo "    ⊗ OMITIDO - Duplicado\n\n";
            continue;
        }

        // ┌─────────────────────────────────────────────────────────────┐
        // │ PASO 4: PREPARAR DATOS PARA EL EMAIL                       │
        // └─────────────────────────────────────────────────────────────┘
        $datosCita = [
            'email_propietario' => $emailSanitizado,
            'nombre_propietario' => $cita['nombre_propietario'],
            'nombre_mascota' => $cita['nombre_mascota'] ?? 'su mascota',
            'tipo_servicio' => $cita['tipo'],
            'fecha_hora' => $cita['fecha_hora']
        ];

        // ┌─────────────────────────────────────────────────────────────┐
        // │ PASO 5: ENVIAR RECORDATORIO POR EMAIL                      │
        // └─────────────────────────────────────────────────────────────┘
        echo "    ⟳ Enviando recordatorio...\n";
        $enviado = enviarRecordatorioCita($datosCita);

        if ($enviado) {
            // ═══════════════════════════════════════════════════════════
            // ENVIO EXITOSO
            // ═══════════════════════════════════════════════════════════

            // Marcar en tabla agendamiento
            $eventosModel->marcarRecordatorioEnviado($cita['id_agendamiento']);

            // Registrar en historial de notificaciones
            registrarNotificacionEnviada([
                'id_agendamiento' => $cita['id_agendamiento'],
                'medio_notificacion' => 'email',
                'destinatario' => $emailSanitizado,
                'estado_envio' => 'exitoso',
                'mensaje_error' => null,
                'intentos_envio' => 1
            ]);

            echo "    ✓ EXITOSO - Recordatorio enviado\n";
            echo "    ✓ Registro actualizado en base de datos\n\n";
            $notificacionesExitosas++;
        } else {
            // ═══════════════════════════════════════════════════════════
            // ENVIO FALLIDO
            // ═══════════════════════════════════════════════════════════

            // Registrar fallo en historial
            registrarNotificacionEnviada([
                'id_agendamiento' => $cita['id_agendamiento'],
                'medio_notificacion' => 'email',
                'destinatario' => $emailSanitizado,
                'estado_envio' => 'fallido',
                'mensaje_error' => 'Error al enviar email - verificar logs de PHPMailer',
                'intentos_envio' => 1
            ]);

            echo "    ✗ FALLIDO - Error al enviar email\n";
            echo "    ⚠ Verificar configuracion SMTP y logs\n\n";
            $notificacionesFallidas++;
        }
    }

    // =========================================
    // REPORTE FINAL
    // =========================================
    echo "════════════════════════════════════════════════════════════════\n";
    echo "RESUMEN DEL PROCESO\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "Total de citas encontradas:        {$totalCitas}\n";
    echo "Notificaciones exitosas:           {$notificacionesExitosas}\n";
    echo "Notificaciones fallidas:           {$notificacionesFallidas}\n";
    echo "Emails invalidos:                  {$emailsInvalidos}\n";
    echo "Usuarios sin preferencia:          {$sinPreferencia}\n";

    // Calcular tasa de exito
    $procesados = $notificacionesExitosas + $notificacionesFallidas;
    if ($procesados > 0) {
        $tasaExito = round(($notificacionesExitosas / $procesados) * 100, 2);
        echo "Tasa de exito:                     {$tasaExito}%\n";
    }

    echo "════════════════════════════════════════════════════════════════\n";
    echo "Proceso finalizado: " . date('Y-m-d H:i:s') . "\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // =========================================
    // LOG PARA AUDITORIA
    // =========================================
    error_log("CRON RECORDATORIOS - Total: {$totalCitas}, Exitosos: {$notificacionesExitosas}, Fallidos: {$notificacionesFallidas}");
} catch (PDOException $e) {
    // =========================================
    // MANEJO DE ERRORES DE BASE DE DATOS
    // =========================================
    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ERROR CRITICO EN BASE DE DATOS                               ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Linea: {$e->getLine()}\n\n";

    error_log("ERROR CRITICO en cron_recordatorios: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    // =========================================
    // MANEJO DE ERRORES GENERALES
    // =========================================
    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║  ERROR GENERAL                                                 ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    echo "Error: {$e->getMessage()}\n";

    error_log("ERROR en cron_recordatorios: " . $e->getMessage());
    exit(1);
}
