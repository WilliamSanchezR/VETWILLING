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

    // Para el polling: cuenta no leídas de un usuario
    public function contarNoLeidas(int $usuario_id): int {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM notificaciones 
             WHERE usuario_id = ? AND leido = 0 AND estado != 'cancelada'"
        );
        $stmt->execute([$usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    // Carga las últimas N notificaciones para el dropdown
    public function obtenerPorUsuario(int $usuario_id, int $limite = 10, int $offset = 0): array {
        $stmt = $this->conexion->prepare(
            "SELECT id, tipo, mensaje, leido, canal, estado, fecha, referencia_id
             FROM notificaciones
             WHERE usuario_id = ? AND estado != 'cancelada'
             ORDER BY fecha DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$usuario_id, $limite, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerNuevas(int $usuario_id, int $desde_id = 0): array {
        if ($desde_id <= 0) {
            return [];
        }

        $stmt = $this->conexion->prepare(
            "SELECT id, tipo, mensaje, leido, canal, estado, fecha, referencia_id
             FROM notificaciones
             WHERE usuario_id = ? AND estado != 'cancelada' AND id > ?
             ORDER BY fecha ASC"
        );
        $stmt->execute([$usuario_id, $desde_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUltimoId(int $usuario_id): int {
        $stmt = $this->conexion->prepare(
            "SELECT COALESCE(MAX(id), 0) FROM notificaciones
             WHERE usuario_id = ? AND estado != 'cancelada'"
        );
        $stmt->execute([$usuario_id]);
        return (int) $stmt->fetchColumn();
    }

    // Crear notificación interna (al crear cita, seguimiento, etc.)
    public function crear(int $usuario_id, string $tipo, string $mensaje, 
                          ?int $referencia_id = null, string $canal = 'plataforma'): int {
        $stmt = $this->conexion->prepare(
            "INSERT INTO notificaciones 
             (usuario_id, tipo, mensaje, leido, estado, canal, referencia_id, fecha)
             VALUES (?, ?, ?, 0, 'pendiente', ?, ?, NOW())"
        );
        $stmt->execute([$usuario_id, $tipo, $mensaje, $canal, $referencia_id]);
        return (int) $this->conexion->lastInsertId();
    }

    // Marcar una como leída
    public function marcarLeida(int $id_notificacion, int $usuario_id): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones SET leido = 1
             WHERE id = ? AND usuario_id = ?"
        );
        $stmt->execute([$id_notificacion, $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    // Marcar todas como leídas
    public function marcarTodasLeidas(int $usuario_id): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones SET leido = 1
             WHERE usuario_id = ? AND leido = 0"
        );
        $stmt->execute([$usuario_id]);
        return $stmt->rowCount() > 0;
    }

    // NUEVO: modificar contenido de una notificación pendiente
    public function modificar(int $id, int $usuario_id, string $mensaje, 
                               string $tipo = null): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones 
             SET mensaje = ?, tipo = COALESCE(?, tipo)
             WHERE id = ? AND usuario_id = ? AND estado = 'pendiente'"
        );
        $stmt->execute([$mensaje, $tipo, $id, $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    // NUEVO: cancelar notificación con motivo
    public function cancelar(int $id, int $usuario_id, string $motivo = null): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE notificaciones 
             SET estado = 'cancelada', motivo_cancelacion = ?
             WHERE id = ? AND usuario_id = ? AND estado = 'pendiente'"
        );
        $stmt->execute([$motivo, $id, $usuario_id]);
        return $stmt->rowCount() > 0;
    }
}