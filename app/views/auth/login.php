<?php
require_once BASE_PATH . '/app/helpers/csrf_helper.php';

// Mensaje de error que deja el controlador al fallar el inicio de sesión (se muestra una sola vez)
csrf_iniciar_sesion();
$flash = $_SESSION['login_flash'] ?? null;
unset($_SESSION['login_flash']);

$emailPrevio = htmlspecialchars($flash['email'] ?? '', ENT_QUOTES, 'UTF-8');
$hayError = $flash !== null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - VetWilling</title>
    <meta name="description" content="Ingresa a VetWilling, el sistema de gestión para tu clínica veterinaria.">
    <meta name="theme-color" content="#0A932C">
    <meta name="robots" content="noindex">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Open+Sans:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/loginStyle.css">
</head>

<body>
    <main class="auth"<?= $hayError ? ' data-error="1"' : '' ?>>
        <div class="auth-card">

            <!-- Panel del formulario -->
            <section class="auth-form" aria-labelledby="login-title">
                <div class="auth-topbar">
                    <a href="<?= BASE_URL ?>/" class="auth-back">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>Volver al inicio
                    </a>
                    <a href="<?= BASE_URL ?>/" class="auth-logo">
                        <img src="<?= BASE_URL ?>/public/assets/auth/img/logo-vetwilling.webp" alt="VetWilling"
                            width="200" height="83">
                    </a>
                </div>

                <div class="auth-form-inner">
                    <!-- Mascota: reacciona a lo que el usuario hace en el formulario (decorativa) -->
                    <svg class="mascota" id="mascota" viewBox="0 0 200 170" aria-hidden="true" focusable="false">
                        <g class="mascota-cabeza">
                            <g class="oreja-izq">
                                <ellipse class="oreja" cx="46" cy="80" rx="24" ry="44" transform="rotate(18 46 80)" />
                            </g>
                            <g class="oreja-der">
                                <ellipse class="oreja" cx="154" cy="80" rx="24" ry="44" transform="rotate(-18 154 80)" />
                            </g>
                            <ellipse class="cara" cx="100" cy="88" rx="62" ry="58" />
                            <ellipse class="mancha" cx="100" cy="52" rx="16" ry="20" />
                            <ellipse class="hocico" cx="100" cy="118" rx="34" ry="26" />
                            <circle class="mejilla" cx="64" cy="112" r="8" />
                            <circle class="mejilla" cx="136" cy="112" r="8" />

                            <path class="ceja ceja-izq" d="M64 64 Q76 58 88 64" />
                            <path class="ceja ceja-der" d="M112 64 Q124 58 136 64" />

                            <g class="ojo">
                                <circle cx="76" cy="84" r="12" class="ojo-blanco" />
                                <g class="pupila">
                                    <circle cx="76" cy="84" r="7" />
                                    <circle cx="78.5" cy="81.5" r="2.2" class="brillo" />
                                </g>
                            </g>
                            <g class="ojo">
                                <circle cx="124" cy="84" r="12" class="ojo-blanco" />
                                <g class="pupila">
                                    <circle cx="124" cy="84" r="7" />
                                    <circle cx="126.5" cy="81.5" r="2.2" class="brillo" />
                                </g>
                            </g>

                            <ellipse class="nariz" cx="100" cy="104" rx="11" ry="8" />
                            <path class="linea-boca" d="M100 112 V119" />
                            <path class="boca boca-normal" d="M88 119 Q100 129 112 119" />
                            <path class="boca boca-triste" d="M88 128 Q100 118 112 128" />
                            <g class="boca boca-feliz">
                                <path d="M84 117 Q100 140 116 117 Z" class="boca-abierta" />
                                <ellipse cx="100" cy="131" rx="8" ry="9" class="lengua" />
                            </g>
                        </g>

                        <g class="pata pata-izq">
                            <ellipse cx="76" cy="86" rx="23" ry="19" />
                            <path d="M68 74 v8 M76 72 v9 M84 74 v8" />
                        </g>
                        <g class="pata pata-der">
                            <ellipse cx="124" cy="86" rx="23" ry="19" />
                            <path d="M116 74 v8 M124 72 v9 M132 74 v8" />
                        </g>
                    </svg>

                    <header class="auth-header">
                        <h1 id="login-title">Iniciar sesión</h1>
                        <p>Ingresa con tus credenciales para continuar.</p>
                    </header>

                    <?php if ($hayError): ?>
                        <div class="auth-alert auth-alert--<?= $flash['tipo'] === 'warning' ? 'warning' : 'error' ?>"
                            role="alert" id="login-error">
                            <i class="bi <?= $flash['tipo'] === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill' ?>"
                                aria-hidden="true"></i>
                            <span><?= htmlspecialchars($flash['mensaje'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endif; ?>

                    <form class="login" action="<?= BASE_URL ?>/iniciar-sesion" method="POST" novalidate>
                        <?= csrf_campo() ?>

                        <div class="field">
                            <label for="correo">Correo electrónico</label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input type="email" id="correo" name="email" value="<?= $emailPrevio ?>"
                                    placeholder="tucorreo@ejemplo.com" autocomplete="username" inputmode="email"
                                    autocapitalize="none" spellcheck="false" required
                                    aria-describedby="correo-error<?= $hayError ? ' login-error' : '' ?>"
                                    <?= $emailPrevio === '' ? 'autofocus' : '' ?>>
                                <i class="bi bi-check-circle-fill valid-icon" aria-hidden="true"></i>
                            </div>
                            <p class="field-error" id="correo-error" hidden></p>
                        </div>

                        <div class="field">
                            <div class="field-label-row">
                                <label for="clave">Contraseña</label>
                                <a href="<?= BASE_URL ?>/recoverpw" class="auth-link">¿Olvidaste tu contraseña?</a>
                            </div>
                            <div class="input-wrap">
                                <i class="bi bi-lock" aria-hidden="true"></i>
                                <input type="password" id="clave" name="password" placeholder="Tu contraseña"
                                    autocomplete="current-password" required
                                    aria-describedby="clave-error caps-aviso<?= $hayError ? ' login-error' : '' ?>"
                                    <?= $emailPrevio !== '' ? 'autofocus' : '' ?>>
                                <button type="button" class="toggle-clave" id="toggleClave"
                                    aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="field-error" id="clave-error" hidden></p>
                            <p class="field-hint" id="caps-aviso" hidden>
                                <i class="bi bi-capslock-fill" aria-hidden="true"></i>Bloq Mayús está activado
                            </p>
                        </div>

                        <button id="btn_ingresar" type="submit" class="btn-primary">
                            <span class="btn-text">Ingresar</span>
                            <span class="spinner" aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="auth-footer">
                        ¿Aún no tienes cuenta?
                        <a href="<?= BASE_URL ?>/registro" class="auth-link">Solicita tu prueba gratis</a>
                    </p>
                </div>
            </section>

            <!-- Panel visual -->
            <aside class="auth-visual" aria-hidden="true">
                <div class="bg-carousel">
                    <div class="bg-slide active"
                        style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/perrito.webp')"></div>
                    <div class="bg-slide"
                        style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/michu.webp')"></div>
                    <div class="bg-slide"
                        style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/vaquita.webp')"></div>
                    <div class="bg-slide"
                        style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/lorito.webp')"></div>
                </div>

                <div class="welcome-box">
                    <h2>¡Bienvenido a VetWilling!</h2>
                    <p>El sistema de gestión veterinaria que optimiza tu tiempo y mejora el cuidado de tus pacientes.</p>
                    <ul>
                        <li><i class="bi bi-calendar-check"></i>Citas y agenda</li>
                        <li><i class="bi bi-file-medical"></i>Historias clínicas</li>
                        <li><i class="bi bi-person-hearts"></i>Portal de clientes</li>
                    </ul>
                </div>

                <div class="carousel-dots">
                    <button type="button" tabindex="-1" class="active"></button>
                    <button type="button" tabindex="-1"></button>
                    <button type="button" tabindex="-1"></button>
                    <button type="button" tabindex="-1"></button>
                </div>
            </aside>
        </div>
    </main>

    <script src="<?= BASE_URL ?>/public/assets/auth/js/login.js"></script>
</body>

</html>
