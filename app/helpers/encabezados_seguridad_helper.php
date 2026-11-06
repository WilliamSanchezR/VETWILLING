<?php

// Encabezados de seguridad HTTP (X-Frame-Options, HSTS, Permissions-Policy...).
// En Apache/LiteSpeed los pone el .htaccess con mod_headers y marca la petición con
// VW_ENCABEZADOS_SERVIDOR; entonces aquí no se repiten. Si falta la marca (php -S, o un
// servidor sin mod_headers), se envían desde PHP como respaldo.

function encabezados_seguridad_lista(bool $https): array
{
    $lista = [
        'X-Frame-Options'        => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        // Ninguna página usa estas APIs; Mercado Pago es Checkout Pro (redirección), no necesita payment
        'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        // Solo frame-ancestors: una CSP estricta rompería los CDN y los scripts en línea
        'Content-Security-Policy' => "frame-ancestors 'self'",
    ];

    // Por HTTP el navegador ignora HSTS; se envía solo por HTTPS
    if ($https) {
        $lista['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
    }

    return $lista;
}

function encabezados_seguridad_puestos_por_servidor(): bool
{
    // Tras la reescritura a index.php, Apache antepone REDIRECT_ a las variables de entorno
    foreach (['VW_ENCABEZADOS_SERVIDOR', 'REDIRECT_VW_ENCABEZADOS_SERVIDOR'] as $clave) {
        if (!empty($_SERVER[$clave]) || getenv($clave)) {
            return true;
        }
    }
    return false;
}

function encabezados_seguridad_es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function encabezados_seguridad_enviar(): void
{
    if (headers_sent()) {
        return;
    }

    // No anunciar la versión de PHP, la ponga quien la ponga (expose_php)
    header_remove('X-Powered-By');

    if (encabezados_seguridad_puestos_por_servidor()) {
        return;
    }

    foreach (encabezados_seguridad_lista(encabezados_seguridad_es_https()) as $nombre => $valor) {
        header($nombre . ': ' . $valor);
    }
}
