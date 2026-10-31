<?php
// Pruebas del módulo de Medios de envío.
// Ejecutar con: bash tests/run_medios_envio.sh
//
// Aislamiento: las tablas del módulo se reemplazan por TABLAS TEMPORALES (solo existen en esta
// conexión y desaparecen al terminar), así que no se tocan los datos reales. El correo sale por un
// servidor SMTP falso local (tests/fake_smtp.py); no se envía nada a internet.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$puerto = (int) ($argv[1] ?? 0);
$salida = $argv[2] ?? '';
if (!$puerto || !is_dir($salida)) {
    fwrite(STDERR, "Uso: php tests/medios_envio_test.php <puerto_smtp_falso> <carpeta_salida>\n");
    exit(2);
}

// ── Entorno de prueba (antes de cargar la configuración) ─────────────────────
$claveDePrueba = base64_encode(random_bytes(32));
$entorno = [
    'MAIL_DRIVER'        => 'smtp',
    'APP_ENCRYPTION_KEY' => $claveDePrueba,
    'SMTP_PASS'          => 'env-pass-de-prueba',
    'SMTP_USER'          => 'env-usuario@vetwilling.test',
    'SMTP_FROM_EMAIL'    => 'env-remitente@vetwilling.test',
];
foreach ($entorno as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $v;
}
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
// BASE_PATH lo define config/config.php (cargado por database.php)

require_once dirname(__DIR__) . '/config/database.php';
require_once BASE_PATH . '/app/helpers/email_helper.php';      // incluye mailer_helper
require_once BASE_PATH . '/app/helpers/notificacion_helper.php';
require_once BASE_PATH . '/app/models/CanalEnvio.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

$db = (new conexion())->getConexion();

// ── Utilidades ───────────────────────────────────────────────────────────────
$resultados = ['ok' => 0, 'fallo' => 0];
function comprobar(bool $condicion, string $descripcion, string $detalle = ''): void
{
    global $resultados;
    $resultados[$condicion ? 'ok' : 'fallo']++;
    echo ($condicion ? '  ✅ ' : '  ❌ ') . $descripcion . ($condicion || $detalle === '' ? '' : "  → $detalle") . "\n";
}
function seccion(string $titulo): void { echo "\n▶ $titulo\n"; }
function mensajes(): array
{
    global $salida;
    $f = glob("$salida/msg_*.eml");
    natsort($f);
    return array_values($f);
}
function ultimaFila(string $destinatario): ?array
{
    global $db;
    $s = $db->prepare("SELECT * FROM notificaciones_enviadas WHERE destinatario = ? ORDER BY id_notificacion DESC LIMIT 1");
    $s->execute([$destinatario]);
    return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
function fila(int $id): array
{
    global $db;
    return $db->query("SELECT * FROM notificaciones_enviadas WHERE id_notificacion = $id")->fetch(PDO::FETCH_ASSOC);
}
function canal(string $codigo, int $habilitado): void
{
    global $db;
    $db->prepare("UPDATE canal_envio_config SET habilitado = ? WHERE codigo_canal = ?")->execute([$habilitado, $codigo]);
}
function datosCita(string $correo, ?int $idAgendamiento = null): array
{
    return [
        'id_agendamiento'    => $idAgendamiento,
        'email_propietario'  => $correo,
        'nombre_propietario' => 'Ana <script>alert(1)</script>',
        'nombre_mascota'     => 'Luna',
        'tipo_servicio'      => 'Vacunación',
        'fecha_hora'         => date('Y-m-d H:i:s', strtotime('+2 days')),
        'estado'             => 'Pendiente',
    ];
}

// ── Tablas temporales con la misma estructura que las reales ─────────────────
$canalesReales    = $db->query("SELECT * FROM canal_envio_config")->fetchAll(PDO::FETCH_ASSOC);
$plantillasReales = $db->query("SELECT * FROM plantilla_mensaje_canal")->fetchAll(PDO::FETCH_ASSOC);

foreach (['canal_envio_config', 'plantilla_mensaje_canal', 'notificaciones_enviadas', 'notificacion', 'agendamiento'] as $tabla) {
    $ddl = $db->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM)[1];
    $ddl = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl);
    // Sin llaves foráneas (las tablas temporales no las admiten); se conservan los CHECK
    $ddl = preg_replace('/^\s*CONSTRAINT `[^`]+` FOREIGN KEY.*$\n?/m', '', $ddl);
    $ddl = preg_replace('/,(\s*\n\))/', '$1', $ddl);
    $db->exec($ddl);
}
foreach (['canal_envio_config' => $canalesReales, 'plantilla_mensaje_canal' => $plantillasReales] as $tabla => $filas) {
    foreach ($filas as $f) {
        $cols = array_keys($f);
        $db->prepare("INSERT INTO `$tabla` (`" . implode('`,`', $cols) . "`) VALUES (" . rtrim(str_repeat('?,', count($cols)), ',') . ")")
           ->execute(array_values($f));
    }
}
$enTemporales = (int) $db->query("SELECT COUNT(*) FROM notificaciones_enviadas")->fetchColumn() === 0;

// Canal de correo apuntando al SMTP falso, con contraseña cifrada distinta de la de .env
$db->prepare("UPDATE canal_envio_config SET habilitado = 1, smtp_host = '127.0.0.1', smtp_port = ?, smtp_encriptacion = 'ninguna',
              smtp_usuario = 'usuario-db@vetwilling.test', smtp_password = ?, smtp_remitente = 'notificaciones@vetwilling.test',
              smtp_nombre_remitente = 'VetWilling Pruebas', max_reintentos = 3, intervalo_reintento_seg = 30
              WHERE codigo_canal = 'email'")
   ->execute([$puerto, secreto_cifrar('db-pass-de-prueba')]);

$db->exec("SET SESSION sql_mode = ''"); // para insertar citas mínimas en la tabla temporal
$db->exec("INSERT INTO agendamiento (id_agendamiento, fecha_hora) VALUES
           (900001, NOW() + INTERVAL 2 DAY), (900002, NOW() - INTERVAL 1 DAY), (900003, NOW() + INTERVAL 1 DAY)");

echo "Pruebas de Medios de envío — MySQL " . $db->query("SELECT VERSION()")->fetchColumn() . ", PHP " . PHP_VERSION . "\n";
comprobar($enTemporales, 'Se trabaja sobre tablas temporales aisladas (no hay filas reales visibles)');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Cifrado de credenciales');
$cifrado = secreto_cifrar('mi-clave-secreta');
comprobar(secreto_esta_cifrado($cifrado) && !str_contains($cifrado, 'mi-clave-secreta'), 'El valor guardado está cifrado y no contiene el texto original');
comprobar(secreto_descifrar($cifrado) === 'mi-clave-secreta', 'Se descifra con la clave de .env');
comprobar(secreto_cifrar('x') !== secreto_cifrar('x'), 'Cada cifrado usa un nonce distinto');
$alterado = substr($cifrado, 0, -3) . (substr($cifrado, -3, 1) === 'A' ? 'B' : 'A') . substr($cifrado, -2);
try { secreto_descifrar($alterado); $detectado = false; } catch (RuntimeException $e) { $detectado = true; }
comprobar($detectado, 'Un valor alterado se rechaza (cifrado autenticado)');
comprobar(secreto_descifrar('texto-antiguo') === 'texto-antiguo', 'Los valores antiguos en texto plano se siguen leyendo (transición)');
comprobar(secreto_estado($cifrado) === 'cifrada' && secreto_estado('plano') === 'texto_plano' && secreto_estado(null) === 'vacia', 'Estados de la credencial: cifrada, texto plano y vacía');

// Compatibilidad entre intérpretes: el PHP de XAMPP no trae sodium; el cifrado debe funcionar en ambos
$phpXampp = '/opt/lampp/bin/php';
if (is_executable($phpXampp)) {
    $codigo = '$_SERVER["HTTP_HOST"]="localhost"; $_SERVER["SCRIPT_NAME"]="/index.php"; require "' . BASE_PATH . '/app/helpers/secretos_helper.php";';
    $ejecutar = fn(string $php, string $expr, string $arg) => trim((string) shell_exec(
        'APP_ENCRYPTION_KEY=' . escapeshellarg($claveDePrueba) . ' ' . escapeshellarg($php) . ' -r ' . escapeshellarg($codigo . ' echo ' . $expr . ';') . ' ' . escapeshellarg($arg) . ' 2>&1'
    ));
    $deXampp = $ejecutar($phpXampp, 'secreto_cifrar($argv[1])', 'valor-cruzado');
    comprobar(secreto_descifrar($deXampp) === 'valor-cruzado', 'Lo cifrado con el PHP de XAMPP (' . trim((string) shell_exec(escapeshellarg($phpXampp) . ' -r "echo PHP_VERSION;"')) . ') se descifra con este PHP');
    comprobar($ejecutar($phpXampp, 'secreto_descifrar($argv[1])', secreto_cifrar('valor-cruzado-2')) === 'valor-cruzado-2', 'Lo cifrado con este PHP (' . PHP_VERSION . ') se descifra con el PHP de XAMPP');
} else {
    echo "  (sin XAMPP en /opt/lampp: se omite la prueba cruzada de intérpretes)\n";
}

$_ENV['APP_ENCRYPTION_KEY'] = 'clave-invalida';
putenv('APP_ENCRYPTION_KEY=clave-invalida');
try { secreto_cifrar('x'); $lanzo = false; } catch (RuntimeException $e) { $lanzo = true; }
comprobar($lanzo, 'Sin clave válida no se cifra (no hay respaldo silencioso en texto plano)');
$antes = $db->query("SELECT smtp_password FROM canal_envio_config WHERE codigo_canal = 'email'")->fetchColumn();
try { (new CanalEnvio())->actualizarConfig((int) $db->query("SELECT id_canal FROM canal_envio_config WHERE codigo_canal='email'")->fetchColumn(), ['smtp_password' => 'nueva']); $guardo = true; } catch (RuntimeException $e) { $guardo = false; }
$despues = $db->query("SELECT smtp_password FROM canal_envio_config WHERE codigo_canal = 'email'")->fetchColumn();
comprobar(!$guardo && $antes === $despues, 'Sin clave, guardar una contraseña falla y no modifica la base de datos');
try { mailer_password_smtp(['smtp_password' => $antes]); $respaldo = true; } catch (RuntimeException $e) { $respaldo = false; }
comprobar(!$respaldo, 'Sin clave, la contraseña cifrada no se usa ni se sustituye en silencio por SMTP_PASS de .env');
comprobar(secreto_estado($antes) === 'ilegible', 'Sin la clave correcta, la credencial cifrada se reporta como ilegible');
$_ENV['APP_ENCRYPTION_KEY'] = $claveDePrueba;
putenv("APP_ENCRYPTION_KEY=$claveDePrueba");

$canales = (new CanalEnvio())->listarCanales();
$email = array_values(array_filter($canales, fn($c) => $c['codigo_canal'] === 'email'))[0];
comprobar(!array_key_exists('smtp_password', $email) && $email['smtp_password_estado'] === 'cifrada', 'La API de canales no devuelve la contraseña, solo su estado (cifrada)');

// Editar otros campos sin escribir contraseña no la borra (ni con "" ni con null, aunque se salte el JS)
$idEmail = (int) $email['id_canal'];
$guardada = $db->query("SELECT smtp_password FROM canal_envio_config WHERE id_canal = $idEmail")->fetchColumn();
(new CanalEnvio())->actualizarConfig($idEmail, ['smtp_nombre_remitente' => 'VetWilling Pruebas', 'smtp_password' => '', 'push_api_key' => null]);
comprobar($db->query("SELECT smtp_password FROM canal_envio_config WHERE id_canal = $idEmail")->fetchColumn() === $guardada,
    'Guardar con la contraseña vacía o null conserva la contraseña cifrada actual');
(new CanalEnvio())->actualizarConfig($idEmail, ['smtp_password' => 'db-pass-de-prueba']);
$recifrada = $db->query("SELECT smtp_password FROM canal_envio_config WHERE id_canal = $idEmail")->fetchColumn();
comprobar(secreto_esta_cifrado($recifrada) && !secreto_esta_cifrado(substr($recifrada, strlen(SECRETO_PREFIJO)))
    && secreto_descifrar($recifrada) === 'db-pass-de-prueba', 'Una contraseña nueva se guarda cifrada una sola vez (sin doble cifrado)');

comprobar(CanalEnvio::validarConfig(['smtp_password' => str_repeat('x', 151)]) !== []
    && CanalEnvio::validarConfig(['smtp_password' => str_repeat('x', 150)]) === []
    && strlen(secreto_cifrar(str_repeat('x', 150))) <= 255, 'La contraseña admitida, una vez cifrada, cabe en smtp_password varchar(255)');

// Uso de SMTP_PASS de .env: solo sin contraseña guardada y con el servidor y usuario de .env
comprobar(mailer_password_smtp(['smtp_password' => null]) === 'env-pass-de-prueba', 'Sin contraseña guardada y con servidor/usuario de .env se usa SMTP_PASS');
try { mailer_password_smtp(['smtp_password' => '', 'smtp_host' => '127.0.0.1']); $respaldo = true; } catch (RuntimeException $e) { $respaldo = false; }
comprobar(!$respaldo, 'SMTP_PASS de .env no se envía a un servidor configurado desde la base de datos');
try { mailer_password_smtp(['smtp_password' => '', 'smtp_usuario' => 'otra@vetwilling.test']); $respaldo = true; } catch (RuntimeException $e) { $respaldo = false; }
comprobar(!$respaldo, 'SMTP_PASS de .env no se combina con otro usuario SMTP');
comprobar(mailer_password_smtp(['smtp_password' => 'texto-antiguo']) === 'texto-antiguo', 'Una contraseña antigua en texto plano se sigue usando hasta migrarla');
$alteradaCanal = substr($recifrada, 0, -3) . (substr($recifrada, -3, 1) === 'A' ? 'B' : 'A') . substr($recifrada, -2);
try { mailer_password_smtp(['smtp_password' => $alteradaCanal]); $mensaje = ''; } catch (RuntimeException $e) { $mensaje = $e->getMessage(); }
comprobar($mensaje !== '' && !str_contains($mensaje, 'db-pass-de-prueba') && !str_contains($mensaje, 'env-pass-de-prueba'),
    'Una contraseña alterada produce un error controlado que no contiene secretos');

// Con la contraseña del canal ilegible, la prueba de envío falla con un mensaje claro y no se autentica con otra
$db->prepare("UPDATE canal_envio_config SET smtp_password = ? WHERE id_canal = ?")->execute([$alteradaCanal, $idEmail]);
@unlink("$salida/auth.txt");
$r = mailer_enviar_prueba('admin-ilegible@vetwilling.test');
comprobar(!$r['ok'] && str_contains($r['mensaje'], 'no se puede descifrar') && !file_exists("$salida/auth.txt"),
    'Con la credencial ilegible no se envía ni se intenta autenticar con SMTP_PASS');
$db->prepare("UPDATE canal_envio_config SET smtp_password = ? WHERE id_canal = ?")->execute([$recifrada, $idEmail]);

// ─────────────────────────────────────────────────────────────────────────────
seccion('Validación de configuración SMTP');
comprobar(CanalEnvio::validarConfig(['smtp_host' => 'smtp.gmail.com', 'smtp_port' => '465', 'smtp_encriptacion' => 'smtps', 'smtp_remitente' => 'a@b.co', 'max_reintentos' => '3', 'intervalo_reintento_seg' => '30']) === [], 'Una configuración correcta pasa la validación');
comprobar(count(CanalEnvio::validarConfig(['smtp_port' => '70000'])) === 1, 'Puerto fuera de rango se rechaza');
comprobar(count(CanalEnvio::validarConfig(['smtp_remitente' => 'no-es-correo'])) === 1, 'Remitente inválido se rechaza');
comprobar(count(CanalEnvio::validarConfig(['smtp_encriptacion' => 'rot13'])) === 1, 'Encriptación desconocida se rechaza');
comprobar(count(CanalEnvio::validarConfig(['smtp_host' => 'host con espacios'])) === 1, 'Host inválido se rechaza');
comprobar(count(CanalEnvio::validarConfig(['smtp_nombre_remitente' => "VetWilling\r\nBcc: x@y.z"])) === 1, 'Inyección de cabeceras en el nombre del remitente se rechaza');
comprobar(count(CanalEnvio::validarConfig(['max_reintentos' => '-1', 'intervalo_reintento_seg' => 'abc'])) === 2, 'Reintentos e intervalo no numéricos se rechazan');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Transporte SMTP y prioridad de configuración');
$m = mailer_init();
comprobar($m->Host === '127.0.0.1' && $m->Port === $puerto && $m->Username === 'usuario-db@vetwilling.test', 'Se usan host, puerto y usuario de Medios de envío (prioridad sobre .env)');
comprobar($m->Timeout === 15, 'Tiempo de espera SMTP de 15 s (antes 300 s por defecto)');
comprobar($m->From === 'notificaciones@vetwilling.test' && $m->FromName === 'VetWilling Pruebas', 'Se usa el remitente configurado');

$n0 = count(mensajes());
$ok = enviarNotificacionCitaCreada(datosCita('ana@vetwilling.test', 900001));
$f = ultimaFila('ana@vetwilling.test');
comprobar($ok && count(mensajes()) === $n0 + 1, 'Con el canal activo, el correo de cita llega al servidor SMTP');
comprobar(trim((string) @file_get_contents("$salida/auth.txt")) === hash('sha256', 'db-pass-de-prueba'), 'Se autenticó con la contraseña descifrada de Medios de envío');
comprobar($f && $f['estado_envio'] === 'exitoso' && $f['tipo_notificacion'] === 'cita' && (int) $f['id_agendamiento'] === 900001, 'El envío queda registrado como exitoso, tipo cita y con su cita de referencia');
comprobar($f && str_contains($f['cuerpo_html'], '&lt;script&gt;') && !str_contains($f['cuerpo_html'], '<script>'), 'Los datos del usuario se escapan en el HTML del correo');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Canal de correo apagado y excepción de seguridad');
canal('email', 0);
$n0 = count(mensajes());
$ok = enviarNotificacionCitaCreada(datosCita('apagado@vetwilling.test', 900001));
$f = ultimaFila('apagado@vetwilling.test');
comprobar(!$ok && count(mensajes()) === $n0 && $f['estado_envio'] === 'omitido', 'Canal apagado: el correo ordinario no sale y queda como omitido');

$mail = mailer_init();
$mail->addAddress('recupera@vetwilling.test');
$mail->Subject = 'Restablece tu contraseña';
$mail->Body = '<a href="https://x/restablecer?token=abc123">enlace</a>';
$ok = mailer_enviar($mail, 'seguridad', ['critico' => true]);
$f = ultimaFila('recupera@vetwilling.test');
comprobar($ok && count(mensajes()) === $n0 + 1, 'Recuperación de contraseña se envía aunque el canal esté apagado');
comprobar($f['cuerpo_html'] === null, 'El enlace privado de recuperación no se guarda en el monitor');

$mail = mailer_init();
$mail->addAddress('cuela@vetwilling.test');
$mail->Subject = 'Bienvenida';
$mail->Body = '<p>Contraseña inicial: 123456</p>';
$ok = mailer_enviar($mail, 'bienvenida', ['critico' => true]);
comprobar(!$ok && count(mensajes()) === $n0 + 1 && ultimaFila('cuela@vetwilling.test')['estado_envio'] === 'omitido', "Otro tipo no puede colarse marcándose como 'critico'");
canal('email', 1);

$mail = mailer_init();
$mail->addAddress('bienvenida@vetwilling.test');
$mail->Subject = 'Bienvenida';
$mail->Body = '<p>Contraseña inicial: 123456</p>';
mailer_enviar($mail, 'bienvenida', ['guardar_contenido' => false]);
$f = ultimaFila('bienvenida@vetwilling.test');
comprobar($f['estado_envio'] === 'exitoso' && $f['cuerpo_html'] === null, 'Bienvenida con credenciales: se envía y no se guarda su contenido');

$mail = mailer_init();
$mail->addAddress('reporte@vetwilling.test');
$mail->Subject = 'Ficha clínica';
$mail->Body = '<p>Diagnóstico confidencial</p>';
mailer_enviar($mail, 'reporte'); // aunque quien llama olvide excluirlo
comprobar(ultimaFila('reporte@vetwilling.test')['cuerpo_html'] === null, 'Documento clínico: no se guarda aunque quien llama no lo excluya (lista de permitidos)');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Fallos SMTP y errores sanitizados');
$mail = mailer_init();
$mail->addAddress('rechazar@vetwilling.test');
$mail->Subject = 'x';
$mail->Body = 'x';
try { mailer_enviar($mail, 'cita'); $lanzo = false; } catch (Throwable $e) { $lanzo = true; }
$f = ultimaFila('rechazar@vetwilling.test');
comprobar($lanzo && $f['estado_envio'] === 'fallido' && $f['mensaje_error'] !== '', 'Un rechazo SMTP se registra como fallido y la excepción llega a quien llama');
comprobar(!str_contains((string) $f['mensaje_error'], 'db-pass-de-prueba'), 'El error registrado no contiene la contraseña');
$limpio = mailer_error_seguro('Fallo con db-pass-de-prueba y env-pass-de-prueba en https://x/?token=abc123 ' . str_repeat('x', 900));
comprobar(!str_contains($limpio, 'db-pass-de-prueba') && !str_contains($limpio, 'env-pass-de-prueba') && !str_contains($limpio, 'abc123') && mb_strlen($limpio) <= 500, 'mailer_error_seguro oculta contraseñas y tokens, y recorta el texto');
comprobar(mailer_error_es_permanente('SMTP Error: 550 5.1.1 User unknown') && !mailer_error_es_permanente('451 4.3.0 Intente mas tarde'), 'Distingue errores permanentes (550) de temporales (451)');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Botón "Enviar correo de prueba"');
comprobar(mailer_enviar_prueba('no-es-correo')['ok'] === false, 'Rechaza un destinatario inválido sin intentar enviar');
canal('email', 0);
$n0 = count(mensajes());
$r = mailer_enviar_prueba('admin@vetwilling.test');
comprobar(!$r['ok'] && count(mensajes()) === $n0 && str_contains($r['mensaje'], 'deshabilitado'), 'Con el canal apagado no envía y lo dice');
canal('email', 1);
$r = mailer_enviar_prueba('admin@vetwilling.test');
comprobar($r['ok'] && count(mensajes()) === $n0 + 1, 'Con el canal activo envía exactamente un correo al administrador');
comprobar(ultimaFila('admin@vetwilling.test')['tipo_notificacion'] === 'prueba' && ultimaFila('admin@vetwilling.test')['cuerpo_html'] === null, 'Queda en el monitor como tipo prueba, sin guardar contenido');
$r = mailer_enviar_prueba('rechazar@vetwilling.test');
comprobar(!$r['ok'] && !str_contains($r['mensaje'], 'db-pass-de-prueba') && !str_contains($r['mensaje'], 'env-pass-de-prueba'), 'Si falla, el mensaje de error no contiene secretos');
$ctrl = file_get_contents(BASE_PATH . '/app/controllers/mediosEnvioController.php');
comprobar(str_contains($ctrl, "csrf_valido(\$_SERVER['HTTP_X_CSRF_TOKEN']") && str_contains($ctrl, "medios_envio_ultima_prueba"), 'El controlador exige token CSRF y limita la prueba a una por minuto');
comprobar(str_contains($ctrl, "mailer_enviar_prueba((string) (\$_SESSION['user']['email']"), 'El destinatario de la prueba sale de la sesión (no se puede elegir)');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Plantillas');
comprobar(CanalEnvio::renderizar('Hola {nombre} {desconocida}', ['nombre' => '<b>Ana</b>']) === 'Hola &lt;b&gt;Ana&lt;/b&gt; ', 'Escapa variables y quita las que no tienen valor');
$idEmail = (int) $db->query("SELECT id_canal FROM canal_envio_config WHERE codigo_canal='email'")->fetchColumn();
$err = (new CanalEnvio())->validarPlantilla(['id_canal' => $idEmail, 'tipo_notificacion' => 'cita', 'asunto' => 'Hola {inventada}', 'cuerpo_html' => '{nombre_mascota}']);
comprobar(count($err) === 1 && str_contains($err[0], 'inventada'), 'Al guardar, se rechazan variables que no existen para ese tipo');
comprobar(count((new CanalEnvio())->validarPlantilla(['id_canal' => $idEmail, 'tipo_notificacion' => 'cita', 'asunto' => ''])) === 1, 'El asunto es obligatorio');

$db->exec("UPDATE plantilla_mensaje_canal SET asunto = 'Cita de {nombre_mascota} EDITADA' WHERE id_canal = $idEmail AND tipo_notificacion = 'cita'");
enviarNotificacionCitaCreada(datosCita('plantilla@vetwilling.test', 900001));
comprobar(ultimaFila('plantilla@vetwilling.test')['asunto'] === 'Cita de Luna EDITADA', 'La plantilla editada cambia el asunto del correo real');
$db->exec("UPDATE plantilla_mensaje_canal SET activa = 0 WHERE id_canal = $idEmail AND tipo_notificacion = 'cita'");
enviarNotificacionCitaCreada(datosCita('inactiva@vetwilling.test', 900001));
comprobar(ultimaFila('inactiva@vetwilling.test')['asunto'] === 'Confirmación de Cita - VetWilling', 'Con la plantilla desactivada se usa el texto por defecto');
$sinMascota = datosCita('sinmascota@vetwilling.test', 900001);
unset($sinMascota['nombre_mascota'], $sinMascota['tipo_servicio']);
comprobar(enviarNotificacionCitaCreada($sinMascota) && str_contains(ultimaFila('sinmascota@vetwilling.test')['cuerpo_html'], 'tu mascota'), 'Con datos faltantes se envía igual con valores por defecto');
enviarNotificacionCitaCancelada(datosCita('cancela@vetwilling.test', 900001) + ['motivo_cancelacion' => 'Agenda llena', 'fecha_cancelacion' => date('Y-m-d H:i:s')]);
$f = ultimaFila('cancela@vetwilling.test');
comprobar($f['tipo_notificacion'] === 'cancelacion' && str_contains($f['cuerpo_html'], 'Agenda llena'), 'El correo de cancelación usa su plantilla e incluye el motivo');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Confirmación y recordatorio son independientes');
enviarNotificacionCitaCreada(datosCita('indep@vetwilling.test', 900003));
comprobar(existeNotificacionExitosa(900003, 'email') === true, 'Hay un correo exitoso para la cita (la confirmación)');
comprobar(existeNotificacionExitosa(900003, 'email', 'recordatorio') === false, 'La confirmación NO cuenta como recordatorio (antes bloqueaba el de 24 h)');
enviarRecordatorioCita(datosCita('indep@vetwilling.test', 900003));
comprobar(existeNotificacionExitosa(900003, 'email', 'recordatorio') === true, 'Después del recordatorio, sí se detecta como enviado (evita duplicados)');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Reintentos');
$insertar = $db->prepare("INSERT INTO notificaciones_enviadas
    (id_agendamiento, medio_notificacion, tipo_notificacion, destinatario, asunto, cuerpo_html, estado_envio, intentos_envio, ultimo_intento)
    VALUES (?, 'email', ?, ?, 'Asunto', ?, ?, ?, ?)");
$casos = [
    'A' => [900001, 'cita',         'reintento-a@vetwilling.test', '<p>A</p>', 'fallido',      1, null],
    'B' => [900001, 'cita',         'agotado@vetwilling.test',     '<p>B</p>', 'fallido',      3, null],
    'C' => [900001, 'bienvenida',   'privado@vetwilling.test',     '<p>C</p>', 'fallido',      1, null],
    'D' => [900002, 'recordatorio', 'pasada@vetwilling.test',      '<p>D</p>', 'fallido',      1, null],
    'E' => [900001, 'cita',         'rechazar-e@vetwilling.test',  '<p>E</p>', 'fallido',      1, null],
    'F' => [900001, 'cita',         'entregado@vetwilling.test',   '<p>F</p>', 'exitoso',      1, null],
    'G' => [900001, 'cita',         'omitido@vetwilling.test',     '<p>G</p>', 'omitido',      1, null],
    'H' => [900001, 'cita',         'enproceso@vetwilling.test',   '<p>H</p>', 'reintentando', 1, date('Y-m-d H:i:s')],
    'I' => [900001, 'cita',         'abandonado@vetwilling.test',  '<p>I</p>', 'reintentando', 1, date('Y-m-d H:i:s', strtotime('-20 minutes'))],
    'J' => [900001, 'cita',         'temporal-j@vetwilling.test',  '<p>J</p>', 'fallido',      1, null],
    'K' => [null,   'cancelacion',  'sincita@vetwilling.test',     '<p>K</p>', 'fallido',      1, null],
    'L' => [900001, 'cita',         'sincontenido@vetwilling.test', null,      'fallido',      1, null],
];
$ids = [];
foreach ($casos as $clave => $c) {
    $insertar->execute($c);
    $ids[$clave] = (int) $db->lastInsertId();
}

canal('email', 0);
$r = reintentarCorreosFallidos(false);
comprobar($r['ok'] === false && fila($ids['A'])['estado_envio'] === 'fallido', 'Con el canal apagado no se reintenta nada');
canal('email', 1);

$n0 = count(mensajes());
$r = reintentarCorreosFallidos(false);
$e = fn($k) => fila($ids[$k]);
comprobar($e('A')['estado_envio'] === 'exitoso' && (int) $e('A')['intentos_envio'] === 2, 'A: fallido elegible se reenvía y suma un intento');
comprobar($e('B')['estado_envio'] === 'fallido' && (int) $e('B')['intentos_envio'] === 3, 'B: con el máximo de intentos no se reintenta');
comprobar($e('C')['estado_envio'] === 'fallido' && (int) $e('C')['intentos_envio'] === 1, 'C: tipo privado (bienvenida) no se reintenta aunque tenga contenido');
comprobar($e('D')['estado_envio'] === 'fallido', 'D: recordatorio de una cita que ya pasó no se reintenta');
comprobar($e('E')['estado_envio'] === 'rechazado', 'E: error permanente (550) queda como rechazado y no se reintentará');
comprobar($e('F')['estado_envio'] === 'exitoso' && (int) $e('F')['intentos_envio'] === 1, 'F: lo ya entregado no se reenvía');
comprobar($e('G')['estado_envio'] === 'omitido', 'G: lo omitido por canal apagado no se reenvía tarde');
comprobar($e('H')['estado_envio'] === 'reintentando' && (int) $e('H')['intentos_envio'] === 1, 'H: una fila reservada por otro proceso no se toca (sin duplicados)');
comprobar($e('I')['estado_envio'] === 'exitoso', 'I: una reserva abandonada (>10 min) vuelve a ser elegible');
comprobar($e('J')['estado_envio'] === 'fallido' && (int) $e('J')['intentos_envio'] === 2, 'J: error temporal (451) sigue como fallido para otro intento');
comprobar($e('K')['estado_envio'] === 'exitoso', 'K: correo sin cita asociada también se reintenta');
comprobar($e('L')['estado_envio'] === 'fallido' && (int) $e('L')['intentos_envio'] === 1, 'L: sin contenido guardado no se puede reenviar');
comprobar(count(mensajes()) === $n0 + 3, 'Salieron exactamente 3 correos (A, I, K)', 'salieron ' . (count(mensajes()) - $n0));
comprobar(!str_contains($r['mensaje'], 'pass'), 'El mensaje del resultado no expone datos sensibles');

$n0 = count(mensajes());
reintentarCorreosFallidos(false);
comprobar(count(mensajes()) === $n0, 'Una segunda ejecución no reenvía lo que ya se entregó');
comprobar(fila($ids['A'])['intentos_envio'] == 2, 'A no cambió en la segunda ejecución');

// Fila nueva para el intervalo (J ya agotó sus intentos con los reintentos manuales anteriores)
$insertar->execute([900001, 'cita', 'intervalo@vetwilling.test', '<p>M</p>', 'fallido', 1, date('Y-m-d H:i:s')]);
$idM = (int) $db->lastInsertId();
$db->prepare("UPDATE notificaciones_enviadas SET ultimo_intento = NOW() WHERE id_notificacion = ?")->execute([$idM]);
reintentarCorreosFallidos(true);
comprobar(fila($idM)['estado_envio'] === 'fallido' && (int) fila($idM)['intentos_envio'] === 1, 'El cron respeta el intervalo: un fallo reciente espera');
$db->prepare("UPDATE notificaciones_enviadas SET ultimo_intento = NOW() - INTERVAL 1 HOUR WHERE id_notificacion = ?")->execute([$idM]);
reintentarCorreosFallidos(true);
comprobar(fila($idM)['estado_envio'] === 'exitoso', 'Pasado el intervalo, el cron sí lo reintenta');
comprobar((int) fila($ids['J'])['intentos_envio'] === 3 && fila($ids['J'])['estado_envio'] === 'fallido', 'J: tras llegar al máximo de intentos, ya no se reintenta más');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Notificaciones internas');
$modelo = new Notificacion();
canal('in_app', 0);
comprobar($modelo->crear(['id_usuario' => 1, 'tipo' => 'general', 'titulo' => 't', 'mensaje' => 'm']) === false, 'Apagadas: no se crea la notificación');
comprobar($modelo->crearBatch([['id_usuario' => 1, 'tipo' => 'general', 'titulo' => 't', 'mensaje' => 'm']]) === 0, 'Apagadas: tampoco en lote');
canal('in_app', 1);
$id = $modelo->crear(['id_usuario' => 1, 'tipo' => 'general', 'titulo' => 't', 'mensaje' => 'm']);
comprobar($id > 0 && (int) $db->query("SELECT COUNT(*) FROM notificacion WHERE id_notificacion = $id")->fetchColumn() === 1, 'Encendidas: se guarda la notificación');

// Regresión: con las notificaciones internas apagadas, Seguimientos lanzaba una excepción y se
// revertía toda la actualización clínica (historial, nota y tratamiento) del veterinario.
require_once BASE_PATH . '/app/models/Seguimientos.php';
$metodo = new ReflectionMethod(Seguimientos::class, 'crearNotificacionesSeguimiento');
$metodo->setAccessible(true);
$seguimientos = new Seguimientos();
$datosSeguimiento = ['id_paciente' => 1, 'propietario_usuario_id' => 2];
canal('in_app', 0);
try { $creadas = $metodo->invoke($seguimientos, $datosSeguimiento, 'Actualización', 'Mensaje', 3); $lanzo = false; } catch (Throwable $e) { $lanzo = true; $creadas = null; }
comprobar(!$lanzo && $creadas === 0, 'Seguimiento clínico con notificaciones apagadas: no lanza excepción (no se revierte la actualización)');
canal('in_app', 1);
$antes = (int) $db->query("SELECT COUNT(*) FROM notificacion")->fetchColumn();
$creadas = $metodo->invoke($seguimientos, $datosSeguimiento, 'Actualización', 'Mensaje', 3);
comprobar($creadas === 2 && (int) $db->query("SELECT COUNT(*) FROM notificacion")->fetchColumn() === $antes + 2, 'Seguimiento clínico con notificaciones encendidas: notifica al propietario y al profesional');
$ctrl = file_get_contents(BASE_PATH . '/app/controllers/seguimientosController.php');
comprobar(str_contains($ctrl, '$notificacionesCreadas === 0'), 'El botón de recordatorio de seguimiento avisa cuando las notificaciones están apagadas');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Monitor (consultas SQL en MySQL real, modo ONLY_FULL_GROUP_BY)');
$db->exec("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
$fallos = (new CanalEnvio())->obtenerFallosRecientes(24, 100);
$resumen = (new CanalEnvio())->obtenerResumenMonitoreo(24);
comprobar(count(array_filter($fallos, fn($x) => $x['estado_envio'] === 'rechazado')) >= 1, 'El monitor lista también los rechazados');
comprobar(isset($resumen[0]['omitidos'], $resumen[0]['rechazados']), 'El resumen cuenta omitidos y rechazados');
comprobar(count(array_filter($fallos, fn($x) => array_key_exists('tipo_notificacion', $x) && array_key_exists('reintentable', $x))) === count($fallos), 'Cada fila del monitor trae tipo y si es reintentable');

// ─────────────────────────────────────────────────────────────────────────────
seccion('Revisión estática de los llamadores');
$reset = file_get_contents(BASE_PATH . '/app/services/PasswordResetService.php');
comprobar(str_contains($reset, "mailer_enviar(\$mail, 'seguridad', ['critico' => true])"), 'Recuperación de contraseña usa la excepción de seguridad');
foreach (['profesionalController', 'registroPacienteController', 'veterinariaController'] as $c) {
    comprobar(str_contains(file_get_contents(BASE_PATH . "/app/controllers/$c.php"), "mailer_enviar(\$mail, 'bienvenida', ['guardar_contenido' => false])"), "$c registra la bienvenida sin guardar contenido");
}
$sinRegistro = [];
foreach (glob(BASE_PATH . '/app/{controllers,services,helpers}/*.php', GLOB_BRACE) as $archivo) {
    if (preg_match('/\$mail->send\(\)/', file_get_contents($archivo)) && !str_ends_with($archivo, 'mailer_helper.php')) {
        $sinRegistro[] = basename($archivo);
    }
}
comprobar($sinRegistro === [], 'Ningún correo de la app usa $mail->send() directo (todos pasan por mailer_enviar)', implode(', ', $sinRegistro));

// ─────────────────────────────────────────────────────────────────────────────
seccion('Migración de la credencial existente (texto plano -> cifrada -> texto plano)');
require_once BASE_PATH . '/app/services/MigracionCredenciales.php';
$migracion = new MigracionCredenciales($db);
$valorCanal = fn() => $db->query("SELECT smtp_password FROM canal_envio_config WHERE codigo_canal = 'email'")->fetchColumn();
$legado = 'pass-legado-' . bin2hex(random_bytes(4));
$db->prepare("UPDATE canal_envio_config SET smtp_password = ? WHERE codigo_canal = 'email'")->execute([$legado]);

$filas = $migracion->ejecutar('cifrar', false);
comprobar($valorCanal() === $legado && in_array('se cifraría', array_column($filas, 'accion'), true), 'La simulación informa y no modifica nada');

$filas = $migracion->ejecutar('cifrar', true);
$guardado = $valorCanal();
comprobar(secreto_esta_cifrado($guardado) && !str_contains($guardado, $legado), 'Al aplicar, la credencial queda cifrada');
comprobar(secreto_descifrar($guardado) === $legado, 'Se descifra exactamente al valor original (sin pérdida)');
comprobar(!str_contains(json_encode($filas), $legado), 'El resultado de la migración no contiene la credencial');

@unlink("$salida/auth.txt");
$mail = mailer_init();
$mail->addAddress('migrada@vetwilling.test');
$mail->Subject = 'x';
$mail->Body = 'x';
mailer_enviar($mail, 'cita');
comprobar(trim((string) @file_get_contents("$salida/auth.txt")) === hash('sha256', $legado), 'Tras migrar, la app se autentica en el SMTP simulado con la contraseña original');

$filas = $migracion->ejecutar('cifrar', true);
comprobar($valorCanal() === $guardado && !in_array('cifrada', array_column($filas, 'accion'), true), 'Repetir la migración no cambia nada (idempotente)');

$_ENV['APP_ENCRYPTION_KEY'] = base64_encode(random_bytes(32));
putenv('APP_ENCRYPTION_KEY=' . $_ENV['APP_ENCRYPTION_KEY']);
$filas = $migracion->ejecutar('revertir', true);
comprobar($valorCanal() === $guardado && str_contains(implode(' ', array_column($filas, 'accion')), 'no se descifra'), 'Con una clave equivocada, revertir no toca la credencial');
$_ENV['APP_ENCRYPTION_KEY'] = '';
putenv('APP_ENCRYPTION_KEY=');
try { $migracion->ejecutar('cifrar', true); $lanzo = false; } catch (RuntimeException $e) { $lanzo = true; }
comprobar($lanzo && $valorCanal() === $guardado, 'Sin clave, la migración se niega y no modifica nada');
$_ENV['APP_ENCRYPTION_KEY'] = $claveDePrueba;
putenv("APP_ENCRYPTION_KEY=$claveDePrueba");

$migracion->ejecutar('revertir', true);
comprobar($valorCanal() === $legado, 'Revertir con la clave correcta devuelve exactamente el texto plano original');

// ─────────────────────────────────────────────────────────────────────────────
echo "\nResultado: {$resultados['ok']} correctas, {$resultados['fallo']} fallidas.\n";
exit($resultados['fallo'] === 0 ? 0 : 1);
