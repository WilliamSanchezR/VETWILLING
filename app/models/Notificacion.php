<?php
/**
 * Modelo: Notificacion
 * Responsabilidad: Acceso a datos de notificaciones (solo lógica de BD)
 */
require_once __DIR__ . '/../../config/database.php';

class Notificacion {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /** Cuenta notificaciones no leídas (leido = 0) de un usuario */
    public function contarNoLeidas(int $usuario_id): int {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM notificaciones
             WHERE usuario_id = ? AND leido = 0"
        );
        $stmt->execute([$usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    /** Lista notificaciones del usuario con paginación */
    public function obtenerPorUsuario(int $usuario_id, int $limite = 10, int $offset = 0): array {
        $limite = max(1, $limite);
        $offset = max(0, $offset);

        $stmt = $this->conexion->prepare(
            "SELECT id, usuario_id, tipo, mensaje, leido, referencia_id, fecha
             FROM notificaciones
             WHERE usuario_id = ?
             ORDER BY fecha DESC
             LIMIT {$limite} OFFSET {$offset}"
        );
        $stmt->execute([$usuario_id]);
        return $this->enriquecerFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Obtiene una notificación por ID, validando que pertenezca al usuario */
    public function obtenerPorId(int $id, int $usuario_id): ?array {
        $stmt = $this->conexion->prepare(
            "SELECT id, usuario_id, tipo, mensaje, leido, referencia_id, fecha
             FROM notificaciones
             WHERE id = ? AND usuario_id = ?
             LIMIT 1"
        );
        $stmt->execute([$id, $usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->enriquecerFila($row) : null;
    }

    /** Notificaciones nuevas desde un ID (polling / SSE) */
    public function obtenerNuevas(int $usuario_id, int $desde_id = 0): array {
        if ($desde_id <= 0) {
            return [];
        }

        $stmt = $this->conexion->prepare(
            "SELECT id, usuario_id, tipo, mensaje, leido, referencia_id, fecha
             FROM notificaciones
             WHERE usuario_id = ? AND id > ?
             ORDER BY fecha ASC"
        );
        $stmt->execute([$usuario_id, $desde_id]);
        return $this->enriquecerFilas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerUltimoId(int $usuario_id): int {
        $stmt = $this->conexion->prepare(
            "SELECT COALESCE(MAX(id), 0) FROM notificaciones WHERE usuario_id = ?"
        );
        $stmt->execute([$usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Registra una notificación no leída.
     * Mapeo: leido=0 equivale a status 'unread'.
     */
    public function registrar(
        int $usuario_id,
        string $titulo,
        string $mensaje,
        string $tipo = 'info',
        ?int $referencia_id = null
    ): int {
        return $this->crear($usuario_id, $titulo, $mensaje, $tipo, $referencia_id);
    }

    /** Alias usado por otros módulos (Seguimientos, etc.) */
    public function crear(
        int $usuario_id,
        string $titulo,
        string $mensaje,
        string $tipo = 'info',
        ?int $referencia_id = null
    ): int {
        $texto = $this->componerMensaje($titulo, $mensaje);

        $stmt = $this->conexion->prepare(
            "INSERT INTO notificaciones
             (usuario_id, tipo, mensaje, leido, referencia_id, fecha)
             VALUES (?, ?, ?, 0, ?, NOW())"
        );
        $stmt->execute([$usuario_id, $tipo, $texto, $referencia_id]);
        return (int) $this->conexion->lastInsertId();
    }

    /** Marca una notificación como leída (leido = 1, status 'read') */
    public function marcarLeida(int $id_notificacion, int $usuario_id): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones SET leido = 1
             WHERE id = ? AND usuario_id = ? AND leido = 0"
        );
        $stmt->execute([$id_notificacion, $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    public function marcarTodasLeidas(int $usuario_id): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones SET leido = 1
             WHERE usuario_id = ? AND leido = 0"
        );
        $stmt->execute([$usuario_id]);
        return $stmt->rowCount() > 0;
    }

    public function modificar(int $id, int $usuario_id, string $mensaje, ?string $tipo = null): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones
             SET mensaje = ?, tipo = COALESCE(?, tipo)
             WHERE id = ? AND usuario_id = ?"
        );
        $stmt->execute([$mensaje, $tipo, $id, $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    public function cancelar(int $id, int $usuario_id, ?string $motivo = null): bool {
        $notif = $this->obtenerPorId($id, $usuario_id);
        if (!$notif) {
            return false;
        }

        $mensaje = $notif['mensaje'];
        if ($motivo) {
            $mensaje .= ' [Cancelada: ' . $motivo . ']';
        }

        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones SET mensaje = ?, leido = 1
             WHERE id = ? AND usuario_id = ?"
        );
        $stmt->execute([$mensaje, $id, $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    private function componerMensaje(string $titulo, string $mensaje): string {
        $titulo  = trim($titulo);
        $mensaje = trim($mensaje);

        if ($titulo !== '' && $mensaje !== '' && $titulo !== $mensaje) {
            return "{$titulo}: {$mensaje}";
        }

        return $mensaje !== '' ? $mensaje : $titulo;
    }

    /** Agrega campo de estado legible para la API */
    private function enriquecerFila(array $row): array {
        $row['estado_lectura'] = !empty($row['leido']) ? 'read' : 'unread';
        return $row;
    }

    private function enriquecerFilas(array $filas): array {
        return array_map([$this, 'enriquecerFila'], $filas);
    }
}
