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

    // RUTA PARA CERRA SESION

    case '/logout':
        require BASE_PATH . '/app/controllers/logoutControllers.php';
        $controller = new LoginController();
        $controller->logout();
        break;

    // ---------------------------------------VETERINARIO-------------------------------------//

    case '/veterinaria/dashboard':
        require BASE_PATH . '/app/views/dashboard/veterinaria/dashBoard.php';
        break;

    case '/veterinaria/seguimientos':
        require BASE_PATH . '/app/views/dashboard/veterinaria/seguimientos.php';
        break;

    case '/veterinaria/calendario':
        require BASE_PATH . '/app/views/dashboard/veterinaria/calendario.php';
        break;

    case '/veterinaria/gestion_clinica':
        require BASE_PATH . '/app/views/dashboard/veterinaria/gestion-clinica.php';
        break;

    case '/veterinaria/laboratorio':
        require BASE_PATH . '/app/views/dashboard/veterinaria/laboratorio.php';
        break;

    case '/veterinaria/recetas':
        require BASE_PATH . '/app/views/dashboard/veterinaria/recetas.php';
        break;

    case '/veterinaria/reportes':
        require BASE_PATH . '/app/views/dashboard/veterinaria/reportes.php';
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

    case '/veterinario/reporte-veterinarios': //esta ruta hace reporte de instructores en pdf
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        break;

    case '/veterinario/consultar-perfil':
        require BASE_PATH . '/app/views/dashboard/veterinaria/perfil.php';
        break;


    // ---------------------------------------ADMINISTRADOR-------------------------------------//

    case '/admin/dashBoard':
        require BASE_PATH . '/app/views/dashboard/administrador/dashBoard.php';
        break;

    case '/admin/registro-usuario':
        require BASE_PATH . '/app/views/dashboard/administrador/registroUsuario.php';
        break;

    case '/admin/listar-usuarios':
        require BASE_PATH . '/app/views/dashboard/administrador/listaUsuarios.php';
        break;

    case '/admin/editar-usuario': // este es para pinatar los dato en el formulario
        require BASE_PATH . '/app/views/dashboard/administrador/editarUsuario.php';
        break;

    case '/admin/guardar-usuario':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/admin/actualizar-usuario':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/admin/eliminar-usuario':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/admin/actualizar-contrasena':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/admin/registro-veterinaria':
        require BASE_PATH . '/app/views/dashboard/administrador/registroVeterinaria.php';
        break;

    case '/admin/guardar-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/admin/perfil-administrador':
        require BASE_PATH . '/app/views/dashboard/administrador/perfilAdministrador.php';
        break;




    // ---------------------------------------PROPIETARIO-------------------------------------//

    //PARA LOS CLIENTES Y SUS RUTAS
    case '/Cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashBoard.php';
        break;
    case '/Cliente/mascotas':
        require BASE_PATH . '/app/views/dashboard/cliente/mascotas.php';
        break;
    case '/Cliente/citas':
        require BASE_PATH . '/app/views/dashboard/cliente/citas.php';
        break;
    case '/Cliente/agenda':
        require BASE_PATH . '/app/views/dashboard/cliente/agenda.php';
        break;
    case '/Cliente/historial':
        require BASE_PATH . '/app/views/dashboard/cliente/historial.php';
        break;
    case '/Cliente/tienda':
        require BASE_PATH . '/app/views/dashboard/cliente/tienda.php';
        break;
    case '/Cliente/perfil':
        require BASE_PATH . '/app/views/dashboard/cliente/perfil.php';
        break;
    case '/Cliente/configuracion':
        require BASE_PATH . '/app/views/dashboard/cliente/confi.php';
        break;
    case '/Cliente/registrar-mascota':
        require BASE_PATH . '/app/views/dashboard/cliente/registro.php';
        break;

    //----------ACCIONES DEL PACIENTE---------//
    case '/Cliente/actualizar':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;


    // ---------------------------------------GENERACION DE PDFS-------------------------------------//

    case '/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        reporteVeterinario();

        //     break;

    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error404.html';
        break;
}
