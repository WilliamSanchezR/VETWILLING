<?php
// Pruebas de la protección central de rutas (config/rutas_acceso.php y acceso_decidir()).
// Ejecutar con: php tests/acceso_rutas_test.php
//
// No usa base de datos, sesiones ni HTTP: comprueba el mapa contra index.php y la decisión
// para cada ruta, identidad y método.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$raiz = dirname(__DIR__);
require_once $raiz . '/app/helpers/acceso_helper.php';

$ok = 0;
$fallo = 0;
function comprobar(bool $c, string $d, string $detalle = ''): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . $d . ($c || $detalle === '' ? '' : "  → $detalle") . "\n";
}

$mapa = acceso_mapa();
preg_match_all('/^\s*case\s+"([^"]+)"\s*:/m', file_get_contents("$raiz/index.php"), $m);
$rutas = $m[1];

echo "▶ El mapa y el enrutador coinciden\n";
comprobar(count($rutas) === count(array_unique($rutas)), 'index.php no tiene rutas duplicadas');
$sinPolitica = array_diff($rutas, array_keys($mapa));
$sobrantes = array_diff(array_keys($mapa), $rutas);
comprobar($sinPolitica === [], 'Cada ruta de index.php tiene política (' . count($rutas) . ' rutas)', implode(', ', $sinPolitica));
comprobar($sobrantes === [], 'El mapa no tiene rutas que no existan', implode(', ', $sobrantes));

echo "▶ Políticas válidas\n";
$invalidas = [];
foreach ($mapa as $ruta => $p) {
    $tipo = $p['tipo'] ?? null;
    if (!in_array($tipo, ['vista', 'accion', 'api', 'deshabilitada'], true)) {
        $invalidas[] = "$ruta: tipo";
    } elseif ($tipo !== 'deshabilitada') {
        $r = $p['roles'] ?? null;
        $valido = $r === 'publica' || (is_array($r) && $r && !array_diff($r, [1, 2, 3, 4]));
        if (!$valido) $invalidas[] = "$ruta: roles";
    }
}
comprobar($invalidas === [], 'Todas las entradas tienen tipo y roles válidos (el rol 5 no aparece)', implode('; ', $invalidas));
$publicas = array_keys(array_filter($mapa, fn($p) => ($p['roles'] ?? null) === 'publica'));
sort($publicas);
$esperadas = ['/', '/cerrar-sesion', '/generar-clave', '/iniciar-sesion', '/login', '/login/ingresarVeterinaria',
    '/login/seleccionarVeterinaria', '/recoverpw', '/registro', '/registro/veterinaria', '/restablecer', '/restablecer/guardar'];
comprobar($publicas === $esperadas, 'Solo son públicas las 12 rutas acordadas', implode(', ', array_diff($publicas, $esperadas)));
comprobar(array_keys(array_filter($mapa, fn($p) => !empty($p['publica_si']))) === ['/pagos/mercadopago'], 'La única excepción pública por parámetro es el webhook de pagos');
comprobar(array_keys(array_filter($mapa, fn($p) => !empty($p['post']))) === ['/cliente/perfil'], 'La única vista que admite POST es /cliente/perfil');
comprobar(count(array_filter($mapa, fn($p) => $p['tipo'] === 'deshabilitada')) === 12, 'Hay 12 rutas deshabilitadas (sin llamadores vivos)');

echo "▶ Decisión para todas las rutas, identidades y métodos\n";
$usuario = fn(int $rol, bool $pendiente = false) => ['id_usuario' => 1, 'id_rol' => $rol, 'autenticado' => true, 'seleccion_pendiente' => $pendiente];
$identidades = ['anónimo' => null, 'rol 1' => $usuario(1), 'rol 2' => $usuario(2), 'rol 3' => $usuario(3), 'rol 4' => $usuario(4),
    'rol 5' => $usuario(5), 'rol 2 con selección pendiente' => $usuario(2, true)];
$errores = [];
$casos = 0;
foreach ($mapa as $ruta => $p) {
    foreach ($identidades as $nombre => $u) {
        foreach (['GET', 'POST', 'DELETE'] as $metodo) {
            $casos++;
            $obtenido = acceso_decidir($p, $u, $metodo)['resultado'];
            if ($p['tipo'] === 'deshabilitada') {
                $esperado = '404';
            } elseif ($p['tipo'] === 'vista' && empty($p['post']) && $metodo !== 'GET') {
                $esperado = '405';
            } elseif ($p['roles'] === 'publica') {
                $esperado = 'permitir';
            } elseif ($u === null) {
                $esperado = 'login';
            } elseif ($u['seleccion_pendiente']) {
                $esperado = 'seleccion';
            } else {
                $esperado = in_array($u['id_rol'], $p['roles'], true) ? 'permitir' : '403';
            }
            if ($obtenido !== $esperado) $errores[] = "$metodo $ruta [$nombre]: $obtenido (esperado $esperado)";
        }
    }
}
comprobar($errores === [], "$casos combinaciones con el resultado esperado", implode('; ', array_slice($errores, 0, 5)));
$denegadoRol5 = array_filter($mapa, fn($p) => $p['tipo'] !== 'deshabilitada' && $p['roles'] !== 'publica'
    && acceso_decidir($p, $usuario(5), 'GET')['resultado'] !== '403');
comprobar($denegadoRol5 === [], 'El rol 5 recibe 403 en todas las rutas privadas');

echo "▶ Casos concretos del bloque 1\n";
$dec = fn(string $ruta, ?array $u, string $metodo = 'GET', array $q = []) => acceso_decidir($mapa[$ruta] ?? null, $u, $metodo, $q)['resultado'];
comprobar($dec('/admin/actualizar-usuario', null, 'POST') === 'login', 'Anónimo → /admin/actualizar-usuario: login (antes se ejecutaba)');
comprobar($dec('/admin/guardar-usuario', $usuario(4), 'POST') === '403', 'Representante → /admin/guardar-usuario: 403');
comprobar($dec('/admin/eliminar-usuario', $usuario(4)) === '403', 'Representante → /admin/eliminar-usuario: 403 (solo el administrador borra)');
comprobar($dec('/admin/eliminar-usuario', $usuario(1)) === 'permitir', 'Administrador → /admin/eliminar-usuario: permitido');
comprobar($dec('/representante/actualizar-profesional', null, 'POST') === 'login' && $dec('/veterinario/actualizar-veterinario', $usuario(3), 'POST') === '403', 'Las vías de cambio de email exigen sesión y rol');
comprobar($dec('/veterinaria/dashboard', $usuario(2), 'POST') === '405' && $dec('/representante/dashboard', $usuario(4), 'POST') === '405', 'POST a una vista de panel: 405 (no ejecuta los controladores que incluye)');
comprobar($dec('/cliente/perfil', $usuario(3), 'POST') === 'permitir', 'POST a /cliente/perfil (cierre de sesiones): permitido al propietario');
comprobar($dec('/representante/agregar-disponibilidad-agenda', $usuario(2), 'POST') === 'permitir', 'La agenda compartida admite al veterinario');
comprobar($dec('/calendario/getVeterinarios', $usuario(3)) === 'permitir' && $dec('/calendario/getPropietarios', $usuario(3)) === '403', 'Calendario: el propietario solo consulta veterinarios');
comprobar($dec('/pagos/mercadopago', null, 'POST', ['action' => 'webhook']) === 'permitir', 'Webhook de pagos (?action=webhook): público');
comprobar($dec('/pagos/mercadopago', null, 'GET', ['action' => 'checkout']) === 'login' && $dec('/pagos/mercadopago', $usuario(2), 'GET', ['action' => 'checkout']) === '403', 'Resto de pagos: solo representante');
comprobar($dec('/cliente/historial-compras', $usuario(3)) === '404', 'Ruta deshabilitada (vista inexistente): 404');
comprobar($dec('/cliente/eliminar-mascota', $usuario(3), 'POST') === 'permitir' && $dec('/cliente/eliminar-mascota', $usuario(2), 'POST') === '403'
    && $dec('/cliente/eliminar-mascota', null, 'POST') === 'login', 'Eliminar mascota: solo el propietario, y pasando por el enrutador');
comprobar($dec('/ruta/que/no/existe', null) === 'login' && $dec('/ruta/que/no/existe', $usuario(1)) === '404', 'Ruta desconocida: login sin sesión, 404 con sesión (igual que antes)');

echo "▶ Ningún formulario envía POST a una ruta de vista\n";
$archivos = array_merge(
    glob("$raiz/app/views/{*,*/*,*/*/*}.php", GLOB_BRACE),
    explode("\n", trim((string) shell_exec('find ' . escapeshellarg("$raiz/public/assets") . " -name '*.js'")))
);
$aVista = [];
foreach (array_filter($archivos) as $f) {
    $c = file_get_contents($f);
    preg_match_all('/<form\b[^>]*>/is', $c, $forms);
    foreach ($forms[0] as $form) {
        if (!preg_match('/method\s*=\s*["\']post["\']/i', $form)) continue;
        if (!preg_match('/action\s*=\s*["\'](?:<\?=\s*BASE_URL\s*\?>)?([^"\'?#]*)/i', $form, $a)) {
            if (preg_match('/id\s*=\s*["\']formAgenda["\']/i', $form)) $aVista[] = basename($f) . ': formAgenda sin action';
            continue;
        }
        $destino = $a[1];
        if (($mapa[$destino]['tipo'] ?? '') === 'vista' && empty($mapa[$destino]['post'])) $aVista[] = basename($f) . " → $destino";
    }
}
comprobar($aVista === [], 'Los formularios POST apuntan a rutas de acción, no de vista', implode('; ', $aVista));

echo "\nResultado: $ok correctas, $fallo fallidas.\n";
exit($fallo === 0 ? 0 : 1);
