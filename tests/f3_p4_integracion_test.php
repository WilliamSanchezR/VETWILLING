<?php
// Pruebas de integración de F3 / P4 (quién crea cuentas de qué rol), de los borrados que
// seguían por GET (especialidades, servicios y sus horarios, subservicios, agenda, inventario)
// y de la consulta de propietario por id.
// Ejecutar con: bash tests/run_f3_p4.sh
//
// Las expectativas son las del comportamiento seguro: contra el código anterior a la
// corrección, los casos de ataque fallan (así se demuestra la vulnerabilidad).
// SOLO contra el contenedor de pruebas (ver tests/integracion_lib.php). Los datos creados se
// borran y las filas existentes se reponen al terminar, también si una prueba falla.

$raiz = dirname(__DIR__);
require __DIR__ . '/integracion_lib.php';
[$pdo, $env] = integracion_conectar();

$instantanea = integracion_instantanea($pdo, [
    'usuario', 'administrador', 'profesional', 'profesional_veterinaria', 'profesional_especialidad', 'profesionales_servicio',
    'veterinaria', 'representante_legal', 'propietario', 'veterinario', 'especialidad', 'esps_por_veterinaria', 'servicio',
    'horario_servicio', 'subservicios', 'disponibilidad_usuario', 'inventario', 'producto', 'movimiento_stock',
    'horario_veterinaria', 'auditoria_eliminacion_propietarios', 'password_reset', 'suscripcion', 'historial_suscripcion',
]);
[$servidor, $base, $log] = integracion_servidor($raiz);

function insertar(string $tabla, array $fila): int
{
    global $pdo;
    $cols = array_keys($fila);
    $pdo->prepare("INSERT INTO `$tabla` (`" . implode('`, `', $cols) . '`) VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
        ->execute(array_values($fila));
    return (int) $pdo->lastInsertId();
}

try {
    // ── Datos de prueba (clínica A = 101, la del representante 1004 y el veterinario 1003; B = 102)
    $clave = 'PruebaF3!' . bin2hex(random_bytes(4));
    $hash = password_hash($clave, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE usuario SET password_hash = ? WHERE id_usuario IN (1001, 1003, 1004)')->execute([$hash]);
    $perfil = ['tipo_documento' => 'CC', 'numero_documento' => '900', 'apellidos' => 'Prueba', 'telefono' => '3000000000'];
    $uProp = insertar('usuario', ['email' => 'f3test+propietario@vetwilling.test', 'password_hash' => $hash, 'estado' => 'activo', 'id_rol' => 3]);
    $propA = insertar('propietario', $perfil + ['id_usuario' => $uProp, 'id_veterinaria' => 101, 'nombres' => 'PropietarioA']);
    $uPropB = insertar('usuario', ['email' => 'f3test+propietariob@vetwilling.test', 'password_hash' => 'x', 'estado' => 'activo', 'id_rol' => 3]);
    $propB = insertar('propietario', $perfil + ['id_usuario' => $uPropB, 'id_veterinaria' => 102, 'nombres' => 'PropietarioB']);

    $sA = insertar('servicio', ['nombre' => 'f3test servicio A', 'id_veterinaria' => 101, 'estado' => 'Activo']);
    $sB = insertar('servicio', ['nombre' => 'f3test servicio B', 'id_veterinaria' => 102, 'estado' => 'Activo']);
    $e1 = insertar('especialidad', ['nombre' => 'f3test especialidad compartida', 'id_servicio_esp' => $sA]);
    $e2 = insertar('especialidad', ['nombre' => 'f3test especialidad solo B', 'id_servicio_esp' => $sB]);
    $evA = insertar('esps_por_veterinaria', ['id_veterinaria' => 101, 'id_especialidad' => $e1, 'estado' => 'Activo']);
    $evB = insertar('esps_por_veterinaria', ['id_veterinaria' => 102, 'id_especialidad' => $e1, 'estado' => 'Activo']);
    $evC = insertar('esps_por_veterinaria', ['id_veterinaria' => 102, 'id_especialidad' => $e2, 'estado' => 'Activo']);
    $hsA = insertar('horario_servicio', ['id_servicio' => $sA, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
    $hsB = insertar('horario_servicio', ['id_servicio' => $sB, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
    $ssA = insertar('subservicios', ['nombre' => 'f3test sub A', 'descripcion' => '-', 'id_servicio' => $sA, 'estado' => 'Activo', 'costo' => 1]);
    $ssB = insertar('subservicios', ['nombre' => 'f3test sub B', 'descripcion' => '-', 'id_servicio' => $sB, 'estado' => 'Activo', 'costo' => 1]);
    $disp = fn(int $u, int $v) => insertar('disponibilidad_usuario', ['id_usuario' => $u, 'id_veterinaria' => $v, 'id_especialidad' => $e1,
        'dia_semana' => 2, 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'duracion_minutos' => 30]);
    $dA = $disp(1003, 101);
    $dA2 = $disp(1003, 101);
    $dB = $disp(1002, 102);
    $dC = $disp(1002, 101);
    $inv = function (int $v) {
        $id = insertar('inventario', ['id_veterinaria' => $v, 'cantidad' => 10, 'categoria' => 'insumo', 'stock_minimo' => 1, 'numero_lote' => 'F3']);
        insertar('producto', ['id_inventario' => $id, 'nombre' => 'f3test producto', 'proveedor' => 'x', 'precio' => 1, 'precio_venta' => 1,
            'costo_mayorista' => 1, 'fecha_vencimiento' => '2030-01-01', 'fecha_creacion' => date('Y-m-d H:i:s')]);
        return $id;
    };
    $invA = $inv(101);
    $invB = $inv(102);

    $admin = new Cliente($base);
    $rep = new Cliente($base);
    $vet = new Cliente($base);
    $prop = new Cliente($base);
    echo "▶ Inicio de sesión (contenedor de pruebas, puerto {$env['PORT']})\n";
    comprobar($admin->iniciarSesion('admin@vetwilling.test', $clave, '/admin/listar-usuarios'), 'Administrador (1001) con token CSRF');
    comprobar($rep->iniciarSesion('rep@vetwilling.test', $clave, '/representante/listar-profesionales'), 'Representante de la clínica A (1004) con token CSRF');
    comprobar($vet->iniciarSesion('vet.una@vetwilling.test', $clave), 'Veterinario de la clínica A (1003)');
    comprobar($prop->iniciarSesion('f3test+propietario@vetwilling.test', $clave), 'Propietario de la clínica A');

    $existe = fn(string $email) => (int) valor('SELECT COUNT(*) FROM usuario WHERE email = ?', [$email]) > 0;
    $n = 0;
    $cuenta = function (int $rol) use (&$n) {
        $n++;
        return ['email' => "f3test+cuenta$n@vetwilling.test", 'rol' => $rol, 'tipo_documento' => 'CC', 'numero_documento' => (string) (880000 + $n),
            'nombres' => 'Atacante', 'apellidos' => 'Prueba', 'telefono' => '3000000000', 'direccion' => 'x'];
    };

    echo "▶ F3 · Propietario y veterinario no crean cuentas; las rutas de contraseña solo cambian la contraseña\n";
    $d = $cuenta(1);
    $r = $prop->pedir('POST', '/cliente/actualizar-contrasena', $d);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Propietario, POST sin accion a /cliente/actualizar-contrasena con rol=1 → 400 y no se crea la cuenta', "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));
    $d = $cuenta(1);
    $r = $prop->pedir('POST', '/cliente/actualizar-contrasena', $d + ['accion' => 'registrar']);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Propietario con accion=registrar → 400 y no se crea', "HTTP {$r[0]}");
    $d = $cuenta(1);
    $r = $vet->pedir('POST', '/veterinario/actualizar-constrasena', $d);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Veterinario, POST sin accion a /veterinario/actualizar-constrasena con rol=1 → 400 y no se crea', "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));
    $d = $cuenta(1) + ['numero_licencia_profesional' => '1'];
    $r = $vet->pedir('POST', '/veterinario/actualizar-veterinario', $d);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Veterinario, POST sin accion a /veterinario/actualizar-veterinario (registrarVeterinario) → 400 y no se crea', "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));
    $d = $cuenta(1);
    $r = $vet->pedir('POST', '/veterinario/cambiar-foto', $d);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Veterinario, POST sin accion a /veterinario/cambiar-foto → 400 y no se crea', "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));

    echo "▶ F3 · El representante solo crea profesionales de rol 2 en su clínica, y no crea clínicas\n";
    foreach (['/veterinario/actualizar-constrasena', '/admin/actualizar-contrasena', '/admin/cambiar-foto'] as $ruta) {
        $d = $cuenta(1);
        $r = $rep->pedir('POST', $ruta, $d);
        comprobar($r[0] === 400 && !$existe($d['email']), "Representante, POST sin accion a $ruta con rol=1 → 400 y no se crea", "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));
    }
    $d = $cuenta(1);
    $r = $rep->pedir('POST', '/representante/actualizar-profesional', $d + ['id_veterinaria' => 101]);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Representante, POST sin accion a /representante/actualizar-profesional (registrarProfesional) → 400', "HTTP {$r[0]}, creada: " . json_encode($existe($d['email'])));
    $d = $cuenta(1);
    $rep->pedir('POST', '/representante/guardar-Profesional', $d + ['accion' => 'registrar', 'id_veterinaria' => 102]);
    $rolCreado = valor('SELECT id_rol FROM usuario WHERE email = ?', [$d['email']]);
    $clinicas = $pdo->prepare('SELECT GROUP_CONCAT(pv.id_veterinaria) FROM usuario u JOIN profesional p ON p.id_usuario = u.id_usuario
                               JOIN profesional_veterinaria pv ON pv.id_profesional = p.id_profesional WHERE u.email = ?');
    $clinicas->execute([$d['email']]);
    $clinicas = $clinicas->fetchColumn();
    comprobar((string) $rolCreado === '2' && $clinicas === '101', 'Alta de profesional pidiendo rol=1 y otra clínica → se crea con rol 2 y solo en su clínica',
        'rol ' . json_encode($rolCreado) . ', clínicas ' . json_encode($clinicas));
    $nit = 'F3' . random_int(100000, 999999);
    $clinica = ['nit' => $nit, 'nombreVeterinaria' => 'f3test clínica', 'direccionVeterinaria' => 'x', 'ciudad' => 'x', 'telefonoVeterinaria' => '1',
        'emailVeterinaria' => 'f3test+clinica@vetwilling.test'];
    $d = $cuenta(4);
    $r = $rep->pedir('POST', '/representante/actualizar-veterinaria', $clinica + $d);
    comprobar($r[0] === 400 && !$existe($d['email']) && !valor('SELECT 1 FROM veterinaria WHERE nit = ?', [$nit]),
        'Representante, POST sin accion a /representante/actualizar-veterinaria (registrarVeterinaria) → 400, sin clínica ni cuenta nuevas', "HTTP {$r[0]}");
    $d = $cuenta(4);
    $r = $rep->post('/representante/actualizar-horario-veterinaria', $clinica + $d + ['accion' => 'registrar']);
    comprobar($r[0] === 400 && !$existe($d['email']), 'Representante con accion=registrar a la ruta de horarios → 400', "HTTP {$r[0]}");

    echo "▶ F3 · El administrador crea cuentas de cualquier rol\n";
    $d = $cuenta(1);
    $admin->pedir('POST', '/admin/guardar-usuario', $d + ['accion' => 'registrar']);
    comprobar((string) valor('SELECT id_rol FROM usuario WHERE email = ?', [$d['email']]) === '1', 'Rol 1 crea un administrador desde /admin/guardar-usuario');
    $d = $cuenta(9);
    $admin->pedir('POST', '/admin/guardar-usuario', $d + ['accion' => 'registrar']);
    comprobar(!$existe($d['email']), 'Rol inexistente (9) → no se crea');
    $d = $cuenta(4);
    $nit2 = 'F3' . random_int(100000, 999999);
    $admin->pedir('POST', '/admin/guardar-veterinaria', ['nit' => $nit2] + $clinica + $d + ['accion' => 'registrar']);
    comprobar((bool) valor('SELECT 1 FROM veterinaria WHERE nit = ?', [$nit2]) && (string) valor('SELECT id_rol FROM usuario WHERE email = ?', [$d['email']]) === '4',
        'Rol 1 crea una clínica con su representante desde /admin/guardar-veterinaria');

    echo "▶ Borrados solo por POST con CSRF y dentro de la clínica\n";
    $estado = fn(string $tabla, string $pk, int $id) => valor("SELECT estado FROM `$tabla` WHERE `$pk` = ?", [$id]);
    $ev = fn(int $id) => $estado('esps_por_veterinaria', 'id_esps_veterinaria', $id);
    $r = $rep->pedir('GET', "/representante/eliminar-especialidad?action=eliminar&id_especialidad=$e2&id_veterinaria=102");
    comprobar($r[0] === 405 && $ev($evC) === 'Activo', 'Especialidad: GET con la clínica B en la URL → 405 y no cambia', "HTTP {$r[0]}, estado " . $ev($evC));
    $r = $rep->post('/representante/eliminar-especialidad', ['accion' => 'eliminar', 'id_especialidad' => $e1], false);
    comprobar($r[0] === 403 && $ev($evA) === 'Activo', 'Especialidad: POST sin token → 403', "HTTP {$r[0]}");
    $r = $rep->post('/representante/eliminar-especialidad', ['accion' => 'eliminar', 'id_especialidad' => $e2]);
    comprobar($r[0] === 404 && $ev($evC) === 'Activo', 'Especialidad que solo tiene la clínica B → 404', "HTTP {$r[0]}");
    $rep->post('/representante/eliminar-especialidad', ['accion' => 'eliminar', 'id_especialidad' => $e1, 'id_veterinaria' => 102]);
    comprobar($ev($evA) === 'Inactivo' && $ev($evB) === 'Activo', 'Especialidad compartida: se quita de su clínica aunque pida la B; la B no cambia', 'A ' . $ev($evA) . ', B ' . $ev($evB));

    $sv = fn(int $id) => $estado('servicio', 'id_servicio', $id);
    $r = $rep->pedir('GET', "/representante/eliminar-servicio?action=eliminar&id=$sB");
    comprobar($r[0] === 405 && $sv($sB) === 'Activo', 'Servicio: GET a un servicio de la clínica B → 405 y no cambia', "HTTP {$r[0]}, estado " . $sv($sB));
    $r = $rep->post('/representante/eliminar-servicio', ['action' => 'eliminar', 'id' => $sA], false);
    comprobar($r[0] === 403 && $sv($sA) === 'Activo', 'Servicio: POST sin token → 403', "HTTP {$r[0]}");
    $r = $rep->post('/representante/eliminar-servicio', ['action' => 'eliminar', 'id' => $sB]);
    comprobar($r[0] === 404 && $sv($sB) === 'Activo', 'Servicio de otra clínica → 404', "HTTP {$r[0]}");

    $hs = fn(int $id) => $estado('horario_servicio', 'id_horario_servicio', $id);
    $json = ['Accept: application/json'];
    $r = $rep->pedir('GET', "/representante/actualizar-servicio?action=eliminar_horarios&id=$hsB");
    comprobar($r[0] === 405 && $hs($hsB) === 'Activo', 'Horario de servicio: GET a uno de la clínica B → 405 y no cambia', "HTTP {$r[0]}, estado " . $hs($hsB));
    $r = $rep->post('/representante/actualizar-servicio', ['action' => 'eliminar_horarios', 'id' => $hsB], true, $json);
    comprobar($r[0] === 404 && $hs($hsB) === 'Activo', 'Horario de servicio de otra clínica → 404', "HTTP {$r[0]}");
    $r = $rep->post('/representante/actualizar-servicio', ['action' => 'eliminar_horarios', 'id' => $hsA], true, $json);
    comprobar($r[0] === 200 && str_contains($r[1], '"success"') && $hs($hsA) === 'Inactivo', 'Horario de servicio de su clínica → eliminado (JSON)', "HTTP {$r[0]}");

    $ss = fn(int $id) => $estado('subservicios', 'id_subservicio', $id);
    $r = $rep->pedir('GET', "/representante/eliminar-subservicio?action=eliminar&id=$ssB");
    comprobar($r[0] === 405 && $ss($ssB) === 'Activo', 'Subservicio: GET a uno de la clínica B → 405 y no cambia', "HTTP {$r[0]}, estado " . $ss($ssB));
    $r = $rep->post('/representante/eliminar-subservicio', ['action' => 'eliminar', 'id' => $ssB]);
    comprobar($r[0] === 404 && $ss($ssB) === 'Activo', 'Subservicio de otra clínica → 404', "HTTP {$r[0]}");
    $rep->post('/representante/eliminar-subservicio', ['action' => 'eliminar', 'id' => $ssA]);
    comprobar($ss($ssA) === 'Inactivo', 'Subservicio de su clínica → eliminado');
    $rep->post('/representante/eliminar-servicio', ['action' => 'eliminar', 'id' => $sA]);
    comprobar($sv($sA) === 'Inactivo', 'Servicio de su clínica → eliminado');

    $da = fn(int $id) => $estado('disponibilidad_usuario', 'id_disponibilidad', $id);
    $r = $rep->pedir('GET', "/representante/eliminar-agenda-usuario?action=eliminar&id=$dB");
    comprobar($r[0] === 405 && $da($dB) === 'Activo', 'Agenda: GET a una disponibilidad de la clínica B → 405 y no cambia', "HTTP {$r[0]}, estado " . $da($dB));
    $r = $rep->post('/representante/eliminar-agenda-usuario', ['action' => 'eliminar', 'id' => $dA], false);
    comprobar($r[0] === 403 && $da($dA) === 'Activo', 'Agenda: POST sin token → 403', "HTTP {$r[0]}");
    $r = $rep->post('/representante/eliminar-agenda-usuario', ['action' => 'eliminar', 'id' => $dB]);
    comprobar($r[0] === 404 && $da($dB) === 'Activo', 'Agenda: disponibilidad de otra clínica → 404', "HTTP {$r[0]}");
    $r = $rep->post('/representante/eliminar-agenda-usuario', ['action' => 'eliminar', 'id' => $dA, 'redirect' => 'https://ataque.example/x']);
    comprobar($da($dA) === 'Inactivo' && !str_contains($r[1], 'ataque.example'), 'Agenda de su clínica → eliminada, sin redirigir a un sitio externo');
    [$vet->token] = [(function () use ($vet) {
        [, $html] = $vet->pedir('GET', '/veterinaria/mi-agenda');
        preg_match('/name="csrf_token" value="([0-9a-f]+)"/', $html, $m);
        return $m[1] ?? '';
    })()];
    comprobar($vet->token !== '', 'La agenda del veterinario imprime el token CSRF');
    $r = $vet->post('/veterinario/eliminar-disponibilidad-agenda', ['action' => 'eliminar', 'id' => $dC]);
    comprobar($r[0] === 404 && $da($dC) === 'Activo', 'Veterinario: disponibilidad de otro profesional de su clínica → 404', "HTTP {$r[0]}");
    $vet->post('/veterinario/eliminar-disponibilidad-agenda', ['action' => 'eliminar', 'id' => $dA2]);
    comprobar($da($dA2) === 'Inactivo', 'Veterinario: su propia disponibilidad → eliminada');

    $iv = fn(int $id) => (int) valor('SELECT estado FROM inventario WHERE id_inventario = ?', [$id]);
    $rep->post('/representante/eliminar-producto', ['accion' => 'eliminar', 'id_inventario' => $invA], false);
    comprobar($iv($invA) === 1, 'Inventario: POST sin token → no se elimina', 'estado ' . $iv($invA));
    $rep->post('/representante/eliminar-producto', ['accion' => 'eliminar', 'id_inventario' => $invB]);
    comprobar($iv($invB) === 1, 'Inventario de otra clínica → no se elimina');
    $rep->post('/representante/eliminar-producto', ['accion' => 'eliminar', 'id_inventario' => $invA]);
    comprobar($iv($invA) === 0, 'Inventario de su clínica con token → eliminado');

    echo "▶ Consulta de propietario por id\n";
    $r = $admin->pedir('GET', "/admin/api/propietarios?accion=consultar&id=$propA");
    comprobar($r[0] === 200 && str_contains($r[1], '"success"') && str_contains($r[1], 'PropietarioA'), 'Administrador consulta un propietario activo → 200', "HTTP {$r[0]}: " . substr(strip_tags($r[1]), 0, 120));
    $r = $rep->pedir('GET', "/representante/api/propietarios?accion=consultar&id=$propA");
    comprobar($r[0] === 200 && str_contains($r[1], 'PropietarioA'), 'Representante consulta un propietario de su clínica → 200', "HTTP {$r[0]}");
    $r = $rep->pedir('GET', "/representante/api/propietarios?accion=consultar&id=$propB");
    comprobar($r[0] === 404 && !str_contains($r[1], 'PropietarioB'), 'Representante consulta un propietario de otra clínica → 404', "HTTP {$r[0]}");
    $r = $prop->pedir('GET', "/cliente/actualizar?accion=consultar&id=$propA");
    comprobar($r[0] === 200 && str_contains($r[1], 'PropietarioA'), 'El propietario se consulta a sí mismo → 200', "HTTP {$r[0]}");
    $r = $prop->pedir('GET', "/cliente/actualizar?accion=consultar&id=$propB");
    comprobar($r[0] === 404 && !str_contains($r[1], 'PropietarioB'), 'El propietario consulta a otro → 404', "HTTP {$r[0]}");
    $r = $vet->pedir('GET', "/representante/api/propietarios?accion=consultar&id=$propA");
    comprobar($r[0] === 403, 'El veterinario no usa la API de propietarios del representante → 403', "HTTP {$r[0]}");

    echo "▶ Las rutas de contraseña siguen cambiando la contraseña\n";
    $nueva = $clave . 'x';
    $vet->pedir('POST', '/veterinario/actualizar-constrasena', ['accion' => 'vet-constrasena', 'id_usuario' => 1003,
        'contrasena-actual' => $clave, 'nueva-contrasena' => $nueva, 'confi-contrasena' => $nueva]);
    comprobar(password_verify($nueva, (string) valor('SELECT password_hash FROM usuario WHERE id_usuario = 1003')), 'Veterinario: accion=vet-constrasena cambia su contraseña');
    $prop->pedir('POST', '/cliente/actualizar-contrasena', ['accion' => 'modificar-contrasena', 'id_usuario' => $uProp,
        'contrasena-actual' => $clave, 'nueva-contrasena' => $nueva, 'confi-contrasena' => $nueva]);
    comprobar(password_verify($nueva, (string) valor('SELECT password_hash FROM usuario WHERE id_usuario = ?', [$uProp])), 'Propietario: accion=modificar-contrasena cambia su contraseña');
} catch (Throwable $e) {
    comprobar(false, 'Excepción en la prueba', $e->getMessage() . ' @ ' . $e->getLine());
} finally {
    integracion_restaurar($pdo, $instantanea);
    comprobar((int) valor("SELECT COUNT(*) FROM usuario WHERE email LIKE 'f3test+%'") === 0
        && (int) valor("SELECT COUNT(*) FROM veterinaria WHERE nit LIKE 'F3%'") === 0, 'Limpieza: no quedan datos de prueba y las filas existentes se repusieron');
    integracion_cerrar_servidor($servidor, $log);
}

echo "\nResultado: $ok correctas, $fallo fallidas.\n";
exit($fallo ? 1 : 0);
