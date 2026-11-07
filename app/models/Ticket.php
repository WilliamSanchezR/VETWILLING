<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Tickets de soporte (tabla tickets) y su histórico de cambios (tabla historico_tikects).
 * Las reglas (quién puede crear, asignar o cambiar un ticket) viven en app/controllers/ticketController.php
 * Requiere las migraciones database/tickets_solucion.sql y database/tickets_historico.sql
 */
class Ticket
{
    const ESTADOS    = ['abierto', 'en_proceso', 'en_espera', 'cerrado'];
    const CATEGORIAS = ['tecnico', 'cuenta', 'facturacion', 'funcionalidad', 'otro'];

    // Prioridad según la categoría (la elige el sistema, no el usuario)
    const PRIORIDAD_CATEGORIA = [
        'tecnico'     => 'alta',
        'cuenta'      => 'baja',
        'facturacion' => 'critica',
    ];

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Dato personal de un usuario según su rol: cada rol lo guarda en su propia tabla.
     * Subconsultas con LIMIT 1 para no duplicar filas si un usuario aparece en varias clínicas.
     */
    private static function sqlDatoPersona(string $columnaUsuario, string $campo): string
    {
        $partes = [];
        foreach (['administrador', 'representante_legal', 'profesional', 'propietario'] as $tabla) {
            $partes[] = "(SELECT $campo FROM $tabla WHERE id_usuario = $columnaUsuario LIMIT 1)";
        }
        return 'COALESCE(' . implode(', ', $partes) . ", '')";
    }

    /** Todos los tickets, de cualquier rol que los haya creado. */
    public function listarTickets(): array
    {
        $sql = "SELECT tic.id, tic.titulo, tic.categoria, tic.prioridad, tic.estado, tic.fecha_creacion,
                    " . self::sqlDatoPersona('tic.usuario_id', 'nombres') . " AS nombres,
                    " . self::sqlDatoPersona('tic.usuario_id', 'apellidos') . " AS apellidos,
                    TRIM(CONCAT(COALESCE(adm.nombres, ''), ' ', COALESCE(adm.apellidos, ''))) AS asignado
                FROM tickets tic
                LEFT JOIN administrador adm ON adm.id_usuario = tic.asignado_a
                ORDER BY tic.id DESC";
        try {
            return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[Ticket::listarTickets] ' . $e->getMessage());
            return [];
        }
    }

    /** Tickets sin cerrar para la campanita del administrador. */
    public function listarTicketsPendientes(int $limite = 10): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT id, titulo, categoria, prioridad, estado, fecha_creacion
             FROM tickets
             WHERE estado <> 'cerrado'
             ORDER BY id DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTicketPorId(int $id): ?array
    {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT tk.id, tk.titulo, tk.fecha_creacion, tk.fecha_cierre, tk.categoria, tk.prioridad, tk.estado,
                        tk.asignado_a AS id_asignado, adm.nombres AS nombre_asignado, adm.apellidos AS apellido_asignado,
                        tk.descripcion, tk.usuario_id AS id_usuario, tk.solucion AS resultado, tk.archivo
                 FROM tickets tk
                 LEFT JOIN administrador adm ON adm.id_usuario = tk.asignado_a
                 WHERE tk.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('[Ticket::obtenerTicketPorId] ' . $e->getMessage());
            return null;
        }
    }

    /** Datos básicos de quien creó el ticket, para cualquier rol (respaldo de la vista). */
    public function datosCreador(int $idUsuario): ?array
    {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT us.id_usuario, us.email, rol.nombre AS rol_name,
                        " . self::sqlDatoPersona('us.id_usuario', 'nombres') . " AS nombres,
                        " . self::sqlDatoPersona('us.id_usuario', 'apellidos') . " AS apellidos,
                        " . self::sqlDatoPersona('us.id_usuario', 'telefono') . " AS telefono
                 FROM usuario us
                 INNER JOIN rol ON rol.id_rol = us.id_rol
                 WHERE us.id_usuario = ?"
            );
            $stmt->execute([$idUsuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('[Ticket::datosCreador] ' . $e->getMessage());
            return null;
        }
    }

    /** ¿Es un administrador activo? (destino válido para asignar un ticket) */
    public function esAdministradorActivo(int $idUsuario): bool
    {
        $stmt = $this->conexion->prepare(
            "SELECT 1 FROM usuario us
             INNER JOIN administrador adm ON adm.id_usuario = us.id_usuario
             WHERE us.id_usuario = ? AND us.id_rol = 1 AND us.estado = 'activo'"
        );
        $stmt->execute([$idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    /** Crea el ticket y su registro de creación. Devuelve el id nuevo. */
    public function crearTicket(array $data): int
    {
        $prioridad = self::PRIORIDAD_CATEGORIA[$data['categoria']] ?? 'media';

        $this->conexion->beginTransaction();
        try {
            $this->conexion->prepare(
                "INSERT INTO tickets (titulo, descripcion, categoria, usuario_id, prioridad, estado, archivo)
                 VALUES (?, ?, ?, ?, ?, 'abierto', ?)"
            )->execute([$data['titulo'], $data['descripcion'], $data['categoria'], $data['id_usuario'], $prioridad, $data['archivo']]);
            $id = (int) $this->conexion->lastInsertId();

            $this->registrarHistorico($id, 'creacion', '', '', "Ticket creado con título: {$data['titulo']}", (int) $data['id_usuario']);
            $this->conexion->commit();
            return $id;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    /**
     * Asigna un ticket que aún no tiene responsable.
     * Devuelve false si ya estaba asignado (otro administrador lo tomó primero) o cerrado.
     */
    public function asignarTicket(int $ticketId, int $adminId, int $usuarioAccion): bool
    {
        $this->conexion->beginTransaction();
        try {
            $stmt = $this->conexion->prepare(
                "UPDATE tickets SET asignado_a = ?, estado = IF(estado = 'abierto', 'en_proceso', estado)
                 WHERE id = ? AND asignado_a IS NULL AND estado <> 'cerrado'"
            );
            $stmt->execute([$adminId, $ticketId]);
            if ($stmt->rowCount() === 0) {
                $this->conexion->rollBack();
                return false;
            }
            $nombre = $this->nombreAdministrador($adminId);
            $this->registrarHistorico($ticketId, 'asignacion', 'Sin asignar', $nombre, "Ticket asignado a $nombre", $usuarioAccion);
            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    /**
     * Cambia estado, solución y (opcional) responsable, y deja cada cambio en el histórico.
     * $ticket es la fila actual (de obtenerTicketPorId). Devuelve false si no había nada que cambiar.
     */
    public function actualizarTicket(array $ticket, string $nuevoEstado, string $solucion, ?int $reasignarA, int $usuarioAccion): bool
    {
        $ticketId = (int) $ticket['id'];
        $estadoActual = (string) $ticket['estado'];
        $solucionActual = (string) ($ticket['resultado'] ?? '');
        $reasignar = $reasignarA !== null && $reasignarA !== (int) $ticket['id_asignado'];

        if ($estadoActual === $nuevoEstado && !$reasignar && $solucionActual === $solucion) {
            return false;
        }

        $this->conexion->beginTransaction();
        try {
            $sql = "UPDATE tickets SET estado = :estado, solucion = :solucion";
            $params = [':estado' => $nuevoEstado, ':solucion' => $solucion === '' ? null : $solucion, ':id' => $ticketId];
            if ($reasignar) {
                $sql .= ", asignado_a = :asignado";
                $params[':asignado'] = $reasignarA;
            }
            $sql .= $nuevoEstado === 'cerrado' ? ", fecha_cierre = NOW()" : ", fecha_cierre = NULL";
            $this->conexion->prepare($sql . " WHERE id = :id")->execute($params);

            if ($estadoActual !== $nuevoEstado) {
                $this->registrarHistorico($ticketId, 'cambio_estado', $estadoActual, $nuevoEstado,
                    "Cambio de estado de '$estadoActual' a '$nuevoEstado'", $usuarioAccion);
            }
            if ($reasignar) {
                $anterior = $ticket['id_asignado'] ? $this->nombreAdministrador((int) $ticket['id_asignado']) : 'Sin asignar';
                $nuevo = $this->nombreAdministrador($reasignarA);
                $this->registrarHistorico($ticketId, 'reasignacion', $anterior, $nuevo, "Reasignación del ticket a $nuevo", $usuarioAccion);
            }
            if ($solucionActual !== $solucion) {
                $this->registrarHistorico($ticketId, 'modificacion', $solucionActual, $solucion,
                    'Modificación de la solución del ticket', $usuarioAccion);
            }
            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function consultarHistoricoTicket(int $ticketId): array
    {
        $stmt = $this->conexion->prepare(
            "SELECT ht.fecha, ht.descripcion, ht.tipo, ht.valor_anterior, ht.valor_nuevo,
                    TRIM(CONCAT(" . self::sqlDatoPersona('ht.usuario_id', 'nombres') . ", ' ',
                                " . self::sqlDatoPersona('ht.usuario_id', 'apellidos') . ")) AS usuario
             FROM historico_tikects ht
             WHERE ht.ticket_id = ?
             ORDER BY ht.fecha DESC"
        );
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function nombreAdministrador(int $idUsuario): string
    {
        $stmt = $this->conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        return (string) ($stmt->fetchColumn() ?: 'Desconocido');
    }

    // "mensaje" es la columna original de la tabla; se llena con la misma descripción
    private function registrarHistorico(int $ticketId, string $tipo, string $anterior, string $nuevo, string $descripcion, int $usuarioId): void
    {
        $this->conexion->prepare(
            "INSERT INTO historico_tikects (ticket_id, usuario_id, tipo, valor_anterior, valor_nuevo, descripcion, mensaje, fecha)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        )->execute([$ticketId, $usuarioId, $tipo, $anterior, $nuevo, $descripcion, $descripcion]);
    }
}
