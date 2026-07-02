<?php

require_once __DIR__ . '/../../config/database.php';

class Notificacion
{
    private PDO $conexion;

    public function __construct()
    {
        $db             = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // =========================================================================
    // ESCRITURA
    // =========================================================================

    /**
     * Crear una notificación para un usuario.
     *
     * Campos obligatorios en $datos:
     *   - id_usuario  (int)
     *   - tipo        (string) 'cita' | 'vacuna' | 'tratamiento'
     *   - titulo      (string)
     *   - mensaje     (string)
     *
     * Campos opcionales:
     *   - url_accion  (string|null)
     *   - id_paciente (int|null)
     *
     * @return int|false  ID insertado o false si falla.
     */
    public function crear(array $datos): int|false
    {
        try {
            $sql = "INSERT INTO notificacion
                        (id_usuario, tipo, titulo, mensaje, url_accion, id_paciente)
                    VALUES
                        (:id_usuario, :tipo, :titulo, :mensaje, :url_accion, :id_paciente)";

            $stmt = $this->conexion->prepare($sql);

            $urlAccion  = $datos['url_accion']  ?? null;
            $idPaciente = isset($datos['id_paciente']) ? (int)$datos['id_paciente'] : null;

            $stmt->bindParam(':id_usuario',  $datos['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo',        $datos['tipo'],       PDO::PARAM_STR);
            $stmt->bindParam(':titulo',      $datos['titulo'],     PDO::PARAM_STR);
            $stmt->bindParam(':mensaje',     $datos['mensaje'],    PDO::PARAM_STR);
            $stmt->bindValue(':url_accion',  $urlAccion,  $urlAccion  !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':id_paciente', $idPaciente, $idPaciente !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $stmt->execute();
            return (int)$this->conexion->lastInsertId();

        } catch (PDOException $e) {
            error_log('[Notificacion::crear] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear múltiples notificaciones en una sola transacción.
     * Útil para notificar a varios usuarios a la vez (ej: recordatorios masivos).
     *
     * @param  array[] $notificaciones  Array de arrays con las mismas claves que crear().
     * @return int     Cantidad de filas insertadas exitosamente.
     */
    public function crearBatch(array $notificaciones): int
    {
        if (empty($notificaciones)) return 0;

        $insertadas = 0;

        try {
            $this->conexion->beginTransaction();

            $sql = "INSERT INTO notificacion
                        (id_usuario, tipo, titulo, mensaje, url_accion, id_paciente)
                    VALUES
                        (:id_usuario, :tipo, :titulo, :mensaje, :url_accion, :id_paciente)";

            $stmt = $this->conexion->prepare($sql);

            foreach ($notificaciones as $datos) {
                $urlAccion  = $datos['url_accion']  ?? null;
                $idPaciente = isset($datos['id_paciente']) ? (int)$datos['id_paciente'] : null;

                $stmt->bindParam(':id_usuario',  $datos['id_usuario'], PDO::PARAM_INT);
                $stmt->bindParam(':tipo',        $datos['tipo'],       PDO::PARAM_STR);
                $stmt->bindParam(':titulo',      $datos['titulo'],     PDO::PARAM_STR);
                $stmt->bindParam(':mensaje',     $datos['mensaje'],    PDO::PARAM_STR);
                $stmt->bindValue(':url_accion',  $urlAccion,  $urlAccion  !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':id_paciente', $idPaciente, $idPaciente !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                if ($stmt->execute()) $insertadas++;
            }

            $this->conexion->commit();
            return $insertadas;

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            error_log('[Notificacion::crearBatch] ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // LECTURA
    // =========================================================================

    /**
     * Listar todas las notificaciones no descartadas de un usuario,
     * ordenadas de más reciente a más antigua.
     *
     * @param  int   $id_usuario
     * @param  int   $limite     Máximo de filas a retornar (default 50).
     * @return array
     */
    public function listarParaUsuario(int $id_usuario, int $limite = 50): array
    {
        try {
            $sql = "SELECT
                        id_notificacion,
                        id_usuario,
                        id_paciente,
                        tipo,
                        titulo,
                        mensaje,
                        estado,
                        motivo_cancelacion,
                        url_accion,
                        leida,
                        descartada,
                        fecha_creacion
                    FROM notificacion
                    WHERE id_usuario  = :id_usuario
                      AND descartada  = 0
                    ORDER BY fecha_creacion DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':limite',     $limite,     PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('[Notificacion::listarParaUsuario] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar notificaciones no leídas (valor del badge en el sidebar/header).
     *
     * @param  int $id_usuario
     * @return int
     */
    public function contarNoLeidas(int $id_usuario): int
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM notificacion
                    WHERE id_usuario = :id_usuario
                      AND leida      = 0
                      AND descartada = 0";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($fila['total'] ?? 0);

        } catch (PDOException $e) {
            error_log('[Notificacion::contarNoLeidas] ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // ACTUALIZACIÓN DE ESTADO
    // =========================================================================

    /**
     * Marcar una notificación como leída.
     * El WHERE incluye id_usuario para evitar que otro usuario marque ajenas.
     *
     * @param  int  $id_notificacion
     * @param  int  $id_usuario
     * @return bool
     */
    public function marcarLeida(int $id_notificacion, int $id_usuario): bool
    {
        try {
            $sql = "UPDATE notificacion
                    SET leida = 1
                    WHERE id_notificacion = :id_notificacion
                      AND id_usuario      = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',      $id_usuario,      PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log('[Notificacion::marcarLeida] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar todas las notificaciones no leídas de un usuario como leídas.
     *
     * @param  int  $id_usuario
     * @return bool
     */
    public function marcarTodasLeidas(int $id_usuario): bool
    {
        try {
            $sql = "UPDATE notificacion
                    SET leida = 1
                    WHERE id_usuario = :id_usuario
                      AND leida      = 0
                      AND descartada = 0";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log('[Notificacion::marcarTodasLeidas] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Descartar (ocultar) una notificación — soft delete.
     * También la marca como leída para que no sume al badge.
     *
     * @param  int  $id_notificacion
     * @param  int  $id_usuario
     * @return bool
     */
    public function descartar(int $id_notificacion, int $id_usuario): bool
    {
        try {
            $sql = "UPDATE notificacion
                    SET descartada = 1,
                        leida      = 1
                    WHERE id_notificacion = :id_notificacion
                      AND id_usuario      = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',      $id_usuario,      PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log('[Notificacion::descartar] ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // RFS 43 — MODIFICACIÓN Y CANCELACIÓN
    // =========================================================================

    /**
     * Obtener una notificación por ID, validando que pertenezca al usuario.
     */
    public function obtenerPorId(int $id_notificacion, int $id_usuario): ?array
    {
        try {
            $sql = "SELECT id_notificacion, id_usuario, id_paciente, tipo,
                           titulo, mensaje, estado, motivo_cancelacion,
                           url_accion, leida, descartada, fecha_creacion
                    FROM notificacion
                    WHERE id_notificacion = :id
                      AND id_usuario      = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id',          $id_notificacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',  $id_usuario,      PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;

        } catch (PDOException $e) {
            error_log('[Notificacion::obtenerPorId] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Modificar título y mensaje de una notificación no cancelada.
     * Solo actúa si la notificación pertenece al usuario y no está cancelada.
     */
    public function modificar(int $id_notificacion, int $id_usuario, string $titulo, string $mensaje): bool
    {
        try {
            $sql = "UPDATE notificacion
                    SET titulo  = :titulo,
                        mensaje = :mensaje
                    WHERE id_notificacion = :id
                      AND id_usuario      = :id_usuario
                      AND estado         != 'cancelada'
                      AND descartada      = 0";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':titulo',      $titulo,          PDO::PARAM_STR);
            $stmt->bindParam(':mensaje',     $mensaje,         PDO::PARAM_STR);
            $stmt->bindParam(':id',          $id_notificacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',  $id_usuario,      PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->registrarHistorial($id_notificacion, $id_usuario, 'modificada',
                    mb_substr($titulo, 0, 200));
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log('[Notificacion::modificar] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancelar una notificación programada/enviada con un motivo.
     * La marca como leída para que no sume al badge.
     */
    public function cancelar(int $id_notificacion, int $id_usuario, string $motivo): bool
    {
        try {
            $sql = "UPDATE notificacion
                    SET estado             = 'cancelada',
                        motivo_cancelacion = :motivo,
                        leida              = 1
                    WHERE id_notificacion = :id
                      AND id_usuario      = :id_usuario
                      AND estado         != 'cancelada'
                      AND descartada      = 0";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':motivo',      $motivo,          PDO::PARAM_STR);
            $stmt->bindParam(':id',          $id_notificacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario',  $id_usuario,      PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->registrarHistorial($id_notificacion, $id_usuario, 'cancelada',
                    mb_substr($motivo, 0, 500));
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log('[Notificacion::cancelar] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna el MAX(id_notificacion) activo del usuario.
     * Usado por el stream SSE como cursor para detectar notificaciones nuevas.
     */
    public function obtenerUltimoIdParaUsuario(int $id_usuario): int
    {
        try {
            $sql = "SELECT COALESCE(MAX(id_notificacion), 0) AS ultimo_id
                    FROM notificacion
                    WHERE id_usuario = :id_usuario
                      AND descartada = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['ultimo_id'] ?? 0);
        } catch (PDOException $e) {
            error_log('[Notificacion::obtenerUltimoIdParaUsuario] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Registrar un cambio en el historial de la notificación.
     */
    private function registrarHistorial(
        int     $id_notificacion,
        int     $id_usuario_accion,
        string  $accion,
        ?string $detalle
    ): void {
        try {
            $sql = "INSERT INTO notificacion_historial
                        (id_notificacion, id_usuario_accion, accion, detalle)
                    VALUES
                        (:id_notif, :id_usuario, :accion, :detalle)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_notif',   $id_notificacion,   PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $id_usuario_accion, PDO::PARAM_INT);
            $stmt->bindParam(':accion',     $accion,            PDO::PARAM_STR);
            $stmt->bindValue(':detalle',    $detalle,
                $detalle !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->execute();

        } catch (PDOException $e) {
            error_log('[Notificacion::registrarHistorial] ' . $e->getMessage());
        }
    }
}