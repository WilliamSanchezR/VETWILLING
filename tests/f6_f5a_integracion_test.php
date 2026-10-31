<?php
// Pruebas de integración de F6 (estado de cuentas, desvinculación, borrados solo por POST con CSRF)
// y F5a (alcance por clínica; el correo solo lo cambia el administrador).
// Ejecutar con: bash tests/run_f6_f5a.sh
//
// SOLO contra el contenedor de pruebas: usa TEST_DB_* y aborta si la base no tiene la tabla
// marcador _entorno_pruebas o el puerto es 3306. Levanta php -S con tests/servidor_pruebas.php,
// crea sus propios datos (correos f6test+…@vetwilling.test), guarda las filas existentes que
// puede tocar y al terminar las restaura y borra lo creado, también si una prueba falla.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$raiz = dirname(__DIR__);
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

$ok = 0;
$fallo = 0;
function comprobar(bool $c, string $d, string $detalle = ''): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . $d . ($c || $detalle === '' ? '' : "  → $detalle") . "\n";
}

function valor(string $sql, array $p = [])
{
    global $pdo;
    $s = $pdo->prepare($sql);
    $s->execute($p);
    return $s->fetchColumn();
}

// ── Datos: copia de las filas existentes y registro de lo creado ─────────────────
$copias = [];
foreach (['usuario' => 'id_usuario', 'profesional' => 'id_profesional', 'profesional_veterinaria' => 'id_prof_veterinaria',
          'veterinaria' => 'id_veterinaria', 'representante_legal' => 'id_representante'] as $tabla => $pk) {
    $copias[$tabla] = [$pk, $pdo->query("SELECT * FROM `$tabla`")->fetchAll()];
}
$creados = [];
function crear(string $tabla, string $pk, array $fila): int
{
    global $pdo, $creados;
    $cols = array_keys($fila);
    $pdo->prepare("INSERT INTO `$tabla` (`" . implode('`, `', $cols) . '`) VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
        ->execute(array_values($fila));
    $id = (int) $pdo->lastInsertId();
    $creados[] = [$tabla, $pk, $id];
    return $id;
}

function restaurar(): void
{
    global $pdo, $copias, $creados;
    $pdo->exec("DELETE FROM auditoria_eliminacion_propietarios WHERE motivo LIKE 'Eliminación%' AND id_propietario IN (SELECT id_propietario FROM propietario p JOIN usuario u ON u.id_usuario = p.id_usuario WHERE u.email LIKE 'f6test+%')");
    foreach (array_reverse($creados) as [$tabla, $pk, $id]) {
        $pdo->prepare("DELETE FROM `$tabla` WHERE `$pk` = ?")->execute([$id]);
    }
    $creados = [];
    foreach ($copias as $tabla => [$pk, $filas]) {
        foreach ($filas as $fila) {
            $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($fila)));
            $pdo->prepare("UPDATE `$tabla` SET $sets WHERE `$pk` = ?")->execute([...array_values($fila), $fila[$pk]]);
        }
    }
}

// ── Servidor php -S y cliente HTTP ───────────────────────────────────────────────
$sock = stream_socket_server('tcp://127.0.0.1:0');
$puerto = (int) substr(strrchr(stream_socket_get_name($sock, false), ':'), 1);
fclose($sock);
$log = tempnam(sys_get_temp_dir(), 'vw_f6_srv_');
$servidor = proc_open([PHP_BINARY, '-S', "127.0.0.1:$puerto", "$raiz/tests/servidor_pruebas.php"],
    [0 => ['file', '/dev/null', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $tuberias, $raiz);
for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', $puerto); $i++) {
    usleep(100000);
}
$base = "http://127.0.0.1:$puerto";

class Cliente
{
    private string $cookies;
    public string $token = '';

    public function __construct(private string $base)
    {
        $this->cookies = tempnam(sys_get_temp_dir(), 'vw_f6_ck_');
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

    public function iniciarSesion(string $email, string $clave, string $paginaConToken): bool
    {
        [, $html] = $this->pedir('GET', '/login');
        preg_match('/name="csrf_token" value="([0-9a-f]+)"/', $html, $m);
        [$codigo, , $destino] = $this->pedir('POST', '/iniciar-sesion', ['email' => $email, 'password' => $clave, 'csrf_token' => $m[1] ?? '']);
        if ($codigo !== 302 || str_ends_with($destino, '/login')) {
            return false;
        }
        [, $html] = $this->pedir('GET', $paginaConToken);
        preg_match('/name="csrf_token" value="([0-9a-f]+)"/', $html, $m);
        $this->token = $m[1] ?? '';
        return $this->token !== '';
    }
}

$codigoDe = fn(array $r) => $r[0];

try {
    // ── Datos de prueba ──────────────────────────────────────────────────────────
    // Clínicas 101 (A, la del representante 1004 y el veterinario 1003) y 102 (B)
    $clave = 'PruebaF6!' . bin2hex(random_bytes(4));
    $pdo->prepare('UPDATE usuario SET password_hash = ? WHERE id_usuario IN (1001, 1003, 1004)')->execute([password_hash($clave, PASSWORD_DEFAULT)]);
    $nuevoUsuario = fn(string $nombre, int $rol) => crear('usuario', 'id_usuario', ['email' => "f6test+$nombre@vetwilling.test", 'password_hash' => 'x', 'estado' => 'activo', 'id_rol' => $rol]);
    $perfil = ['tipo_documento' => 'CC', 'numero_documento' => '900', 'apellidos' => 'Prueba', 'telefono' => '3000000000'];

    $uVictima = $nuevoUsuario('victima', 2);
    $uX = $nuevoUsuario('profx', 2);
    $pX = crear('profesional', 'id_profesional', $perfil + ['id_usuario' => $uX, 'nombres' => 'ProfesionalSoloB', 'direccion' => '-', 'nivel_acceso' => 'Completo']);
    $pvX = crear('profesional_veterinaria', 'id_prof_veterinaria', ['id_profesional' => $pX, 'id_veterinaria' => 102, 'estado' => 'Activo']);
    $uP = $nuevoUsuario('propietario', 3);
    $propB = crear('propietario', 'id_propietario', $perfil + ['id_usuario' => $uP, 'id_veterinaria' => 102, 'nombres' => 'PropietarioB']);
    $uW = $nuevoUsuario('vetb', 2);
    crear('veterinario', 'id_veterinario', $perfil + ['id_usuario' => $uW, 'nombres' => 'VeterinarioB', 'id_veterinaria' => 102]);
    crear('veterinario', 'id_veterinario', $perfil + ['id_usuario' => 1003, 'nombres' => 'VeterinarioA', 'id_veterinaria' => 101]);
    $sA = crear('servicio', 'id_servicio', ['nombre' => 'f6test servicio A', 'id_veterinaria' => 101, 'estado' => 'Activo']);
    $sB = crear('servicio', 'id_servicio', ['nombre' => 'f6test servicio B', 'id_veterinaria' => 102, 'estado' => 'Activo']);
    $psA = crear('profesionales_servicio', 'id_prof_servicio', ['id_servicio' => $sA, 'id_profesional' => 2002, 'estado' => 'Activo']);
    $psB = crear('profesionales_servicio', 'id_prof_servicio', ['id_servicio' => $sB, 'id_profesional' => 2002, 'estado' => 'Activo']);
    $esp = crear('especialidad', 'id_especialidad', ['nombre' => 'f6test especialidad', 'id_servicio_esp' => $sA]);
    $peA = crear('profesional_especialidad', 'id_profesional_esp', ['id_usuario' => 1003, 'id_especialidad' => $esp, 'estado' => 'Activo', 'id_veterinaria' => 101]);
    $peB = crear('profesional_especialidad', 'id_profesional_esp', ['id_usuario' => 1003, 'id_especialidad' => $esp, 'estado' => 'Activo', 'id_veterinaria' => 102]);
    $hA = crear('horario_veterinaria', 'id_horario_veterinaria', ['id_veterinaria' => 101, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
    $hB = crear('horario_veterinaria', 'id_horario_veterinaria', ['id_veterinaria' => 102, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);

    $estadoUsuario = fn(int $id) => valor('SELECT estado FROM usuario WHERE id_usuario = ?', [$id]);
    $estadoPv = fn(int $id) => valor('SELECT estado FROM profesional_veterinaria WHERE id_prof_veterinaria = ?', [$id]);

    $admin = new Cliente($base);
    $rep = new Cliente($base);
    $vet = new Cliente($base);
    echo "▶ Inicio de sesión (contenedor de pruebas, puerto {$env['PORT']})\n";
    comprobar($admin->iniciarSesion('admin@vetwilling.test', $clave, '/admin/listar-usuarios'), 'Administrador (1001) con token CSRF');
    comprobar($rep->iniciarSesion('rep@vetwilling.test', $clave, '/representante/listar-profesionales'), 'Representante de la clínica A (1004) con token CSRF');
    comprobar($vet->iniciarSesion('vet.una@vetwilling.test', $clave, '/login') || $vet->pedir('GET', '/veterinaria/dashboard')[0] === 200, 'Veterinario de la clínica A (1003)');

    echo "▶ F6 · Inhabilitar usuarios (solo rol 1, POST y CSRF)\n";
    comprobar($codigoDe($admin->pedir('GET', "/admin/eliminar-usuario?accion=eliminar&id=$uVictima")) === 405 && $estadoUsuario($uVictima) === 'activo', 'GET /admin/eliminar-usuario → 405 y la cuenta no cambia');
    comprobar($codigoDe($admin->pedir('GET', "/admin/listar-usuarios?accion=eliminar&id=$uVictima")) === 405 && $estadoUsuario($uVictima) === 'activo', 'GET a una vista que incluye el controlador (?accion=eliminar) → 405');
    comprobar($codigoDe($rep->pedir('GET', "/representante/dashboard?accion=eliminar&id=$uVictima")) === 405 && $estadoUsuario($uVictima) === 'activo', 'Desde el panel del representante (?accion=eliminar) → 405');
    comprobar($codigoDe($admin->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => $uVictima], false)) === 403 && $estadoUsuario($uVictima) === 'activo', 'POST sin token CSRF → 403');
    comprobar($codigoDe($admin->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => $uVictima, 'csrf_token' => str_repeat('0', 64)], false)) === 403 && $estadoUsuario($uVictima) === 'activo', 'POST con token falso → 403');
    comprobar($codigoDe($rep->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => $uVictima])) === 403 && $estadoUsuario($uVictima) === 'activo', 'Representante → 403 (protección central)');
    comprobar($codigoDe($rep->post('/veterinario/actualizar-constrasena', ['accion' => 'eliminar', 'id' => $uVictima])) === 403 && $estadoUsuario($uVictima) === 'activo', 'Representante por otra ruta que incluye usuarioController → 403 (comprobación en la función)');
    $emailVictima = valor('SELECT email FROM usuario WHERE id_usuario = ?', [$uVictima]);
    $r = $rep->post('/veterinario/actualizar-constrasena', ['accion' => 'actualizar', 'id_usuario' => $uVictima, 'email' => 'f6test+robado@vetwilling.test', 'estado' => 'inactivo',
        'tipo_documento' => 'CC', 'numero_documento' => '1', 'nombres' => 'x', 'apellidos' => 'x', 'telefono' => '1', 'id_rol' => '2', 'direccion' => 'x']);
    comprobar($r[0] === 403 && valor('SELECT email FROM usuario WHERE id_usuario = ?', [$uVictima]) === $emailVictima && $estadoUsuario($uVictima) === 'activo', 'actualizarUsuario desde el rol 4 → 403; correo y estado sin cambios');
    $admin->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => 1001]);
    comprobar($estadoUsuario(1001) === 'activo', 'El administrador no puede inhabilitarse a sí mismo');
    comprobar($codigoDe($admin->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => 999999])) === 404, 'Usuario inexistente → 404');
    $admin->post('/admin/eliminar-usuario', ['accion' => 'eliminar', 'id' => $uVictima]);
    comprobar($estadoUsuario($uVictima) === 'inactivo', 'POST con token desde el rol 1 → la cuenta queda inactiva');
    $datosAdmin = ['accion' => 'actualizar', 'tipo_documento' => 'CC', 'numero_documento' => '1', 'nombres' => 'Admin', 'apellidos' => 'Prueba',
        'telefono' => '1', 'id_rol' => '1', 'direccion' => 'x'];
    $emailAdmin = valor('SELECT email FROM usuario WHERE id_usuario = 1001');
    $admin->pedir('POST', '/admin/actualizar-usuario', $datosAdmin + ['id_usuario' => 1001, 'email' => $emailAdmin, 'estado' => 'inactivo']);
    comprobar($estadoUsuario(1001) === 'activo', 'actualizarUsuario: el administrador no se inhabilita a sí mismo por el formulario de edición');
    $admin->pedir('POST', '/admin/actualizar-usuario', $datosAdmin + ['id_usuario' => $uVictima, 'email' => 'f6test+nuevo@vetwilling.test', 'estado' => 'activo']);
    comprobar(valor('SELECT email FROM usuario WHERE id_usuario = ?', [$uVictima]) === 'f6test+nuevo@vetwilling.test' && $estadoUsuario($uVictima) === 'activo', 'El rol 1 sí cambia el correo y reactiva la cuenta');
    $admin->pedir('POST', '/admin/actualizar-usuario', $datosAdmin + ['id_usuario' => $uVictima, 'email' => 'f6test+nuevo@vetwilling.test', 'estado' => 'superusuario']);
    comprobar($estadoUsuario($uVictima) === 'activo', 'Estado fuera de la lista blanca → rechazado');

    // Último administrador: por HTTP no se alcanza (quien actúa es otro administrador activo); se prueba el modelo
    $_ENV += ['DB_HOST' => $env['HOST'], 'DB_PORT' => $env['PORT'], 'DB_NAME' => $env['NAME'], 'DB_USER' => $env['USER'], 'DB_PASS' => $env['PASS']];
    require_once "$raiz/app/models/usuario.php";
    $soloUnAdmin = (int) valor("SELECT COUNT(*) FROM usuario WHERE id_rol = 1 AND LOWER(estado) = 'activo'") === 1;
    comprobar($soloUnAdmin && (new Usuario())->cambiarEstadoCuenta(1001, 'inactivo', 0) === 'ultimo_admin' && $estadoUsuario(1001) === 'activo', 'Modelo: no se inhabilita al último administrador activo');

    echo "▶ F6 · Inhabilitar propietarios (solo rol 1, POST y CSRF)\n";
    $json = ['Accept: application/json'];
    comprobar($codigoDe($admin->pedir('DELETE', "/admin/api/propietarios?id=$propB")) === 405 && $estadoUsuario($uP) === 'activo', 'DELETE → 405');
    comprobar($codigoDe($admin->pedir('GET', "/admin/api/propietarios?accion=eliminar&id=$propB")) === 405 && $estadoUsuario($uP) === 'activo', 'GET ?accion=eliminar → 405');
    comprobar($codigoDe($admin->post('/admin/api/propietarios', ['accion' => 'eliminar', 'id' => $propB], false, $json)) === 403 && $estadoUsuario($uP) === 'activo', 'POST sin token → 403');
    comprobar($codigoDe($rep->post('/representante/api/propietarios', ['accion' => 'eliminar', 'id' => $propB], true, $json)) === 403 && $estadoUsuario($uP) === 'activo', 'Representante → 403 (P3: no cambia el estado de cuentas)');
    $r = $admin->pedir('POST', '/admin/api/propietarios', ['accion' => 'eliminar', 'id' => $propB], [...$json, 'X-CSRF-Token: ' . $admin->token]);
    comprobar($r[0] === 200 && str_contains($r[1], '"success"') && strtolower($estadoUsuario($uP)) === 'inactivo', 'POST con token en cabecera desde el rol 1 → inhabilitado');

    echo "▶ F6 · Desvincular profesionales (P3)\n";
    comprobar($codigoDe($rep->pedir('GET', '/representante/eliminar-profesional?action=eliminar&id=27')) === 405 && $estadoPv(27) === 'Activo', 'GET → 405 y el vínculo no cambia');
    comprobar($codigoDe($rep->post('/representante/eliminar-profesional', ['accion' => 'eliminar', 'id' => 27], false)) === 403 && $estadoPv(27) === 'Activo', 'POST sin token → 403');
    comprobar($codigoDe($rep->post('/representante/eliminar-profesional', ['accion' => 'eliminar', 'id' => $pvX])) === 404 && $estadoPv($pvX) === 'Activo', 'Vínculo de otra clínica → 404');
    comprobar($codigoDe($rep->post('/representante/eliminar-profesional', ['accion' => 'eliminar', 'id' => 25])) === 404 && $estadoPv(25) === 'Activo', 'Vínculo en otra clínica de un profesional compartido → 404');
    $rep->post('/representante/eliminar-profesional', ['accion' => 'eliminar', 'id' => 27]);
    comprobar($estadoPv(27) === 'Inactivo' && $estadoUsuario(1003) === 'activo', 'Desvincular de su clínica: el vínculo queda Inactivo y la cuenta sigue activa');
    $pdo->exec("UPDATE profesional_veterinaria SET estado = 'Activo' WHERE id_prof_veterinaria = 27");

    $estadoPe = fn(int $id) => valor('SELECT estado FROM profesional_especialidad WHERE id_profesional_esp = ?', [$id]);
    $estadoPs = fn(int $id) => valor('SELECT estado FROM profesionales_servicio WHERE id_prof_servicio = ?', [$id]);
    comprobar($codigoDe($rep->pedir('GET', "/representante/eliminar-esp-profesional?action=eliminarEspProfesional&id_profesional=1003&id_especialidad=$peA")) === 405 && $estadoPe($peA) === 'Activo', 'Quitar especialidad por GET → 405');
    comprobar($codigoDe($rep->post('/representante/eliminar-esp-profesional', ['accion' => 'eliminarEspProfesional', 'id_profesional' => 1003, 'id_especialidad' => $peB])) === 404 && $estadoPe($peB) === 'Activo', 'Especialidad registrada en otra clínica → 404');
    $rep->post('/representante/eliminar-esp-profesional', ['accion' => 'eliminarEspProfesional', 'id_profesional' => 1003, 'id_especialidad' => $peA]);
    comprobar($estadoPe($peA) === 'Inactivo', 'Especialidad de su clínica → eliminada');
    comprobar($codigoDe($rep->pedir('GET', "/representante/eliminar-serv-profesional?action=eliminarServProfesional&id_usuario=1003&id_servicio=$psA")) === 405 && $estadoPs($psA) === 'Activo', 'Quitar servicio por GET → 405');
    comprobar($codigoDe($rep->post('/representante/eliminar-serv-profesional', ['accion' => 'eliminarServProfesional', 'id_usuario' => 1003, 'id_servicio' => $psB])) === 404 && $estadoPs($psB) === 'Activo', 'Servicio de otra clínica → 404');
    comprobar($codigoDe($rep->post('/representante/eliminar-serv-profesional', ['accion' => 'eliminarServProfesional', 'id_usuario' => $uX, 'id_servicio' => $psA])) === 404, 'Profesional de otra clínica → 404');
    $rep->post('/representante/eliminar-serv-profesional', ['accion' => 'eliminarServProfesional', 'id_usuario' => 1003, 'id_servicio' => $psA]);
    comprobar($estadoPs($psA) === 'Inactivo', 'Servicio de su clínica → eliminado');

    echo "▶ F6 · Clínicas y horarios\n";
    $estadoVet = fn(int $id) => valor('SELECT estado FROM veterinaria WHERE id_veterinaria = ?', [$id]);
    $r = $rep->pedir('GET', '/representante/dashboard?action=eliminar&id=103');
    comprobar($estadoVet(103) === 'Activo' && str_contains($r[1], 'Método no permitido'), 'GET ?action=eliminar en una página del panel 4 (layout) → no se ejecuta');
    comprobar($codigoDe($admin->pedir('GET', '/admin/eliminar-veterinaria?action=eliminar&id=103')) === 405 && $estadoVet(103) === 'Activo', 'GET /admin/eliminar-veterinaria → 405');
    comprobar($codigoDe($admin->post('/admin/eliminar-veterinaria', ['accion' => 'eliminar', 'id' => 103], false)) === 403 && $estadoVet(103) === 'Activo', 'POST sin token → 403');
    $admin->post('/admin/eliminar-veterinaria', ['accion' => 'eliminar', 'id' => 103]);
    comprobar($estadoVet(103) === 'Eliminado', 'POST con token desde el rol 1 → clínica eliminada');
    $estadoH = fn(int $id) => valor('SELECT estado FROM horario_veterinaria WHERE id_horario_veterinaria = ?', [$id]);
    comprobar($codigoDe($rep->pedir('GET', "/representante/actualizar-horario-veterinaria?action=eliminar_horarios&id=$hA")) === 405 && $estadoH($hA) === 'Activo', 'Eliminar horario por GET → 405');
    comprobar($codigoDe($rep->post('/representante/actualizar-horario-veterinaria', ['accion' => 'eliminar_horarios', 'id' => $hB], true, $json)) === 404 && $estadoH($hB) === 'Activo', 'Horario de otra clínica → 404');
    $r = $rep->post('/representante/actualizar-horario-veterinaria', ['accion' => 'eliminar_horarios', 'id' => $hA], true, $json);
    comprobar($r[0] === 200 && str_contains($r[1], '"success"') && $estadoH($hA) === 'Inactivo', 'Horario de su clínica → eliminado (JSON success)');

    echo "▶ F5a · Profesionales de su clínica\n";
    $datosProf = ['accion' => 'actualizar', 'tipo_documento' => 'CC', 'numero_documento' => '123', 'nombres' => 'NombreF5a', 'apellidos' => 'Prueba',
        'telefono' => '1', 'direccion' => 'x', 'rol' => '2', 'estado' => 'Inactivo', 'email' => 'f6test+robado@vetwilling.test', 'id_veterinaria' => 102];
    comprobar($codigoDe($rep->pedir('POST', '/representante/actualizar-profesional', $datosProf + ['id_usuario' => $uX])) === 404
        && valor('SELECT nombres FROM profesional WHERE id_profesional = ?', [$pX]) === 'ProfesionalSoloB', 'Editar un profesional de otra clínica → 404, sin cambios');
    $email1003 = valor('SELECT email FROM usuario WHERE id_usuario = 1003');
    $rep->pedir('POST', '/representante/actualizar-profesional', $datosProf + ['id_usuario' => 1003]);
    comprobar(valor('SELECT nombres FROM profesional WHERE id_usuario = 1003') === 'NombreF5a', 'Profesional de su clínica → perfil actualizado');
    comprobar(valor('SELECT email FROM usuario WHERE id_usuario = 1003') === $email1003, 'El correo enviado por el representante se ignora');
    comprobar($estadoUsuario(1003) === 'activo' && $estadoPv(27) === 'Inactivo' && $estadoPv(24) === 'Activo',
        '"estado" cambia solo el vínculo con su clínica (ni la cuenta ni el vínculo con otra clínica)');
    $pdo->exec("UPDATE profesional_veterinaria SET estado = 'Activo' WHERE id_prof_veterinaria = 27");
    comprobar($codigoDe($rep->pedir('GET', "/representante/editar-profesional?id=$uX")) === 404, 'Ver la edición de un profesional de otra clínica → 404');
    $r = $rep->pedir('GET', '/representante/editar-profesional?id=1003');
    comprobar($r[0] === 200 && str_contains($r[1], 'readonly') && !str_contains($r[1], 'name="email"'), 'Edición de un profesional propio: el correo es de solo lectura y no se envía');
    comprobar($codigoDe($rep->pedir('GET', "/representante/agenda-usuario?id=$uX")) === 404, 'Agenda de un profesional de otra clínica → 404');
    $r = $rep->pedir('GET', '/representante/listar-profesionales');
    comprobar($r[0] === 200 && !str_contains($r[1], 'ProfesionalSoloB') && str_contains($r[1], 'NombreF5a'), 'La lista solo muestra profesionales de su clínica');

    echo "▶ F5a · Datos de la clínica, horarios y propietarios\n";
    $info = ['accion' => 'actualizarInfo', 'direccion' => 'Dirección F5a', 'ciudad' => 'Ciudad', 'telefono' => '123'];
    $dir102 = valor('SELECT direccion FROM veterinaria WHERE id_veterinaria = 102');
    comprobar($codigoDe($rep->pedir('POST', '/representante/actualizar-veterinaria', $info + ['id_veterinaria' => 102])) === 404
        && valor('SELECT direccion FROM veterinaria WHERE id_veterinaria = 102') === $dir102, 'Actualizar la información de otra clínica → 404, sin cambios');
    $rep->pedir('POST', '/representante/actualizar-veterinaria', $info + ['id_veterinaria' => 101]);
    comprobar(valor('SELECT direccion FROM veterinaria WHERE id_veterinaria = 101') === 'Dirección F5a', 'Su propia clínica → actualizada');
    comprobar($codigoDe($rep->pedir('POST', '/representante/actualizar-veterinaria', ['accion' => 'actualizar', 'id_veterinaria' => 102, 'id_usuario' => 1004, 'email_user' => 'f6test+robado@vetwilling.test'])) === 403,
        'Edición completa de clínica (con el correo del representante) desde el rol 4 → 403');
    comprobar($codigoDe($rep->pedir('GET', '/representante/obtener-horarios-veterinaria?action=obtener_horarios&id=102')) === 404, 'Horarios de otra clínica → 404');
    $r = $rep->pedir('GET', '/representante/obtener-horarios-veterinaria?action=obtener_horarios&id=101');
    comprobar($r[0] === 200 && str_contains($r[1], '"success"'), 'Horarios de su clínica → 200');
    comprobar($codigoDe($rep->pedir('GET', '/representante/propietarios/api/listar?accion=listar-veterinaria&id=102')) === 404, 'Propietarios de otra clínica → 404');
    $r = $rep->pedir('GET', '/representante/propietarios/api/listar?accion=listar-veterinaria&id=101');
    comprobar($r[0] === 200 && !str_contains($r[1], 'PropietarioB'), 'Propietarios de su clínica → 200, sin los de otra');
    comprobar($codigoDe($rep->pedir('GET', '/representante/api/propietarios')) === 403, 'Lista de todos los propietarios desde el rol 4 → 403');

    echo "▶ F5a · Veterinarios (veterinario: él mismo; representante: su clínica, provisional D1)\n";
    $datosVet = ['accion' => 'actualizar', 'nombres' => 'VetF5a', 'apellidos' => 'Prueba', 'tipo_documento' => 'CC', 'numero_documento' => '1', 'telefono' => '1',
        'email' => 'f6test+robado@vetwilling.test', 'estado' => 'inactivo'];
    comprobar($codigoDe($vet->pedir('POST', '/veterinario/actualizar-veterinario', $datosVet + ['id_usuario' => $uW])) === 404
        && valor('SELECT nombres FROM veterinario WHERE id_usuario = ?', [$uW]) === 'VeterinarioB', 'El veterinario edita a otro → 404, sin cambios');
    $vet->pedir('POST', '/veterinario/actualizar-veterinario', $datosVet + ['id_usuario' => 1003]);
    comprobar(valor('SELECT nombres FROM veterinario WHERE id_usuario = 1003') === 'VetF5a' && valor('SELECT email FROM usuario WHERE id_usuario = 1003') === $email1003
        && $estadoUsuario(1003) === 'activo', 'El veterinario edita su perfil; el correo y el estado enviados se ignoran');
    comprobar($codigoDe($rep->pedir('POST', '/veterinario/actualizar-veterinario', $datosVet + ['id_usuario' => $uW])) === 404, 'El representante edita un veterinario de otra clínica → 404');
    comprobar($codigoDe($rep->pedir('GET', "/veterinario/editar-veterinario?id=$uW")) === 404 && $codigoDe($rep->pedir('GET', '/veterinario/editar-veterinario?id=1003')) === 200,
        'Vista de edición: otra clínica → 404; su clínica → 200');
    comprobar($codigoDe($vet->pedir('GET', "/veterinario/editar-veterinario?accion=eliminar&id=$uW")) === 405 && $estadoUsuario($uW) === 'activo', 'GET ?accion=eliminar en la vista de veterinario → 405, la cuenta no cambia');
} catch (Throwable $e) {
    comprobar(false, 'Excepción en la prueba', $e->getMessage());
} finally {
    restaurar();
    proc_terminate($servidor);
    proc_close($servidor);
    $quedan = (int) valor("SELECT COUNT(*) FROM usuario WHERE email LIKE 'f6test+%'");
    comprobar($quedan === 0, 'Limpieza: no quedan datos de prueba y las filas existentes se restauraron');
    if ($fallo) {
        $lineas = array_filter(file($log), fn($l) => !preg_match('/ (Accepted|Closing|Closed without)/', $l));
        echo "\n— Registro del servidor —\n" . implode('', array_slice($lineas, -(int) (getenv('LINEAS_LOG') ?: 30)));
    }
    @unlink($log);
}

echo "\nResultado: $ok correctas, $fallo fallidas.\n";
exit($fallo ? 1 : 0);
