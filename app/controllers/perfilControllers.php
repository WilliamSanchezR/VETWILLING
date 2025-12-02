<?php

// Importamos las dependencias

// require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Perfil.php';

function mostrarPerfil($id)
{

    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfil($id);

    return $usuario;
}