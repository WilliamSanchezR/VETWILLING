<?php

require_once __DIR__ . '/../models/SoporteCuenta.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';

/**
 * Soporte de cuentas del superadministrador:
 *   - claveTemporal()  asigna una clave temporal que el usuario debe cambiar al entrar
 *   - enviarPrueba()   envía un correo de prueba a la dirección de la cuenta
 *   - estadoBrevo()    qué pasó con los últimos envíos a esa dirección según Brevo
 */
class SoporteCuentaService
{
    // Sin caracteres que se confunden al dictarla (0/O, 1/l/I)
    const ALFABETO_CLAVE = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const LONGITUD_CLAVE = 12;
    const DIAS_BREVO     = 30;

    // Eventos de la API de Brevo (smtp/statistics/events)
    const EVENTOS_BREVO = [
        'requests'     => ['Recibido por Brevo', 'info'],
        'delivered'    => ['Entregado', 'ok'],
        'opened'       => ['Abierto', 'ok'],
        'clicks'       => ['Clic en un enlace', 'ok'],
        'deferred'     => ['Demorado (Brevo reintenta)', 'aviso'],
        'softBounces'  => ['Rebote temporal', 'aviso'],
        'hardBounces'  => ['Rebote permanente', 'error'],
        'invalid'      => ['Dirección inválida', 'error'],
        'blocked'      => ['Bloqueado', 'error'],
        'error'        => ['Rechazado por Brevo', 'error'],
        'spam'         => ['Marcado como spam', 'error'],
        'unsubscribed' => ['Canceló la suscripción', 'aviso'],
    ];

    private $modelo;

    public function __construct()
    {
        $this->modelo = new SoporteCuenta();
    }

    public static function generarClave(): string
    {
        $max = strlen(self::ALFABETO_CLAVE) - 1;
        do {
            $clave = '';
            for ($i = 0; $i < self::LONGITUD_CLAVE; $i++) {
                $clave .= self::ALFABETO_CLAVE[random_int(0, $max)];
            }
        } while (!preg_match('/[a-z]/', $clave) || !preg_match('/[A-Z]/', $clave) || !preg_match('/[0-9]/', $clave));
        return $clave;
    }

    /**
     * @return array{ok: bool, mensaje: string, clave?: string}
     */
    public function claveTemporal(int $idUsuario, int $idAdmin): array
    {
        if ($idUsuario === $idAdmin) {
            return ['ok' => false, 'mensaje' => 'Para tu propia cuenta usa "Mi perfil" → cambiar contraseña.'];
        }
        $cuenta = $this->modelo->obtener($idUsuario);
        if ($cuenta === null) {
            return ['ok' => false, 'mensaje' => 'La cuenta no existe.'];
        }
        if (mb_strtolower(trim($cuenta['estado'])) !== 'activo') {
            return ['ok' => false, 'mensaje' => 'La cuenta está ' . $cuenta['estado'] . ': actívala antes de asignarle una clave.'];
        }

        $clave = self::generarClave();
        try {
            $this->modelo->asignarClaveTemporal($idUsuario, password_hash($clave, PASSWORD_DEFAULT));
        } catch (PDOException $e) {
            error_log('[soporte-cuentas] no se pudo asignar la clave temporal: ' . $e->getMessage());
            return ['ok' => false, 'mensaje' => 'No se pudo asignar la clave. ¿Se aplicó la migración database/soporte_cuentas.sql?'];
        }
        // Auditoría sin la clave
        error_log(sprintf('[soporte-cuentas] el administrador %d asignó una clave temporal al usuario %d', $idAdmin, $idUsuario));
        return ['ok' => true, 'mensaje' => 'Clave temporal asignada. Se cerraron sus sesiones abiertas.', 'clave' => $clave];
    }

    /**
     * @return array{ok: bool, mensaje: string}
     */
    public function enviarPrueba(int $idUsuario, int $idAdmin): array
    {
        $cuenta = $this->modelo->obtener($idUsuario);
        if ($cuenta === null || !filter_var($cuenta['email'], FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensaje' => 'La cuenta no tiene un correo válido.'];
        }
        $mail = null;
        try {
            $mail = mailer_init();
            $mail->addAddress($cuenta['email']);
            $mail->Subject = 'Correo de prueba - VetWilling';
            $mail->Body = '<p>Hola,</p><p>Este es un correo de prueba que te envió el equipo de soporte de '
                . '<strong>VetWilling</strong> para comprobar que tu correo recibe nuestros mensajes.</p>'
                . '<p>No tienes que hacer nada.</p>';
            $mail->AltBody = "Hola,\n\nEste es un correo de prueba que te envió el equipo de soporte de VetWilling "
                . "para comprobar que tu correo recibe nuestros mensajes.\n\nNo tienes que hacer nada.";
            if (!mailer_enviar($mail, 'prueba')) {
                return ['ok' => false, 'mensaje' => 'No se envió: el canal de correo está deshabilitado en Medios de envío.'];
            }
        } catch (Throwable $e) {
            return ['ok' => false, 'mensaje' => 'Falló el envío: ' . mailer_error_seguro(($mail && $mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage())];
        }
        error_log(sprintf('[soporte-cuentas] el administrador %d envió un correo de prueba al usuario %d', $idAdmin, $idUsuario));
        $aviso = MAIL_DRIVER === 'brevo' ? ' Brevo lo aceptó; en unos segundos revisa "Estado en Brevo" para saber si se entregó.' : '';
        return ['ok' => true, 'mensaje' => "Correo de prueba enviado a {$cuenta['email']}.{$aviso}"];
    }

    /**
     * Eventos de Brevo de los últimos días para la dirección, del más reciente al más antiguo.
     *
     * @return array{disponible: bool, mensaje?: string, eventos?: array, resumen?: array}
     */
    public function estadoBrevo(string $email): array
    {
        if (MAIL_DRIVER !== 'brevo' || BREVO_API_KEY === '') {
            return ['disponible' => false, 'mensaje' => 'El correo no se envía con Brevo: no hay registro de entregas.'];
        }
        $url = 'https://api.brevo.com/v3/smtp/statistics/events?' . http_build_query([
            'email' => $email, 'days' => self::DIAS_BREVO, 'limit' => 50, 'sort' => 'desc',
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['accept: application/json', 'api-key: ' . BREVO_API_KEY],
        ]);
        $respuesta = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);

        if ($respuesta === false || $codigo !== 200) {
            error_log('[soporte-cuentas] Brevo respondió ' . $codigo . ' ' . mailer_error_seguro($errorCurl ?: (string) $respuesta));
            return ['disponible' => false, 'mensaje' => 'No se pudo consultar Brevo en este momento (HTTP ' . $codigo . ').'];
        }

        $eventos = [];
        foreach (json_decode($respuesta, true)['events'] ?? [] as $ev) {
            [$etiqueta, $nivel] = self::EVENTOS_BREVO[$ev['event'] ?? ''] ?? [(string) ($ev['event'] ?? '?'), 'info'];
            $eventos[] = [
                'mensaje'   => $ev['messageId'] ?? '',
                'fecha'     => $ev['date'] ?? '',
                'evento'    => $etiqueta,
                'nivel'     => $nivel,
                'asunto'    => $ev['subject'] ?? '',
                'remitente' => $ev['from'] ?? '',
                'motivo'    => $ev['reason'] ?? '',
            ];
        }
        usort($eventos, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

        return ['disponible' => true, 'eventos' => $eventos, 'resumen' => self::resumir($eventos)];
    }

    /**
     * Conclusión en una frase sobre el envío más reciente (todos sus eventos comparten messageId).
     * Si ese envío tiene un error, gana el error aunque también tenga otros eventos.
     */
    private static function resumir(array $eventos): array
    {
        if (!$eventos) {
            return ['nivel' => 'info', 'texto' => 'Brevo no tiene envíos a este correo en los últimos ' . self::DIAS_BREVO . ' días.'];
        }
        $ultimo = $eventos[0]['mensaje'];
        $peor = null;
        $gravedad = ['info' => 0, 'ok' => 1, 'aviso' => 2, 'error' => 3];
        foreach ($eventos as $ev) {
            if ($ev['mensaje'] === $ultimo && ($peor === null || $gravedad[$ev['nivel']] > $gravedad[$peor['nivel']])) {
                $peor = $ev;
            }
        }
        $asunto = $peor['asunto'] !== '' ? ' ("' . $peor['asunto'] . '")' : '';
        $texto = match ($peor['nivel']) {
            'ok'    => "El correo funciona: el último envío{$asunto} se entregó.",
            'aviso' => "El último envío{$asunto} tuvo problemas temporales: {$peor['evento']}.",
            'error' => "El último envío{$asunto} falló: {$peor['evento']}" . ($peor['motivo'] !== '' ? " ({$peor['motivo']})" : '') . '.',
            default => "Brevo recibió el último envío{$asunto} pero aún no informa si se entregó. Vuelve a consultar en unos segundos.",
        };
        return ['nivel' => $peor['nivel'], 'texto' => $texto];
    }
}
