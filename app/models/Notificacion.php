<?php
/**
 * Modelo: Notificacion
 * Responsabilidad: Acceso a datos de notificaciones (solo lógica de BD)
 */
require_once __DIR__ . '/../../config/database.php';

class Notificacion {

    private $conexion;

    public function __construct() {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Crear una notificación para un usuario.
     *
     * @param int         $usuario_id
     * @param string      $titulo
     * @param string      $mensaje
     * @param string      $tipo         INFO | ALERTA | ERROR
     * @param int|null    $referencia_id  ID del agendamiento u otro recurso
     * @return bool
     */
    public function crear(int $usuario_id, string $titulo, string $mensaje, string $tipo = 'INFO', ?int $referencia_id = null): bool {
        try {
            $tipos_validos = ['INFO', 'ALERTA', 'ERROR'];
            if (!in_array($tipo, $tipos_validos)) {
                $tipo = 'INFO';
            }

            $sql = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, referencia_id)
                    VALUES (:usuario_id, :titulo, :mensaje, :tipo, :referencia_id)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':usuario_id'    => $usuario_id,
                ':titulo'        => $titulo,
                ':mensaje'       => $mensaje,
                ':tipo'          => $tipo,
                ':referencia_id' => $referencia_id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("[Notificacion::crear] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener notificaciones de un usuario con soporte de paginación.
     *
     * @param int $usuario_id
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function obtenerPorUsuario(int $usuario_id, int $limite = 50, int $offset = 0): array {
        try {
            $sql = "SELECT * FROM notificaciones
                    WHERE usuario_id = :usuario_id
                    ORDER BY fecha DESC
                    LIMIT :limite OFFSET :offset";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':limite',     $limite,     PDO::PARAM_INT);
            $stmt->bindValue(':offset',     $offset,     PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Notificacion::obtenerPorUsuario] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar notificaciones no leídas de un usuario.
     *
     * @param int $usuario_id
     * @return int
     */
    public function contarNoLeidas(int $usuario_id): int {
        try {
            $sql = "SELECT COUNT(*) AS total FROM notificaciones
                    WHERE usuario_id = :usuario_id AND leido = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("[Notificacion::contarNoLeidas] " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marcar una notificación específica como leída.
     * Se asegura de que la notificación pertenezca al usuario.
     *
     * @param int $id
     * @param int $usuario_id
     * @return bool
     */
    public function marcarLeida(int $id, int $usuario_id): bool {
        try {
            $sql = "UPDATE notificaciones
                    SET leido = 1
                    WHERE id = :id AND usuario_id = :usuario_id AND leido = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':id'         => $id,
                ':usuario_id' => $usuario_id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("[Notificacion::marcarLeida] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas.
     *
     * @param int $usuario_id
     * @return bool
     */
    public function marcarTodasLeidas(int $usuario_id): bool {
        try {
            $sql = "UPDATE notificaciones
                    SET leido = 1
                    WHERE usuario_id = :usuario_id AND leido = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            return true;
        } catch (PDOException $e) {
            error_log("[Notificacion::marcarTodasLeidas] " . $e->getMessage());
            return false;
        }
    }
}