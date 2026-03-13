<?php

// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN GENERAL DEL PROYECTO
// Este archivo centraliza configuraciones globales para que el sistema
// funcione correctamente tanto en entorno LOCAL como en PRODUCCIÓN.
// ═══════════════════════════════════════════════════════════════════════════


// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE ZONA HORARIA
// ═══════════════════════════════════════════════════════════════════════════
// Define la zona horaria del servidor para evitar errores en fechas,
// registros de base de datos, logs o funciones como date() y time().
// Debe coincidir con la ubicación del sistema.
date_default_timezone_set('America/Bogota');


// ═══════════════════════════════════════════════════════════════════════════
// DETECTAR EL PROTOCOLO DEL SITIO (HTTP o HTTPS)
// ═══════════════════════════════════════════════════════════════════════════
// Algunos servidores usan HTTPS y otros HTTP.
// Esta línea detecta automáticamente cuál está usando el servidor.
$protocolo = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';


// ═══════════════════════════════════════════════════════════════════════════
// OBTENER EL HOST DEL SERVIDOR
// ═══════════════════════════════════════════════════════════════════════════
// Obtiene el dominio o dirección del servidor actual.
// Ejemplos:
// localhost
// vetwilling.com
// www.vetwilling.com
$host = $_SERVER['HTTP_HOST'];


// ═══════════════════════════════════════════════════════════════════════════
// DETECTAR SI EL PROYECTO SE EJECUTA EN LOCALHOST
// ═══════════════════════════════════════════════════════════════════════════
// Si la dirección IP del servidor es 127.0.0.1 o ::1 significa que
// el proyecto está ejecutándose en el entorno local del desarrollador.
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);


// ═══════════════════════════════════════════════════════════════════════════
// DEFINIR CARPETA BASE DEL PROYECTO
// ═══════════════════════════════════════════════════════════════════════════
// En desarrollo local el proyecto se encuentra dentro de una carpeta
// llamada "vetwilling", por ejemplo:
//
// http://localhost/vetwilling
//
// En producción (hosting) el proyecto está en la raíz del dominio:
//
// https://vetwilling.com
//
// Por lo tanto:
// - En LOCAL se usa "/vetwilling"
// - En PRODUCCIÓN se usa ""
$baseFolder = $isLocal ? '/vetwilling' : '';


// ═══════════════════════════════════════════════════════════════════════════
// URL BASE DEL PROYECTO
// ═══════════════════════════════════════════════════════════════════════════
// Construye la URL completa del proyecto combinando:
//
// protocolo + dominio + carpeta base
//
// Ejemplos:
//
// LOCAL
// http://localhost/vetwilling
//
// PRODUCCIÓN
// https://vetwilling.com
//
// Esta constante se usa para generar rutas dinámicas en todo el sistema.
define('BASE_URL', $protocolo . $host . $baseFolder);


// ═══════════════════════════════════════════════════════════════════════════
// RUTA BASE DEL SISTEMA EN EL SERVIDOR
// ═══════════════════════════════════════════════════════════════════════════
// Obtiene la ruta física del proyecto en el servidor.
// Se usa principalmente para:
//
// require
// include
// cargar archivos
// importar controladores o helpers
//
// Ejemplo:
// C:\xampp\htdocs\vetwilling
define('BASE_PATH', dirname(__DIR__));