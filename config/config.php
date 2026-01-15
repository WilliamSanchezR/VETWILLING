<?php

// Este archivo se creo para crear menor complicacion al momento de subier el proyecto a un hosting

// ═══════════════════════════════════════════════════════════════════════════
//  CONFIGURACIÓN DE ZONA HORARIA
// ═══════════════════════════════════════════════════════════════════════════
// Establece la zona horaria para evitar problemas con fechas y horas
// Ajusta según tu ubicación: América/Bogotá, América/Mexico_City, etc.
date_default_timezone_set('America/Bogota');

// Configuracion global del proyecto

// Detectar protocolo (http o https)

$protocolo = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

// nombre de la carpeta del proyecto en local

$baseFolder = '/vetwilling';

// Host casual

$host = $_SERVER['HTTP_HOST'];

// url base dianmica (funcion en local y hosting)

define('BASE_URL', $protocolo . $host . $baseFolder);

// Ruta base del proyecto (para require o include)

define('BASE_PATH', dirname(__DIR__));
