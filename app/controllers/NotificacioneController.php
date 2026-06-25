<?php

require_once BASE_PATH . '/app/helpers/session_helper.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

redirectIfNoSession('/login');

$idUsuario      = (int)getCurrentUserId();

$notifModel     = new Notificacion();
$notificaciones = $notifModel->listarParaUsuario($idUsuario, 100);
$totalNoLeidas  = $notifModel->contarNoLeidas($idUsuario);

require BASE_PATH . '/app/views/dashboard/cliente/notificaciones.php';