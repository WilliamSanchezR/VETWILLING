<?php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/RecoveryPass.php';

$email = $_POST['email'] ?? '';



    if (empty($email)) {
        mostrarSweetAlert('error', 'Campo vacío', 'Por favor completa el campo correspondiente');
        exit();
    }


    $objModelo = new RecoveryPass();
    $resultado = $objModelo->recuperarClave($email);

    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Nueva Clave Generada',
            'Se ha enviado una nueva clave a tu correo electrónico',
            BASE_URL . '/login'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Usuario No Encontrado',
            'Verifique su correo electrónico e intente nuevamente'
        );
    }
    exit();