<?php

require_once __DIR__ . '/../models/RecoveryPass.php';
require_once __DIR__ . '/../models/CorreoRecuperacion.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';

/**
 * Flujo de recuperación de contraseña por enlace con token:
 *   1. solicitar()     -> genera token de un solo uso y envía el enlace por correo
 *   2. validarToken()  -> comprueba que el enlace siga vigente
 *   3. restablecer()   -> guarda la nueva contraseña y consume el token
 *
 * La contraseña actual NO se modifica hasta el paso 3, así que nadie puede
 * dejar a otro usuario sin acceso solo por conocer su correo.
 */
class PasswordResetService
{
    // La vigencia del enlace de recuperación se configura en Medios de envío (CorreoRecuperacion)
    const VENTANA_LIMITE_MIN   = 60;
    const MAX_SOLICITUDES      = 5;   // por usuario o IP dentro de la ventana
    const LONGITUD_MINIMA      = 8;
    const MINUTOS_CUENTA_NUEVA = 4320; // 3 días para crear la primera contraseña

    private $modelo;
    private $config;

    public function __construct()
    {
        $this->modelo = new RecoveryPass();
        $this->config = (new CorreoRecuperacion())->obtener();
    }

    /**
     * Siempre responde igual exista o no el correo (evita enumerar usuarios).
     * Devuelve false solo si se superó el límite de solicitudes.
     */
    public function solicitar(string $email, string $ip): bool
    {
        $email   = trim(strtolower($email));
        $usuario = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $this->modelo->buscarUsuarioActivoPorEmail($email)
            : false;

        $idUsuario = $usuario ? (int) $usuario['id_usuario'] : null;
        if ($this->modelo->contarSolicitudesRecientes($idUsuario, $ip, self::VENTANA_LIMITE_MIN) >= self::MAX_SOLICITUDES) {
            return false;
        }

        if (!$usuario) {
            return true;
        }

        $token = bin2hex(random_bytes(32));

        $this->modelo->invalidarTokensUsuario($idUsuario);
        $this->modelo->crearToken($idUsuario, hash('sha256', $token), $this->config['minutos_vigencia'], $ip);

        try {
            $this->enviarCorreo($usuario['email'], BASE_URL . '/restablecer?token=' . $token);
        } catch (Throwable $e) {
            // El token queda creado pero la contraseña no cambió: el usuario solo debe reintentar
            error_log('PasswordResetService::solicitar -> fallo envío a ' . $usuario['email'] . ': ' . $e->getMessage());
        }

        return true;
    }

    /** Clave imposible de adivinar para una cuenta nueva: el usuario crea la suya con el enlace. */
    public static function claveInicialAleatoria(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Envía a una cuenta nueva el enlace para crear su contraseña.
     * Si falla, el usuario puede pedir otro desde "Recuperar contraseña".
     */
    public function enviarEnlaceCuentaNueva(string $email): bool
    {
        $email   = trim(strtolower($email));
        $usuario = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $this->modelo->buscarUsuarioActivoPorEmail($email)
            : false;
        if (!$usuario) {
            return false;
        }

        try {
            $idUsuario = (int) $usuario['id_usuario'];
            $token = bin2hex(random_bytes(32));
            $this->modelo->invalidarTokensUsuario($idUsuario);
            $this->modelo->crearToken($idUsuario, hash('sha256', $token), self::MINUTOS_CUENTA_NUEVA, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $this->enviarCorreo($usuario['email'], BASE_URL . '/restablecer?token=' . $token, true);
            return true;
        } catch (Throwable $e) {
            error_log('PasswordResetService::enviarEnlaceCuentaNueva -> fallo de envío: ' . $e->getMessage());
            return false;
        }
    }

    public function validarToken(string $token)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }
        return $this->modelo->buscarTokenVigente(hash('sha256', $token));
    }

    /**
     * @return string|null Mensaje de error, o null si se restableció correctamente
     */
    public function restablecer(string $token, string $clave, string $confirmacion): ?string
    {
        $registro = $this->validarToken($token);
        if (!$registro) {
            return 'El enlace no es válido o ya expiró. Solicita uno nuevo.';
        }
        if (strlen($clave) < self::LONGITUD_MINIMA) {
            return 'La contraseña debe tener al menos ' . self::LONGITUD_MINIMA . ' caracteres.';
        }
        if ($clave !== $confirmacion) {
            return 'Las contraseñas no coinciden.';
        }

        $ok = $this->modelo->actualizarClaveYConsumirToken(
            (int) $registro['id_reset'],
            (int) $registro['id_usuario'],
            password_hash($clave, PASSWORD_DEFAULT)
        );

        return $ok ? null : 'No se pudo actualizar la contraseña. Intenta de nuevo.';
    }

    /**
     * Correo de muestra con la configuración guardada, para el administrador que lo pide.
     * No crea ningún enlace real: el botón lleva a la página de inicio de sesión.
     */
    public function enviarPrueba(string $email): void
    {
        $this->enviarCorreo($email, BASE_URL . '/login', false, true);
    }

    private function enviarCorreo(string $email, string $enlace, bool $cuentaNueva = false, bool $prueba = false): void
    {
        $minutos = $cuentaNueva ? self::MINUTOS_CUENTA_NUEVA : $this->config['minutos_vigencia'];
        // Mensaje editable de la recuperación: texto plano, se escapa y respeta los saltos de línea
        $mensajeHtml = $cuentaNueva ? null : nl2br(htmlspecialchars(
            CorreoRecuperacion::reemplazar($this->config['mensaje'], $email, $minutos), ENT_QUOTES, 'UTF-8'
        ));

        ob_start();
        require __DIR__ . '/../views/emails/recuperar-password.php';
        $html = ob_get_clean();

        $mail = mailer_init();
        if ($this->config['remitente'] !== '') {
            $mail->setFrom($this->config['remitente'], $this->config['nombre_remitente'] ?: $mail->FromName);
        } elseif ($this->config['nombre_remitente'] !== '') {
            $mail->setFrom($mail->From, $this->config['nombre_remitente']);
        }
        $mail->addAddress($email);
        $mail->Subject = ($prueba ? '[Prueba] ' : '') . ($cuentaNueva
            ? 'VetWilling - Crea tu contraseña'
            : CorreoRecuperacion::reemplazar($this->config['asunto'], $email, $minutos));
        $mail->Body    = $html;
        $mail->AltBody = $cuentaNueva
            ? "Tu cuenta de VetWilling fue creada.\n\nCrea tu contraseña con este enlace (válido por 3 días):\n{$enlace}\n\nSi ya venció, pide otro desde Recuperar contraseña."
            : CorreoRecuperacion::reemplazar($this->config['mensaje'], $email, $minutos) . "\n\n"
            . "Abre este enlace (válido por {$minutos} minutos):\n{$enlace}\n\n"
            . "Si no fuiste tú, ignora este correo: tu contraseña actual no ha cambiado.";
        // Crítico: se envía aunque el canal de correo esté deshabilitado, y no se guarda
        // el contenido porque lleva el enlace privado para cambiar la contraseña
        mailer_enviar($mail, 'seguridad', ['critico' => true]);
    }
}
