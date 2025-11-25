<?php

session_start();

// validamos si hay una secion activa

if (!isset($_SESSION['user'])) {
    header('Location: /vetwilling/login');
    exit();
}

// validamos que el rol sea el correspondiente

if ($_SESSION['user']['id_rol'] != 3) {
    header('Location: /vetwilling/login');
    exit();
}
