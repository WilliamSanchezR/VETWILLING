<?php

/**
 * ============================================================
 * SESSION HELPER - VETWILLING
 * ============================================================
 * Funciones auxiliares para manejo de sesiones, autenticación
 * y control de acceso por roles.
 */

/**
 * Inicia la sesión si aún no existe.
 */
function initSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica si existe una sesión activa.
 *
 * @return bool
 */
function isSessionActive()
{
    initSession();

    return isset($_SESSION['user']) &&
           is_array($_SESSION['user']) &&
           !empty($_SESSION['user']);
}

/**
 * Obtiene los datos del usuario autenticado.
 *
 * @return array|null
 */
function getCurrentUser()
{
    return isSessionActive()
        ? $_SESSION['user']
        : null;
}

/**
 * Obtiene un campo específico del usuario.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getCurrentUserData($key, $default = null)
{
    $user = getCurrentUser();

    return $user[$key] ?? $default;
}

/**
 * Retorna el ID del usuario actual.
 *
 * @return int|null
 */
function getCurrentUserId()
{
    return getCurrentUserData('id_usuario');
}

/**
 * Retorna el rol del usuario actual.
 *
 * @return string|null
 */
function getCurrentUserRole()
{
    return getCurrentUserData('rol');
}

/**
 * Guarda los datos del usuario en sesión.
 *
 * @param array $userData
 * @return void
 */
function setUserSession(array $userData)
{
    initSession();

    $_SESSION['user'] = $userData;

    session_regenerate_id(true);
}

/**
 * Verifica si el usuario tiene un rol específico.
 *
 * @param string $role
 * @return bool
 */
function hasRole($role)
{
    return isSessionActive()
        && getCurrentUserRole() === $role;
}

/**
 * Verifica si el usuario tiene alguno de los roles indicados.
 *
 * @param array $roles
 * @return bool
 */
function hasAnyRole(array $roles)
{
    return isSessionActive()
        && in_array(getCurrentUserRole(), $roles, true);
}

/**
 * Destruye completamente la sesión.
 */
function destroySession()
{
    initSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Normaliza una URL de redirección.
 *
 * @param string|null $redirectUrl
 * @return string
 */
function normalizeSessionRedirectUrl($redirectUrl)
{
    if (empty($redirectUrl)) {
        return (defined('BASE_URL')
            ? rtrim(BASE_URL, '/')
            : '') . '/login';
    }

    $target = trim($redirectUrl);

    if (preg_match('/^https?:\/\//i', $target)) {
        return $target;
    }

    if ($target === '/siademy') {
        $target = '/';
    } elseif (strpos($target, '/siademy/') === 0) {
        $target = substr($target, 8);
    }

    if ($target === '') {
        $target = '/';
    }

    if ($target[0] !== '/') {
        $target = '/' . $target;
    }

    $baseUrl = defined('BASE_URL')
        ? rtrim(BASE_URL, '/')
        : '';

    return $baseUrl . $target;
}

/**
 * Redirige si no existe sesión activa.
 *
 * @param string $redirectUrl
 */
function redirectIfNoSession($redirectUrl = '/login')
{
    if (!isSessionActive()) {
        header('Location: ' . normalizeSessionRedirectUrl($redirectUrl));
        exit;
    }
}

/**
 * Redirige si el usuario no posee el rol requerido.
 *
 * @param string $requiredRole
 * @param string $redirectUrl
 */
function redirectIfNotRole(
    $requiredRole,
    $redirectUrl = '/login'
) {
    if (!hasRole($requiredRole)) {
        header('Location: ' . normalizeSessionRedirectUrl($redirectUrl));
        exit;
    }
}

/**
 * Redirige si el usuario no tiene ninguno
 * de los roles permitidos.
 *
 * @param array $roles
 * @param string $redirectUrl
 */
function redirectIfNotAnyRole(
    array $roles,
    $redirectUrl = '/login'
) {
    if (!hasAnyRole($roles)) {
        header('Location: ' . normalizeSessionRedirectUrl($redirectUrl));
        exit;
    }
}
?>
