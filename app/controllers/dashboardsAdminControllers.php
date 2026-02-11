<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/DashboardsAdmin.php';

// Funcion para obtener la informacion de la veterinaria
function getTotalVeterinarias()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getTotalVeterinarias();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta veterinaria', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}