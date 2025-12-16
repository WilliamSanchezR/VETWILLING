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

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/estilosRecuperarContraseña.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
</head>

<body>
    <main>
        <div class="container">
            <!-- Columna izquierda -->
            <div class="cont-left">
                <div class="logo">
                    <img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="Logo de VetWilling">
                </div>

                <div class="form">
                    <h2>Recupera tu contraseña</h2>
                    <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

                    <form class="login" id="formRecuperar" action="generar-clave" method="POST">
                        <div class="cont-input">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
                        </div>
                      

                        <button id="btn_ingresar" type="submit">Enviar enlace</button>
                    </form>

                    <a id="recover" href="login.html">Volver al inicio de sesión</a>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="cont-rigth">
                <div class="info">
                    <p>“En VetWilling te ayudamos a recuperar el acceso a tu cuenta para que sigas brindando bienestar a tus pacientes.”</p>
                </div>
            </div>
        </div>
    </main>

    
</body>

</html>