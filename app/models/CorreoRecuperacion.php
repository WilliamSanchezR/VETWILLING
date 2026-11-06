<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Configuración del correo de recuperación de contraseña (tabla correo_recuperacion_config, fila 1).
 * Si la tabla no existe (migración database/soporte_cuentas.sql sin aplicar) se usan los valores
 * por defecto, para que la recuperación siga funcionando.
 */
class CorreoRecuperacion
{
    const MINUTOS_MIN = 5;
    const MINUTOS_MAX = 1440;

    const ASUNTO_DEFECTO  = 'VetWilling - Restablece tu contraseña';
    const MENSAJE_DEFECTO = "Hemos recibido una solicitud para restablecer la contraseña de la cuenta {email}.\n"
        . 'Haz clic en el botón para elegir una nueva contraseña.';

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    /** Valores guardados, con los campos vacíos ya completados con los de por defecto. */
    public function obtener(): array
    {
        $fila = [];
        try {
            $fila = $this->conexion->query('SELECT * FROM correo_recuperacion_config WHERE id = 1')->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('[CorreoRecuperacion] sin configuración guardada: ' . $e->getMessage());
        }

        return [
            'remitente'          => trim((string) ($fila['remitente'] ?? '')),
            'nombre_remitente'   => trim((string) ($fila['nombre_remitente'] ?? '')),
            'asunto'             => trim((string) ($fila['asunto'] ?? '')) ?: self::ASUNTO_DEFECTO,
            'mensaje'            => trim((string) ($fila['mensaje'] ?? '')) ?: self::MENSAJE_DEFECTO,
            'minutos_vigencia'   => (int) ($fila['minutos_vigencia'] ?? 30) ?: 30,
            'fecha_modificacion' => $fila['fecha_modificacion'] ?? null,
        ];
    }

    /** @return string[] Errores de validación (vacío si los datos son válidos) */
    public static function validar(array $d): array
    {
        $errores = [];
        $remitente = trim((string) ($d['remitente'] ?? ''));
        if ($remitente !== '' && !filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El remitente no es un correo válido.';
        }
        if (mb_strlen(trim((string) ($d['nombre_remitente'] ?? ''))) > 255) {
            $errores[] = 'El nombre del remitente es demasiado largo.';
        }
        if (mb_strlen(trim((string) ($d['asunto'] ?? ''))) > 150) {
            $errores[] = 'El asunto admite máximo 150 caracteres.';
        }
        if (mb_strlen(trim((string) ($d['mensaje'] ?? ''))) > 1000) {
            $errores[] = 'El mensaje admite máximo 1000 caracteres.';
        }
        $minutos = filter_var($d['minutos_vigencia'] ?? null, FILTER_VALIDATE_INT);
        if ($minutos === false || $minutos < self::MINUTOS_MIN || $minutos > self::MINUTOS_MAX) {
            $errores[] = 'La vigencia debe estar entre ' . self::MINUTOS_MIN . ' y ' . self::MINUTOS_MAX . ' minutos.';
        }
        return $errores;
    }

    /** Guarda la configuración. Un campo vacío vuelve a su valor por defecto. */
    public function guardar(array $d, int $idAdmin): bool
    {
        $vacioANulo = fn($v) => ($v = trim((string) $v)) === '' ? null : $v;
        $stmt = $this->conexion->prepare(
            'INSERT INTO correo_recuperacion_config (id, remitente, nombre_remitente, asunto, mensaje, minutos_vigencia, modificado_por)
             VALUES (1, :remitente, :nombre, :asunto, :mensaje, :minutos, :admin)
             ON DUPLICATE KEY UPDATE remitente = VALUES(remitente), nombre_remitente = VALUES(nombre_remitente),
                 asunto = VALUES(asunto), mensaje = VALUES(mensaje), minutos_vigencia = VALUES(minutos_vigencia),
                 modificado_por = VALUES(modificado_por)'
        );
        return $stmt->execute([
            ':remitente' => $vacioANulo(strtolower($d['remitente'] ?? '')),
            ':nombre'    => $vacioANulo($d['nombre_remitente'] ?? ''),
            ':asunto'    => $vacioANulo($d['asunto'] ?? ''),
            ':mensaje'   => $vacioANulo($d['mensaje'] ?? ''),
            ':minutos'   => (int) $d['minutos_vigencia'],
            ':admin'     => $idAdmin,
        ]);
    }

    /** Sustituye {email} y {minutos} en el asunto o el mensaje (texto plano, sin escapar). */
    public static function reemplazar(string $texto, string $email, int $minutos): string
    {
        return strtr($texto, ['{email}' => $email, '{minutos}' => (string) $minutos]);
    }
}
