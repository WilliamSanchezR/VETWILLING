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
// DETECTAR EL PROTOCOLO DEL SITIO (HTTP o HTTPS)
// ═══════════════════════════════════════════════════════════════════════════
// Algunos servidores usan HTTPS y otros HTTP.
// También soporta proxys/túneles como ngrok mediante X-Forwarded-Proto.
$httpsActivo = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$protocolo = ($httpsActivo || $forwardedProto === 'https') ? 'https://' : 'http://';


// ═══════════════════════════════════════════════════════════════════════════
// OBTENER EL HOST DEL SERVIDOR
// ═══════════════════════════════════════════════════════════════════════════
// Obtiene el dominio o dirección del servidor actual.
// Ejemplos:
// localhost
// vetwilling.com
// www.vetwilling.com
// Fallback para ejecución por CLI (cron)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';


// ═══════════════════════════════════════════════════════════════════════════
// DETECTAR SI EL PROYECTO SE EJECUTA EN LOCALHOST
// ═══════════════════════════════════════════════════════════════════════════
// Si la dirección IP del servidor es 127.0.0.1 o ::1 significa que
// el proyecto está ejecutándose en el entorno local del desarrollador.
// En CLI no existe REMOTE_ADDR, por lo que asumimos entorno local.
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$isLocal = in_array($remoteAddr, ['127.0.0.1', '::1']);


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
$baseFolder = env_value('BASE_FOLDER', $isLocal ? '/vetwilling' : '');


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
$baseUrlDetectada = $protocolo . $host . $baseFolder;
$baseUrlPublica = rtrim((string) env_value('APP_PUBLIC_URL', ''), '/');
define('BASE_URL', $baseUrlPublica !== '' ? $baseUrlPublica : $baseUrlDetectada);


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
// require_once $projectRoot . '/bootstrap.php';