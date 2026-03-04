<?php

/**
 * Muestra un SweetAlert2 con estilo SENA (glassmorphism oscuro).
 *
 * @param string      $tipo     'success' | 'error' | 'warning' | 'info' | 'question'
 * @param string      $titulo   Título del modal
 * @param string      $mensaje  Cuerpo del modal
 * @param string|null $redirect URL de redirección al cerrar (null = history.back)
 */
function mostrarSweetAlert(string $tipo, string $titulo, string $mensaje, ?string $redirect = null): void
{
    $tipo     = htmlspecialchars($tipo,    ENT_QUOTES, 'UTF-8');
    $titulo   = addslashes(htmlspecialchars($titulo,   ENT_QUOTES, 'UTF-8'));
    $mensaje  = addslashes(htmlspecialchars($mensaje,  ENT_QUOTES, 'UTF-8'));
    $redirect = $redirect ? htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') : null;

    $redirectJs = $redirect
        ? "window.location.href = '{$redirect}';"
        : "window.history.back();";

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aviso</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            /* ── Fondo SENA — más oscuro para que el popup contraste ── */
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Montserrat', sans-serif;
                background: linear-gradient(135deg, #001a2e 0%, #002d14 55%, #004d20 100%);
                overflow: hidden;
            }

            /* Orbes decorativos */
            body::before,
            body::after {
                content: '';
                position: fixed;
                border-radius: 50%;
                filter: blur(90px);
                opacity: 0.35;
                pointer-events: none;
                z-index: 0;
            }
            body::before {
                width: 550px; height: 550px;
                background: radial-gradient(circle, #007832, transparent);
                top: -180px; left: -180px;
            }
            body::after {
                width: 450px; height: 450px;
                background: radial-gradient(circle, #00304D, transparent);
                bottom: -120px; right: -120px;
            }

            /* ── Popup glassmorphism ─────────────────────────────────── */
            .swal2-popup {
                font-family: 'Montserrat', sans-serif !important;
                border-radius: 26px !important;
                padding: 2.8rem 2.5rem 2.4rem !important;
                backdrop-filter: blur(28px) saturate(1.6) !important;
                -webkit-backdrop-filter: blur(28px) saturate(1.6) !important;
                border: 1px solid rgba(255, 255, 255, 0.18) !important;
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.55),
                    0 2px 8px rgba(0, 0, 0, 0.30),
                    inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
            }

            /*
             * FIX ICONO ÉXITO:
             * SweetAlert2 usa .swal2-success-circular-line-left/right y
             * .swal2-success-fix con background del mismo color que el popup
             * para "borrar" visualmente el borde del anillo circular.
             * Con glassmorphism eso crea manchas de color sólido.
             * Solución: hacerlas transparentes.
             */
            .swal2-icon.swal2-success .swal2-success-circular-line-left,
            .swal2-icon.swal2-success .swal2-success-circular-line-right,
            .swal2-icon.swal2-success .swal2-success-fix {
                background: transparent !important;
            }
            .swal2-icon.swal2-success .swal2-success-ring {
                border-color: rgba(25, 196, 107, 0.35) !important;
            }
            .swal2-icon.swal2-success [class^="swal2-success-line"] {
                background-color: #19c46b !important;
            }
            .swal2-icon.swal2-success {
                border-color: rgba(25, 196, 107, 0.35) !important;
                color: #19c46b !important;
            }

            /* Error */
            .swal2-icon.swal2-error {
                border-color: rgba(220, 53, 69, 0.40) !important;
                color: #e05c6a !important;
            }
            .swal2-icon.swal2-error [class^="swal2-x-mark-line"] {
                background-color: #e05c6a !important;
            }

            /* Advertencia */
            .swal2-icon.swal2-warning {
                border-color: rgba(255, 193, 7, 0.40) !important;
                color: #ffc107 !important;
            }

            /* Info */
            .swal2-icon.swal2-info {
                border-color: rgba(13, 202, 240, 0.40) !important;
                color: #0dcaf0 !important;
            }

            .swal2-icon { margin-bottom: 1rem !important; }

            /* ── Textos ──────────────────────────────────────────────── */
            .swal2-title {
                color: #ffffff !important;
                font-weight: 700 !important;
                font-size: 1.45rem !important;
                letter-spacing: -0.2px !important;
                margin-bottom: 0.4rem !important;
            }
            .swal2-html-container,
            .swal2-content {
                color: rgba(255, 255, 255, 0.72) !important;
                font-size: 0.95rem !important;
                line-height: 1.65 !important;
            }

            /* ── Botones ─────────────────────────────────────────────── */
            .swal2-actions { gap: 0.6rem !important; margin-top: 1.6rem !important; }

            .swal2-confirm {
                background: linear-gradient(135deg, #007832 0%, #00a844 100%) !important;
                border: none !important;
                border-radius: 10px !important;
                font-family: 'Montserrat', sans-serif !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                padding: 0.7rem 2rem !important;
                letter-spacing: 0.3px !important;
                box-shadow: 0 4px 18px rgba(0, 168, 68, 0.45) !important;
                transition: opacity 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease !important;
            }
            .swal2-confirm:hover {
                opacity: 0.88 !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 8px 24px rgba(0, 168, 68, 0.55) !important;
            }
            .swal2-confirm:active { transform: translateY(0) !important; }

            .swal2-cancel {
                background: rgba(255, 255, 255, 0.09) !important;
                border: 1px solid rgba(255, 255, 255, 0.20) !important;
                border-radius: 10px !important;
                font-family: 'Montserrat', sans-serif !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                padding: 0.7rem 2rem !important;
                color: #ffffff !important;
                transition: background 0.2s ease !important;
            }
            .swal2-cancel:hover { background: rgba(255, 255, 255, 0.17) !important; }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon:             '{$tipo}',
                title:            '{$titulo}',
                text:             '{$mensaje}',
                confirmButtonText: 'Aceptar',

                /*
                 * FIX GLASSMORPHISM:
                 * SweetAlert2 v11 inyecta inline style="background: X; color: Y"
                 * directamente en el elemento .swal2-popup, lo que tiene mayor
                 * especificidad que las clases CSS aunque uses !important.
                 * Hay que pasar los valores aquí para que el inline-style sea
                 * el correcto, no uno sólido que tape el vidrio.
                 */
                background: 'rgba(255, 255, 255, 0.10)',
                color: '#ffffff',

                /*
                 * FIX BACKDROP:
                 * backdrop: false hacía que el popup no se vea separado del fondo.
                 * Un rgba oscuro semi-transparente da profundidad visual.
                 */
                backdrop: 'rgba(0, 0, 0, 0.50)',

                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                }

            }).then(() => {
                {$redirectJs}
            });
        </script>
    </body>
    </html>
    HTML;

    exit();
}