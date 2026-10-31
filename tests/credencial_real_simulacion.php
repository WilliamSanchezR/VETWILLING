<?php
// Ensayo de la migración con la credencial SMTP REAL, sin tocar la base real ni enviar correos.
// Ejecutar con: bash tests/run_medios_envio.sh  (usa el SMTP falso local)
//
// - Lee (solo lectura) la fila real del canal de correo y la copia a una TABLA TEMPORAL.
// - La cifra con una clave EFÍMERA (no la de .env) usando el mismo servicio que el script.
// - Comprueba que la app se autentica en el SMTP falso con exactamente la misma contraseña
//   (se compara su SHA-256; nunca se imprime) y que se puede revertir sin pérdida.
// - Comprueba el lector real de .env con claves base64 terminadas en "=".
//
// El ensayo solo tiene sentido con la credencial real en texto plano. Si ya está cifrada (tras la
// migración), la clave efímera no puede descifrarla y a propósito no se usa la real: esas comprobaciones
// se OMITEN (se informan y no cuentan como aprobadas). Las del lector de .env y la de que la tabla real
// no cambió se hacen siempre. Sale con 0 si todas las comprobaciones hechas pasan y con 1 si alguna falla.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$puerto = (int) ($argv[1] ?? 0);
$salida = $argv[2] ?? '';
if (!$puerto || !is_dir($salida)) {
    fwrite(STDERR, "Uso: php tests/credencial_real_simulacion.php <puerto_smtp_falso> <carpeta_salida>\n");
    exit(2);
}

$claveEfimera = base64_encode(random_bytes(32));
foreach (['MAIL_DRIVER' => 'smtp', 'APP_ENCRYPTION_KEY' => $claveEfimera] as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $v;
}
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
// BASE_PATH lo define config/config.php (cargado por database.php)
require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/app/helpers/mailer_helper.php';
require_once BASE_PATH . '/app/services/MigracionCredenciales.php';

$ok = 0;
$fallo = 0;
$omitidas = 0;
function comprobar(bool $c, string $d): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . "$d\n";
}
// Comprobación que no se puede hacer en este estado: se informa y NO cuenta como aprobada
function omitir(string $d, string $motivo): void
{
    global $omitidas;
    $omitidas++;
    echo "  ⏭️  OMITIDA: $d  → $motivo\n";
}
function resultado(): void
{
    global $ok, $fallo, $omitidas;
    echo "\nResultado: $ok correctas, $fallo fallidas, $omitidas omitidas.\n";
}

$db = (new conexion())->getConexion();

// Huella de la fila real leída siempre con una conexión APARTE (nunca ve las tablas temporales de $db):
// antes y después se compara la tabla real consigo misma
$huellaFilaReal = function (): ?string {
    $dsn = 'mysql:host=' . env_value('DB_HOST', '127.0.0.1') . ';port=' . env_value('DB_PORT', '3306') . ';dbname=' . env_value('DB_NAME', 'vetwilling') . ';charset=utf8mb4';
    // Mismos valores por defecto que la clase conexion de config/database.php
    $otra = new PDO($dsn, env_value('DB_USER', 'vetwilling'), env_value('DB_PASS', 'vetwilling_local'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $fila = $otra->query("SELECT * FROM canal_envio_config WHERE codigo_canal = 'email'")->fetch(PDO::FETCH_ASSOC);
    return $fila === false ? null : hash('sha256', json_encode($fila));
};
$huellaRealAntes = $huellaFilaReal();

// 1. Lectura de la fila real (única consulta sobre la tabla real en esta conexión)
$filaReal = $db->query("SELECT * FROM canal_envio_config WHERE codigo_canal = 'email'")->fetch(PDO::FETCH_ASSOC);
comprobar($filaReal !== false, 'Existe la fila del canal de correo en canal_envio_config');
if ($filaReal === false) {
    resultado();
    exit(1);
}
$credencialReal = (string) $filaReal['smtp_password'];
$cifradaReal = secreto_esta_cifrado($credencialReal);
$estadoReal = $cifradaReal ? 'cifrada' : secreto_estado($credencialReal); // con la clave efímera, una cifrada saldría 'ilegible'
echo "Ensayo con la credencial real — estado en la base real: $estadoReal\n";

// Comprobaciones del ensayo (las mismas que se hacen abajo con la credencial en texto plano)
$ensayo = [
    'Antes de migrar' => [
        'La app se autentica con la contraseña real guardada',
    ],
    'Migración con el mismo servicio que usa el script' => [
        'La copia de la credencial real queda cifrada',
        'Descifrada es idéntica a la original (comparación por SHA-256)',
        'Cifrada, la app se autentica en el SMTP simulado con la misma contraseña',
    ],
    'Reversión' => [
        'Revertida, vuelve a ser exactamente el texto plano original',
    ],
];

if ($estadoReal !== 'texto_plano') {
    $motivo = $cifradaReal
        ? 'la credencial real ya está cifrada y esta prueba usa una clave efímera: no hay clave compatible para descifrarla (la real no se usa a propósito)'
        : 'no hay contraseña SMTP guardada en la base: no hay credencial que ensayar';
    foreach ($ensayo as $seccion => $comprobaciones) {
        echo "\n▶ $seccion\n";
        foreach ($comprobaciones as $d) omitir($d, $motivo);
    }
} else {
    $hashReal = hash('sha256', $credencialReal);

    // 2. Copia en una tabla temporal (tapa a la real solo en esta conexión)
    $ddl = $db->query("SHOW CREATE TABLE canal_envio_config")->fetch(PDO::FETCH_NUM)[1];
    $db->exec(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl));
    $cols = array_keys($filaReal);
    $db->prepare("INSERT INTO canal_envio_config (`" . implode('`,`', $cols) . "`) VALUES (" . rtrim(str_repeat('?,', count($cols)), ',') . ")")
       ->execute(array_values($filaReal));
    // Solo el servidor cambia: SMTP falso local, sin cifrado de transporte
    $db->prepare("UPDATE canal_envio_config SET smtp_host = '127.0.0.1', smtp_port = ?, smtp_encriptacion = 'ninguna' WHERE codigo_canal = 'email'")
       ->execute([$puerto]);

    // Las tablas de registro también temporales (mailer_enviar escribe en ellas)
    foreach (['notificaciones_enviadas'] as $t) {
        $d = $db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1];
        $d = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $d);
        $d = preg_replace('/^\s*CONSTRAINT `[^`]+` FOREIGN KEY.*$\n?/m', '', $d);
        $db->exec(preg_replace('/,(\s*\n\))/', '$1', $d));
    }

    $valor = fn() => $db->query("SELECT smtp_password FROM canal_envio_config WHERE codigo_canal = 'email'")->fetchColumn();
    $autenticarComo = function () use ($salida): string {
        @unlink("$salida/auth.txt");
        $mail = mailer_init();
        $mail->addAddress('ensayo@vetwilling.test');
        $mail->Subject = 'Ensayo de credencial';
        $mail->Body = '<p>ensayo</p>';
        mailer_enviar($mail, 'prueba');
        return trim((string) @file_get_contents("$salida/auth.txt"));
    };

    echo "\n▶ Antes de migrar (texto plano, como hoy)\n";
    comprobar($autenticarComo() === $hashReal, 'La app se autentica con la contraseña real guardada');

    echo "\n▶ Migración con el mismo servicio que usa el script\n";
    $migracion = new MigracionCredenciales($db);
    $migracion->ejecutar('cifrar', true);
    comprobar(secreto_esta_cifrado($valor()), 'La copia de la credencial real queda cifrada');
    comprobar(hash('sha256', secreto_descifrar($valor())) === $hashReal, 'Descifrada es idéntica a la original (comparación por SHA-256)');
    comprobar($autenticarComo() === $hashReal, 'Cifrada, la app se autentica en el SMTP simulado con la misma contraseña');

    echo "\n▶ Reversión\n";
    $migracion->ejecutar('revertir', true);
    comprobar(hash('sha256', (string) $valor()) === $hashReal, 'Revertida, vuelve a ser exactamente el texto plano original');
}

echo "\n▶ Lector real de .env (config/config.php) con claves base64 terminadas en '='\n";
$tmp = sys_get_temp_dir() . '/vw_env_' . bin2hex(random_bytes(4));
mkdir("$tmp/config", 0700, true);
// Solo el bloque real del lector de .env y env_value() (el resto de config.php carga bootstrap.php)
$codigoConfig = file_get_contents(BASE_PATH . '/config/config.php');
$corte = strpos($codigoConfig, '// CONFIGURACIÓN GENERAL');
$corte = $corte === false ? strlen($codigoConfig) : strrpos(substr($codigoConfig, 0, $corte), "\n");
file_put_contents("$tmp/config/config.php", substr($codigoConfig, 0, $corte) . "\n");
foreach (['sin comillas' => 'APP_ENCRYPTION_KEY=%s', 'entre comillas' => 'APP_ENCRYPTION_KEY="%s"', 'con espacios' => 'APP_ENCRYPTION_KEY = %s'] as $nombre => $formato) {
    $clave = base64_encode(random_bytes(32)); // 44 caracteres, termina en "="
    file_put_contents("$tmp/.env", "# prueba\n" . sprintf($formato, $clave) . "\n");
    $leida = trim((string) shell_exec('env -i ' . escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg(
        '$_SERVER["HTTP_HOST"]="localhost"; $_SERVER["SCRIPT_NAME"]="/index.php"; require "' . $tmp . '/config/config.php"; echo env_value("APP_ENCRYPTION_KEY");'
    ) . ' 2>/dev/null'));
    comprobar($leida === $clave && strlen(base64_decode($leida, true)) === 32, "Clave $nombre: se lee completa (incluido el '=' final)");
}
@unlink("$tmp/config/config.php");
@rmdir("$tmp/config");
@unlink("$tmp/.env");
@rmdir($tmp);

// 3. La tabla real no cambió (conexión aparte, sin las tablas temporales)
echo "\n▶ Base real\n";
comprobar($huellaRealAntes !== null && $huellaFilaReal() === $huellaRealAntes, 'La fila real del canal de correo no cambió');

resultado();
exit($fallo === 0 ? 0 : 1);
