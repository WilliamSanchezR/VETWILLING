<?php
// Reintenta los correos fallidos registrados en Medios de envío. Solo por consola o cron:
//   php scripts/reintentar-notificaciones.php
// Ejemplo crontab (cada 5 minutos):
//   */5 * * * * /usr/bin/php /ruta/a/vetwilling/scripts/reintentar-notificaciones.php >> /var/log/vetwilling-reintentos.log 2>&1
//
// Respeta "Máx. reintentos" e "Intervalo entre reintentos" configurados en Medios de envío.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
require_once __DIR__ . '/../app/helpers/mailer_helper.php';

// Evita dos ejecuciones simultáneas del cron (además, cada fila se reserva antes de enviarla)
$lock = fopen(sys_get_temp_dir() . '/vetwilling_reintentos.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    echo '[' . date('Y-m-d H:i:s') . "] Ya hay otra ejecución de reintentos en curso. Se omite esta.\n";
    exit(0);
}

$resultado = reintentarCorreosFallidos(true);

echo '[' . date('Y-m-d H:i:s') . '] ' . $resultado['mensaje'] . "\n";
exit($resultado['ok'] ? 0 : 1);
