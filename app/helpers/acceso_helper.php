<?php

// Identidad del usuario autenticado.
//
// Una sesión solo cuenta como iniciada si la creó el login (acceso_iniciar_sesion_usuario):
// $_SESSION['user'] con la marca 'autenticado', un id_usuario entero positivo y un id_rol válido.
// Cualquier otro contenido de $_SESSION['user'] (por ejemplo, solo 'id_veterinaria') se trata
// como anónimo. Los helpers session_*.php e isSessionActive() delegan aquí.
//
// Veterinarios con varias clínicas: el login guarda las clínicas permitidas y deja la selección
// pendiente; solo acceso_seleccionar_veterinaria() fija la clínica activa, y únicamente entre
// las permitidas.
//
// Además, en cada petición la sesión se contrasta con la base (ver "Versión de credenciales"):
// si cambió la versión de credenciales, el estado o el rol del usuario, deja de valer.

const ACCESO_ROLES_VALIDOS = [1, 2, 3, 4, 5];
const ACCESO_RUTA_SELECCION = '/login/seleccionarVeterinaria';
const ACCESO_PANELES = [
    1 => '/admin/dashBoard',         // Administrador
    2 => '/veterinaria/dashboard',   // Veterinario
    3 => '/cliente/dashboard',       // Propietario
    4 => '/representante/dashboard', // Representante
];

function acceso_iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Usuario autenticado o null. No confía en un $_SESSION['user'] parcial ni sin la marca del login.
 */
function acceso_usuario(): ?array
{
    acceso_iniciar_sesion();
    $usuario = $_SESSION['user'] ?? null;
    if (!is_array($usuario) || ($usuario['autenticado'] ?? false) !== true) {
        return null;
    }
    $id  = filter_var($usuario['id_usuario'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $rol = filter_var($usuario['id_rol'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || !in_array($rol, ACCESO_ROLES_VALIDOS, true)) {
        return null;
    }
    if (!acceso_credenciales_vigentes($usuario)) {
        // Contraseña o correo cambiados, sesiones cerradas, cuenta inactiva o rol cambiado
        unset($_SESSION['user']);
        return null;
    }
    return $usuario;
}

function acceso_id_usuario(): ?int
{
    $usuario = acceso_usuario();
    return $usuario === null ? null : (int) $usuario['id_usuario'];
}

function acceso_rol(): ?int
{
    $usuario = acceso_usuario();
    return $usuario === null ? null : (int) $usuario['id_rol'];
}

function acceso_seleccion_pendiente(): bool
{
    $usuario = acceso_usuario();
    return $usuario !== null && !empty($usuario['seleccion_pendiente']);
}

/**
 * Clínica activa del usuario, o null si no tiene o aún no la ha seleccionado.
 */
function acceso_id_veterinaria(): ?int
{
    $usuario = acceso_usuario();
    if ($usuario === null || !empty($usuario['seleccion_pendiente'])) {
        return null;
    }
    $id = filter_var($usuario['id_veterinaria'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

/**
 * Panel de inicio de cada rol (null si el rol no tiene panel).
 */
function acceso_panel_rol(int $rol): ?string
{
    return ACCESO_PANELES[$rol] ?? null;
}

/**
 * Convierte la lista de clínicas que devuelve el login ("3, 7") en enteros únicos.
 */
function acceso_lista_veterinarias($valor): array
{
    $ids = [];
    foreach (explode(',', (string) $valor) as $parte) {
        $id = filter_var(trim($parte), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id !== false) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

/**
 * Crea la sesión del usuario que acaba de autenticarse. Solo la usa el login.
 * Regenera el id de sesión (evita la fijación de sesión) y el token CSRF.
 */
function acceso_iniciar_sesion_usuario(array $datos): void
{
    acceso_iniciar_sesion();
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);

    $veterinarias = acceso_lista_veterinarias($datos['id_veterinaria'] ?? null);
    $pendiente = count($veterinarias) > 1;
    $credenciales = acceso_leer_credenciales_o_503((int) $datos['id_usuario']);

    // El hash de la contraseña no se guarda en sesión: ninguna vista lo necesita
    $_SESSION['user'] = [
        'id_usuario'              => (int) $datos['id_usuario'],
        'id_rol'                  => (int) $datos['id_rol'],
        'email'                   => $datos['email'],
        'id_veterinaria'          => $pendiente ? null : ($veterinarias[0] ?? null),
        'nombres'                 => $datos['nombres'],
        'apellidos'               => $datos['apellidos'],
        'estado'                  => $datos['estado'],
        'veterinarias_permitidas' => $veterinarias,
        'seleccion_pendiente'     => $pendiente,
        'credenciales_version'    => (int) ($credenciales['credenciales_version'] ?? 0),
        'autenticado'             => true,
    ];
    acceso_olvidar_verificacion();
}

/**
 * Fija la clínica activa de un usuario con la selección pendiente.
 * Devuelve false (sin tocar la sesión) si no hay sesión iniciada, no hay selección pendiente
 * o la clínica no está entre las permitidas.
 */
function acceso_seleccionar_veterinaria($idVeterinaria): bool
{
    $usuario = acceso_usuario();
    if ($usuario === null || empty($usuario['seleccion_pendiente'])) {
        return false;
    }
    $id = filter_var($idVeterinaria, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || !in_array($id, $usuario['veterinarias_permitidas'] ?? [], true)) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user']['id_veterinaria'] = $id;
    $_SESSION['user']['seleccion_pendiente'] = false;
    return true;
}

function acceso_redirigir(string $ruta): never
{
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . $ruta);
    exit();
}

/**
 * Exige una sesión iniciada y, si se indican, uno de los roles. Mantiene el comportamiento de los
 * helpers session_*.php: sin sesión o con otro rol redirige al login. Con la selección de clínica
 * pendiente lleva a la pantalla de selección.
 */
function acceso_exigir_usuario(array $roles = []): array
{
    $usuario = acceso_usuario();
    if ($usuario === null) {
        acceso_redirigir('/login');
    }
    if ($roles && !in_array((int) $usuario['id_rol'], $roles, true)) {
        acceso_redirigir('/login');
    }
    if (!empty($usuario['seleccion_pendiente'])) {
        acceso_redirigir(ACCESO_RUTA_SELECCION);
    }
    return $usuario;
}

/**
 * Solo para la pantalla de selección de clínica: exige sesión iniciada con la selección pendiente.
 */
function acceso_exigir_seleccion_pendiente(): array
{
    $usuario = acceso_usuario();
    if ($usuario === null) {
        acceso_redirigir('/login');
    }
    if (empty($usuario['seleccion_pendiente'])) {
        acceso_redirigir(acceso_panel_rol((int) $usuario['id_rol']) ?? '/login');
    }
    return $usuario;
}

// ── Protección central de rutas (fase 2) ─────────────────────────────────────────
//
// index.php llama a acceso_verificar() antes del switch. La política de cada ruta está en
// config/rutas_acceso.php. Modo (variable ACCESO_MODO en .env): 'aplicar' (por defecto),
// 'observar' (solo registra lo que se habría denegado) o 'apagado'.

const ACCESO_METODOS_VISTA = ['GET', 'HEAD'];

function acceso_mapa(): array
{
    static $mapa = null;
    return $mapa ??= require __DIR__ . '/../../config/rutas_acceso.php';
}

/**
 * Decide qué hacer con una petición. Función pura: no lee la sesión ni envía nada.
 *
 * @param array|null $politica Entrada del mapa (null si la ruta no está en él)
 * @param array|null $usuario  acceso_usuario()
 * @param array      $query    Parámetros GET
 * @return array{resultado: string, motivo: string}
 *         resultado: 'permitir' | 'login' | 'seleccion' | '403' | '404' | '405'
 */
function acceso_decidir(?array $politica, ?array $usuario, string $metodo, array $query = []): array
{
    $d = fn(string $resultado, string $motivo) => ['resultado' => $resultado, 'motivo' => $motivo];

    // Ruta desconocida: igual que la rama default del enrutador (login o 404)
    if ($politica === null) {
        return $usuario === null ? $d('login', 'ruta desconocida sin sesión') : $d('404', 'ruta desconocida');
    }
    $tipo = $politica['tipo'] ?? '';
    if ($tipo === 'deshabilitada') {
        return $d('404', 'ruta deshabilitada');
    }
    if ($tipo === 'vista' && empty($politica['post']) && !in_array(strtoupper($metodo), ACCESO_METODOS_VISTA, true)) {
        return $d('405', 'una vista solo admite GET');
    }

    if (acceso_es_publica($politica, $query)) {
        return $d('permitir', 'ruta pública');
    }

    if ($usuario === null) {
        return $d('login', 'sin sesión');
    }
    if (!empty($usuario['seleccion_pendiente'])) {
        return $d('seleccion', 'selección de veterinaria pendiente');
    }
    $roles = $politica['roles'] ?? [];
    if (!is_array($roles) || !in_array((int) $usuario['id_rol'], $roles, true)) {
        return $d('403', 'rol no permitido');
    }
    return $d('permitir', 'rol permitido');
}

/**
 * Ruta pública: 'roles' => 'publica', o un parámetro GET de 'publica_si' coincide (webhook de pagos).
 */
function acceso_es_publica(?array $politica, array $query): bool
{
    if ($politica === null) {
        return false;
    }
    if (($politica['roles'] ?? null) === 'publica') {
        return true;
    }
    foreach ($politica['publica_si'] ?? [] as $parametro => $valor) {
        if (($query[$parametro] ?? null) === $valor) {
            return true;
        }
    }
    return false;
}

/**
 * La respuesta va en JSON si la ruta es una API o la petición la hace JavaScript.
 */
function acceso_responder_json(?array $politica): bool
{
    if (($politica['tipo'] ?? '') === 'api') {
        return true;
    }
    $acepta = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    $contenido = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
    return str_contains($acepta, 'application/json')
        || str_contains($contenido, 'application/json')
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

/**
 * Aplica la política de la ruta. Si la petición no está permitida, responde y termina.
 * Los mensajes son genéricos; el motivo concreto solo se registra en el log del servidor.
 */
function acceso_verificar(string $ruta, string $metodo): void
{
    $modo = function_exists('env_value') ? (string) env_value('ACCESO_MODO', 'aplicar') : 'aplicar';
    if ($modo === 'apagado') {
        return;
    }

    $politica = acceso_mapa()[$ruta] ?? null;
    $estado = &acceso_estado();
    $estado['politica'] = $politica; // para responder en JSON si la base no está disponible
    // Las rutas públicas no necesitan la identidad (ni la base de datos para validarla)
    $usuario = acceso_es_publica($politica, $_GET) ? null : acceso_usuario();
    $decision = acceso_decidir($politica, $usuario, $metodo, $_GET);
    if ($decision['resultado'] === 'permitir') {
        return;
    }

    error_log(sprintf(
        '[acceso] %s %s %s → %s (%s, rol %s)',
        $modo === 'observar' ? 'OBSERVADO' : 'DENEGADO',
        strtoupper($metodo),
        $ruta,
        $decision['resultado'],
        $decision['motivo'],
        $usuario === null ? 'anónimo' : (string) $usuario['id_rol']
    ));
    if ($modo === 'observar') {
        return;
    }

    $json = acceso_responder_json($politica);
    $base = defined('BASE_URL') ? BASE_URL : '';
    [$codigo, $mensaje] = match ($decision['resultado']) {
        'login'     => [401, 'Debes iniciar sesión.'],
        'seleccion' => [403, 'Selecciona una veterinaria para continuar.'],
        '403'       => [403, 'No tienes permiso para realizar esta acción.'],
        '404'       => [404, 'Recurso no encontrado.'],
        '405'       => [405, 'Método no permitido.'],
    };

    if ($json) {
        http_response_code($codigo);
        if ($codigo === 405) {
            header('Allow: ' . implode(', ', ACCESO_METODOS_VISTA));
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => $mensaje, 'message' => $mensaje]);
        exit();
    }

    switch ($decision['resultado']) {
        case 'login':
            acceso_redirigir('/login');
        case 'seleccion':
            acceso_redirigir(ACCESO_RUTA_SELECCION);
        case '403':
            http_response_code(403);
            require __DIR__ . '/../views/auth/error403.php';
            exit();
        case '404':
            http_response_code(404);
            require __DIR__ . '/../views/auth/error404.php';
            exit();
        default:
            http_response_code(405);
            header('Allow: ' . implode(', ', ACCESO_METODOS_VISTA));
            header('Content-Type: text/plain; charset=utf-8');
            echo $mensaje;
            exit();
    }
}

// ── Versión de credenciales (T1) ─────────────────────────────────────────────────
//
// usuario.credenciales_version (migración database/credenciales_version.sql) se guarda en la
// sesión al iniciar sesión y se compara con la base en cada petición que usa la identidad: una
// consulta por clave primaria, una sola vez por petición y sin caché entre peticiones. Subirla
// (acceso_rotar_credenciales) invalida todas las demás sesiones del usuario en su siguiente
// petición. La misma consulta comprueba que la cuenta siga activa y con el mismo rol.
//
// Si la base no responde no se puede validar la identidad: se falla cerrado con un 503 genérico,
// también cuando config/database.php termina con die() al no poder conectar.

class AccesoBaseNoDisponible extends RuntimeException
{
}

/**
 * Estado interno del control de acceso durante la petición (por referencia).
 */
function &acceso_estado(): array
{
    static $estado = ['lector' => null, 'verificado' => null, 'conectando' => false, 'politica' => null, 'cierre' => false];
    return $estado;
}

/**
 * Obliga a volver a validar la sesión contra la base en la siguiente llamada a acceso_usuario().
 */
function acceso_olvidar_verificacion(): void
{
    $estado = &acceso_estado();
    $estado['verificado'] = null;
}

/**
 * Sustituye la lectura de credenciales de la base (solo para pruebas). null restaura la lectura real.
 */
function acceso_configurar_lector(?callable $lector): void
{
    $estado = &acceso_estado();
    $estado['lector'] = $lector;
    $estado['verificado'] = null;
}

/**
 * Conexión de la aplicación (config/database.php). Si no se puede conectar, lanza
 * AccesoBaseNoDisponible; si la conexión termina con die(), su salida se sustituye por un 503.
 */
function acceso_conexion(): PDO
{
    if (!class_exists('conexion')) {
        require_once __DIR__ . '/../../config/database.php';
    }
    $estado = &acceso_estado();
    if (!$estado['cierre']) {
        register_shutdown_function('acceso_cierre_sin_conexion');
        $estado['cierre'] = true;
    }
    $estado['conectando'] = true;
    ob_start();
    try {
        $pdo = (new conexion())->getConexion();
    } catch (Throwable $e) {
        ob_end_clean();
        $estado['conectando'] = false;
        throw new AccesoBaseNoDisponible('La base de datos no está disponible.', 0, $e);
    }
    ob_end_flush();
    $estado['conectando'] = false;
    return $pdo;
}

/**
 * Función de cierre: si la petición terminó mientras se conectaba (die() de config/database.php),
 * descarta ese texto y responde con el 503 genérico.
 */
function acceso_cierre_sin_conexion(): void
{
    if (empty(acceso_estado()['conectando'])) {
        return;
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('[acceso] no se pudo validar la identidad: la conexión a la base de datos terminó la petición');
    acceso_responder_503();
}

/**
 * 503 genérico: no revela el motivo ni datos de la conexión.
 */
function acceso_responder_503(): void
{
    $mensaje = 'El servicio no está disponible en este momento. Inténtalo de nuevo en unos minutos.';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $mensaje . "\n");
        return;
    }
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 30');
        header('Cache-Control: no-store');
    }
    if (acceso_responder_json(acceso_estado()['politica'])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['ok' => false, 'status' => 'error', 'mensaje' => $mensaje, 'message' => $mensaje]);
    } else {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Servicio no disponible</title></head>'
            . '<body><h1>Servicio no disponible</h1><p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
    }
}

/**
 * Versión de credenciales, estado y rol del usuario (null si no existe).
 *
 * @throws AccesoBaseNoDisponible|PDOException
 */
function acceso_leer_credenciales(int $idUsuario): ?array
{
    $lector = acceso_estado()['lector'];
    if ($lector !== null) {
        return $lector($idUsuario);
    }
    $stmt = acceso_conexion()->prepare('SELECT credenciales_version, estado, id_rol FROM usuario WHERE id_usuario = ?');
    $stmt->execute([$idUsuario]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    return $fila ?: null;
}

/**
 * acceso_leer_credenciales() con fallo cerrado: si la base no responde (o falta la columna
 * credenciales_version porque la migración no se aplicó), termina la petición con el 503 genérico.
 */
function acceso_leer_credenciales_o_503(int $idUsuario): ?array
{
    try {
        return acceso_leer_credenciales($idUsuario);
    } catch (AccesoBaseNoDisponible | PDOException $e) {
        error_log('[acceso] no se pudo validar la identidad: base de datos no disponible o sin la migración credenciales_version');
        acceso_responder_503();
        exit(1);
    }
}

/**
 * ¿La sesión sigue siendo válida según la base? (una vez por petición y por sesión).
 * Si la base no responde, acceso_leer_credenciales_o_503() termina la petición con un 503 (fallo cerrado).
 */
function acceso_credenciales_vigentes(array $usuario): bool
{
    $version = filter_var($usuario['credenciales_version'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($version === false) {
        return false; // sesión creada antes de la versión de credenciales: debe volver a iniciarse
    }
    $estado = &acceso_estado();
    $clave = session_id() . '|' . $usuario['id_usuario'] . '|' . $version . '|' . $usuario['id_rol'];
    if ($estado['verificado'] === $clave) {
        return true;
    }

    $fila = acceso_leer_credenciales_o_503((int) $usuario['id_usuario']);

    if (!acceso_fila_coincide($fila, $usuario, $version)) {
        error_log(sprintf('[acceso] sesión revocada del usuario %d (credenciales, estado o rol cambiados)', (int) $usuario['id_usuario']));
        return false;
    }
    $estado['verificado'] = $clave;
    return true;
}

/** La fila de la base coincide con la sesión: misma versión, cuenta activa y mismo rol. */
function acceso_fila_coincide(?array $fila, array $usuario, int $version): bool
{
    return $fila !== null
        && (int) $fila['credenciales_version'] === $version
        && mb_strtolower(trim((string) $fila['estado'])) === 'activo'
        && (int) $fila['id_rol'] === (int) $usuario['id_rol'];
}

/**
 * Revalidación para conexiones largas (SSE), que siguen abiertas después de cerrar la sesión:
 * consulta la base en cada llamada, sin memoria. Si la base no responde devuelve false
 * (fallo cerrado: el llamador corta la conexión y la reconexión pasará por la comprobación normal).
 *
 * @param array $usuario Copia de $_SESSION['user'] tomada al abrir la conexión
 */
function acceso_sesion_sigue_vigente(array $usuario): bool
{
    $version = filter_var($usuario['credenciales_version'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($version === false || !isset($usuario['id_usuario'])) {
        return false;
    }
    try {
        $fila = acceso_leer_credenciales((int) $usuario['id_usuario']);
    } catch (AccesoBaseNoDisponible | PDOException $e) {
        error_log('[acceso] conexión larga cerrada: no se pudo revalidar la identidad');
        return false;
    }
    if (!acceso_fila_coincide($fila, $usuario, $version)) {
        error_log(sprintf('[acceso] conexión larga cerrada: sesión revocada del usuario %d', (int) $usuario['id_usuario']));
        return false;
    }
    return true;
}

/**
 * Sube la versión de credenciales del usuario e invalida sus tokens de recuperación pendientes.
 * Se llama DENTRO de la transacción del cambio (contraseña, correo, "cerrar las demás sesiones");
 * después de confirmarla, si el cambio lo hizo el propio usuario, llamar a acceso_actualizar_sesion_actual().
 *
 * @return int Versión nueva
 */
function acceso_rotar_credenciales(PDO $pdo, int $idUsuario): int
{
    $pdo->prepare('UPDATE usuario SET credenciales_version = credenciales_version + 1 WHERE id_usuario = ?')->execute([$idUsuario]);
    $pdo->prepare('UPDATE password_reset SET usado_en = NOW() WHERE id_usuario = ? AND usado_en IS NULL')->execute([$idUsuario]);
    $stmt = $pdo->prepare('SELECT credenciales_version FROM usuario WHERE id_usuario = ?');
    $stmt->execute([$idUsuario]);
    $version = $stmt->fetchColumn();
    if ($version === false) {
        throw new RuntimeException('Usuario inexistente al rotar las credenciales.');
    }
    return (int) $version;
}

/**
 * Mantiene abierta la sesión que hizo el cambio: nuevo id de sesión, la versión nueva y un nuevo
 * token CSRF. No hace nada si la sesión actual es de otro usuario (p. ej. un administrador que
 * cambió la cuenta de otra persona).
 */
function acceso_actualizar_sesion_actual(int $idUsuario, int $nuevaVersion): void
{
    acceso_iniciar_sesion();
    if ((int) ($_SESSION['user']['id_usuario'] ?? 0) !== $idUsuario) {
        return;
    }
    session_regenerate_id(true);
    $_SESSION['user']['credenciales_version'] = $nuevaVersion;
    unset($_SESSION['csrf_token']);
    acceso_olvidar_verificacion();
}
