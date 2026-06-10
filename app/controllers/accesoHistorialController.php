<?php
/**
 * accesoHistorialController.php
 *
 * API JSON para el sistema de permisos de historial clínico.
 *
 * Rutas (agregar en index.php):
 *   GET  /cliente/api/historial/acceso/estado    → estado actual del permiso
 *   POST /cliente/api/historial/acceso/solicitar → crear solicitud
 *
 *   POST /veterinaria/api/historial/acceso/aprobar   → veterinario aprueba
 *   POST /veterinaria/api/historial/acceso/rechazar  → veterinario rechaza
 *   GET  /veterinaria/api/historial/acceso/pendientes → listar pendientes
 */

header('Content-Type: application/json; charset=utf-8');

$uri    = rtrim(str_replace($baseFolder, '', strtok($_SERVER['REQUEST_URI'], '?')), '/');
$method = $_SERVER['REQUEST_METHOD'];

require_once BASE_PATH . '/app/models/AccesoHistorial.php';
$modelo = new AccesoHistorial();

/* ──────────────────────────────────────────────────────────────
   RUTAS DEL CLIENTE
────────────────────────────────────────────────────────────── */

/* GET /cliente/api/historial/acceso/estado?id_paciente=X */
if ($uri === '/cliente/api/historial/acceso/estado' && $method === 'GET') {
    require_once BASE_PATH . '/app/helpers/session_propietario.php';
    require_once BASE_PATH . '/app/models/CitasCliente.php';

    $id_paciente = (int)($_GET['id_paciente'] ?? 0);
    if ($id_paciente <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'id_paciente requerido']);
        exit;
    }

    $cm            = new CitasCliente();
    $id_propietario = $cm->obtenerIdPropietarioPorUsuario($_SESSION['user']['id_usuario']);

    echo json_encode([
        'status' => 'ok',
        'acceso' => $modelo->estadoAcceso($id_paciente, $id_propietario),
    ]);
    exit;
}

/* POST /cliente/api/historial/acceso/solicitar */
if ($uri === '/cliente/api/historial/acceso/solicitar' && $method === 'POST') {
    require_once BASE_PATH . '/app/helpers/session_propietario.php';
    require_once BASE_PATH . '/app/models/CitasCliente.php';

    $body        = json_decode(file_get_contents('php://input'), true) ?? [];
    $id_paciente = (int)($body['id_paciente'] ?? 0);

    if ($id_paciente <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'id_paciente requerido']);
        exit;
    }

    $cm             = new CitasCliente();
    $id_propietario = $cm->obtenerIdPropietarioPorUsuario($_SESSION['user']['id_usuario']);

    /* Verificar que la mascota pertenece al propietario */
    $mascota = $cm->obtenerInfoPacienteConPropiedad($id_propietario, $id_paciente);
    if (!$mascota) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Sin permiso para esta mascota']);
        exit;
    }

    $id_acceso = $modelo->solicitar($id_paciente, $id_propietario);

    if ($id_acceso === false) {
        echo json_encode([
            'status'  => 'error',
            'code'    => 'ya_existe',
            'message' => 'Ya tienes una solicitud pendiente o un acceso activo.',
        ]);
        exit;
    }

    /* Crear notificación para el veterinario en la tabla notificaciones */
    _crearNotificacionVeterinario(
        $id_acceso,
        $id_paciente,
        $id_propietario,
        $mascota,
        $modelo
    );

    echo json_encode([
        'status'    => 'ok',
        'id_acceso' => $id_acceso,
        'message'   => 'Solicitud enviada. El veterinario recibirá una notificación.',
    ]);
    exit;
}

/* ──────────────────────────────────────────────────────────────
   RUTAS DEL VETERINARIO
────────────────────────────────────────────────────────────── */

/* GET /veterinaria/api/historial/acceso/pendientes */
if ($uri === '/veterinaria/api/historial/acceso/pendientes' && $method === 'GET') {
    require_once BASE_PATH . '/app/helpers/session_veterinario.php';

    echo json_encode([
        'status'     => 'ok',
        'pendientes' => $modelo->listarPendientes($_SESSION['user']['id_usuario']),
    ]);
    exit;
}

/* POST /veterinaria/api/historial/acceso/aprobar */
if ($uri === '/veterinaria/api/historial/acceso/aprobar' && $method === 'POST') {
    require_once BASE_PATH . '/app/helpers/session_veterinario.php';

    $body          = json_decode(file_get_contents('php://input'), true) ?? [];
    $id_acceso     = (int)($body['id_acceso']     ?? 0);
    $duracion_horas= (int)($body['duracion_horas'] ?? 24);

    if ($id_acceso <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'id_acceso requerido']);
        exit;
    }

    $solicitud = $modelo->obtenerPorId($id_acceso);
    if (!$solicitud || $solicitud['estado'] !== 'pendiente') {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada o ya procesada']);
        exit;
    }

    $ok = $modelo->aprobar($id_acceso, $_SESSION['user']['id_usuario'], $duracion_horas);

    if ($ok) {
        /* Notificar al propietario que fue aprobado */
        _notificarPropietario(
            $solicitud['id_propietario'],
            $solicitud['id_paciente'],
            'aprobado',
            $duracion_horas
        );
    }

    echo json_encode([
        'status'  => $ok ? 'ok' : 'error',
        'message' => $ok ? 'Acceso aprobado correctamente.' : 'No se pudo aprobar.',
    ]);
    exit;
}

/* POST /veterinaria/api/historial/acceso/rechazar */
if ($uri === '/veterinaria/api/historial/acceso/rechazar' && $method === 'POST') {
    require_once BASE_PATH . '/app/helpers/session_veterinario.php';

    $body      = json_decode(file_get_contents('php://input'), true) ?? [];
    $id_acceso = (int)($body['id_acceso'] ?? 0);
    $motivo    = trim($body['motivo'] ?? '');

    if ($id_acceso <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'id_acceso requerido']);
        exit;
    }

    $solicitud = $modelo->obtenerPorId($id_acceso);
    if (!$solicitud || $solicitud['estado'] !== 'pendiente') {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada o ya procesada']);
        exit;
    }

    $ok = $modelo->rechazar($id_acceso, $_SESSION['user']['id_usuario'], $motivo);

    if ($ok) {
        _notificarPropietario(
            $solicitud['id_propietario'],
            $solicitud['id_paciente'],
            'rechazado',
            0,
            $motivo
        );
    }

    echo json_encode([
        'status'  => $ok ? 'ok' : 'error',
        'message' => $ok ? 'Solicitud rechazada.' : 'No se pudo rechazar.',
    ]);
    exit;
}

/* ──────────────────────────────────────────────────────────────
   HELPERS INTERNOS
────────────────────────────────────────────────────────────── */

/**
 * Inserta una notificación en la tabla `notificaciones` para el veterinario.
 * Usa la estructura: usuario_id, tipo, mensaje, leido, estado, canal, referencia_id
 */
function _crearNotificacionVeterinario(
    int $id_acceso,
    int $id_paciente,
    int $id_propietario,
    array $mascota,
    AccesoHistorial $modelo
): void {
    global $db;

    try {
        require_once BASE_PATH . '/app/models/Database.php';
        $db = Database::getConnection();

        /* Obtener el veterinario asignado a la mascota (último que atendió) */
        $stmt = $db->prepare(
            "SELECT DISTINCT hc.id_usuario_veterinario
             FROM historial_clinico hc
             WHERE hc.id_paciente = :pac
             ORDER BY hc.fecha_atencion DESC
             LIMIT 1"
        );
        $stmt->execute([':pac' => $id_paciente]);
        $id_vet_usuario = $stmt->fetchColumn();

        if (!$id_vet_usuario) return; /* Sin veterinario asignado, no se puede notificar */

        $nombre_mascota    = $mascota['nombre'] ?? 'la mascota';
        $nombre_propietario= trim(($mascota['propietario_nombres'] ?? '') . ' ' . ($mascota['propietario_apellidos'] ?? ''));
        $mensaje = "El propietario {$nombre_propietario} solicita acceso al historial clínico de {$nombre_mascota}.";

        $ins = $db->prepare(
            "INSERT INTO notificaciones
                 (usuario_id, tipo, mensaje, leido, estado, canal, referencia_id, fecha)
             VALUES
                 (:uid, 'acceso_historial', :msg, 0, 'pendiente', 'plataforma', :ref, NOW())"
        );
        $ins->execute([
            ':uid' => $id_vet_usuario,
            ':msg' => $mensaje,
            ':ref' => $id_acceso,
        ]);

        $id_notif = (int) $db->lastInsertId();
        $modelo->vincularNotificacion($id_acceso, $id_notif);

    } catch (\Exception $e) {
        error_log('accesoHistorialController: error al crear notificación vet - ' . $e->getMessage());
    }
}

/**
 * Inserta una notificación en la tabla `notificaciones` para el propietario.
 */
function _notificarPropietario(
    int    $id_propietario,
    int    $id_paciente,
    string $resultado,        // 'aprobado' | 'rechazado'
    int    $duracion_horas,
    string $motivo = ''
): void {
    try {
        require_once BASE_PATH . '/app/models/Database.php';
        $db2 = Database::getConnection();

        /* Obtener id_usuario del propietario */
        $stmt = $db2->prepare(
            "SELECT id_usuario FROM propietarios WHERE id_propietario = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id_propietario]);
        $id_usuario_prop = $stmt->fetchColumn();
        if (!$id_usuario_prop) return;

        /* Nombre de la mascota */
        $stmt2 = $db2->prepare("SELECT nombre FROM pacientes WHERE id_paciente = :id LIMIT 1");
        $stmt2->execute([':id' => $id_paciente]);
        $nombre_mascota = $stmt2->fetchColumn() ?: 'tu mascota';

        if ($resultado === 'aprobado') {
            $label   = match($duracion_horas) {
                168    => '7 días',
                72     => '3 días',
                48     => '48 horas',
                default=> '24 horas',
            };
            $mensaje = "Tu solicitud de acceso al historial clínico de {$nombre_mascota} fue aprobada por {$label}.";
        } else {
            $mensaje = "Tu solicitud de acceso al historial clínico de {$nombre_mascota} fue rechazada."
                     . ($motivo ? " Motivo: {$motivo}" : '');
        }

        $db2->prepare(
            "INSERT INTO notificaciones
                 (usuario_id, tipo, mensaje, leido, estado, canal, referencia_id, fecha)
             VALUES
                 (:uid, 'acceso_historial_respuesta', :msg, 0, 'enviada', 'plataforma', :ref, NOW())"
        )->execute([
            ':uid' => $id_usuario_prop,
            ':msg' => $mensaje,
            ':ref' => $id_paciente,
        ]);

    } catch (\Exception $e) {
        error_log('accesoHistorialController: error al notificar propietario - ' . $e->getMessage());
    }
}

/* Ruta no encontrada */
http_response_code(404);
echo json_encode(['status' => 'error', 'code' => 'not_found']);