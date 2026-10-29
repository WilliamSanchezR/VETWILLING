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
