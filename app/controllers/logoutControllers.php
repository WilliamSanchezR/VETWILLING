
<?php


class LoginController
{
    public function logout()
    {
        session_start();

        // Destruir TODA la sesión
        session_unset();
        session_destroy();

        // Redirigir al login
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}