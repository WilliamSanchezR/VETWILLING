<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/RecoveryPass.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    mostrarSweetAlert('error', 'campos vacio', 'Por favor complete todos los campos');
    exit();
}

$objModelo = new RecoveryPass();
$resultado = $objModelo->recuperarClave($email);

// Agreganos el sweer alert


if ($resultado === true) {
    mostrarSweetAlert('success', 'Nueva clave generada', 'Se ha enviado una nueva contraseña a tu correo', '/vetwilling/login');
} else {
    mostrarSweetAlert('error', 'Usuario no encontrado', 'Verifique su correo electronico e intente nuevamente');
}
exit();
