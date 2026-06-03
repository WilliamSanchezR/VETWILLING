<?php
// Helper para iniciar sesión y validar usuario paciente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: /vetwilling/login');
    exit();
}
