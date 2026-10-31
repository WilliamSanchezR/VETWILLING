<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/secretos_helper.php';

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
                    fecha_modificacion,
                    smtp_password
             FROM canal_envio_config
             ORDER BY id_canal"
        );
        $canales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // La contraseña nunca sale del servidor: solo se informa su estado
        foreach ($canales as &$canal) {
            $canal['smtp_password_estado'] = secreto_estado($canal['smtp_password']);
            unset($canal['smtp_password']);
        }
        return $canales;
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

    /**
     * Valida los parámetros técnicos antes de guardarlos.
     * @return string[] Mensajes de error (vacío si todo es válido)
     */
    public static function validarConfig(array $d): array
    {
        $errores = [];
        if (isset($d['smtp_host']) && $d['smtp_host'] !== ''
            && !preg_match('/^[a-z0-9]([a-z0-9.-]{0,251}[a-z0-9])?$/i', $d['smtp_host'])) {
            $errores[] = 'El servidor SMTP no es un nombre de host válido.';
        }
        if (isset($d['smtp_port']) && $d['smtp_port'] !== ''
            && (!ctype_digit((string) $d['smtp_port']) || (int) $d['smtp_port'] < 1 || (int) $d['smtp_port'] > 65535)) {
            $errores[] = 'El puerto debe ser un número entre 1 y 65535.';
        }
        if (isset($d['smtp_encriptacion']) && $d['smtp_encriptacion'] !== ''
            && !in_array($d['smtp_encriptacion'], ['smtps', 'tls', 'ninguna'], true)) {
            $errores[] = 'La encriptación debe ser SMTPS, TLS o ninguna.';
        }
        foreach (['smtp_remitente' => 'El correo remitente', 'smtp_usuario' => 'El usuario SMTP'] as $campo => $nombre) {
            // El usuario SMTP puede no ser un correo (algunos proveedores usan un login), solo se valida el remitente
            if ($campo === 'smtp_remitente' && isset($d[$campo]) && $d[$campo] !== ''
                && !filter_var($d[$campo], FILTER_VALIDATE_EMAIL)) {
                $errores[] = "$nombre no es un correo válido.";
            }
        }
        foreach (['smtp_host', 'smtp_usuario', 'smtp_remitente', 'smtp_nombre_remitente'] as $campo) {
            if (isset($d[$campo]) && mb_strlen((string) $d[$campo]) > 255) {
                $errores[] = "El campo $campo supera 255 caracteres.";
            }
        }
        // Cifrada debe caber en la columna smtp_password varchar(255): 150 bytes ocupan 247 caracteres
        if (isset($d['smtp_password']) && strlen((string) $d['smtp_password']) > 150) {
            $errores[] = 'La contraseña SMTP supera 150 caracteres.';
        }
        if (isset($d['smtp_nombre_remitente']) && preg_match('/[\r\n]/', (string) $d['smtp_nombre_remitente'])) {
            $errores[] = 'El nombre del remitente no puede tener saltos de línea.';
        }
        if (isset($d['max_reintentos'])
            && (!ctype_digit((string) $d['max_reintentos']) || (int) $d['max_reintentos'] > 10)) {
            $errores[] = 'Máx. reintentos debe ser un número entre 0 y 10.';
        }
        if (isset($d['intervalo_reintento_seg'])
            && (!ctype_digit((string) $d['intervalo_reintento_seg']) || (int) $d['intervalo_reintento_seg'] > 86400)) {
            $errores[] = 'El intervalo debe ser un número de segundos entre 0 y 86400.';
        }
        return $errores;
    }

    // Actualiza parámetros técnicos (SMTP, push) de un canal.
    // Las credenciales se guardan cifradas; sin APP_ENCRYPTION_KEY se lanza excepción y no se guarda nada.
    public function actualizarConfig(int $idCanal, array $datos): bool
    {
        // Un secreto vacío significa "mantener el actual": nunca se borra al editar otros campos
        foreach (['smtp_password', 'push_api_key'] as $secreto) {
            if (!isset($datos[$secreto]) || $datos[$secreto] === '') {
                unset($datos[$secreto]);
            } else {
                $datos[$secreto] = secreto_cifrar((string) $datos[$secreto]);
            }
        }

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

    /**
     * Revisa que la plantilla solo use variables disponibles para su tipo.
     * @return string[] Mensajes de error
     */
    public function validarPlantilla(array $d): array
    {
        $errores = [];
        $stmt = $this->db->prepare(
            "SELECT variables_disponibles FROM plantilla_mensaje_canal
             WHERE id_canal = :id AND tipo_notificacion = :tipo LIMIT 1"
        );
        $stmt->execute([':id' => $d['id_canal'], ':tipo' => $d['tipo_notificacion']]);
        $permitidas = json_decode((string) $stmt->fetchColumn(), true) ?: [];

        if (trim((string) ($d['asunto'] ?? '')) === '') {
            $errores[] = 'El asunto es obligatorio.';
        }
        if (mb_strlen((string) ($d['asunto'] ?? '')) > 255) {
            $errores[] = 'El asunto supera 255 caracteres.';
        }
        $texto = ($d['asunto'] ?? '') . ' ' . ($d['cuerpo_html'] ?? '') . ' ' . ($d['cuerpo_texto'] ?? '');
        preg_match_all('/\{([a-z_]+)\}/', $texto, $usadas);
        $desconocidas = array_diff(array_unique($usadas[1]), $permitidas);
        if ($permitidas && $desconocidas) {
            $errores[] = 'Variables no disponibles: {' . implode('}, {', $desconocidas) . '}. Usa: {'
                . implode('}, {', $permitidas) . '}.';
        }
        return $errores;
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
                 ne.tipo_notificacion,
                 ne.destinatario,
                 ne.asunto,
                 (ne.cuerpo_html IS NOT NULL)          AS reintentable,
                 ne.estado_envio,
                 ne.mensaje_error,
                 ne.intentos_envio,
                 ne.fecha_envio,
                 ne.ultimo_intento,
                 a.fecha_hora                         AS fecha_cita
             FROM notificaciones_enviadas ne
             -- (antes se unía propietario por a.id_propietario, columna que no existe: la consulta fallaba siempre)
             LEFT JOIN agendamiento a  ON ne.id_agendamiento = a.id_agendamiento
             WHERE ne.estado_envio IN ('fallido', 'rechazado')
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
                 SUM(estado_envio = 'omitido')                                  AS omitidos,
                 SUM(estado_envio = 'rechazado')                                AS rechazados,
                 ROUND(AVG(intentos_envio), 1)                                  AS promedio_intentos,
                 MAX(ultimo_intento)                                             AS ultimo_intento
             FROM notificaciones_enviadas
             WHERE fecha_envio >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
             GROUP BY medio_notificacion"
        );
        $stmt->execute([':horas' => $horas]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Los reintentos reales están en reintentarCorreosFallidos() (app/helpers/mailer_helper.php):
    // reenvían el correo guardado, no solo cambian su estado.

    // ── Helper de renderizado de plantillas ──────────────────

    // Sustituye {variable} en el template con los valores del array $vars.
    // Las variables sin valor se quitan para que el destinatario no vea "{algo}".
    public static function renderizar(string $template, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $template = str_replace('{' . $k . '}', htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $template);
        }
        return preg_replace('/\{[a-z_]+\}/', '', $template);
    }
}
