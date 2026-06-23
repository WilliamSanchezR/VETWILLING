<?php
/**
 * Controlador: NotificacionController
 * Responsabilidad: Recibir peticiones HTTP, validar sesión/input y responder JSON.
 * 
 * Rutas esperadas (ejemplo con router propio):
 *   GET  /notificaciones?accion=listar   → obtenerNotificaciones()
 *   GET  /notificaciones?accion=detalle  → verDetalle()
 *   POST /notificaciones?accion=registrar → registrarNotificacion()
 *   POST /notificaciones?accion=leida    → marcarLeida()
 *   POST /notificaciones?accion=todas    → marcarTodasLeidas()
 */

require_once BASE_PATH . '/app/models/Notificacion.php';

class NotificacionController {

    private Notificacion $modelo;

    public function __construct() {
        $this->modelo = new Notificacion();
    }

    // ─────────────────────────────────────────────
    // GET /notificaciones
    // ─────────────────────────────────────────────
    public function obtenerNotificaciones(): void {
        $this->requireSesion();

        $id_usuario = (int) $_SESSION['user']['id_usuario'];

        // Paginación opcional por query string: ?limite=20&pagina=2
        $limite = isset($_GET['limite']) ? max(1, min((int) $_GET['limite'], 100)) : 50;
        $pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
        $offset = ($pagina - 1) * $limite;

        $this->responderJson([
            'status'                => 'success',
            'notificaciones'        => $this->modelo->obtenerPorUsuario($id_usuario, $limite, $offset),
            'no_leidas'             => $this->modelo->contarNoLeidas($id_usuario),
            'ultima_notificacion_id'=> $this->modelo->obtenerUltimoId($id_usuario),
            'pagina'                => $pagina,
            'limite'                => $limite
        ]);
    }

    public function streamNotificaciones(): void {
        $this->requireSesion();
        ignore_user_abort(true);
        set_time_limit(0);

        $id_usuario = (int) $_SESSION['user']['id_usuario'];
        $since_id   = isset($_GET['since_id']) ? max(0, (int) $_GET['since_id']) : 0;
        if ($since_id <= 0) {
            $since_id = $this->modelo->obtenerUltimoId($id_usuario);
        }

        // Release session lock so other concurrent requests (e.g. login POST)
        // can start and write to the session. The SSE loop below must not
        // hold the session file lock while running indefinitely.
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $lastCount = $this->modelo->contarNoLeidas($id_usuario);
        echo ": connected\n\n";
        flush();

        while (!connection_aborted()) {
            $nuevas = $this->modelo->obtenerNuevas($id_usuario, $since_id);
            $count  = $this->modelo->contarNoLeidas($id_usuario);

            if (!empty($nuevas) || $count !== $lastCount) {
                if (!empty($nuevas)) {
                    $ids = array_column($nuevas, 'id');
                    $since_id = max($since_id, max($ids));
                }
                $lastCount = $count;
                $payload = [
                    'notificaciones' => $nuevas,
                    'no_leidas'      => $count
                ];
                echo "event: notify\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                flush();
            } else {
                echo ": heartbeat\n\n";
                flush();
            }

            sleep(3);
        }

        exit();
    }

    // ─────────────────────────────────────────────
    // POST /notificaciones/leida
    // Body JSON: { "id": 123 }
    // ─────────────────────────────────────────────
    // ─────────────────────────────────────────────
    // GET /notificaciones?accion=detalle&id=123
    // ─────────────────────────────────────────────
    public function verDetalle(): void {
        $this->requireSesion();

        $id         = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $id_usuario = (int) $_SESSION['user']['id_usuario'];

        if ($id <= 0) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'ID de notificación inválido.'], 400);
        }

        $notificacion = $this->modelo->obtenerPorId($id, $id_usuario);

        if (!$notificacion) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'Notificación no encontrada.'], 404);
        }

        $this->responderJson([
            'status'       => 'success',
            'notificacion' => $notificacion,
        ]);
    }

    // ─────────────────────────────────────────────
    // POST /notificaciones?accion=registrar
    // Body JSON: { "titulo", "mensaje", "tipo", "usuario_id?" }
    // ─────────────────────────────────────────────
    public function registrarNotificacion(): void {
        $this->requireSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['status' => 'error', 'mensaje' => 'Método no permitido.'], 405);
        }

        $data       = $this->getJsonInput();
        $id_usuario = isset($data['usuario_id']) ? (int) $data['usuario_id'] : (int) $_SESSION['user']['id_usuario'];
        $titulo     = trim($data['titulo'] ?? $data['title'] ?? '');
        $mensaje    = trim($data['mensaje'] ?? $data['message'] ?? '');
        $tipo       = trim($data['tipo'] ?? $data['type'] ?? 'info');
        $referencia = isset($data['referencia_id']) ? (int) $data['referencia_id'] : null;

        if ($titulo === '' && $mensaje === '') {
            $this->responderJson(['status' => 'error', 'mensaje' => 'Título o mensaje requerido.'], 400);
        }

        if ($id_usuario <= 0) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'Usuario destino inválido.'], 400);
        }

        $id = $this->modelo->registrar($id_usuario, $titulo, $mensaje, $tipo, $referencia);

        if ($id <= 0) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'No se pudo registrar la notificación.'], 500);
        }

        $this->responderJson([
            'status'       => 'success',
            'mensaje'      => 'Notificación registrada.',
            'id'           => $id,
            'notificacion' => $this->modelo->obtenerPorId($id, $id_usuario),
        ], 201);
    }

    public function marcarLeida(): void {
        $this->requireSesion();

        $data = $this->getJsonInput();
        $id_usuario = (int) $_SESSION['user']['id_usuario'];

        if (empty($data['id']) || !is_numeric($data['id'])) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'ID de notificación inválido.'], 400);
        }

        $resultado = $this->modelo->marcarLeida((int) $data['id'], $id_usuario);

        if ($resultado) {
            $this->responderJson(['status' => 'success', 'mensaje' => 'Notificación marcada como leída.']);
        } else {
            $this->responderJson(['status' => 'error', 'mensaje' => 'No se pudo marcar la notificación.'], 404);
        }
    }

    // ─────────────────────────────────────────────
    // POST /notificaciones/todas
    // ─────────────────────────────────────────────
    public function marcarTodasLeidas(): void {
        $this->requireSesion();

        $id_usuario = (int) $_SESSION['user']['id_usuario'];
        $resultado  = $this->modelo->marcarTodasLeidas($id_usuario);

        if ($resultado) {
            $this->responderJson(['status' => 'success', 'mensaje' => 'Todas las notificaciones marcadas como leídas.']);
        } else {
            $this->responderJson(['status' => 'error', 'mensaje' => 'No se pudieron actualizar las notificaciones.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────

    /**
     * Verificar que haya una sesión activa con usuario válido.
     * Si no, responde 401 y termina la ejecución.
     */
    private function requireSesion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']['id_usuario'])) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'No autorizado.'], 401);
        }
    }

    /**
     * Leer y decodificar el body JSON de la petición.
     *
     * @return array
     */
    private function getJsonInput(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Enviar respuesta JSON con el código HTTP indicado y terminar.
     *
     * @param array $datos
     * @param int   $codigo
     */
    private function responderJson(array $datos, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ─────────────────────────────────────────────
    // Métodos para dispatcher directo
    // ─────────────────────────────────────────────
    public function contarNotificaciones(): void {
        $this->requireSesion();
        $id_usuario = (int) $_SESSION['user']['id_usuario'];
        $this->responderJson([
            'status' => 'success',
            'count' => $this->modelo->contarNoLeidas($id_usuario)
        ]);
    }

    public function listarNotificaciones(): void {
        $this->obtenerNotificaciones();
    }

    public function modificarNotificacion(): void {
        $this->requireSesion();

        $data = $this->getJsonInput();
        $id_usuario = (int) $_SESSION['user']['id_usuario'];
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $mensaje = isset($data['mensaje']) ? trim($data['mensaje']) : '';
        $tipo = $data['tipo'] ?? null;

        if (!$id || $mensaje === '') {
            $this->responderJson(['status' => 'error', 'mensaje' => 'Datos incompletos'], 400);
        }

        $resultado = $this->modelo->modificar($id, $id_usuario, $mensaje, $tipo);
        $this->responderJson(['status' => $resultado ? 'success' : 'error', 'ok' => $resultado]);
    }

    public function cancelarNotificacion(): void {
        $this->requireSesion();

        $data = $this->getJsonInput();
        $id_usuario = (int) $_SESSION['user']['id_usuario'];
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $motivo = $data['motivo'] ?? null;

        if (!$id) {
            $this->responderJson(['status' => 'error', 'mensaje' => 'ID requerido'], 400);
        }

        $resultado = $this->modelo->cancelar($id, $id_usuario, $motivo);
        $this->responderJson(['status' => $resultado ? 'success' : 'error', 'ok' => $resultado]);
    }
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$controller = new NotificacionController();

switch ($accion) {
    case 'contar':
        $controller->contarNotificaciones();
        break;

    case 'listar':
        $controller->listarNotificaciones();
        break;

    case 'detalle':
        $controller->verDetalle();
        break;

    case 'registrar':
        $controller->registrarNotificacion();
        break;

    case 'leida':
        $controller->marcarLeida();
        break;

    case 'todas':
        $controller->marcarTodasLeidas();
        break;

    case 'stream':
        $controller->streamNotificaciones();
        break;

    case 'modificar':
        $controller->modificarNotificacion();
        break;

    case 'cancelar':
        $controller->cancelarNotificacion();
        break;

    default:
        if ($accion !== '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        break;
}