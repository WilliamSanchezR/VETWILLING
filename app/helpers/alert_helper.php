<?php

/**
 * Obtiene y sanitiza los datos necesarios para la pantalla de cierre de sesión.
 *
 * @param string|null $redirect URL de redirección personalizada.
 * @return array{titulo: string, msg: string, redirectUrl: string}
 */
function obtenerDatosCierreSesion(?string $redirect = null): array
{
    $rol    = $_SESSION['user']['rol']    ?? 'invitado';
    $nombre = htmlspecialchars($_SESSION['user']['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $saludo = $nombre ? ", $nombre" : '';

    $mensajes = [
        'veterinario'   => ["Hasta pronto{$saludo}",    "Gracias por cuidar con dedicación. Cada consulta deja una huella importante."],
        'representante' => ["Nos vemos pronto{$saludo}", "Tu compromiso fortalece la confianza de cada familia que atendemos."],
        'auxiliar'      => ["Buen descanso{$saludo}",   "El trabajo en equipo también se construye con vocación y entrega."],
        'admin'         => ["Hasta luego{$saludo}",     "El sistema queda en orden y listo para tu próximo inicio."],
        'invitado'      => ["Hasta pronto",              "Gracias por confiar en nuestro servicio."],
    ];

    $entrada = $mensajes[$rol] ?? $mensajes['invitado'];
    [$titulo, $msg] = array_pad($entrada, 2, '');

    // Validar la URL de redirección para prevenir open redirect y XSS en JS
    $urlDefault  = '/vetwilling/login';
    $redirectUrl = $urlDefault;

    if ($redirect !== null) {
        $parsed = parse_url($redirect);
        // Solo se permiten rutas relativas (sin esquema ni host externo)
        $esRelativa = isset($parsed['path']) && !isset($parsed['scheme']) && !isset($parsed['host']);
        if ($esRelativa) {
            $redirectUrl = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
        }
    }

    return [
        'titulo'      => htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'),
        'msg'         => htmlspecialchars($msg,    ENT_QUOTES, 'UTF-8'),
        'redirectUrl' => $redirectUrl,
    ];
}

/**
 * Renderiza la pantalla de cierre de sesión con redirección automática.
 *
 * @param string|null $redirect URL relativa a la que redirigir tras 4 segundos.
 */
function mostrarCierreSesion(?string $redirect = null): void
{
    ['titulo' => $titulo, 'msg' => $msg, 'redirectUrl' => $redirectUrl]
        = obtenerDatosCierreSesion($redirect);

    // Serializar la URL de forma segura para usarla dentro de JS
    $redirectUrlJs = json_encode($redirectUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hasta pronto — VetWilling</title>

        <style>
            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #0d1f14, #123524, #0f2b1b);
                color: #fff;
            }

            .card {
                width: min(480px, 92vw);
                padding: 3rem 2.5rem;
                text-align: center;
                border-radius: 26px;
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.45);
                animation: fadeIn 0.8s ease forwards;
            }

            .icon-wrap {
                width: 90px;
                height: 90px;
                margin: 0 auto 1.8rem;
            }

            .circle {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(15, 143, 58, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulse 2.5s infinite;
            }

            .check {
                width: 38px;
                height: 38px;
                stroke: #0f8f3a;
                stroke-width: 3;
                fill: none;
                stroke-dasharray: 50;
                stroke-dashoffset: 50;
                animation: draw 0.6s ease forwards 0.4s;
            }

            h1 {
                font-size: 1.7rem;
                margin-bottom: 0.8rem;
                font-weight: 600;
            }

            p {
                font-size: 0.95rem;
                opacity: 0.7;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .progress {
                width: 100%;
                height: 4px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 99px;
                overflow: hidden;
            }

            .bar {
                height: 100%;
                width: 0%;
                background: linear-gradient(90deg, #0f8f3a, #19c46b);
                animation: load 4s linear forwards;
            }

            @keyframes draw    { to { stroke-dashoffset: 0 } }
            @keyframes pulse   { 0%, 100% { transform: scale(1) } 50% { transform: scale(1.1) } }
            @keyframes fadeIn  { from { opacity: 0; transform: translateY(20px) } to { opacity: 1; transform: translateY(0) } }
            @keyframes load    { to { width: 100% } }
        </style>
    </head>
    <body>

        <div class="card">
            <div class="icon-wrap">
                <div class="circle">
                    <svg viewBox="0 0 24 24" class="check" aria-hidden="true">
                        <polyline points="4,13 9,18 20,7"/>
                    </svg>
                </div>
            </div>

            <h1>{$titulo}</h1>
            <p>{$msg}</p>

            <div class="progress" role="progressbar" aria-label="Redirigiendo...">
                <div class="bar"></div>
            </div>
        </div>

        <script>
            (function () {
                var destino = {$redirectUrlJs};
                setTimeout(function () {
                    window.location.href = destino;
                }, 4000);
            }());
        </script>

    </body>
    </html>
    HTML;
}