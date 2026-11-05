<?php

// Ayudas para pintar datos en las vistas sin riesgo de XSS.

if (!function_exists('e')) {
    /** Escapa un valor para mostrarlo en HTML (texto o atributo). */
    function e($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Indica si existe un archivo subido. basename() impide salir de la carpeta indicada. */
function archivo_subido_existe(string $carpeta, $nombre): bool
{
    $nombre = basename((string) ($nombre ?? ''));
    return $nombre !== '' && is_file(BASE_PATH . "/public/uploads/$carpeta/$nombre");
}

/** Foto del usuario, o un avatar con sus iniciales si no tiene o el archivo no existe. */
function avatar_usuario_src(array $usuario, string $carpeta = 'usuarios'): string
{
    $foto = $usuario['img_perfil'] ?? '';
    if (archivo_subido_existe($carpeta, $foto)) {
        return BASE_URL . "/public/uploads/$carpeta/" . rawurlencode(basename($foto));
    }
    require_once __DIR__ . '/avatar_helper.php';
    return generarAvatarSVG((string) ($usuario['nombres'] ?? ''), (string) ($usuario['apellidos'] ?? ''));
}

/** Mueve una imagen subida y crea la carpeta si no existe. */
function guardar_archivo_subido(string $origen, string $destino): bool
{
    $carpeta = dirname($destino);
    if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
        return false;
    }
    return move_uploaded_file($origen, $destino);
}
