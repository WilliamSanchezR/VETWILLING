<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login VetWilling</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
                        <div class="cont-input"> <i class="bi bi-person"></i>
                            <input type="text" id="correo" name="correo" placeholder="Correo" required>
                        </div>

                        <div class="cont-input"> <i class="bi bi-lock"></i>

                            <input type="password" id="clave" name="password" placeholder="Contraseña" required>
                        </div>

                        <a href="<?= BASE_URL ?>/recuperar" id="recover">¿Olvidaste Tu Contraseña?</a>
                        <button id="btn_ingresar" type="submit">Ingresar</button>

                    </form>
                </div>
            </div>


            <div class="welcome-box">
                <!-- <img src="/assets/website/img/FAVICON.png" width="50" alt=""> -->
                <h1 class="welcome-title">¡Bienvenido a VetWilling¡</h1>
                <p class="welcome-sub">
                    el sistema de gestión veterinaria que optimiza tu tiempo y mejora el
                    cuidado de tus pacientes.
                </p>
            </div>

            <div class="cont-rigth">
                <div class="bg-carousel" aria-hidden="true">
                    <div class="bg-slide active"
                        style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/muuuu.jpg')"></div>
                    <div class="bg-slide" style="back<?= BASE_URL ?>/publicground-image:url('<?= BASE_URL ?>/public/assets/auth/img/chadis.jpg')"></div>
                    <div class="bg-slide" style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/ponejo.jpg')"></div>
                    <div class="bg-slide" style="background-image:url('<?= BASE_URL ?>/public/assets/auth/img/macu.jpg')"></div>
                </div>
            </div>


        </div>

    </main>

</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const slides = document.querySelectorAll(".bg-slide");
        let index = 0;

        setInterval(() => {
            slides[index].classList.remove("active");

            index = (index + 1) % slides.length;

            slides[index].classList.add("active");
        }, 5500); // Cambia cada 5.5 segundos
    });
</script>

</html>