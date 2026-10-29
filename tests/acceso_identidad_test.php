<?php
// Pruebas de la identidad estricta (app/helpers/acceso_helper.php).
// Ejecutar con: php tests/acceso_identidad_test.php
//
// No usa base de datos ni HTTP: trabaja con $_SESSION en una carpeta de sesiones temporal propia
// y con un lector de credenciales ficticio (nunca conecta con ninguna base).

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// La salida se acumula y se imprime al final: en consola, imprimir antes contaría como
// "cabeceras enviadas" y PHP no permitiría regenerar el id de sesión (como en una petición real).
ob_start();

$carpetaSesiones = sys_get_temp_dir() . '/vw_acceso_test_' . bin2hex(random_bytes(4));
mkdir($carpetaSesiones, 0700);
ini_set('session.save_path', $carpetaSesiones);
ini_set('session.use_cookies', '0');

require_once dirname(__DIR__) . '/app/helpers/acceso_helper.php';

// Lector de credenciales ficticio, configurado antes de cualquier otra cosa: id_usuario => fila
$credencialesBD = [];
$lecturas = 0;
acceso_configurar_lector(function (int $id) use (&$credencialesBD, &$lecturas) {
    $lecturas++;
    if (($credencialesBD[$id] ?? null) === 'caida') {
        throw new AccesoBaseNoDisponible('base caída (simulada)');
    }
    return $credencialesBD[$id] ?? null;
});
$fila = fn(int $rol, int $version = 1, string $estado = 'activo') => ['credenciales_version' => $version, 'estado' => $estado, 'id_rol' => $rol];

require_once dirname(__DIR__) . '/app/helpers/session_helper.php';

$ok = 0;
$fallo = 0;
function comprobar(bool $c, string $d): void
{
    global $ok, $fallo;
    $c ? $ok++ : $fallo++;
    echo ($c ? '  ✅ ' : '  ❌ ') . "$d\n";
}

$datosLogin = fn(array $cambios = []) => array_merge([
    'id_usuario' => 7, 'id_rol' => 2, 'email' => 'vet@vetwilling.test', 'id_veterinaria' => '3',
    'nombres' => 'Ana', 'apellidos' => 'Prueba', 'estado' => 'activo',
], $cambios);

session_start();

echo "▶ Sesiones que no cuentan como iniciadas\n";
$_SESSION = [];
comprobar(acceso_usuario() === null && !isSessionActive(), 'Sin $_SESSION[user]: anónimo');
$_SESSION['user'] = ['id_veterinaria' => 5];
comprobar(acceso_usuario() === null && !isSessionActive(), 'Sesión parcial con solo id_veterinaria (la que creaba seleccionarVeterinaria): anónimo');
$_SESSION['user'] = ['id_usuario' => 7, 'id_rol' => 2];
comprobar(acceso_usuario() === null, 'Sesión sin la marca del login (p. ej. creada antes de esta versión): anónimo');
foreach ([['id_usuario' => 0], ['id_usuario' => -1], ['id_usuario' => 'abc'], ['id_rol' => 9], ['id_rol' => 0], ['id_rol' => null]] as $cambio) {
    $_SESSION['user'] = array_merge(['id_usuario' => 7, 'id_rol' => 2, 'autenticado' => true], $cambio);
    comprobar(acceso_usuario() === null, 'Marca presente pero ' . json_encode($cambio) . ' no es válido: anónimo');
}
$_SESSION['user'] = ['id_usuario' => 7, 'id_rol' => 2, 'autenticado' => 'true'];
comprobar(acceso_usuario() === null, 'La marca debe ser el booleano true, no un texto');

echo "▶ Lista de clínicas del login\n";
comprobar(acceso_lista_veterinarias('3, 7') === [3, 7], '"3, 7" → [3, 7]');
comprobar(acceso_lista_veterinarias('3,3, 7') === [3, 7], 'Sin duplicados');
comprobar(acceso_lista_veterinarias('3, x, -2, 0') === [3], 'Descarta valores no válidos');
comprobar(acceso_lista_veterinarias(null) === [] && acceso_lista_veterinarias(4) === [4], 'null → []; entero → [entero]');

echo "▶ Sesión creada por el login\n";
$_SESSION = ['csrf_token' => 'anterior'];
$idAntes = session_id();
$credencialesBD[7] = $fila(2);
acceso_iniciar_sesion_usuario($datosLogin());
$u = acceso_usuario();
comprobar($u !== null && $u['autenticado'] === true && $u['id_usuario'] === 7 && $u['id_rol'] === 2, 'Usuario válido con la marca, id_usuario e id_rol enteros');
comprobar(session_id() !== $idAntes, 'Regenera el id de sesión');
comprobar(!isset($_SESSION['csrf_token']), 'Descarta el token CSRF anterior');
comprobar($u['id_veterinaria'] === 3 && $u['seleccion_pendiente'] === false && $u['veterinarias_permitidas'] === [3], 'Una clínica: activa directamente, sin selección pendiente');
comprobar(!array_key_exists('password_hash', $u), 'No guarda el hash de la contraseña');
comprobar(acceso_id_usuario() === 7 && acceso_rol() === 2 && acceso_id_veterinaria() === 3, 'acceso_id_usuario / acceso_rol / acceso_id_veterinaria');
comprobar(isSessionActive() && getCurrentUserRole() === 2 && getCurrentUserId() === 7, 'session_helper: isSessionActive y getCurrentUserRole leen id_rol');
comprobar(hasRole(2) && !hasRole(1) && hasAnyRole([2, 4]) && !hasAnyRole([1, 3]), 'hasRole / hasAnyRole funcionan con id_rol');

$credencialesBD[7] = $fila(1);
acceso_iniciar_sesion_usuario($datosLogin(['id_rol' => 1, 'id_veterinaria' => null]));
comprobar(acceso_id_veterinaria() === null && acceso_usuario()['veterinarias_permitidas'] === [], 'Administrador: sin clínica');

echo "▶ Selección de clínica (varias clínicas)\n";
$credencialesBD[7] = $fila(2);
acceso_iniciar_sesion_usuario($datosLogin(['id_veterinaria' => '3, 7']));
comprobar(acceso_seleccion_pendiente() && acceso_id_veterinaria() === null, 'Varias clínicas: selección pendiente y sin clínica activa');
$antes = $_SESSION['user'];
comprobar(!acceso_seleccionar_veterinaria(9) && $_SESSION['user'] === $antes, 'Clínica no permitida: rechazada y la sesión no cambia');
comprobar(!acceso_seleccionar_veterinaria('3 OR 1=1') && !acceso_seleccionar_veterinaria('') && $_SESSION['user'] === $antes, 'Valores no enteros: rechazados');
$idAntes = session_id();
comprobar(acceso_seleccionar_veterinaria('7') && acceso_id_veterinaria() === 7 && !acceso_seleccion_pendiente(), 'Clínica permitida: aceptada y activa');
comprobar(session_id() !== $idAntes, 'Al seleccionar se regenera el id de sesión');
comprobar(!acceso_seleccionar_veterinaria(3) && acceso_id_veterinaria() === 7, 'Sin selección pendiente no se puede cambiar de clínica');

echo "▶ Anónimo intentando seleccionar\n";
$_SESSION = [];
comprobar(!acceso_seleccionar_veterinaria(3) && !isset($_SESSION['user']), 'Sin sesión: rechazado y NO se crea $_SESSION[user]');
$_SESSION['user'] = ['id_veterinaria' => 3, 'seleccion_pendiente' => true, 'veterinarias_permitidas' => [3]];
comprobar(!acceso_seleccionar_veterinaria(3), 'Con una sesión parcial que imita la selección pendiente: rechazado');

echo "▶ Versión de credenciales (T1)\n";
$credencialesBD[7] = $fila(2, 4);
acceso_iniciar_sesion_usuario($datosLogin());
comprobar(($_SESSION['user']['credenciales_version'] ?? null) === 4, 'El login guarda la versión de credenciales de la base');
$lecturas = 0;
acceso_usuario(); acceso_usuario(); isSessionActive(); acceso_rol();
comprobar($lecturas === 1, 'Una sola lectura de la base por petición aunque se consulte la identidad varias veces', "lecturas: $lecturas");
acceso_olvidar_verificacion(); // siguiente petición
$lecturas = 0;
acceso_usuario();
comprobar($lecturas === 1, 'En la siguiente petición vuelve a comprobarse (sin caché entre peticiones)');

$credencialesBD[7] = $fila(2, 5);
acceso_olvidar_verificacion();
comprobar(acceso_usuario() === null && !isset($_SESSION['user']), 'Versión cambiada en la base (otra sesión cambió la contraseña): sesión anónima y se borra');
foreach (['cuenta inactiva' => $fila(2, 4, 'inactivo'), 'rol cambiado' => $fila(1, 4), 'usuario eliminado' => null] as $caso => $filaBD) {
    $credencialesBD[7] = $fila(2, 4);
    acceso_iniciar_sesion_usuario($datosLogin());
    $credencialesBD[7] = $filaBD;
    acceso_olvidar_verificacion();
    comprobar(acceso_usuario() === null, "Con la $caso: sesión anónima");
}
$credencialesBD[7] = $fila(2, 4);
acceso_iniciar_sesion_usuario($datosLogin());
unset($_SESSION['user']['credenciales_version']);
acceso_olvidar_verificacion();
comprobar(acceso_usuario() === null, 'Sesión sin versión de credenciales (creada antes de T1): anónima');

$credencialesBD[7] = $fila(2, 4);
acceso_iniciar_sesion_usuario($datosLogin());
$_SESSION['csrf_token'] = 'anterior';
$idAntes = session_id();
$credencialesBD[7] = $fila(2, 5);
acceso_actualizar_sesion_actual(7, 5);
comprobar(acceso_usuario() !== null && $_SESSION['user']['credenciales_version'] === 5, 'La sesión que hizo el cambio sigue válida con la versión nueva');
comprobar(session_id() !== $idAntes && !isset($_SESSION['csrf_token']), '…con nuevo id de sesión y nuevo token CSRF');
acceso_actualizar_sesion_actual(99, 9);
comprobar($_SESSION['user']['credenciales_version'] === 5, 'Si el cambio era de otro usuario, la sesión actual no se toca');

echo "▶ Revalidación en conexiones largas (SSE)\n";
$copia = ['id_usuario' => 7, 'id_rol' => 2, 'credenciales_version' => 5];
$credencialesBD[7] = $fila(2, 5);
$lecturas = 0;
comprobar(acceso_sesion_sigue_vigente($copia) && acceso_sesion_sigue_vigente($copia) && $lecturas === 2, 'Sesión vigente: true, y consulta la base en cada vuelta del bucle');
foreach (['versión cambiada' => $fila(2, 6), 'cuenta inactiva' => $fila(2, 5, 'inactivo'), 'rol cambiado' => $fila(4, 5), 'usuario eliminado' => null, 'base caída' => 'caida'] as $caso => $filaBD) {
    $credencialesBD[7] = $filaBD;
    comprobar(!acceso_sesion_sigue_vigente($copia), "Con la $caso: false (se cierra el stream)");
}
comprobar(!acceso_sesion_sigue_vigente(['id_usuario' => 7, 'id_rol' => 2]), 'Copia sin versión: false');

echo "▶ Base de datos no disponible (fallo cerrado)\n";
$codigo = 'require ' . var_export(dirname(__DIR__) . '/app/helpers/acceso_helper.php', true) . ';'
    . 'ini_set("session.save_path", ' . var_export($carpetaSesiones, true) . '); ini_set("session.use_cookies", "0"); session_start();'
    . 'acceso_configurar_lector(function () { throw new AccesoBaseNoDisponible("simulada"); });'
    . '$_SESSION["user"] = ["id_usuario" => 7, "id_rol" => 2, "autenticado" => true, "credenciales_version" => 1];'
    . 'acceso_usuario(); echo "CONTINUÓ";';
exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($codigo) . ' 2>&1', $salidaHijo, $codigoHijo);
$salidaHijo = implode("\n", $salidaHijo);
comprobar($codigoHijo === 1 && !str_contains($salidaHijo, 'CONTINUÓ'), 'Si no se puede validar la identidad, la petición termina (código 1) sin continuar', "código $codigoHijo");
comprobar(str_contains($salidaHijo, 'no está disponible') && !str_contains($salidaHijo, 'simulada'), 'Solo muestra el mensaje genérico, sin detalles del error');

session_write_close();
array_map('unlink', glob("$carpetaSesiones/*") ?: []);
rmdir($carpetaSesiones);

echo "\nResultado: $ok correctas, $fallo fallidas.\n";
ob_end_flush();
exit($fallo === 0 ? 0 : 1);
