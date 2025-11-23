<?php

// Importamos las dependencias

// require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Perfil.php';

function mostrarPerfilVeteri($id)
{

    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfilVeteri($id);

    return $usuario;
}
