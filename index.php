<?php

// index.php - Router principal

require_once __DIR__ . '/config/config.php';

// obtener la URL actual (por ejemplo: /nexus_center/login)

$requestURI = $_SERVER['REQUEST_URI'];

// Quitar le prefijo de la carpeta del proyecto

#$request = str_replace('/vetwilling', '', $requestURI);

// Elimina de la URL el nombre de la carpeta base del proyecto (si existe).
// Esto es necesario porque en entorno local el proyecto se ejecuta dentro
// de la carpeta "/vetwilling" (ej: localhost/vetwilling/login), mientras
// que en producción se ejecuta desde la raíz del dominio (ej: vetwilling.com/login).
// De esta manera el router puede trabajar con rutas limpias como "/login".
$request = str_replace($baseFolder, '', $requestURI);

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

    case '/login/seleccionarVeterinaria':
        require BASE_PATH . '/app/views/auth/seleccionarVeterinaria.php';
        break;

    case '/login/ingresarVeterinaria':
        require BASE_PATH . '/app/controllers/loginControllers.php';
        break;

    // Restablecer contraseña

    case '/recoverpw':
        require BASE_PATH . '/app/views/auth/recover-pass.php';
        break;

    // Fin rutas necesarias para el login

    // RUTA PARA CERRA SESION
    case '/cerrar-sesion':
        require BASE_PATH . '/app/controllers/cerrarSesion.php';
        break;

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

    // API de seguimientos
    case '/veterinaria/api/seguimientos':
        require BASE_PATH . '/app/controllers/seguimientosController.php';
        break;

    case '/veterinaria/calendario':
        require BASE_PATH . '/app/views/dashboard/veterinaria/calendario.php';
        break;

    case '/veterinaria/mi-agenda':
        require BASE_PATH . '/app/views/dashboard/veterinaria/agenda-veterinario.php';
        break;

    case '/veterinario/notificaciones':
    case '/veterinaria/notificaciones':
        require BASE_PATH . '/app/views/dashboard/veterinaria/notificaciones.php';
        break;

    case '/veterinario/api/notificaciones':
    case '/veterinaria/api/notificaciones':
        require BASE_PATH . '/app/controllers/NotificacioneController.php';
        break;

    case '/veterinario/agregar-disponibilidad-agenda':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    case '/veterinario/editar-disponibilidad-agenda':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    case '/veterinario/eliminar-disponibilidad-agenda':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;
    case '/representante/suscripcion':
        require BASE_PATH . '/app/views/dashboard/representante/suscripcion.php';
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

    case '/calendario/getVeterinarios':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/calendario/getSubservicios':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/disponibilidad/horarios':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    // ╔═══════════════════════════════════════════════════════════════════════╗
    // ║  RUTAS PARA RFS 36: CANCELACIÓN DE CITAS                             ║
    // ╚═══════════════════════════════════════════════════════════════════════╝
    case '/cancelarCita':
        require BASE_PATH . '/app/controllers/calendarioController.php';
        break;

    case '/veterinaria/gestion-pacientes':
        require BASE_PATH . '/app/views/dashboard/veterinaria/gestion-pacientes.php';
        break;

    case '/veterinaria/laboratorio':
        require BASE_PATH . '/app/views/dashboard/veterinaria/laboratorio.php';
        break;

    case '/veterinaria/api/laboratorio':
        require BASE_PATH . '/app/controllers/laboratorioController.php';
        break;

    case '/veterinaria/recetas':
        require BASE_PATH . '/app/views/dashboard/veterinaria/recetas.php';
        break;

    case '/veterinaria/api/recetas':
        require BASE_PATH . '/app/controllers/recetasController.php';
        break;

    case '/veterinaria/reportes':
        require BASE_PATH . '/app/views/dashboard/veterinaria/reportes.php';
        break;

    case '/veterinaria/reportes/data':
        require BASE_PATH . '/app/controllers/reportesController.php';
        break;

    case '/veterinaria/reportes/pdf':
        require BASE_PATH . '/app/controllers/reportesController.php';
        break;

    case '/veterinaria/reportes/excel':
        require BASE_PATH . '/app/controllers/reportesController.php';
        break;

    case '/veterinaria/reportes/enviar-ficha':
        require BASE_PATH . '/app/controllers/reportesController.php';
        break;

    // RUTA COMENTADA: Registrar veterinarios
    // case '/veterinario/registrar-veterinarios':
    //     require BASE_PATH . '/app/views/dashboard/veterinaria/registro-veterinario.php';
    //     break;
    case '/veterinario/registrar-pacientes':
        require BASE_PATH . '/app/views/dashboard/veterinaria/registro-pacientes.php';
        break;

    case '/veterinario/guardar-paciente':
        require BASE_PATH . '/app/controllers/registroPacienteController.php';
        break;

    case '/veterinario/pacientes-asignacion':
        require BASE_PATH . '/app/controllers/pacienteProfesionalAsignacionController.php';
        break;

    case '/veterinaria/pacientes/acciones':
        require BASE_PATH . '/app/controllers/pacientesVeterinarioController.php';
        break;

    case '/veterinaria/gestion-pacientes/pdf':
        require BASE_PATH . '/app/controllers/historialesClinicosController.php';
        break;

    case '/veterinaria/gestion-pacientes/pdf-mascota':
        require BASE_PATH . '/app/controllers/fichaClinicaPacientePdfController.php';
        break;

    case '/veterinario/guardar-veterinario':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
        break;

    case '/veterinario/consultar-citas':
        require BASE_PATH . '/app/views/dashboard/veterinaria/citas.php';
        break;
    // RUTA COMENTADA: Consultar veterinarios
    // case '/veterinario/consultar-veterinarios':
    //     require BASE_PATH . '/app/views/dashboard/veterinaria/consultarVet.php';
    //     break;
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

    case '/veterinario/actualizar-constrasena':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    case '/veterinario/cambiar-foto':
        require BASE_PATH . '/app/controllers/veterinarioController.php';
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

    case '/admin/lista-profesionales':
        require BASE_PATH . '/app/controllers/usuarioController.php';
        break;

    // RFS 24: Eliminación de registros de propietario
    case '/admin/listar-propietarios':
        require BASE_PATH . '/app/views/dashboard/administrador/listaPropietarios.php';
        break;

    case '/admin/api/propietarios':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;

    // RFS 14: Gestión de reportes — administrador
    case '/admin/reportes':
        require BASE_PATH . '/app/views/dashboard/administrador/reportesAdmin.php';
        break;
    case '/admin/reportes/data':
        $GLOBALS['_route_action'] = 'data';
        require BASE_PATH . '/app/controllers/reportesAdminController.php';
        break;
    case '/admin/reportes/pdf':
        $GLOBALS['_route_action'] = 'pdf';
        require BASE_PATH . '/app/controllers/reportesAdminController.php';
        break;
    case '/admin/reportes/excel':
        $GLOBALS['_route_action'] = 'excel';
        require BASE_PATH . '/app/controllers/reportesAdminController.php';
        break;

    // ---------------------------------------PROPIETARIO-------------------------------------//

    //PARA LOS CLIENTES Y SUS RUTAS
    // ═══════════════════════════════════════════════════════════════
    //  RUTAS DE CITAS PARA CLIENTES
    // ═══════════════════════════════════════════════════════════════

    // VISTA: Ver todas las citas del cliente
    case '/cliente/citas':
        require BASE_PATH . '/app/views/dashboard/cliente/citas.php';
        break;

    // VISTA: Agendar nueva cita
    case '/cliente/agendar-cita':
        require BASE_PATH . '/app/views/dashboard/cliente/agendarCita.php';
        break;

    // API: Listar citas del cliente
    case '/cliente/api/citas/listar':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Crear cita
    case '/cliente/api/citas/crear':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Cancelar cita
    case '/cliente/api/citas/cancelar':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Modificar cita
    case '/cliente/api/citas/modificar':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Obtener servicios
    case '/cliente/api/servicios':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Obtener subservicios
    case '/cliente/api/subservicios':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    // API: Obtener mascotas del cliente
    case '/cliente/api/mascotas':
        require BASE_PATH . '/app/controllers/citasClienteController.php';
        break;

    case '/cliente/cambiar-foto':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;




    case '/cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashBoard.php';
        break;
    case '/cliente/mascotas':
        require BASE_PATH . '/app/views/dashboard/cliente/mascotas.php';
        break;
    // case '/cliente/citas':
    //     require BASE_PATH . '/app/views/dashboard/cliente/citas.php';
    //     break;
    case '/cliente/agenda':
        require BASE_PATH . '/app/views/dashboard/cliente/agenda.php';
        break;
    // case '/cliente/agendar-cita':
    //     require BASE_PATH . '/app/views/dashboard/cliente/agendarCita.php';
    //     break;
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
    case '/cliente/vacunas-mascota':
        require BASE_PATH . '/app/views/dashboard/cliente/vacunas.php';
        break;

    case '/cliente/notificaciones':
        require BASE_PATH . '/app/views/dashboard/cliente/notificaciones.php';
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
    case '/paciente/api/notificaciones':
        require BASE_PATH . '/app/controllers/NotificacioneController.php';
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
        require BASE_PATH . '/app/views/auth/error404.php';
        break;


    // ---------------------------------------REPRESENTANTE-------------------------------------//
    case '/representante/dashboard':
        require BASE_PATH . '/app/views/dashboard/representante/dashboardRepresentante.php';
        break;

    case '/representante/veterinaria_info':
        require BASE_PATH . '/app/views/dashboard/representante/configVeterinaria.php';
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

    case '/representante/eliminar-serv-profesional':
        require BASE_PATH . '/app/controllers/profesionalController.php';
        break;

    case '/representante/lista-agenda-usuario':
        require BASE_PATH . '/app/views/dashboard/representante/listaAgendaDisponibilidadUsuario.php';
        break;

    case '/representante/agenda-usuario':
        require BASE_PATH . '/app/views/dashboard/representante/agendaProfesional.php';
        break;

    case '/representante/agregar-disponibilidad-agenda':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    case '/representante/eliminar-agenda-usuario':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    case '/representante/editar-disponibilidad-agenda':
        require BASE_PATH . '/app/controllers/disponibilidadUsuarioController.php';
        break;

    case '/representante/perfil-representante':
        require BASE_PATH . '/app/views/dashboard/representante/perfilRepresentante.php';
        break;

    case '/representante/api/especialidades':
        require BASE_PATH . '/app/controllers/especialidadController.php';
        break;

    case '/representante/actualizar-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/representante/obtener-horarios-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/representante/actualizar-horario-veterinaria':
        require BASE_PATH . '/app/controllers/veterinariaController.php';
        break;

    case '/representante/propietarios':
        require BASE_PATH . '/app/views/dashboard/representante/listaPropietarios.php';
        break;

    case '/representante/propietarios/api/listar':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;

    case '/representante/api/propietarios':
        require BASE_PATH . '/app/controllers/propetarioController.php';
        break;

    case '/representante/gestion_clinica':
        require BASE_PATH . '/app/views/dashboard/representante/gestion-clinica.php';
        break;

    //------------------------------INVENTARIO REPRESENTANTE------------------------------//
    case '/representante/inventario':
        require BASE_PATH . '/app/views/dashboard/representante/listaInventario.php';
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

    case '/representante/obtener-horarios-servicio':
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

    // pasarela de pago
    case '/pasarela-pago':
        $_GET['action'] = 'pasarela';
        require BASE_PATH . '/app/controllers/pagosMercadoPagoController.php';
        break;
    case '/pagos/mercadopago':
        require BASE_PATH . '/app/controllers/pagosMercadoPagoController.php';
        break;
    case '/pagos/confirmacion':
        $_GET['action'] = 'confirmacion';
        require BASE_PATH . '/app/controllers/pagosMercadoPagoController.php';
        break;

    // ------------------------- SOPORTE TECNICO ------------------------- //
    /// crear ticket
    case '/soporte/api/crear-ticket':
        require BASE_PATH . '/app/controllers/ticketController.php';
        break;
    case '/soporte/api/ticket':
        require BASE_PATH . '/app/controllers/ticketController.php';
        break;



    // ---------------------------REGISTRO--------------------------- //
    case '/registro':
        require BASE_PATH . '/app/views/website/registrarse.php';
        break;

    case '/registro/veterinaria':
        require BASE_PATH . '/app/controllers/registrarseController.php';
        break;


    // -------------------------SESIONES-------------------------------//
    // ── API: Sesiones activas (confi.php) ──
    case '/cliente/api/sesiones/cerrar':
        require BASE_PATH . '/app/controllers/sesionesClienteController.php';
        break;

    case '/cliente/api/sesiones/cerrar-todas':
        require BASE_PATH . '/app/controllers/sesionesClienteController.php';
        break;

    // ── API: Preferencias de usuario ──
    case '/cliente/api/preferencias':
        require BASE_PATH . '/app/controllers/preferenciasController.php';
        break;

    case '/cliente/api/preferencias/guardar':
        require BASE_PATH . '/app/controllers/preferenciasController.php';
        break;

    case '/cliente/api/preferencias/reset':
        require BASE_PATH . '/app/controllers/preferenciasController.php';
        break;

    // ── API: Acceso temporal al historial clínico ──

    // Cliente
    case '/cliente/api/historial/acceso/estado':
        require BASE_PATH . '/app/controllers/accesoHistorialController.php';
        break;

    case '/cliente/api/historial/acceso/solicitar':
        require BASE_PATH . '/app/controllers/accesoHistorialController.php';
        break;

    // Veterinario
    case '/veterinaria/api/historial/acceso/pendientes':
        require BASE_PATH . '/app/controllers/accesoHistorialController.php';
        break;

    case '/veterinaria/api/historial/acceso/aprobar':
        require BASE_PATH . '/app/controllers/accesoHistorialController.php';
        break;

    case '/veterinaria/api/historial/acceso/rechazar':
        require BASE_PATH . '/app/controllers/accesoHistorialController.php';
        break;
}
