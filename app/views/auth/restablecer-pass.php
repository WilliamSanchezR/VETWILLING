<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - VetWilling</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Estilos (comparte diseño con recuperar contraseña) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/estilosRecuperarContraseña.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
</head>

<body>
    <div class="container">
        <!-- Columna izquierda -->
        <div class="cont-left">
            <div class="logo">
                <a href="<?= BASE_URL ?>/"><img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="Logo VetWilling"></a>
            </div>

            <div class="form-header">
                <div class="icon-container">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h2>Crea una nueva contraseña</h2>
                <p>Elige una contraseña de al menos <?= PasswordResetService::LONGITUD_MINIMA ?> caracteres.</p>
            </div>

            <div class="form">
                <form action="<?= BASE_URL ?>/restablecer/guardar" method="POST">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="cont-input">
                        <input
                            type="password"
                            name="password"
                            placeholder="Nueva contraseña"
                            minlength="<?= PasswordResetService::LONGITUD_MINIMA ?>"
                            autocomplete="new-password"
                            required>
                        <i class="bi bi-lock-fill"></i>
                    </div>

                    <div class="cont-input">
                        <input
                            type="password"
                            name="password_confirm"
                            placeholder="Confirmar contraseña"
                            minlength="<?= PasswordResetService::LONGITUD_MINIMA ?>"
                            autocomplete="new-password"
                            required>
                        <i class="bi bi-lock-fill"></i>
                    </div>

                    <button class="btn-submit" type="submit">
                        <i class="bi bi-check-circle-fill"></i>
                        Guardar contraseña
                    </button>
                </form>

                <div class="link-volver">
                    <a href="<?= BASE_URL ?>/login">
                        <i class="bi bi-arrow-left"></i>
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="cont-right">
            <div class="info">
                <h3>Ya casi terminas</h3>
                <p>Tu nueva contraseña reemplazará la anterior y el enlace dejará de funcionar.</p>

                <div class="info-features">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Enlace de un solo uso</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-clock"></i>
                        <span>Válido por <?= PasswordResetService::MINUTOS_VIGENCIA ?> minutos</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-key"></i>
                        <span>Mínimo <?= PasswordResetService::LONGITUD_MINIMA ?> caracteres</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
