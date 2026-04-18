<?php

/**
 * SessionManager.php – VetCare Dashboard
 *
 * Gestiona sesiones activas del usuario en un archivo JSON por usuario.
 * Detecta SO, navegador, marca de dispositivo y geolocalización básica.
 *
 * Uso:
 *   $sm = new SessionManager($id_usuario);
 *   $sm->registrar();              // Al hacer login
 *   $sm->listar();                 // Para mostrar en perfil
 *   $sm->cerrar($token);           // Para cerrar una sesión específica
 *   $sm->cerrarTodas($excluir);    // Cerrar todas menos la actual
 */

class SessionManager
{
    private string $id_usuario;
    private string $archivo;
    private string $directorio;
    private string $token_actual;

    /**
     * @param int|string $id_usuario  ID del usuario autenticado
     * @param string     $base_dir    Directorio donde guardar los JSON
     *                                (debe estar fuera del webroot)
     */
    public function __construct(int|string $id_usuario, string $base_dir = '')
    {
        $this->id_usuario  = (string) $id_usuario;
        $this->directorio  = rtrim($base_dir ?: (BASE_PATH . '/storage/sesiones'), '/');
        $this->archivo     = $this->directorio . "/usuario_{$this->id_usuario}.json";
        $this->token_actual = $this->obtenerTokenActual();

        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0750, true);
        }
    }

    /* ─────────────────────────────────────────────────────────
       PÚBLICO
    ───────────────────────────────────────────────────────── */

    /**
     * Registra la sesión actual en el JSON.
     * Si el token ya existe, actualiza last_seen; si no, lo crea.
     */
    public function registrar(): void
    {
        $sesiones = $this->leer();
        $info     = $this->detectar();
        $token    = $this->token_actual;
        $ahora    = time();

        foreach ($sesiones as $t => $s) {
            if ($s['fingerprint'] === $info['fingerprint']) {
                $sesiones[$t]['last_seen'] = $ahora;
                $sesiones[$t]['ip'] = $info['ip'];
                $this->escribir($sesiones);
                return;
            }
        }

        // Marcar cuál es la sesión actual
        foreach ($sesiones as $t => &$s) {
            $s['is_current'] = ($t === $token);
        }
        unset($s);

        // Limpiar sesiones expiradas (más de 30 días sin actividad)
        $sesiones = array_filter($sesiones, function ($s) use ($ahora) {
            return ($ahora - $s['last_seen']) < (86400 * 30);
        });

        $this->escribir($sesiones);
    }

    /**
     * Devuelve todas las sesiones ordenadas: actual primero, luego por last_seen desc.
     */
    public function listar(): array
    {
        $sesiones = array_values($this->leer());

        usort($sesiones, function ($a, $b) {
            if ($a['is_current']) return -1;
            if ($b['is_current']) return  1;
            return $b['last_seen'] - $a['last_seen'];
        });

        return $sesiones;
    }

    /**
     * Cierra una sesión por token.
     */
    public function cerrar(string $token): bool
    {
        $sesiones = $this->leer();
        if (!isset($sesiones[$token]) || $sesiones[$token]['is_current']) {
            return false; // No se puede cerrar la sesión actual desde aquí
        }
        unset($sesiones[$token]);
        $this->escribir($sesiones);
        return true;
    }

    /**
     * Cierra todas las sesiones excepto la actual.
     */
    public function cerrarTodas(): int
    {
        $sesiones = $this->leer();
        $token    = $this->token_actual;
        $cerradas = 0;

        foreach (array_keys($sesiones) as $t) {
            if ($t !== $token) {
                unset($sesiones[$t]);
                $cerradas++;
            }
        }

        $this->escribir($sesiones);
        return $cerradas;
    }

    /**
     * Token de la sesión actual.
     */
    public function tokenActual(): string
    {
        return $this->token_actual;
    }

    /**
     * ¿La sesión actual es nueva (dispositivo no visto antes)?
     */
    public function esNueva(): bool
    {
        $sesiones = $this->leer();
        return isset($sesiones[$this->token_actual])
            && $sesiones[$this->token_actual]['es_nueva'] === true;
    }

    /* ─────────────────────────────────────────────────────────
       DETECCIÓN DE DISPOSITIVO
    ───────────────────────────────────────────────────────── */

    /**
     * Detecta SO, navegador, tipo de dispositivo, marca y geolocalización básica.
     */
    public function detectar(): array
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $this->obtenerIp();

        return [
            'ip'          => $ip,
            'user_agent'  => $ua,
            'navegador'   => $this->detectarNavegador($ua),
            'so'          => $this->detectarSO($ua),
            'tipo'        => $this->detectarTipo($ua),
            'marca'       => $this->detectarMarca($ua),
            'ciudad'      => $this->geolocalizarIp($ip),
            'fingerprint' => $this->fingerprint($ua),
        ];
    }

    /* ─────────────────────────────────────────────────────────
       PRIVADOS
    ───────────────────────────────────────────────────────── */

    private function leer(): array
    {
        if (!file_exists($this->archivo)) return [];
        $json = file_get_contents($this->archivo);
        return json_decode($json, true) ?: [];
    }

    private function escribir(array $data): void
    {
        file_put_contents(
            $this->archivo,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function obtenerTokenActual(): string
    {
        // Usa el ID de sesión PHP como base del token
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $key = 'sm_token_' . $this->id_usuario;
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        }
        return $_SESSION[$key];
    }

    private function obtenerIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                return trim(explode(',', $_SERVER[$h])[0]);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Geolocalización ligera usando ip-api.com (libre para uso no comercial).
     * En producción puedes reemplazarlo por MaxMind GeoIP2 local.
     */
    private function geolocalizarIp(string $ip): string
    {
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'])) {
            return 'Local / Desarrollo';
        }

        $cache_dir  = $this->directorio . '/geo_cache';
        $cache_file = $cache_dir . '/' . md5($ip) . '.json';

        if (!is_dir($cache_dir)) mkdir($cache_dir, 0750, true);

        // Caché de 24 horas para no abusar de la API
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
            $data = json_decode(file_get_contents($cache_file), true);
            return $data['ciudad'] ?? 'Desconocida';
        }

        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $raw = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city,country,regionName", false, $ctx);

        if ($raw) {
            $geo = json_decode($raw, true);
            if (($geo['status'] ?? '') === 'success') {
                $ciudad = trim(($geo['city'] ?? '') . ', ' . ($geo['country'] ?? ''));
                file_put_contents($cache_file, json_encode(['ciudad' => $ciudad]), LOCK_EX);
                return $ciudad;
            }
        }

        return 'Desconocida';
    }

    private function detectarNavegador(string $ua): array
    {
        $navegadores = [
            'Edg'      => ['nombre' => 'Edge',           'icono' => 'bi-browser-edge'],
            'OPR'      => ['nombre' => 'Opera',          'icono' => 'bi-browser-opera'],
            'Firefox'  => ['nombre' => 'Firefox',        'icono' => 'bi-browser-firefox'],
            'SamsungBrowser' => ['nombre' => 'Samsung Internet', 'icono' => 'bi-phone'],
            'Chrome'   => ['nombre' => 'Chrome',         'icono' => 'bi-browser-chrome'],
            'Safari'   => ['nombre' => 'Safari',         'icono' => 'bi-browser-safari'],
        ];

        foreach ($navegadores as $key => $data) {
            if (stripos($ua, $key) !== false) {
                // Intentar extraer versión
                preg_match('/' . preg_quote($key, '/') . '[\/\s]([\d.]+)/i', $ua, $m);
                $version = isset($m[1]) ? explode('.', $m[1])[0] : '';
                return [
                    'nombre' => $data['nombre'] . ($version ? " $version" : ''),
                    'icono'  => $data['icono'],
                ];
            }
        }

        return ['nombre' => 'Navegador desconocido', 'icono' => 'bi-globe'];
    }

    private function detectarSO(string $ua): array
    {
        $sistemas = [
            'Windows NT 10.0' => ['nombre' => 'Windows 11/10', 'icono' => 'bi-windows'],
            'Windows NT 6.1'  => ['nombre' => 'Windows 7',     'icono' => 'bi-windows'],
            'Android'         => ['nombre' => 'Android',       'icono' => 'bi-android2'],
            'iPhone'          => ['nombre' => 'iOS',           'icono' => 'bi-apple'],
            'iPad'            => ['nombre' => 'iPadOS',        'icono' => 'bi-apple'],
            'Mac OS X'        => ['nombre' => 'macOS',         'icono' => 'bi-apple'],
            'Linux'           => ['nombre' => 'Linux',         'icono' => 'bi-ubuntu'],
            'CrOS'            => ['nombre' => 'ChromeOS',      'icono' => 'bi-google'],
        ];

        foreach ($sistemas as $key => $data) {
            if (stripos($ua, $key) !== false) {
                // Android: extraer versión
                if ($key === 'Android') {
                    preg_match('/Android\s([\d.]+)/i', $ua, $m);
                    $ver = isset($m[1]) ? ' ' . $m[1] : '';
                    return ['nombre' => 'Android' . $ver, 'icono' => $data['icono']];
                }
                // iOS: extraer versión
                if (in_array($key, ['iPhone', 'iPad'])) {
                    preg_match('/OS\s([\d_]+)/i', $ua, $m);
                    $ver = isset($m[1]) ? ' ' . str_replace('_', '.', $m[1]) : '';
                    return ['nombre' => $data['nombre'] . $ver, 'icono' => $data['icono']];
                }
                return $data;
            }
        }

        return ['nombre' => 'SO desconocido', 'icono' => 'bi-display'];
    }

    private function detectarTipo(string $ua): array
    {
        if (preg_match('/iPad|Tablet|Kindle|PlayBook|Silk/i', $ua)) {
            return ['nombre' => 'Tableta', 'icono' => 'bi-tablet-fill'];
        }
        if (preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua)) {
            return ['nombre' => 'Móvil', 'icono' => 'bi-phone-fill'];
        }
        return ['nombre' => 'Escritorio', 'icono' => 'bi-laptop-fill'];
    }

    private function detectarMarca(string $ua): array
    {
        $marcas = [
            'Samsung'   => ['nombre' => 'Samsung',   'icono' => 'bi-phone-fill'],
            'Xiaomi'    => ['nombre' => 'Xiaomi',     'icono' => 'bi-phone-fill'],
            'Huawei'    => ['nombre' => 'Huawei',     'icono' => 'bi-phone-fill'],
            'OPPO'      => ['nombre' => 'OPPO',       'icono' => 'bi-phone-fill'],
            'OnePlus'   => ['nombre' => 'OnePlus',    'icono' => 'bi-phone-fill'],
            'Motorola'  => ['nombre' => 'Motorola',   'icono' => 'bi-phone-fill'],
            'LG'        => ['nombre' => 'LG',         'icono' => 'bi-phone-fill'],
            'Sony'      => ['nombre' => 'Sony',       'icono' => 'bi-phone-fill'],
            'Nokia'     => ['nombre' => 'Nokia',      'icono' => 'bi-phone-fill'],
            'iPhone'    => ['nombre' => 'Apple iPhone', 'icono' => 'bi-phone-fill'],
            'iPad'      => ['nombre' => 'Apple iPad', 'icono' => 'bi-tablet-fill'],
            'Macintosh' => ['nombre' => 'Apple Mac',  'icono' => 'bi-laptop-fill'],
        ];

        // Samsung: intentar obtener modelo
        if (preg_match('/SM-[A-Z0-9]+/i', $ua, $m)) {
            return ['nombre' => 'Samsung ' . $m[0], 'icono' => 'bi-phone-fill'];
        }
        // Xiaomi: Redmi / Mi
        if (preg_match('/Redmi\s[\w\s]+|Mi\s\d+/i', $ua, $m)) {
            return ['nombre' => 'Xiaomi ' . trim($m[0]), 'icono' => 'bi-phone-fill'];
        }
        // Motorola
        if (preg_match('/moto\s[\w\s]+/i', $ua, $m)) {
            return ['nombre' => ucfirst(trim($m[0])), 'icono' => 'bi-phone-fill'];
        }

        foreach ($marcas as $key => $data) {
            if (stripos($ua, $key) !== false) return $data;
        }

        return ['nombre' => 'Desconocida', 'icono' => 'bi-display'];
    }

    /**
     * Huella digital del dispositivo (para detectar si es nuevo sin depender del token).
     */
    private function fingerprint(string $ua): string
    {
        return substr(
            hash('sha256', $ua . ($this->detectarSO($ua)['nombre'] ?? '')),
            0,
            16
        );
    }

    /**
     * ¿El fingerprint del UA se ve por primera vez para este usuario?
     */
    private function esDispositvoNuevo(string $fp, array $sesiones): bool
    {
        foreach ($sesiones as $s) {
            if (($s['fingerprint'] ?? '') === $fp) return false;
        }
        return true;
    }
}
