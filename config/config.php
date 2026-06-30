<?php

$projectRoot = dirname(__DIR__);

// Carga variables .env si existe el archivo y la libreria esta disponible.
$autoloadPath = $projectRoot . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
	require_once $autoloadPath;
	if (class_exists('Dotenv\\Dotenv')) {
		$dotenvPath = $projectRoot . '/.env';
		if (file_exists($dotenvPath)) {
			$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
			$dotenv->safeLoad();
		}
	}
}

if (!function_exists('env_value')) {
	function env_value($key, $default = '')
	{
		$fromEnv = $_ENV[$key] ?? null;
		if ($fromEnv !== null && $fromEnv !== '') {
			return $fromEnv;
		}

		$fromServer = $_SERVER[$key] ?? null;
		if ($fromServer !== null && $fromServer !== '') {
			return $fromServer;
		}

		$fromGetEnv = getenv($key);
		if ($fromGetEnv !== false && $fromGetEnv !== '') {
			return $fromGetEnv;
		}

		return $default;
	}
}

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
// URL BASE DEL PROYECTO — detección automática de entorno
// ═══════════════════════════════════════════════════════════════════════════
// Usa dirname(SCRIPT_NAME) para detectar automáticamente la carpeta base
// sin necesidad de variables de entorno ni configuración manual.
//
// Cómo funciona:
//   XAMPP local  → SCRIPT_NAME = /vetwilling/index.php
//                  BASE_URL    = http://localhost/vetwilling
//
//   Hostinger    → SCRIPT_NAME = /index.php
//                  BASE_URL    = https://vetwilling.com
//
// También soporta proxies/CDN (Hostinger LiteSpeed) con X-Forwarded-Proto.
$_vw_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
$_vw_proto  = $_vw_https ? 'https' : 'http';
$_vw_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseFolder = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');
define('BASE_URL', $_vw_proto . '://' . $_vw_host . $baseFolder);
unset($_vw_https, $_vw_proto, $_vw_host);

// ── Versión de assets ─────────────────────────────────────────────────────
// Incrementar este valor cada vez que se suban cambios de CSS o JS a
// producción. Eso fuerza al navegador y a LiteSpeed Cache a descargar
// los archivos nuevos en lugar de servir la copia cacheada.
define('APP_VERSION', '1.0.0');


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
define('BASE_PATH', $projectRoot);

// Token de Mercado Pago (opcional).
// Prioriza variable de entorno MP_ACCESS_TOKEN y usa este valor solo como respaldo.
define('MP_ACCESS_TOKEN', env_value('MP_ACCESS_TOKEN', ''));


// ═══════════════════════════════════════════════════════════════════════════
// BOOTSTRAP GLOBAL (sesión + i18n + helpers)
// ═══════════════════════════════════════════════════════════════════════════
require_once $projectRoot . '/bootstrap.php';