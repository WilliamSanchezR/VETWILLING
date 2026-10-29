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
        'autenticado'             => true,
    ];
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

    $publica = ($politica['roles'] ?? null) === 'publica';
    foreach ($politica['publica_si'] ?? [] as $parametro => $valor) {
        if (($query[$parametro] ?? null) === $valor) {
            $publica = true;
        }
    }
    if ($publica) {
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
    $usuario = acceso_usuario();
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
