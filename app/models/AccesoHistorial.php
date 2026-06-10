<?php
/**
 * AccesoHistorial.php
 * Modelo que gestiona los permisos temporales de acceso
 * al historial clínico de una mascota.
 */

require_once __DIR__ . '/../../config/database.php'; 

class AccesoHistorial
{
    private PDO $db;

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }
    /* ================================================================
       CLIENTE — solicitar acceso
    ================================================================ */

    /**
     * Crea una solicitud de acceso si no hay una pendiente o vigente.
     * Devuelve el id de la solicitud creada, o false si ya existe una.
     */
    public function solicitar(int $id_paciente, int $id_propietario): int|false
    {
        /* Bloquear si ya hay una solicitud pendiente o acceso vigente */
        if ($this->tieneSolicitudActiva($id_paciente, $id_propietario)) {
            return false;
        }

        $sql = "INSERT INTO acceso_historial_clinico
                    (id_paciente, id_propietario, estado, fecha_solicitud)
                VALUES (:pac, :prop, 'pendiente', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pac' => $id_paciente, ':prop' => $id_propietario]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Guarda el id de la notificación creada para la solicitud.
     */
    public function vincularNotificacion(int $id_acceso, int $id_notificacion): void
    {
        $this->db->prepare(
            "UPDATE acceso_historial_clinico
             SET notificacion_id = :nid
             WHERE id = :id"
        )->execute([':nid' => $id_notificacion, ':id' => $id_acceso]);
    }

    /* ================================================================
       VETERINARIO — aprobar / rechazar
    ================================================================ */

    /**
     * Aprueba la solicitud y calcula la fecha de expiración.
     *
     * @param  int $id_acceso      ID del registro en acceso_historial_clinico
     * @param  int $id_veterinario ID del veterinario que aprueba
     * @param  int $duracion_horas Horas de acceso: 24 | 48 | 72 | 168
     */
    public function aprobar(int $id_acceso, int $id_veterinario, int $duracion_horas): bool
    {
        $horas = in_array($duracion_horas, [24, 48, 72, 168], true)
            ? $duracion_horas
            : 24;

        $stmt = $this->db->prepare(
            "UPDATE acceso_historial_clinico
             SET estado           = 'aprobado',
                 id_veterinario   = :vet,
                 duracion_horas   = :dur,
                 fecha_aprobacion = NOW(),
                 fecha_expiracion = DATE_ADD(NOW(), INTERVAL :dur2 HOUR)
             WHERE id = :id AND estado = 'pendiente'"
        );

        return $stmt->execute([
            ':vet'  => $id_veterinario,
            ':dur'  => $horas,
            ':dur2' => $horas,
            ':id'   => $id_acceso,
        ]) && $stmt->rowCount() > 0;
    }

    /**
     * Rechaza la solicitud con un motivo opcional.
     */
    public function rechazar(int $id_acceso, int $id_veterinario, string $motivo = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE acceso_historial_clinico
             SET estado          = 'rechazado',
                 id_veterinario  = :vet,
                 motivo_rechazo  = :mot,
                 fecha_aprobacion= NOW()
             WHERE id = :id AND estado = 'pendiente'"
        );

        return $stmt->execute([
            ':vet' => $id_veterinario,
            ':mot' => $motivo,
            ':id'  => $id_acceso,
        ]) && $stmt->rowCount() > 0;
    }

    /* ================================================================
       VERIFICACIÓN DE ACCESO
    ================================================================ */

    /**
     * Comprueba si el propietario tiene acceso vigente al historial
     * clínico de la mascota.
     * Expira automáticamente los registros vencidos.
     */
    public function tieneAccesoVigente(int $id_paciente, int $id_propietario): bool
    {
        /* Expirar automáticamente los que ya vencieron */
        $this->expirarVencidos($id_paciente, $id_propietario);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM acceso_historial_clinico
             WHERE id_paciente    = :pac
               AND id_propietario = :prop
               AND estado         = 'aprobado'
               AND fecha_expiracion > NOW()"
        );
        $stmt->execute([':pac' => $id_paciente, ':prop' => $id_propietario]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Devuelve el estado actual del acceso para mostrar en la UI del cliente.
     * Retorna: 'sin_solicitud' | 'pendiente' | 'aprobado' | 'rechazado' | 'expirado'
     */
    public function estadoAcceso(int $id_paciente, int $id_propietario): array
    {
        $this->expirarVencidos($id_paciente, $id_propietario);

        $stmt = $this->db->prepare(
            "SELECT id, estado, duracion_horas,
                    fecha_solicitud, fecha_aprobacion, fecha_expiracion,
                    motivo_rechazo,
                    TIMESTAMPDIFF(MINUTE, NOW(), fecha_expiracion) AS minutos_restantes
             FROM acceso_historial_clinico
             WHERE id_paciente    = :pac
               AND id_propietario = :prop
             ORDER BY fecha_solicitud DESC
             LIMIT 1"
        );
        $stmt->execute([':pac' => $id_paciente, ':prop' => $id_propietario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return ['estado' => 'sin_solicitud'];

        return [
            'estado'            => $row['estado'],
            'id'                => (int) $row['id'],
            'duracion_horas'    => (int) $row['duracion_horas'],
            'fecha_solicitud'   => $row['fecha_solicitud'],
            'fecha_aprobacion'  => $row['fecha_aprobacion'],
            'fecha_expiracion'  => $row['fecha_expiracion'],
            'minutos_restantes' => max(0, (int) $row['minutos_restantes']),
            'motivo_rechazo'    => $row['motivo_rechazo'],
        ];
    }

    /* ================================================================
       VETERINARIO — listar solicitudes pendientes
    ================================================================ */

    /**
     * Lista solicitudes pendientes para el panel del veterinario.
     * Incluye nombre del propietario y la mascota.
     */
    public function listarPendientes(int $id_veterinario): array
    {
        /* Traemos pendientes de mascotas que tienen citas con este vet */
        $stmt = $this->db->prepare(
            "SELECT
                a.id,
                a.id_paciente,
                a.id_propietario,
                a.fecha_solicitud,
                p.nombre        AS mascota_nombre,
                p.especie,
                u.nombres       AS propietario_nombres,
                u.apellidos     AS propietario_apellidos
             FROM acceso_historial_clinico a
             INNER JOIN pacientes        p  ON p.id_paciente  = a.id_paciente
             INNER JOIN propietarios     pr ON pr.id_propietario = a.id_propietario
             INNER JOIN usuarios         u  ON u.id_usuario   = pr.id_usuario
             WHERE a.estado = 'pendiente'
             ORDER BY a.fecha_solicitud ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una solicitud por ID (para validar que el vet puede aprobarla).
     */
    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM acceso_historial_clinico WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ================================================================
       HELPERS INTERNOS
    ================================================================ */

    private function tieneSolicitudActiva(int $id_paciente, int $id_propietario): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM acceso_historial_clinico
             WHERE id_paciente    = :pac
               AND id_propietario = :prop
               AND estado IN ('pendiente', 'aprobado')
               AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())"
        );
        $stmt->execute([':pac' => $id_paciente, ':prop' => $id_propietario]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function expirarVencidos(int $id_paciente, int $id_propietario): void
    {
        $this->db->prepare(
            "UPDATE acceso_historial_clinico
             SET estado = 'expirado'
             WHERE id_paciente    = :pac
               AND id_propietario = :prop
               AND estado         = 'aprobado'
               AND fecha_expiracion <= NOW()"
        )->execute([':pac' => $id_paciente, ':prop' => $id_propietario]);
    }
}