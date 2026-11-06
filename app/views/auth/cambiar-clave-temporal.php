<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambia tu contraseña - VetWilling</title>

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
                <img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="Logo VetWilling">
            </div>

            <div class="form-header">
                <div class="icon-container">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h2>Cambia tu contraseña temporal</h2>
                <p>Antes de continuar, elige una contraseña propia de al menos <?= PasswordResetService::LONGITUD_MINIMA ?> caracteres.</p>
            </div>

            <div class="form">
                <form action="<?= BASE_URL ?>/cuenta/guardar-clave" method="POST">
                    <?= csrf_campo() ?>

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
                        Guardar y continuar
                    </button>
                </form>

                <div class="link-volver">
                    <a href="<?= BASE_URL ?>/cerrar-sesion">
                        <i class="bi bi-box-arrow-left"></i>
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="cont-right">
            <div class="info">
                <h3>Tu cuenta tiene una clave temporal</h3>
                <p>El administrador te asignó una contraseña temporal. Cámbiala ahora: nadie más la conocerá.</p>

                <div class="info-features">
                    <div class="feature-item">
                        <i class="bi bi-person-lock"></i>
                        <span>Solo tú conocerás la nueva</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Debe ser distinta de la temporal</span>
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
