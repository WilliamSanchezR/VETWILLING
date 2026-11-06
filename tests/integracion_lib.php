<?php
// Utilidades comunes de las pruebas de integración por HTTP (tests/*_integracion_test.php).
//
// SOLO contra el contenedor de pruebas: integracion_conectar() usa TEST_DB_* y aborta si la base
// no tiene la tabla marcador _entorno_pruebas o el puerto es 3306. integracion_servidor() levanta
// php -S con tests/servidor_pruebas.php, que repite esa comprobación en cada petición.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$ok = 0;
$fallo = 0;
function comprobar(bool $c, string $d, string $detalle = ''): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . $d . ($c || $detalle === '' ? '' : "  → $detalle") . "\n";
}

/** @return array{0: PDO, 1: array} conexión y datos de TEST_DB_* */
function integracion_conectar(): array
{
    $env = [];
    foreach (['HOST', 'PORT', 'NAME', 'USER', 'PASS'] as $k) {
        $env[$k] = (string) getenv("TEST_DB_$k");
        if ($env[$k] === '') {
            fwrite(STDERR, "Falta TEST_DB_$k\n");
            exit(2);
        }
    }
    if ($env['PORT'] === '3306') {
        fwrite(STDERR, "El puerto 3306 no es el del contenedor de pruebas\n");
        exit(2);
    }
    $pdo = new PDO("mysql:host={$env['HOST']};port={$env['PORT']};dbname={$env['NAME']};charset=utf8mb4", $env['USER'], $env['PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== $env['NAME']
        || (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '_entorno_pruebas'")->fetchColumn() !== 1) {
        fwrite(STDERR, "La base no es la de pruebas (falta _entorno_pruebas): no se ejecuta nada\n");
        exit(2);
    }
    return [$pdo, $env];
}

function valor(string $sql, array $p = [])
{
    global $pdo;
    $s = $pdo->prepare($sql);
    $s->execute($p);
    return $s->fetchColumn();
}

/**
 * Instantánea de tablas con clave primaria de una columna: filas actuales y mayor id.
 * integracion_restaurar() borra las filas creadas después (id mayor) y repone las demás.
 */
function integracion_instantanea(PDO $pdo, array $tablas): array
{
    $inst = [];
    foreach ($tablas as $tabla) {
        $pk = $pdo->query("SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE()
                           AND TABLE_NAME = " . $pdo->quote($tabla) . " AND CONSTRAINT_NAME = 'PRIMARY'")->fetchAll(PDO::FETCH_COLUMN);
        if (count($pk) !== 1) {
            throw new LogicException("$tabla no tiene una clave primaria de una columna");
        }
        $inst[$tabla] = [$pk[0], (int) $pdo->query("SELECT COALESCE(MAX(`{$pk[0]}`), 0) FROM `$tabla`")->fetchColumn(), $pdo->query("SELECT * FROM `$tabla`")->fetchAll()];
    }
    return $inst;
}

function integracion_restaurar(PDO $pdo, array $inst): void
{
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0'); // solo en esta conexión
    try {
        foreach ($inst as $tabla => [$pk, $max, $filas]) {
            $pdo->prepare("DELETE FROM `$tabla` WHERE `$pk` > ?")->execute([$max]);
            foreach ($filas as $fila) {
                $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($fila)));
                $pdo->prepare("UPDATE `$tabla` SET $sets WHERE `$pk` = ?")->execute([...array_values($fila), $fila[$pk]]);
            }
            $pdo->exec("ALTER TABLE `$tabla` AUTO_INCREMENT = 1"); // vuelve al siguiente libre
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

/** @return array{0: resource, 1: string, 2: string} proceso, URL base y archivo de registro */
function integracion_servidor(string $raiz): array
{
    $sock = stream_socket_server('tcp://127.0.0.1:0');
    $puerto = (int) substr(strrchr(stream_socket_get_name($sock, false), ':'), 1);
    fclose($sock);
    $log = tempnam(sys_get_temp_dir(), 'vw_integracion_srv_');
    $proceso = proc_open([PHP_BINARY, '-S', "127.0.0.1:$puerto", "$raiz/tests/servidor_pruebas.php"],
        [0 => ['file', '/dev/null', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $tuberias, $raiz);
    for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', $puerto); $i++) {
        usleep(100000);
    }
    return [$proceso, "http://127.0.0.1:$puerto", $log];
}

/** Detiene el servidor; si hubo fallos muestra el final de su registro. */
function integracion_cerrar_servidor($proceso, string $log): void
{
    global $fallo;
    proc_terminate($proceso);
    proc_close($proceso);
    if ($fallo) {
        $lineas = array_filter(file($log), fn($l) => !preg_match('/ (Accepted|Closing|Closed without)/', $l));
        echo "\n— Registro del servidor —\n" . implode('', array_slice($lineas, -(int) (getenv('LINEAS_LOG') ?: 30)));
    }
    @unlink($log);
}

class Cliente
{
    private string $cookies;
    public string $token = '';

    public function __construct(private string $base)
    {
        $this->cookies = tempnam(sys_get_temp_dir(), 'vw_integracion_ck_');
    }

    public function __destruct()
    {
        @unlink($this->cookies);
    }

    /** @return array{0:int,1:string,2:string} código, cuerpo, Location */
    public function pedir(string $metodo, string $ruta, array $datos = [], array $cabeceras = []): array
    {
        $c = curl_init($this->base . $ruta);
        curl_setopt_array($c, [
            CURLOPT_CUSTOMREQUEST => $metodo, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
            CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies, CURLOPT_HTTPHEADER => $cabeceras,
        ]);
        if ($metodo === 'POST') {
            curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($datos));
        }
        $resp = (string) curl_exec($c);
        $codigo = (int) curl_getinfo($c, CURLINFO_RESPONSE_CODE);
        $tam = (int) curl_getinfo($c, CURLINFO_HEADER_SIZE);
        curl_close($c);
        preg_match('/^Location:\s*(\S+)/mi', substr($resp, 0, $tam), $m);
        return [$codigo, substr($resp, $tam), $m[1] ?? ''];
    }

    public function post(string $ruta, array $datos, bool $conToken = true, array $cabeceras = []): array
    {
        return $this->pedir('POST', $ruta, $conToken ? $datos + ['csrf_token' => $this->token] : $datos, $cabeceras);
    }

    /** Inicia sesión y, si se indica una página que imprime csrf_campo(), lee su token. */
    public function iniciarSesion(string $email, string $clave, ?string $paginaConToken = null): bool
    {
        [, $html] = $this->pedir('GET', '/login');
        preg_match('/name="csrf_token" value="([0-9a-f]+)"/', $html, $m);
        [$codigo, , $destino] = $this->pedir('POST', '/iniciar-sesion', ['email' => $email, 'password' => $clave, 'csrf_token' => $m[1] ?? '']);
        if ($codigo !== 302 || str_ends_with($destino, '/login')) {
            return false;
        }
        if ($paginaConToken === null) {
            return true;
        }
        [, $html] = $this->pedir('GET', $paginaConToken);
        preg_match('/name="csrf_token" value="([0-9a-f]+)"/', $html, $m);
        $this->token = $m[1] ?? '';
        return $this->token !== '';
    }
}
