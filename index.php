<?php

// index.php - Router principal

require_once __DIR__ . '/config/config.php';

// obtener la URL actual (por ejemplo: /nexus_center/login)

$requestURI = $_SERVER['REQUEST_URI'];

// Quitar le prefijo de la carpeta del proyecto

$request = str_replace('/vetwilling', '', $requestURI);

// Quitar parametros tipo ?id=123

$request = strtok($request, '?');

// Quitar la barra final (si existe)

$request = rtrim($request, '/');

// Si la ruta queda vacia, se interpreta como "/"

if ($request === '') $request = '/';

// Entutamiento basico

switch ($request) {

    case '/':
        require BASE_PATH . '/app/views/website/index.html';
        break;

    // Inicio rutas necesarias para le login

    case '/login':
        require BASE_PATH . '/app/views/auth/login.php';
        break;

    case '/iniciar-sesion':
        require BASE_PATH . '/app/controllers/loginControllers.php';
        break;

    case '/generar-clave':
        require BASE_PATH . '/app/controllers/recoveryPassControllers.php';
        break;

    // Restablecer contraseña

    case '/recoverpw':
        require BASE_PATH . '/app/views/auth/recover-pass.php';
        break;

    // Fin rutas necesarias para el login

    // ---------------------------------------VETERINARIO-------------------------------------//

    case '/veterinaria/dashboard':
        require BASE_PATH . '/app/views/dashboard/veterinaria/dashBoard.php';
        break;

    case '/veterinario/registrar-veterinario':
        require BASE_PATH . '/app/views/dashboard/veterinaria/registro-veterinario.php';
        break;

    case '/veterinario/guardar-veterinario':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
        break;

    case '/veterinario/consultar-veterinario':
        require BASE_PATH . '/app/views/dashboard/veterinaria/citas.php';
        break;

    case '/veterinario/editar-veterinario':
        require BASE_PATH . '/app/views/dashboard/veterinaria/editar-veterinario.php';
        break;

    case '/veterinario/actualizar-veterinario':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
        break;

    case '/veterinario/eliminar-veterinario':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
        break;

    case '/veterinario/consultar-perfil':
        require BASE_PATH . '/app/views/dashboard/veterinaria/perfil.php';
        break;


    // ---------------------------------------ADMINISTRADOR-------------------------------------//

    case '/administrador/dashBoard':
        require BASE_PATH . '/app/views/dashboard/admin/dashBoard.php';
        break;


    // ---------------------------------------PROPIETARIO-------------------------------------//

    case '/cliente/dashBoard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashBoard.php';
        break;

    // ---------------------------------------GENERACION DE PDFS-------------------------------------//

    case '/generarPDF/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        reportesPdfController();
        break;

    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error404.html';
        break;
}
