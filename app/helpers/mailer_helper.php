<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/secretos_helper.php';

// ═══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE CORREO ELECTRÓNICO
// ═══════════════════════════════════════════════════════════════════════════
// Configuración SMTP para Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPT', 'smtps');
define('SMTP_DEBUG', 0); // 0 = Sin debug, 1 = debug básico, 2 = debug verboso
// Las credenciales se leen de .env (nunca se suben al repositorio)
define('SMTP_USER', env_value('SMTP_USER', 'vetwillingsoporte@gmail.com'));
define('SMTP_PASS', env_value('SMTP_PASS', '')); // APP PASSWORD de Gmail
define('SMTP_FROM_EMAIL', env_value('SMTP_FROM_EMAIL', SMTP_USER));
define('SMTP_FROM_NAME', 'VetWilling - Sistema de Gestión Veterinaria');

// Transporte de correo: 'brevo' (API HTTP, puerto 443) o 'smtp' (Gmail).
// Si hay BREVO_API_KEY en .env se usa Brevo, salvo que MAIL_DRIVER=smtp.
define('BREVO_API_KEY', env_value('BREVO_API_KEY', ''));
define('MAIL_DRIVER', env_value('MAIL_DRIVER', BREVO_API_KEY !== '' ? 'brevo' : 'smtp'));

// Configuración de notificaciones automáticas
define('HORAS_ANTICIPACION_RECORDATORIO', 24);
define('MAX_REINTENTOS_NOTIFICACION', 3);
define('INTERVALO_REINTENTO_NOTIFICACION', 30);

// Tiempo máximo de espera del servidor SMTP (PHPMailer usa 300 s por defecto y bloquearía la petición)
define('SMTP_TIMEOUT_SEG', 15);

// Únicos tipos de correo cuyo contenido se guarda y se puede reintentar desde el monitor.
// Bienvenidas (contraseña inicial), recuperación (enlace privado) y fichas clínicas (PDF) quedan fuera.
const MAILER_TIPOS_REENVIABLES = ['cita', 'recordatorio', 'cancelacion'];

// Único tipo que se envía aunque el canal de correo esté apagado
const MAILER_TIPO_CRITICO = 'seguridad';

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN: INICIALIZAR MAILER CON CONFIGURACIÓN CENTRALIZADA
// ARCHIVO: mailer_helper.php
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Configuración del canal de correo guardada en Medios de envío (canal_envio_config).
 * Devuelve [] si la tabla no está disponible, para que el correo siga funcionando con .env.
 */
function mailer_config_canal(): array
{
    // Sin caché a propósito: si el administrador apaga el canal, el cambio aplica al instante
    // (también en procesos largos como el cron de recordatorios)
    try {
        require_once __DIR__ . '/../models/CanalEnvio.php';
        return (new CanalEnvio())->obtenerPorCodigo('email') ?? [];
    } catch (Throwable $e) {
        error_log('[mailer_config_canal] ' . $e->getMessage());
        return [];
    }
}

/**
 * Describe el transporte de correo que se está usando de verdad (para mostrarlo en Medios de envío).
 */
function mailer_transporte_actual(): array
{
    $cfg = mailer_config_canal();
    if (MAIL_DRIVER === 'brevo') {
        return [
            'driver'  => 'brevo',
            'detalle' => 'API de Brevo (BREVO_API_KEY en .env). Los datos de servidor SMTP no se usan; el remitente sí.',
        ];
    }
    return [
        'driver'  => 'smtp',
        'detalle' => 'SMTP ' . (($cfg['smtp_host'] ?? '') ?: SMTP_HOST) . ':' . (($cfg['smtp_port'] ?? '') ?: SMTP_PORT),
    ];
}

/**
 * Contraseña SMTP: la de Medios de envío (descifrada). Nunca se escribe en logs.
 *
 * - Si hay una guardada y no se puede descifrar (clave distinta o valor alterado) lanza una
 *   excepción: usar otra contraseña ocultaría el fallo.
 * - SMTP_PASS de .env solo se usa si no hay contraseña guardada y el servidor y el usuario son
 *   los de .env, para no enviarla a un servidor o cuenta configurados desde la base de datos.
 *
 * @throws RuntimeException
 */
function mailer_password_smtp(array $cfg): string
{
    $guardada = $cfg['smtp_password'] ?? null;
    if ($guardada !== null && $guardada !== '') {
        try {
            return secreto_descifrar($guardada);
        } catch (Throwable $e) {
            error_log('[mailer] Contraseña SMTP de Medios de envío no utilizable: ' . $e->getMessage());
            throw new RuntimeException('La contraseña SMTP guardada en Medios de envío no se puede descifrar '
                . '(APP_ENCRYPTION_KEY distinta o valor alterado). Vuelve a escribirla en Medios de envío.');
        }
    }

    $host    = ($cfg['smtp_host'] ?? '') ?: SMTP_HOST;
    $usuario = ($cfg['smtp_usuario'] ?? '') ?: SMTP_USER;
    if (strcasecmp($host, SMTP_HOST) !== 0 || strcasecmp($usuario, SMTP_USER) !== 0) {
        throw new RuntimeException('No hay contraseña SMTP guardada para el servidor o usuario de Medios de envío. '
            . 'Escríbela en Medios de envío (la de .env solo vale para el servidor y usuario de .env).');
    }
    return SMTP_PASS;
}

/**
 * Limpia un error técnico antes de guardarlo o mostrarlo: quita credenciales que pudieran
 * aparecer, enlaces con token y lo recorta.
 */
function mailer_error_seguro(string $error): string
{
    $secretos = array_filter([SMTP_PASS, BREVO_API_KEY]);
    $cfg = mailer_config_canal();
    try {
        $secretos[] = secreto_descifrar($cfg['smtp_password'] ?? null);
    } catch (Throwable $e) {
        // sin clave: no hay contraseña descifrada que ocultar
    }
    foreach (array_filter($secretos) as $secreto) {
        $error = str_replace($secreto, '[oculto]', $error);
    }
    $error = preg_replace('/(token|key|password|clave)=[^\s&"]+/i', '$1=[oculto]', $error);
    return mb_substr(trim($error), 0, 500);
}

/**
 * Distingue errores permanentes (no tiene sentido reintentar) de temporales.
 * SMTP 5xx sobre el destinatario o direcciones inválidas son permanentes.
 */
function mailer_error_es_permanente(string $error): bool
{
    return (bool) preg_match(
        '/\b(550|551|553|554)\b|invalid (email|address|recipient)|user unknown|mailbox unavailable|no existe/i',
        $error
    );
}

/**
 * Inicializa una instancia de PHPMailer con configuración centralizada.
 * Con MAIL_DRIVER=brevo devuelve un BrevoMailer (misma API, envío por HTTPS).
 *
 * Usa los parámetros guardados en Medios de envío (servidor SMTP, usuario,
 * contraseña y remitente). Si un campo está vacío, toma el valor de .env.
 *
 * @return PHPMailer Instancia configurada de PHPMailer
 */
function mailer_init()
{
    $cfg = mailer_config_canal();
    $remitente       = ($cfg['smtp_remitente'] ?? '') ?: SMTP_FROM_EMAIL;
    $nombreRemitente = ($cfg['smtp_nombre_remitente'] ?? '') ?: SMTP_FROM_NAME;

    if (MAIL_DRIVER === 'brevo') {
        require_once __DIR__ . '/BrevoMailer.php';
        $mail = new BrevoMailer(BREVO_API_KEY, true);
        $mail->CharSet = "UTF-8";
        $mail->isHTML(true);
        $mail->setFrom($remitente, $nombreRemitente);
        return $mail;
    }

    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);

    // Configuración de servidor SMTP (Medios de envío, con respaldo en .env)
    $mail->SMTPDebug = SMTP_DEBUG;
    $mail->isSMTP();
    $mail->Host = ($cfg['smtp_host'] ?? '') ?: SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = ($cfg['smtp_usuario'] ?? '') ?: SMTP_USER;
    $mail->Password = mailer_password_smtp($cfg);
    $encriptacion = $cfg['smtp_encriptacion'] ?? '';
    if ($encriptacion === 'ninguna') {
        // Solo para servidores locales de prueba (Mailpit, MailHog)
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    } else {
        $mail->SMTPSecure = ($encriptacion === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    }
    $mail->Port = (int) (($cfg['smtp_port'] ?? '') ?: SMTP_PORT);
    $mail->Timeout = SMTP_TIMEOUT_SEG;

    // Configuración de idioma y encoding
    $mail->CharSet = "UTF-8";
    $mail->isHTML(true);

    // Email remitente
    $mail->setFrom($remitente, $nombreRemitente);

    return $mail;
}

/**
 * Envía un correo respetando Medios de envío y lo registra en el monitor.
 * Usar en lugar de $mail->send().
 *
 * - Si el canal de correo está deshabilitado, no envía (salvo 'critico' => true,
 *   para correos que no se pueden bloquear, como recuperar contraseña) y devuelve false.
 * - Registra el resultado en notificaciones_enviadas con asunto y contenido,
 *   para que el monitor lo muestre y "Reintentar" pueda reenviarlo.
 * - Si el envío falla relanza la excepción, igual que $mail->send(), para no
 *   cambiar el manejo de errores de quien llama.
 *
 * @param PHPMailer $mail     Correo ya armado (destinatario, asunto, cuerpo)
 * @param string    $tipo     'cita' | 'recordatorio' | 'cancelacion' | 'bienvenida' | 'reporte' | 'seguridad' | 'prueba'
 * @param array     $opciones 'id_agendamiento' => ?int, 'critico' => bool,
 *                            'guardar_contenido' => bool (por defecto, no en críticos: pueden llevar enlaces privados)
 */
function mailer_enviar(PHPMailer $mail, string $tipo, array $opciones = []): bool
{
    require_once __DIR__ . '/notificacion_helper.php';

    // La excepción del canal apagado solo vale para recuperar contraseña: ningún otro tipo puede usarla
    $critico = !empty($opciones['critico']) && $tipo === MAILER_TIPO_CRITICO;
    if (!empty($opciones['critico']) && !$critico) {
        error_log("[mailer_enviar] Se ignoró 'critico' para el tipo '$tipo': solo aplica a '" . MAILER_TIPO_CRITICO . "'.");
    }
    // El contenido solo se guarda para tipos de la lista (y nunca si quien llama lo prohíbe)
    $guardarContenido = in_array($tipo, MAILER_TIPOS_REENVIABLES, true)
        && ($opciones['guardar_contenido'] ?? true) !== false;
    $destinatarios = implode(', ', array_map(fn($d) => $d[0], $mail->getToAddresses()));

    $registro = [
        'id_agendamiento'    => $opciones['id_agendamiento'] ?? null,
        'medio_notificacion' => 'email',
        'tipo_notificacion'  => $tipo,
        'destinatario'       => $destinatarios,
        'asunto'             => $mail->Subject,
        'cuerpo_html'        => $guardarContenido ? $mail->Body : null,
        'intentos_envio'     => 1,
    ];

    // Sin configuración en BD (tabla ausente), el canal se considera habilitado para no bloquear correos
    $cfg = mailer_config_canal();
    $canalHabilitado = empty($cfg) || (bool) $cfg['habilitado'];

    if (!$critico && !$canalHabilitado) {
        registrarNotificacionEnviada($registro + [
            'estado_envio'  => 'omitido',
            'mensaje_error' => 'Canal de correo deshabilitado en Medios de envío',
        ]);
        return false;
    }

    try {
        $enviado = $mail->send();
    } catch (Throwable $e) {
        registrarNotificacionEnviada($registro + [
            'estado_envio'  => 'fallido',
            'mensaje_error' => mailer_error_seguro($mail->ErrorInfo ?: $e->getMessage()),
        ]);
        throw $e;
    }

    if ($enviado === false) {
        registrarNotificacionEnviada($registro + [
            'estado_envio'  => 'fallido',
            'mensaje_error' => mailer_error_seguro($mail->ErrorInfo ?: 'El servidor de correo rechazó el envío'),
        ]);
        return false;
    }

    registrarNotificacionEnviada($registro + ['estado_envio' => 'exitoso', 'mensaje_error' => null]);
    return true;
}

/**
 * Reenvía de verdad los correos fallidos que tienen su contenido guardado.
 * La usan el botón "Reintentar" de Medios de envío y scripts/reintentar-notificaciones.php (cron).
 *
 * Reglas:
 * - Solo tipos de MAILER_TIPOS_REENVIABLES con contenido guardado (nunca bienvenidas, recuperación ni fichas).
 * - Respeta "Máx. reintentos" e "Intervalo entre reintentos" del canal de correo.
 * - No reenvía lo ya entregado ni los 'omitido' (canal apagado) ni los 'rechazado' (error permanente).
 * - Una reserva 'reintentando' con más de 10 minutos se considera abandonada y vuelve a ser elegible.
 * - No reenvía avisos de citas que ya pasaron.
 * - Cada fila se reserva con un UPDATE condicional antes de enviarla: si dos procesos corren a la vez,
 *   solo uno la envía.
 *
 * @param bool $respetarIntervalo true en el cron; el botón manual reintenta sin esperar
 * @return array ['ok', 'mensaje', 'reenviados', 'fallidos', 'rechazados', 'sin_contenido', 'agotados']
 */
function reintentarCorreosFallidos(bool $respetarIntervalo = false, int $limite = 50): array
{
    require_once __DIR__ . '/../../config/database.php';

    $cfg = mailer_config_canal();
    if (!empty($cfg) && !$cfg['habilitado']) {
        return ['ok' => false, 'mensaje' => 'El canal de correo está deshabilitado: actívalo para poder reintentar.'];
    }

    $maxReintentos = (int) ($cfg['max_reintentos'] ?? MAX_REINTENTOS_NOTIFICACION);
    $intervalo     = (int) ($cfg['intervalo_reintento_seg'] ?? INTERVALO_REINTENTO_NOTIFICACION);
    $db = (new conexion())->getConexion();
    $tiposSql = "'" . implode("','", MAILER_TIPOS_REENVIABLES) . "'";

    $sql = "SELECT ne.id_notificacion, ne.destinatario, ne.asunto, ne.cuerpo_html, ne.estado_envio
            FROM notificaciones_enviadas ne
            LEFT JOIN agendamiento a ON a.id_agendamiento = ne.id_agendamiento
            WHERE ne.medio_notificacion = 'email'
              AND (ne.estado_envio IN ('fallido', 'pendiente')
                   -- reservas abandonadas (el proceso se cayó a mitad del envío)
                   OR (ne.estado_envio = 'reintentando' AND ne.ultimo_intento < DATE_SUB(NOW(), INTERVAL 10 MINUTE)))
              AND ne.tipo_notificacion IN ($tiposSql)
              AND ne.cuerpo_html IS NOT NULL
              AND ne.intentos_envio < :max
              AND (a.id_agendamiento IS NULL OR a.fecha_hora > NOW())"
        . ($respetarIntervalo
            ? " AND (ne.ultimo_intento IS NULL OR ne.ultimo_intento <= DATE_SUB(NOW(), INTERVAL :intervalo SECOND))"
            : "")
        . " ORDER BY ne.id_notificacion
            LIMIT :limite";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':max', $maxReintentos, PDO::PARAM_INT);
    if ($respetarIntervalo) $stmt->bindValue(':intervalo', $intervalo, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reserva atómica: solo continúa quien logra pasar la fila a 'reintentando'
    $reservar = $db->prepare(
        "UPDATE notificaciones_enviadas SET estado_envio = 'reintentando', ultimo_intento = NOW()
         WHERE id_notificacion = :id AND estado_envio = :estado"
    );
    $actualizar = $db->prepare(
        "UPDATE notificaciones_enviadas
         SET estado_envio = :estado, mensaje_error = :error,
             intentos_envio = intentos_envio + 1, ultimo_intento = NOW()
         WHERE id_notificacion = :id AND estado_envio = 'reintentando'"
    );

    $reenviados = $fallidos = $rechazados = 0;
    foreach ($candidatos as $fila) {
        $reservar->execute([':id' => $fila['id_notificacion'], ':estado' => $fila['estado_envio']]);
        if ($reservar->rowCount() !== 1) continue; // otro proceso ya la tomó

        $mail = null;
        try {
            $mail = mailer_init();
            foreach (array_filter(array_map('trim', explode(',', $fila['destinatario']))) as $correo) {
                $mail->addAddress($correo);
            }
            $mail->Subject = $fila['asunto'] ?? 'VetWilling';
            $mail->Body    = $fila['cuerpo_html'];
            $mail->AltBody = trim(html_entity_decode(strip_tags($fila['cuerpo_html']), ENT_QUOTES, 'UTF-8'));

            if ($mail->send() === false) {
                throw new RuntimeException($mail->ErrorInfo ?: 'El servidor de correo rechazó el envío');
            }
            $actualizar->execute([':estado' => 'exitoso', ':error' => null, ':id' => $fila['id_notificacion']]);
            $reenviados++;
        } catch (Throwable $e) {
            $error = mailer_error_seguro(($mail && $mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage());
            $permanente = mailer_error_es_permanente($error);
            $actualizar->execute([
                ':estado' => $permanente ? 'rechazado' : 'fallido',
                ':error'  => $error,
                ':id'     => $fila['id_notificacion'],
            ]);
            $permanente ? $rechazados++ : $fallidos++;
        }
    }

    // Lo que no se pudo intentar, para explicarlo en pantalla
    $conteo = $db->prepare(
        "SELECT
             SUM(cuerpo_html IS NULL)                                AS sin_contenido,
             SUM(cuerpo_html IS NOT NULL AND intentos_envio >= :max) AS agotados
         FROM notificaciones_enviadas
         WHERE medio_notificacion = 'email' AND estado_envio = 'fallido'"
    );
    $conteo->execute([':max' => $maxReintentos]);
    $resto = $conteo->fetch(PDO::FETCH_ASSOC) ?: [];
    $sinContenido = (int) ($resto['sin_contenido'] ?? 0);
    $agotados     = (int) ($resto['agotados'] ?? 0);

    $partes = [];
    if ($reenviados || $fallidos || $rechazados) {
        $partes[] = "$reenviados reenviados";
        $partes[] = "$fallidos volvieron a fallar";
        if ($rechazados) $partes[] = "$rechazados rechazados de forma permanente (dirección inválida o inexistente)";
    } else {
        $partes[] = 'No hay correos fallidos que se puedan reintentar';
    }
    if ($sinContenido) $partes[] = "$sinContenido no guardan contenido por privacidad y no se reenvían";
    if ($agotados)     $partes[] = "$agotados ya alcanzaron el máximo de $maxReintentos intentos";

    return [
        'ok'            => true,
        'mensaje'       => implode('. ', $partes) . '.',
        'reenviados'    => $reenviados,
        'fallidos'      => $fallidos,
        'rechazados'    => $rechazados,
        'sin_contenido' => $sinContenido,
        'agotados'      => $agotados,
    ];
}

/**
 * Envía UN correo de prueba al destinatario indicado (el administrador que lo pidió).
 * Lo usa el botón "Enviar correo de prueba". Respeta el canal (si está apagado no envía).
 *
 * @return array ['ok' => bool, 'mensaje' => string] con el error ya sanitizado
 */
function mailer_enviar_prueba(string $destino): array
{
    if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'Tu usuario no tiene un correo válido para la prueba.'];
    }

    $transporte = mailer_transporte_actual();
    $mail = null;
    try {
        $mail = mailer_init();
        $mail->addAddress($destino);
        $mail->Subject = 'Correo de prueba - VetWilling';
        $mail->Body = '<p>Este es un correo de prueba enviado desde <strong>Medios de envío</strong>.</p>'
            . '<p>Transporte: ' . htmlspecialchars($transporte['detalle'], ENT_QUOTES, 'UTF-8') . '</p>';
        $mail->AltBody = 'Correo de prueba enviado desde Medios de envío. Transporte: ' . $transporte['detalle'];

        return mailer_enviar($mail, 'prueba')
            ? ['ok' => true, 'mensaje' => "Correo de prueba enviado a {$destino}. Revisa tu bandeja (y spam)."]
            : ['ok' => false, 'mensaje' => 'No se envió: el canal de correo está deshabilitado.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'mensaje' => 'Falló el envío: ' . mailer_error_seguro(($mail && $mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage())];
    }
}
