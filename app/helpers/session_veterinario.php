<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// validamos si hay una secion activa

if (!isset($_SESSION['user'])) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
    exit();
}

// validamos que el rol sea el correspondiente

if ($_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 4) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
    exit();
}
