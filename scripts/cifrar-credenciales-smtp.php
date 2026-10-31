<?php
// Cifra (o revierte) las credenciales de canal_envio_config: contraseña SMTP y clave push.
// No cambia su valor: solo la forma en que se guarda. Nunca imprime credenciales.
//
//   php scripts/cifrar-credenciales-smtp.php                       (simulación: no escribe)
//   php scripts/cifrar-credenciales-smtp.php --aplicar             (cifra)
//   php scripts/cifrar-credenciales-smtp.php --revertir            (simula la reversión)
//   php scripts/cifrar-credenciales-smtp.php --revertir --aplicar  (vuelve a texto plano con la misma clave)
//
// Requiere APP_ENCRYPTION_KEY en .env o en el entorno. Antes de --aplicar en un servidor,
// haz copia de seguridad de canal_envio_config (ver docs/auditoria/medios-envio.md).

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/services/MigracionCredenciales.php';

$aplicar  = in_array('--aplicar', $argv, true);
$modo     = in_array('--revertir', $argv, true) ? 'revertir' : 'cifrar';

$huella = MigracionCredenciales::huellaClave();
if ($huella === null) {
    fwrite(STDERR, "Falta APP_ENCRYPTION_KEY válida en .env (32 bytes en base64). No se modificó nada.\n"
        . "Instrucciones: docs/auditoria/medios-envio.md, sección 4.\n");
    exit(1);
}

echo ($aplicar ? 'APLICANDO' : 'SIMULACIÓN (no se escribe nada; usa --aplicar para escribir)')
    . " — modo: $modo — huella de la clave: $huella\n";

try {
    $filas = (new MigracionCredenciales((new conexion())->getConexion()))->ejecutar($modo, $aplicar);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}

$errores = 0;
foreach ($filas as $f) {
    if ($f['estado_antes'] === 'vacia' && $f['accion'] === 'sin cambios') continue;
    printf("  %-7s %-14s %-12s -> %-12s %s\n", $f['canal'], $f['campo'], $f['estado_antes'], $f['estado_despues'], $f['accion']);
    if (str_starts_with($f['accion'], 'error')) $errores++;
}
echo $errores ? "Terminó con $errores error(es): esas credenciales no se modificaron.\n" : "Listo.\n";
exit($errores ? 1 : 0);
