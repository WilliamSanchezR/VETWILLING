<?php
/**
 * Controlador: NotificacionController
 * Responsabilidad: Recibir peticiones HTTP, validar sesión/input y responder JSON.
 * 
 * Rutas esperadas (ejemplo con router propio):
 *   GET  /notificaciones           → obtenerNotificaciones()
 *   POST /notificaciones/leida     → marcarLeida()
 *   POST /notificaciones/todas     → marcarTodasLeidas()
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
            'status'         => 'success',
            'notificaciones' => $this->modelo->obtenerPorUsuario($id_usuario, $limite, $offset),
            'no_leidas'      => $this->modelo->contarNoLeidas($id_usuario),
            'pagina'         => $pagina,
            'limite'         => $limite
        ]);
    }

    // ─────────────────────────────────────────────
    // POST /notificaciones/leida
    // Body JSON: { "id": 123 }
    // ─────────────────────────────────────────────
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
}