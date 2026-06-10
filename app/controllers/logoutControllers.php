<?php

class LoginController
{
    public function logout()
    {
        // Iniciar sesión para poder manipularla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Eliminar todas las variables de sesión
        session_unset();


        // Destruir la cookie que almacena la sesión (MUY IMPORTANTE)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finalmente destruir la sesión
        session_destroy();

        // Redirigir al login
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}
