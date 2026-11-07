<?php

// API JSON de tickets de soporte.
//   POST /soporte/api/crear-ticket                  crea un ticket (profesional, propietario, representante)
//   GET  /soporte/api/ticket?action=notificaciones  tickets sin cerrar para la campanita (administrador)
//   GET  /soporte/api/ticket?action=historico&id=N  histórico de cambios de un ticket (administrador)
//   PUT  /soporte/api/ticket?action=asignar         asigna un ticket sin responsable (administrador)
//   PUT  /soporte/api/ticket                        cambia estado, solución o responsable (solo el asignado)
//
// Quién hace la acción sale siempre de la sesión, nunca de los datos enviados.
// Las vistas de administración usan el modelo directamente: este archivo solo atiende la API.

require_once BASE_PATH . '/app/helpers/acceso_helper.php';
require_once BASE_PATH . '/app/helpers/html_helper.php';
require_once BASE_PATH . '/app/models/Ticket.php';

const TICKET_MAX_TITULO      = 200;
const TICKET_MAX_DESCRIPCION = 2000;
const TICKET_MAX_SOLUCION    = 5000;
const TICKET_MAX_ARCHIVO     = 2 * 1024 * 1024;
// extensión => tipos MIME aceptados (se comprueba el contenido, no solo el nombre)
const TICKET_TIPOS_ARCHIVO = [
    'png'  => ['image/png'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'gif'  => ['image/gif'],
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword', 'application/vnd.ms-office', 'application/CDFV2'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
];

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function ticket_responder(array $datos, int $codigo = 200): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit();
}

function ticket_error(string $mensaje, int $codigo): never
{
    ticket_responder(['status' => 'error', 'ok' => false, 'message' => $mensaje], $codigo);
}

/** Texto recortado; null si pasa de la longitud máxima (en caracteres). */
function ticket_texto($valor, int $max): ?string
{
    $texto = trim((string) ($valor ?? ''));
    return mb_strlen($texto) > $max ? null : $texto;
}

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$accion = (string) ($_GET['action'] ?? '');

try {
    if ($metodo === 'POST') {
        $usuario = acceso_exigir_accion_post([2, 3, 4]);
        crearTicket((int) $usuario['id_usuario']);
    }

    if ($metodo === 'GET') {
        acceso_exigir_rol([1]);
        if ($accion === 'notificaciones') {
            ticket_responder(['ok' => true, 'tickets' => (new Ticket())->listarTicketsPendientes()]);
        }
        if ($accion === 'historico') {
            $id = acceso_entero_positivo($_GET['id'] ?? null);
            if ($id === null) {
                ticket_error('Ticket no válido.', 400);
            }
            ticket_responder(['status' => 'success', 'data' => (new Ticket())->consultarHistoricoTicket($id)]);
        }
        ticket_error('Acción no reconocida.', 400);
    }

    if ($metodo === 'PUT') {
        $admin = acceso_exigir_rol([1]);
        if (!csrf_valido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            error_log('[ticketController] token CSRF no válido');
            ticket_error('La sesión de la página expiró. Recarga e intenta de nuevo.', 403);
        }
        $datos = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($datos)) {
            ticket_error('Datos no válidos.', 400);
        }
        $accion === 'asignar'
            ? asignarTicket($datos, (int) $admin['id_usuario'])
            : actualizarTicket($datos, (int) $admin['id_usuario']);
    }

    header('Allow: GET, POST, PUT');
    ticket_error('Método no permitido.', 405);
} catch (Throwable $e) {
    error_log('[ticketController] ' . $e->getMessage());
    ticket_error('Error interno del servidor. Intenta de nuevo.', 500);
}

/** Guarda el adjunto validado y devuelve su nombre, o null si no se envió. */
function guardarAdjuntoTicket(): ?string
{
    $file = $_FILES['archivo'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > TICKET_MAX_ARCHIVO) {
        ticket_error('El archivo supera los 2 MB.', 400);
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        ticket_error('No se pudo recibir el archivo. Intenta de nuevo.', 400);
    }

    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(TICKET_TIPOS_ARCHIVO[$ext]) || !in_array($mime, TICKET_TIPOS_ARCHIVO[$ext], true)) {
        ticket_error('Solo se permiten archivos PNG, JPG, GIF, PDF, DOC o DOCX.', 400);
    }

    $nombre = 'ticket_' . bin2hex(random_bytes(12)) . '.' . $ext;
    if (!guardar_archivo_subido($file['tmp_name'], BASE_PATH . '/public/uploads/tickets/' . $nombre)) {
        error_log('[ticketController] no se pudo guardar el adjunto en public/uploads/tickets');
        ticket_error('No se pudo guardar el archivo adjunto.', 500);
    }
    return $nombre;
}

function crearTicket(int $idUsuario): never
{
    $titulo = ticket_texto($_POST['asunto'] ?? '', TICKET_MAX_TITULO);
    $descripcion = ticket_texto($_POST['descripcion'] ?? '', TICKET_MAX_DESCRIPCION);
    $categoria = (string) ($_POST['categoria'] ?? '');

    if ($titulo === null || $descripcion === null) {
        ticket_error('El asunto o la descripción son demasiado largos.', 400);
    }
    if ($titulo === '' || $descripcion === '' || $categoria === '') {
        ticket_error('Faltan campos requeridos.', 400);
    }
    if (!in_array($categoria, Ticket::CATEGORIAS, true)) {
        ticket_error('Categoría no válida.', 400);
    }

    $archivo = guardarAdjuntoTicket();
    try {
        $id = (new Ticket())->crearTicket([
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
            'categoria'   => $categoria,
            'id_usuario'  => $idUsuario,
            'archivo'     => $archivo,
        ]);
    } catch (Throwable $e) {
        if ($archivo !== null) {
            @unlink(BASE_PATH . '/public/uploads/tickets/' . $archivo);
        }
        throw $e;
    }

    ticket_responder([
        'status'  => 'success',
        'message' => 'Ticket creado exitosamente con número de ticket: ' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
    ]);
}

function asignarTicket(array $datos, int $idAdmin): never
{
    $ticketId = acceso_entero_positivo($datos['id_ticket'] ?? null);
    $asignarA = acceso_entero_positivo($datos['id_usuario'] ?? null);
    if ($ticketId === null || $asignarA === null) {
        ticket_error('Selecciona el administrador al que se asigna el ticket.', 400);
    }

    $modelo = new Ticket();
    if (!$modelo->esAdministradorActivo($asignarA)) {
        ticket_error('Solo se puede asignar a un administrador activo.', 422);
    }
    if (!$modelo->asignarTicket($ticketId, $asignarA, $idAdmin)) {
        ticket_error('El ticket no existe, está cerrado o ya fue asignado. Recarga la página.', 409);
    }
    ticket_responder(['status' => 'success', 'message' => 'Ticket asignado correctamente']);
}

function actualizarTicket(array $datos, int $idAdmin): never
{
    $ticketId = acceso_entero_positivo($datos['id_ticket'] ?? null);
    $estado = (string) ($datos['estado'] ?? '');
    $solucion = ticket_texto($datos['solucion'] ?? '', TICKET_MAX_SOLUCION);
    $reasignarA = ($datos['id_usuario_reasignado'] ?? '') === '' ? null : acceso_entero_positivo($datos['id_usuario_reasignado']);

    if ($ticketId === null || !in_array($estado, Ticket::ESTADOS, true)) {
        ticket_error('Faltan campos requeridos o el estado no es válido.', 400);
    }
    if ($solucion === null) {
        ticket_error('La solución es demasiado larga (máximo ' . TICKET_MAX_SOLUCION . ' caracteres).', 400);
    }

    $modelo = new Ticket();
    $ticket = $modelo->obtenerTicketPorId($ticketId);
    if ($ticket === null) {
        ticket_error('El ticket no existe.', 404);
    }
    // Las mismas reglas que muestra la pantalla
    if ((int) $ticket['id_asignado'] !== $idAdmin) {
        ticket_error('Solo el administrador asignado puede modificar este ticket.', 403);
    }
    if ($ticket['estado'] === 'cerrado') {
        ticket_error('El ticket está cerrado y no se puede modificar.', 409);
    }
    if (in_array($estado, ['en_espera', 'cerrado'], true) && $solucion === '') {
        ticket_error("Escribe la solución para pasar el ticket a 'En Espera' o 'Cerrado'.", 422);
    }
    if ($reasignarA !== null) {
        if (in_array($estado, ['en_espera', 'cerrado'], true)) {
            ticket_error("No se puede reasignar un ticket 'En Espera' o 'Cerrado'.", 422);
        }
        if (!$modelo->esAdministradorActivo($reasignarA)) {
            ticket_error('Solo se puede reasignar a un administrador activo.', 422);
        }
    }

    $cambio = $modelo->actualizarTicket($ticket, $estado, $solucion, $reasignarA, $idAdmin);
    ticket_responder([
        'status'  => 'success',
        'message' => $cambio ? 'Ticket actualizado correctamente' : 'No había cambios que guardar.',
    ]);
}
