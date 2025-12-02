<?php

session_start();

// validamos si hay una secion activa

if (!isset($_SESSION['user'])) {
    header('Location: /vetwilling/login');
    exit();
}


?>