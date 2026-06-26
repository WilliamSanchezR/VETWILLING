<?php

require_once BASE_PATH . '/app/helpers/session_helper.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

redirectIfNoSession('/login');

$idUsuario      = (int)getCurrentUserId();
$notifModel     = new Notificacion();
$notificaciones = $notifModel->listarParaUsuario($idUsuario, 100);
$totalNoLeidas  = $notifModel->contarNoLeidas($idUsuario);

function iconoNotificacion(string $tipo): string {
    return match($tipo) {
        'cita'             => 'bi-calendar-check',
        'vacuna'           => 'bi-syringe',
        'seguimiento'      => 'bi-activity',
        'acceso_historial' => 'bi-file-earmark-text',
        'general'          => 'bi-info-circle',
        default            => 'bi-bell',
    };
}

function etiquetaNotificacion(string $tipo): string {
    return match($tipo) {
        'cita'             => 'Cita',
        'vacuna'           => 'Vacuna',
        'seguimiento'      => 'Seguimiento',
        'acceso_historial' => 'Historial',
        'general'          => 'General',
        default            => 'Aviso',
    };
}

function tiempoRelativo(string $fechaCreacion): string {
    $fecha = new DateTime($fechaCreacion);
    $diff  = time() - $fecha->getTimestamp();
    if ($diff < 60)      return 'Hace un momento';
    if ($diff < 3600)    return 'Hace ' . floor($diff / 60)    . ' min';
    if ($diff < 86400)   return 'Hace ' . floor($diff / 3600)  . ' h';
    if ($diff < 604800)  return 'Hace ' . floor($diff / 86400) . ' días';
    return $fecha->format('d/m/Y');
}

require BASE_PATH . '/app/views/dashboard/cliente/notificaciones.php';