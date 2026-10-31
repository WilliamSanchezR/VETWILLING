<?php
// Prueba de las migraciones de Medios de envío: base -> medios_envio -> repetición -> reversión.
// Ejecutar con: php tests/migraciones_test.php
//
// Aislamiento: cada sentencia se reescribe para usar TABLAS TEMPORALES con prefijo "mig_"
// (mig_canal_envio_config, ...). Las tablas reales no se nombran en ninguna sentencia ejecutada.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$raiz = dirname(__DIR__);
require_once $raiz . '/config/database.php';
$db = (new conexion())->getConexion();

$ok = 0;
$fallo = 0;
function comprobar(bool $c, string $d, string $detalle = ''): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . $d . ($c || $detalle === '' ? '' : "  → $detalle") . "\n";
}

const TABLAS = ['canal_envio_config', 'plantilla_mensaje_canal', 'notificaciones_enviadas'];

// Reescribe una sentencia para que solo toque tablas temporales mig_*
function aislar(string $sql): string
{
    $sql = preg_replace('/\b(' . implode('|', TABLAS) . ')\b/', 'mig_$1', $sql);
    $sql = preg_replace('/^CREATE TABLE IF NOT EXISTS/m', 'CREATE TEMPORARY TABLE IF NOT EXISTS', $sql);
    // Las tablas temporales no admiten llaves foráneas
    $sql = preg_replace('/^\s*CONSTRAINT `[^`]+` FOREIGN KEY.*$\n?/m', '', $sql);
    $sql = preg_replace('/,(\s*\n\))/', '$1', $sql);
    if (preg_match('/\b(' . implode('|', TABLAS) . ')\b/', preg_replace('/\bmig_\w+/', '', $sql))) {
        throw new LogicException('Sentencia no aislada');
    }
    return $sql;
}

function sentencias(string $archivo): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', file_get_contents($archivo));
    return array_values(array_filter(array_map('trim', preg_split('/;\s*\n/', $sql . "\n"))));
}

function ejecutarArchivo(string $archivo, bool $ignorarDuplicados = false): array
{
    global $db;
    $errores = [];
    foreach (sentencias($archivo) as $s) {
        try {
            $db->exec(aislar($s));
        } catch (PDOException $e) {
            $errores[] = $e->errorInfo[1] ?? $e->getCode();
            if (!$ignorarDuplicados) throw $e;
        }
    }
    return $errores;
}

$columna = fn(string $c) => (bool) $db->query("SHOW COLUMNS FROM mig_notificaciones_enviadas LIKE '$c'")->fetch();
$admiteNull = fn() => $db->query("SHOW COLUMNS FROM mig_notificaciones_enviadas LIKE 'id_agendamiento'")->fetch()['Null'] === 'YES';
$indice = fn() => (bool) $db->query("SHOW INDEX FROM mig_notificaciones_enviadas WHERE Key_name = 'idx_notifenv_reintento'")->fetch();
$cancelacion = fn() => (int) $db->query("SELECT COUNT(*) FROM mig_plantilla_mensaje_canal WHERE tipo_notificacion = 'cancelacion'")->fetchColumn();
$varsRecordatorio = fn() => $db->query("SELECT variables_disponibles FROM mig_plantilla_mensaje_canal p JOIN mig_canal_envio_config c ON c.id_canal = p.id_canal AND c.codigo_canal = 'email' WHERE p.tipo_notificacion = 'recordatorio'")->fetchColumn();

echo "Prueba de migraciones — MySQL " . $db->query('SELECT VERSION()')->fetchColumn() . "\n\n▶ Base (canales y plantillas)\n";
ejecutarArchivo($raiz . '/database/medios_envio_base.sql');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_canal_envio_config")->fetchColumn() === 3, 'Crea los 3 canales (correo, internas, push)');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_canal_envio_config WHERE smtp_password IS NOT NULL OR smtp_usuario IS NOT NULL")->fetchColumn() === 0, 'Los canales se crean sin credenciales');
$plantillasBase = (int) $db->query("SELECT COUNT(*) FROM mig_plantilla_mensaje_canal")->fetchColumn();
comprobar($plantillasBase === 10, "Crea las plantillas iniciales ($plantillasBase)");
ejecutarArchivo($raiz . '/database/medios_envio_base.sql');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_plantilla_mensaje_canal")->fetchColumn() === $plantillasBase, 'Repetir la base no duplica nada');

echo "\n▶ Estado anterior de notificaciones_enviadas (como en una base sin migrar)\n";
$ddl = $db->query("SHOW CREATE TABLE notificaciones_enviadas")->fetch(PDO::FETCH_NUM)[1]; // solo lectura de la estructura real
$db->exec(aislar(preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $ddl)));
$db->exec("DROP INDEX idx_notifenv_reintento ON mig_notificaciones_enviadas");
$db->exec("ALTER TABLE mig_notificaciones_enviadas DROP COLUMN cuerpo_html, DROP COLUMN asunto, DROP COLUMN tipo_notificacion, MODIFY id_agendamiento INT NOT NULL");
$db->exec("INSERT INTO mig_notificaciones_enviadas (id_agendamiento, medio_notificacion, destinatario, estado_envio) VALUES (1, 'email', 'a@x.test', 'exitoso'), (2, 'email', 'b@x.test', 'fallido')");
comprobar(!$columna('cuerpo_html') && !$admiteNull(), 'Estructura previa reproducida (sin columnas nuevas, cita obligatoria)');

echo "\n▶ medios_envio.sql\n";
ejecutarArchivo($raiz . '/database/medios_envio.sql');
comprobar($columna('tipo_notificacion') && $columna('asunto') && $columna('cuerpo_html'), 'Agrega tipo, asunto y contenido');
comprobar($admiteNull(), 'id_agendamiento admite NULL (correos que no son de citas)');
comprobar($indice(), 'Crea el índice para los reintentos');
comprobar($cancelacion() === 1, 'Agrega la plantilla de cancelación');
comprobar(str_contains((string) $varsRecordatorio(), 'tipo_servicio'), 'Actualiza las variables del recordatorio');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_notificaciones_enviadas")->fetchColumn() === 2, 'Conserva los envíos existentes');

$errores = ejecutarArchivo($raiz . '/database/medios_envio.sql', true);
comprobar($errores === [1060] && $cancelacion() === 1, 'Repetirla: el ALTER falla por columna duplicada (1060) y lo demás no duplica datos', json_encode($errores));

echo "\n▶ medios_envio_rollback.sql\n";
$db->exec("INSERT INTO mig_notificaciones_enviadas (id_agendamiento, medio_notificacion, tipo_notificacion, destinatario, estado_envio) VALUES (NULL, 'email', 'bienvenida', 'c@x.test', 'exitoso'), (3, 'email', 'cita', 'd@x.test', 'rechazado')");
ejecutarArchivo($raiz . '/database/medios_envio_rollback.sql');
comprobar(!$columna('tipo_notificacion') && !$columna('asunto') && !$columna('cuerpo_html') && !$indice(), 'Quita columnas e índice');
comprobar(!$admiteNull(), 'id_agendamiento vuelve a ser obligatorio');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_notificaciones_enviadas")->fetchColumn() === 3, 'Borra solo los envíos sin cita (3 de 4 quedan)');
comprobar((int) $db->query("SELECT COUNT(*) FROM mig_notificaciones_enviadas WHERE estado_envio = 'rechazado'")->fetchColumn() === 0, "Convierte 'rechazado' en 'fallido'");
comprobar($cancelacion() === 0 && !str_contains((string) $varsRecordatorio(), 'tipo_servicio'), 'Quita la plantilla de cancelación y restaura las variables');

echo "\n▶ Volver a aplicar después de revertir\n";
ejecutarArchivo($raiz . '/database/medios_envio.sql');
comprobar($columna('cuerpo_html') && $admiteNull() && $cancelacion() === 1, 'La migración se puede aplicar de nuevo tras la reversión');

echo "\nResultado: $ok correctas, $fallo fallidas.\n";
exit($fallo === 0 ? 0 : 1);
