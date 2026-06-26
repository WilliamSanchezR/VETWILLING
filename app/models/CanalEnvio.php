<?php

require_once __DIR__ . '/../../config/database.php';

class CanalEnvio
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Conexion())->getConexion();
    }

    // ── Canales ──────────────────────────────────────────────

    public function listarCanales(): array
    {
        $stmt = $this->db->query(
            "SELECT id_canal, codigo_canal, nombre_canal, descripcion, habilitado,
                    smtp_host, smtp_port, smtp_encriptacion, smtp_usuario,
                    smtp_remitente, smtp_nombre_remitente,
                    push_endpoint,
                    max_reintentos, intervalo_reintento_seg,
                    fecha_modificacion
             FROM canal_envio_config
             ORDER BY id_canal"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM canal_envio_config WHERE codigo_canal = :codigo LIMIT 1"
        );
        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM canal_envio_config WHERE id_canal = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function estaHabilitado(string $codigo): bool
    {
        $stmt = $this->db->prepare(
            "SELECT habilitado FROM canal_envio_config WHERE codigo_canal = :codigo LIMIT 1"
        );
        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (bool)$row['habilitado'];
    }

    // Actualiza parámetros técnicos (SMTP, push) de un canal
    public function actualizarConfig(int $idCanal, array $datos): bool
    {
        $permitidos = [
            'nombre_canal', 'descripcion',
            'smtp_host', 'smtp_port', 'smtp_encriptacion',
            'smtp_usuario', 'smtp_password',
            'smtp_remitente', 'smtp_nombre_remitente',
            'push_api_key', 'push_endpoint',
            'max_reintentos', 'intervalo_reintento_seg',
            'modificado_por',
        ];

        $sets  = [];
        $bind  = [':id_canal' => $idCanal];
        foreach ($datos as $k => $v) {
            if (in_array($k, $permitidos, true)) {
                $sets[]       = "`$k` = :$k";
                $bind[":$k"]  = ($v === '') ? null : $v;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE canal_envio_config SET " . implode(', ', $sets) .
               " WHERE id_canal = :id_canal";
        return $this->db->prepare($sql)->execute($bind);
    }

    public function toggleHabilitado(int $idCanal, bool $habilitado): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE canal_envio_config SET habilitado = :h WHERE id_canal = :id"
        );
        return $stmt->execute([':h' => (int)$habilitado, ':id' => $idCanal]);
    }

    // ── Plantillas ───────────────────────────────────────────

    public function listarPlantillas(int $idCanal): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_plantilla, tipo_notificacion, asunto,
                    cuerpo_html, cuerpo_texto, variables_disponibles, activa,
                    fecha_modificacion
             FROM plantilla_mensaje_canal
             WHERE id_canal = :id
             ORDER BY tipo_notificacion"
        );
        $stmt->execute([':id' => $idCanal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPlantilla(int $idCanal, string $tipo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM plantilla_mensaje_canal
             WHERE id_canal = :id AND tipo_notificacion = :tipo AND activa = 1
             LIMIT 1"
        );
        $stmt->execute([':id' => $idCanal, ':tipo' => $tipo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function guardarPlantilla(array $d): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO plantilla_mensaje_canal
                 (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, activa)
             VALUES (:id_canal, :tipo, :asunto, :html, :texto, :activa)
             ON DUPLICATE KEY UPDATE
                 asunto       = VALUES(asunto),
                 cuerpo_html  = VALUES(cuerpo_html),
                 cuerpo_texto = VALUES(cuerpo_texto),
                 activa       = VALUES(activa)"
        );
        return $stmt->execute([
            ':id_canal' => $d['id_canal'],
            ':tipo'     => $d['tipo_notificacion'],
            ':asunto'   => $d['asunto']      ?? null,
            ':html'     => $d['cuerpo_html'] ?? null,
            ':texto'    => $d['cuerpo_texto'] ?? null,
            ':activa'   => $d['activa']      ?? 1,
        ]);
    }

    // ── Monitoreo de fallos ──────────────────────────────────

    public function obtenerFallosRecientes(int $horas = 24, int $limite = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                 ne.id_notificacion,
                 ne.id_agendamiento,
                 ne.medio_notificacion,
                 ne.destinatario,
                 ne.estado_envio,
                 ne.mensaje_error,
                 ne.intentos_envio,
                 ne.fecha_envio,
                 ne.ultimo_intento,
                 CONCAT(p.nombres, ' ', p.apellidos) AS nombre_propietario,
                 a.fecha_hora                         AS fecha_cita
             FROM notificaciones_enviadas ne
             LEFT JOIN agendamiento a  ON ne.id_agendamiento = a.id_agendamiento
             LEFT JOIN propietario  p  ON a.id_propietario   = p.id_propietario
             WHERE ne.estado_envio = 'fallido'
               AND ne.fecha_envio >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
             ORDER BY ne.fecha_envio DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':horas',  $horas,  PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resumen de estados por canal para el dashboard de monitoreo
    public function obtenerResumenMonitoreo(int $horas = 24): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                 medio_notificacion                                              AS canal,
                 COUNT(*)                                                        AS total,
                 SUM(estado_envio = 'exitoso')                                  AS exitosos,
                 SUM(estado_envio = 'fallido')                                  AS fallidos,
                 SUM(estado_envio = 'pendiente')                                AS pendientes,
                 ROUND(AVG(intentos_envio), 1)                                  AS promedio_intentos,
                 MAX(ultimo_intento)                                             AS ultimo_intento
             FROM notificaciones_enviadas
             WHERE fecha_envio >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
             GROUP BY medio_notificacion"
        );
        $stmt->execute([':horas' => $horas]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marca fallidos como pendientes para que el cron los reintente
    public function reintentarFallidos(string $canal, int $maxIntentos = 3): int
    {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones_enviadas
             SET estado_envio = 'pendiente', ultimo_intento = NOW()
             WHERE medio_notificacion = :canal
               AND estado_envio = 'fallido'
               AND intentos_envio < :max"
        );
        $stmt->execute([':canal' => $canal, ':max' => $maxIntentos]);
        return $stmt->rowCount();
    }

    // ── Helper de renderizado de plantillas ──────────────────

    // Sustituye {variable} en el template con los valores del array $vars
    public static function renderizar(string $template, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $template = str_replace('{' . $k . '}', htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $template);
        }
        return $template;
    }
}
