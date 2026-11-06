<?php
// Enrutador de php -S para las pruebas de integración (tests/f6_f5a_integracion_test.php).
// Uso: TEST_DB_HOST=… TEST_DB_PORT=… TEST_DB_NAME=… TEST_DB_USER=… TEST_DB_PASS=… \
//      php -S 127.0.0.1:PUERTO tests/servidor_pruebas.php
//
// Fija DB_* a partir de TEST_DB_* y, en cada petición, aborta con 500 si la base conectada no es
// la de pruebas: otro nombre, el puerto 3306 o sin la tabla marcador _entorno_pruebas.
//
// Ningún correo sale del equipo: las constantes de mailer_helper.php se definen antes que él
// (gana la primera definición) apuntando a 127.0.0.1 en un puerto cerrado, sin Brevo. Los avisos
// "constant already defined" van al registro del servidor, no a la respuesta.

ini_set('display_errors', '0');
ini_set('log_errors', '1');
define('SMTP_HOST', '127.0.0.1');
define('SMTP_PORT', 1);
define('SMTP_USER', 'pruebas@vetwilling.test');
define('SMTP_PASS', '');
define('SMTP_FROM_EMAIL', 'pruebas@vetwilling.test');
define('BREVO_API_KEY', '');
define('MAIL_DRIVER', 'smtp');

$raiz = dirname(__DIR__);
$faltan = [];
foreach (['HOST', 'PORT', 'NAME', 'USER', 'PASS'] as $k) {
    $valor = getenv("TEST_DB_$k");
    if ($valor === false || $valor === '') {
        $faltan[] = "TEST_DB_$k";
    }
    $_ENV["DB_$k"] = (string) $valor;
}
if ($faltan || $_ENV['DB_PORT'] === '3306') {
    http_response_code(500);
    exit('Entorno de pruebas no configurado: ' . ($faltan ? implode(', ', $faltan) : 'puerto 3306 no permitido'));
}

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $ok = $pdo->query('SELECT DATABASE()')->fetchColumn() === $_ENV['DB_NAME']
        && $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '_entorno_pruebas'")->fetchColumn() == 1;
} catch (PDOException $e) {
    $ok = false;
}
unset($pdo);
if (!$ok) {
    http_response_code(500);
    exit('La base conectada no es la de pruebas (falta _entorno_pruebas).');
}

// La conexión de la propia aplicación (config/database.php de esta copia, que puede leer un .env
// real) también tiene que ser la de pruebas: DB_* de arriba deben haber ganado.
require_once $raiz . '/config/database.php';
try {
    $app = (new conexion())->getConexion();
    $ok = $app->query('SELECT DATABASE()')->fetchColumn() === $_ENV['DB_NAME']
        && $app->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '_entorno_pruebas'")->fetchColumn() == 1;
} catch (Throwable $e) {
    $ok = false;
}
unset($app);
if (!$ok) {
    http_response_code(500);
    exit('La conexión de la aplicación no es la de pruebas: no se atiende la petición.');
}

// Archivos estáticos de public/ (igual que router.php)
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$public = realpath($raiz . '/public');
if (is_string($ruta) && str_starts_with($ruta, '/public/') && $public !== false) {
    $archivo = realpath($raiz . rawurldecode($ruta));
    if ($archivo !== false && str_starts_with($archivo, $public . DIRECTORY_SEPARATOR) && is_file($archivo)) {
        return false;
    }
}

// Con php -S, SCRIPT_NAME es la ruta pedida; config.php calcula BASE_URL a partir de él
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $raiz . '/index.php';
