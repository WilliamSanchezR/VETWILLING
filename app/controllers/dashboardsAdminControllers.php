<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/DashboardsAdmin.php';


// Funcion para obtener la informacion de los usuarios
function getTotalUsuarios()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getTotalUsuarios();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta usuarios', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

// Funcion para traer los usuarios registrados en el ultimo mes
function getUsuariosRegistradosUltimoMes()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getUsuariosRegistradosUltimoMes();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta usuarios ultimo mes', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

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

// Funcion para traer el porcentaje de veterinarias activas vs inactivas
function getPorcentajeVeterinarias()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getPorcentajeVeterinarias();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta porcentaje veterinarias', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

// Funcion para obtener la informacion de profesionales
function getTotalProfesionales()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getTotalProfesionales();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta profesionales', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

 // Funcion para traer los profesionales agregados en el ultimo mes
function getProfesionalesUltimoMes()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getProfesionalesRegistradosUltimoMes();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta profesionales ultimo mes', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

// Funcion para traer el porcentaje de profesionales activos vs inactivos
function getPorcentajeProfesionales()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getPorcentajeProfesionales();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta porcentaje profesionales', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

    // Funcion para obtener los Usuarios que usaron el sistema en el ultimo mes
function getUsuariosUltimoMes()
{
    $dashboardAdminModel = new DashboardsAdmin();
    $info = $dashboardAdminModel->getUsuariosUltimoMes();

    if ($info === false) {
        mostrarSweetAlert('error', 'consulta usuarios ultimo mes', 'Error al obtener la información del dashboard.');
        exit();
    }
    return $info;
}

