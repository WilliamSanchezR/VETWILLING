<?php

require __DIR__ . '/../models/Perfil.php';

function mostrarPerfil($id)
{
    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfil($id);

    return $usuario;
}

function obtenerEstadisticasPerfil(int $id_usuario): array
{
    $objPerfil = new Perfil();
    return $objPerfil->obtenerEstadisticasPropietario($id_usuario);
}
