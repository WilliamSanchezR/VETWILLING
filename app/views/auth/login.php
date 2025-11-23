<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login VetWilling</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/loginStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

</head>

<body>

    <main>
        <div class="container">


            <div class="cont-left">
                <div class="logo">
                    <img src="<?= BASE_URL ?>/public/assets/auth/img/LOGO-POSITIVO 1.png" alt="Logo VetWilling">
                </div>

                <div class="form">
                    <h2>INICIAR SESIÓN</h2>
                    <p>Por favor, ingresa tu usuario y contraseña para acceder al sistema.</p>

                    <form class="login" action="iniciar-sesion" method="POST">
                        <div class="cont-input">
                            <i class="fa-regular fa-user"></i>
                            <input type="email" id="email" name="email" placeholder="Usuario" required>
                        </div>


                        <div class="cont-input"><i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Contraseña" required>
                        </div>

                        <a href="/vetwilling/recoverpw" id="recover">¿Olvidaste Tu Contraseña?</a>
                        <button id="btn_ingresar" type="submit">Ingresar</button>

                    </form>
                </div>
            </div>


            <div class="cont-rigth">
                <div class="info">
                    <p>¡Bienvenido a VetWilling, el sistema de gestión veterinaria que optimiza tu tiempo y mejora el
                        cuidado de tus pacientes!</p>
                    <img id="icon-datos" src="<?= BASE_URL ?>/public/assets/auth/img/clipboard.png" alt="Icono tabla de datos">
                    <img id="img_info" src="<?= BASE_URL ?>/public/assets/auth/img/veterinaria.PNG" alt="Md veterinario">
                </div>
            </div>


        </div>

    </main>

</body>

</html>