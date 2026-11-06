<?php
// Pruebas de los permisos por acción (config/permisos_acciones.php y acceso_accion_permitida()).
// Ejecutar con: php tests/permisos_acciones_test.php
// No usa base de datos, sesiones ni HTTP.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$raiz = dirname(__DIR__);
require_once $raiz . '/app/helpers/acceso_helper.php';
$permisos = require $raiz . '/config/permisos_acciones.php';

$esperado = [
    ['usuario', 'POST', '', []],
    ['usuario', 'POST', 'registrar', [1]],
    ['usuario', 'POST', 'actualizar', [1]],
    ['usuario', 'POST', 'actualizar-constrasena', [1, 4]],
    ['usuario', 'POST', 'modificar-contrasena', [3]],
    ['usuario', 'POST', 'vet-constrasena', [2, 4]],
    ['usuario', 'POST', 'cambiar-foto', [1, 4]],
    ['usuario', 'POST', 'accion-inventada', []],
    ['usuario', 'POST', 'eliminar', [1]],
    ['usuario', 'GET', 'listarAdmin', [1]],
    ['usuario', 'GET', '', [1, 2, 3, 4, 5]],
    ['usuario', 'DELETE', '', []],
    ['veterinaria', 'POST', '', []],
    ['veterinaria', 'POST', 'registrar', [1]],
    ['veterinaria', 'POST', 'actualizar', [1]],
    ['veterinaria', 'POST', 'actualizarInfo', [4]],
    ['veterinaria', 'POST', 'accion-inventada', []],
    ['veterinaria', 'POST', 'eliminar', [1]],
    ['veterinaria', 'GET', 'obtener_horarios', [4]],
    ['veterinaria', 'POST', 'eliminar_horarios', [4]],
    ['veterinaria', 'GET', '', [1, 2, 3, 4, 5]],
];

$fallos = 0;
$casos = 0;
foreach ($esperado as [$controlador, $metodo, $accion, $roles]) {
    foreach ([1, 2, 3, 4, 5] as $rol) {
        $casos++;
        if (acceso_accion_permitida($permisos, $controlador, $metodo, $accion, $rol) !== in_array($rol, $roles, true)) {
            $fallos++;
            echo "  ❌ $metodo $controlador '$accion' con rol $rol\n";
        }
    }
    // Sin sesión solo pasan las consultas GET sin acción limitada
    $casos++;
    if (acceso_accion_permitida($permisos, $controlador, $metodo, $accion, null) !== ($metodo === 'GET' && $accion === '')) {
        $fallos++;
        echo "  ❌ $metodo $controlador '$accion' sin sesión\n";
    }
}

// Todo formulario que envía a estos controladores debe llevar el token CSRF
$rutasProtegidas = ['/admin/guardar-usuario', '/admin/actualizar-usuario', '/admin/eliminar-usuario', '/admin/cambiar-foto',
    '/admin/actualizar-contrasena', '/admin/guardar-veterinaria', '/admin/actualizar-veterinaria', '/admin/eliminar-veterinaria',
    '/veterinario/actualizar-constrasena', '/cliente/actualizar-contrasena', '/representante/actualizar-veterinaria', '/directorio/equipo/guardar'];
$vistas = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz . '/app/views', FilesystemIterator::SKIP_DOTS));
foreach ($vistas as $archivo) {
    if ($archivo->getExtension() !== 'php') {
        continue;
    }
    preg_match_all('/<form\b[^>]*>.*?<\/form>/is', file_get_contents($archivo->getPathname()), $formularios);
    foreach ($formularios[0] as $form) {
        preg_match('/<form\b[^>]*action\s*=\s*["\'](?:<\?=\s*BASE_URL\s*\?>)?([^"\'?#]*)/is', $form, $accion);
        if (in_array($accion[1] ?? '', $rutasProtegidas, true)) {
            $casos++;
            if (!str_contains($form, 'csrf_campo()')) {
                $fallos++;
                echo "  ❌ Sin token CSRF: {$archivo->getFilename()} → {$accion[1]}\n";
            }
        }
    }
}

echo "Resultado: " . ($casos - $fallos) . " correctas, $fallos fallidas.\n";
exit($fallos === 0 ? 0 : 1);
