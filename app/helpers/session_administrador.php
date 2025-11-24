<?php

session_start();

// validamos si hay una secion activa

if (!isset($_SESSION['user'])) {
    header('Location: /vetwilling/login');
    exit();
}

// validamos que el rol sea el correspondiente

if ($_SESSION['user']['rol'] != 1) {
    header('Location: /vetwilling/login');
    exit();
}
