<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - VetWilling</title>

    <!-- font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


    <!-- Estilos -->
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
                    <i class="bi bi-key-fill"></i>
                </div>
                <h2>Recupera tu contraseña</h2>
                <p>Ingresa tu correo electrónico y te enviaremos una contraseña nueva.</p>
            </div>

            <div class="form">
                <form id="formRecuperar" action="generar-clave" method="POST">
                    <div class="cont-input">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Correo electrónico"
                            required>
                        <i class="bi bi-envelope-fill"></i>
                    </div>

                    <button class="btn-submit" type="submit" id="btnEnviar">
                        <i class="bi bi-send-fill"></i>
                        Enviar contraseña de recuperación
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
                <div class="info-icon">🔐</div>
                <h3>¿Olvidaste tu contraseña?</h3>
                <p>No te preocupes, es normal. Te ayudaremos a recuperar el acceso a tu cuenta de forma rápida y segura.</p>

                <div class="info-features">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Proceso seguro y encriptado</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-clock"></i>
                        <span>Recuperación en minutos</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-envelope-check"></i>
                        <span>Enlace enviado a tu correo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>