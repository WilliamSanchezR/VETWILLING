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

    // RUTAS DEL CONTROLADOR DE CALENDARIO
    case '/calendario/cargar':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/storeEvent':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/updateEvent':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/deleteEvent':
        $_GET['accion'] = 'eliminar';
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/loadEvents':
        $_GET['accion'] = 'cargar';
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/getPropietarios':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/getMascotas':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/getServicios':
        require BASE_PATH . '/app/controllers/calendarioController.php';
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


    case '/veterinario/registrar-veterinarios':
        require BASE_PATH . '/app/views/dashboard/veterinaria/registro-veterinario.php';
        break;
    case '/veterinario/registrar-pacientes':
        require BASE_PATH . '/app/views/dashboard/veterinaria/registro-pacientes-laboratorio.php';
        break;

    case '/veterinario/guardar-veterinario':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
        break;

    case '/veterinario/consultar-citas':
        require BASE_PATH . '/app/views/dashboard/veterinaria/citas.php';
        break;
    case '/veterinario/consultar-veterinarios':
        require BASE_PATH . '/app/views/dashboard/veterinaria/consultarVet.php';
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
    case 'soporte':
        require_once 'controllers/soporteControllers.php';
        $controller = new SoporteController();
        // $controller->index();
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

    case '/admin/cambiar-foto':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/admin/perfil-administrador':
        require BASE_PATH . '/app/views/dashboard/administrador/perfilAdministrador.php';
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

    case '/admin/listar-veterinarias':
        require BASE_PATH . '/app/views/dashboard/administrador/listaVeterinarias.php';
        break;

    case '/admin/editar-veterinaria':
        require BASE_PATH . '/app/views/dashboard/administrador/editarVeterinaria.php';
        break;

    case '/admin/actualizar-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/admin/eliminar-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/admin/listar-tickets':
        require BASE_PATH . '/app/views/dashboard/administrador/listaTicket.php';
        break;

    case '/admin/gestion-tickets':
        require BASE_PATH . '/app/views/dashboard/administrador/gestionTicket.php';
        break;

    case '/admin/pdf-veterinarias':
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        break;

    // ---------------------------------------PROPIETARIO-------------------------------------//

    //PARA LOS CLIENTES Y SUS RUTAS
    case '/cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashBoard.php';
        break;
    case '/cliente/mascotas':
        require BASE_PATH . '/app/views/dashboard/cliente/mascotas.php';
        break;
    case '/cliente/citas':
        require BASE_PATH . '/app/views/dashboard/cliente/citas.php';
        break;
    case '/cliente/agenda':
        require BASE_PATH . '/app/views/dashboard/cliente/agenda.php';
        break;
    case '/cliente/historial':
        require BASE_PATH . '/app/views/dashboard/cliente/historial.php';
        break;
    case '/cliente/tienda':
        require BASE_PATH . '/app/views/dashboard/cliente/tienda.php';
        break;
    case '/cliente/perfil':
        require BASE_PATH . '/app/views/dashboard/cliente/perfil.php';
        break;
    case '/cliente/configuracion':
        require BASE_PATH . '/app/views/dashboard/cliente/confi.php';
        break;
    case '/cliente/registrar-mascota':
        require BASE_PATH . '/app/views/dashboard/cliente/registro.php';
        break;
    case '/cliente/editar-mascota':
        require BASE_PATH . '/app/views/dashboard/cliente/editarMasc.php';
        break;
    case '/cliente/historial-mascota':
        require BASE_PATH . '/app/views/dashboard/cliente/historialMascota.php';
        break;
    //----------ACCIONES DEL PACIENTE---------//
    case '/cliente/actualizar':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;
    case '/cliente/guardar-mascota':
        require BASE_PATH . '/app/controllers/mascotasController.php';
        break;
    case '/cliente/actualizar-mascota':
        require BASE_PATH . '/app/controllers/mascotasController.php';
        break;
    case '/cliente/eliminar-mascota':
        require BASE_PATH . '/app/controllers/mascotasController.php';
        break;

    case '/cliente/actualizar-contrasena':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    // ---------------------------------------GENERACION DE PDFS-------------------------------------//

    case '/reporte-veterinarios':
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        reporteVeterinario();
        break;

    case '/reporte-mascotas':
        require BASE_PATH . '/app/controllers/reportesPdfControllers.php';
        reporteMascotas();
        break;
    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error404.html';
        break;


    // ---------------------------------------REPRESENTANTE-------------------------------------//
    case '/representante/dashboard':
        require BASE_PATH . '/app/views/dashboard/representante/dashboardRepresentante.php';
        break;

    case '/representante/listar-especialidades':
        require BASE_PATH . '/app/views/dashboard/representante/listaEspecialidad.php';
        break;

    case '/representante/guardar-especialidad':
        require BASE_PATH . '/app/controllers/especialidadController.php';
        break;

    case '/representante/actualizar-especialidad':
        require BASE_PATH . '/app/controllers/especialidadController.php';
        break;

    case '/representante/eliminar-especialidad':
        require BASE_PATH . '/app/controllers/especialidadController.php';
        break;

    case '/representante/registro-profesionales':
        require BASE_PATH . '/app/views/dashboard/representante/registroProfesional.php';
        break;

    case '/representante/guardar-Profesional':
        require BASE_PATH . '/app/controllers/profesionalController.php';
        break;

    case '/representante/listar-profesionales':
        require BASE_PATH . '/app/views/dashboard/representante/listaProfesionales.php';
        break;

    case '/representante/editar-profesional':
        require BASE_PATH . '/app/views/dashboard/representante/editarProfesional.php';
        break;

    case '/representante/eliminar-esp-profesional':
        require BASE_PATH . '/app/controllers/profesionalController.php';
        break;

    case '/representante/actualizar-profesional':
        require BASE_PATH . '/app/controllers/profesionalController.php';
        break;

    case '/representante/eliminar-profesional':
        require BASE_PATH . '/app/controllers/profesionalController.php';
        break;
    //---------------------------------------SERVICIOS-------------------------------------//
    case '/representante/registro-servicio':
        require BASE_PATH . '/app/views/dashboard/representante/registroServicio.php';
        break;

    case '/representante/editar-servicio':
        require BASE_PATH . '/app/views/dashboard/representante/editarServicio.php';
        break;

    case '/representante/listar-servicios':
        require BASE_PATH . '/app/views/dashboard/representante/listaServicios.php';
        break;

    case '/representante/guardar-servicio':
        require BASE_PATH . '/app/controllers/servicioController.php';
        break;

    case '/representante/actualizar-servicio':
        require BASE_PATH . '/app/controllers/servicioController.php';
        break;

    case '/representante/eliminar-servicio':
        require BASE_PATH . '/app/controllers/servicioController.php';
        break;

    //---------------------------------------SUBSERVICIOS-------------------------------------//    
    case '/representante/registro-subservicio':
        require BASE_PATH . '/app/views/dashboard/representante/registrarSubservicio.php';
        break;

    case '/representante/guardar-subservicio':
        require BASE_PATH . '/app/controllers/subservicioController.php';
        break;
        
    case '/representante/listar-subservicios':
        require BASE_PATH . '/app/views/dashboard/representante/listaSubservicio.php';
        break;

    case '/representante/editar-subservicio':
        require BASE_PATH . '/app/views/dashboard/representante/editarSubservicio.php';
        break;

    case '/representante/actualizar-subservicio':
        require BASE_PATH . '/app/controllers/subservicioController.php';
        break;

    case '/representante/eliminar-subservicio':
        require BASE_PATH . '/app/controllers/subservicioController.php';
        break;
        

}
