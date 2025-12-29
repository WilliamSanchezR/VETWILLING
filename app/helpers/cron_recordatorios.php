<?php

// =========================================
// SCRIPT PARA ENVIAR RECORDATORIOS DE CITAS
// Este script debe ejecutarse mediante un cron job cada hora
// Ejemplo crontab: 0 * * * * /usr/bin/php /opt/lampp/htdocs/vetwilling/app/helpers/cron_recordatorios.php
// =========================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/email_config.php';
require_once __DIR__ . '/email_helper.php';

// =========================================
// CREAR CONEXION A LA BASE DE DATOS
// =========================================
$conexionDB = new conexion();

// =========================================
// CALCULAR RANGO DE TIEMPO PARA RECORDATORIOS
// =========================================
$horasAntes = RECORDATORIO_HORAS_ANTES;
$fechaInicio = date('Y-m-d H:i:s', strtotime("+{$horasAntes} hours"));
$fechaFin = date('Y-m-d H:i:s', strtotime("+{$horasAntes} hours +1 hour"));

echo "Buscando citas entre {$fechaInicio} y {$fechaFin}\n";

try {
    // =========================================
    // BUSCAR CITAS QUE NECESITAN RECORDATORIO
    // =========================================
    $consulta = "SELECT 
                    a.id_agendamiento,
                    a.tipo,
                    a.fecha_hora,
                    CONCAT(p.nombres, ' ', p.apellidos) as nombre_propietario,
                    p.email as email_propietario,
                    pac.nombre as nombre_mascota,
                    a.recordatorio_enviado
                    FROM agendamiento a
                    INNER JOIN propietario p ON a.id_propietario = p.id_propietario
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    WHERE a.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
                    AND a.estado = 'Pendiente'
                    AND (a.recordatorio_enviado IS NULL OR a.recordatorio_enviado = 0)
                    AND p.email IS NOT NULL
                    AND p.email != ''";

    $resultado = $conn->prepare($consulta);
    $resultado->bindParam(':fecha_inicio', $fechaInicio);
    $resultado->bindParam(':fecha_fin', $fechaFin);
    $resultado->execute();

    $citas = $resultado->fetchAll(PDO::FETCH_ASSOC);

    echo "Se encontraron " . count($citas) . " citas para recordar\n";

    // =========================================
    // PROCESAR CADA CITA Y ENVIAR RECORDATORIO
    // =========================================
    foreach ($citas as $cita) {
        $datosCita = [
            'email_propietario' => $cita['email_propietario'],
            'nombre_propietario' => $cita['nombre_propietario'],
            'nombre_mascota' => $cita['nombre_mascota'],
            'tipo_servicio' => $cita['tipo'],
            'fecha_hora' => $cita['fecha_hora']
        ];

        // ENVIAR RECORDATORIO POR EMAIL
        $enviado = enviarRecordatorioCita($datosCita);

        if ($enviado) {
            // MARCAR COMO RECORDATORIO ENVIADO
            $consultaUpdate = "UPDATE agendamiento SET recordatorio_enviado = 1 WHERE id_agendamiento = :id";
            $resultadoUpdate = $conn->prepare($consultaUpdate);
            $resultadoUpdate->bindParam(':id', $cita['id_agendamiento'], PDO::PARAM_INT);
            $resultadoUpdate->execute();

            echo "✓ Recordatorio enviado a {$cita['email_propietario']} para cita {$cita['id_agendamiento']}\n";
        } else {
            echo "✗ Error al enviar recordatorio a {$cita['email_propietario']}\n";
        }
    }

    echo "\nProceso completado\n";
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage() . "\n";
    error_log("Error en cron_recordatorios -> " . $e->getMessage());
}
