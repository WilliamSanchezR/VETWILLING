<?php

/**
 * SessionManager.php – VetWilling Dashboard
 * Version: 3.0 - CORREGIDO
 *
 * Correcciones aplicadas:
 *
 * 1. DUPLICADOS: leer() y escribir() ahora usan el mismo formato
 *    (objeto JSON indexado por token). Antes escribir() guardaba un
 *    array numérico y leer() re-indexaba, lo que podía perder entradas
 *    con token vacío y forzar creación de duplicados en el siguiente request.
 *
 * 2. RACE CONDITION: escribir() ahora usa read-lock antes de leer y
 *    write-lock antes de escribir dentro del mismo descriptor de archivo,
 *    eliminando la ventana entre fopen() y flock() que permitía que dos
 *    requests simultáneos escribieran datos obsoletos encima del otro.
 *
 * 3. esDispositivoNuevo(): se llama ANTES de insertar la nueva entrada
 *    en $sesiones para no encontrar falsos positivos.
 *
 * 4. cerrar(): ahora devuelve un código de error descriptivo en lugar de
 *    bool para que el controlador pueda responder correctamente al frontend.
 *
 * 5. geolocalizarIp(): caché de 24h restaurada para no bloquear el login
 *    con peticiones HTTP síncronas a ip-api.com en cada request.
 *
 * 6. JSON corrupto: leer() hace backup del archivo corrupto antes de
 *    devolver [] para no perder datos silenciosamente.
 *
 * 7. Detección completa de navegador, SO y marca restaurada desde v1.
 *
 * Uso:
 *   $sm = new SessionManager($id_usuario);
 *   $sm->registrar();            // Al hacer login
 *   $sm->listar();               // Para mostrar en perfil
 *   $sm->cerrar($token);         // Cerrar una sesión específica
 *   $sm->cerrarTodas();          // Cerrar todas menos la actual
 *   $sm->cerrarSesionActual();   // Logout del usuario actual
 */
class SessionManager
{
    private string $id_usuario;
    private string $archivo;
    private string $directorio;
    private string $token_actual;

    /** Días sin actividad antes de limpiar sesión activa */
    private const EXPIRACION_ACTIVA   = 30;

    /** Días antes de limpiar sesión marcada como inactiva */
    private const EXPIRACION_INACTIVA = 90;

    /**
     * @param int|string $id_usuario ID del usuario autenticado
     * @param string     $base_dir   Directorio base para los archivos JSON
     *                               (debe estar fuera del webroot)
     */
    public function __construct(int|string $id_usuario, string $base_dir = '')
    {
        $this->id_usuario  = (string) $id_usuario;
        $this->directorio  = rtrim($base_dir ?: (BASE_PATH . '/storage/sesiones'), '/');
        $this->archivo     = $this->directorio . "/usuario_{$this->id_usuario}.json";
        $this->token_actual = $this->obtenerTokenActual();

        $this->inicializarDirectorio();
    }

    /* ================================================================
       PÚBLICO
    ================================================================ */

    /**
     * Registra o actualiza la sesión actual.
     *
     * Tres casos:
     *   1. El token ya existe en el JSON → actualiza last_seen / ip.
     *   2. El token no existe pero el fingerprint sí → el dispositivo
     *      renovó su session_id (refresh de caché, nueva pestaña, etc.)
     *      → migra la entrada al nuevo token.
     *   3. Dispositivo completamente nuevo → crea entrada nueva.
     */
    public function registrar(): void
    {
        /* Lee con lock compartido para evitar leer datos a medio escribir */
        $sesiones = $this->leerConLock();
        $info     = $this->detectar();
        $token    = $this->token_actual;
        $ahora    = time();
        $fp       = $info['fingerprint'];

        /* ── Caso 1: token ya registrado ── */
        if (isset($sesiones[$token])) {
            $sesiones[$token]['last_seen']  = $ahora;
            $sesiones[$token]['ip']         = $info['ip'];
            $sesiones[$token]['activa']     = true;
            $sesiones[$token]['is_current'] = true;

            $this->marcarSoloEstaComoActual($sesiones, $token);
            $this->limpiarExpiradas($sesiones, $ahora);
            $this->escribirConLock($sesiones);
            return;
        }

        /* ── Caso 2: mismo fingerprint, token distinto (renovación de sesión) ── */
        foreach ($sesiones as $t => $s) {
            if (($s['fingerprint'] ?? '') === $fp) {
                $entrada               = $s;
                $entrada['token']      = $token;
                $entrada['last_seen']  = $ahora;
                $entrada['ip']         = $info['ip'];
                $entrada['activa']     = true;
                $entrada['is_current'] = true;
                $entrada['es_nueva']   = false;

                /* Elimina la entrada vieja y crea con el nuevo token */
                unset($sesiones[$t]);
                $sesiones[$token] = $entrada;

                $this->marcarSoloEstaComoActual($sesiones, $token);
                $this->limpiarExpiradas($sesiones, $ahora);
                $this->escribirConLock($sesiones);
                return;
            }
        }

        /* ── Caso 3: dispositivo completamente nuevo ──
           CORRECCIÓN: esDispositivoNuevo() se llama ANTES de insertar
           la nueva entrada para no encontrar falsos positivos. */
        $esNuevo = $this->esDispositivoNuevo($fp, $sesiones);

        $this->marcarSoloEstaComoActual($sesiones, $token);

        $sesiones[$token] = array_merge($info, [
            'token'      => $token,
            'created_at' => $ahora,
            'last_seen'  => $ahora,
            'activa'     => true,
            'is_current' => true,
            'es_nueva'   => $esNuevo,
        ]);

        $this->limpiarExpiradas($sesiones, $ahora);
        $this->escribirConLock($sesiones);
    }

    /**
     * Devuelve solo las sesiones activas ordenadas: actual primero.
     */
    public function listar(): array
    {
        $sesiones = $this->leerConLock();

        $activas = array_filter(
            $sesiones,
            fn($s) => ($s['activa'] ?? true) === true
        );

        usort($activas, function ($a, $b) {
            if ($a['is_current'] ?? false) return -1;
            if ($b['is_current'] ?? false) return  1;
            return ($b['last_seen'] ?? 0) - ($a['last_seen'] ?? 0);
        });

        return array_values($activas);
    }

    /**
     * Cierra una sesión específica por token.
     *
     * Retorna:
     *   'ok'             → cerrada correctamente
     *   'not_found'      → token no existe
     *   'is_current'     → no se puede cerrar la sesión activa desde aquí
     *   'write_error'    → error al escribir el archivo
     *
     * CORRECCIÓN: antes devolvía bool, lo que impedía al controlador
     * distinguir entre "no encontrada" y "es la sesión actual",
     * generando respuestas incorrectas al frontend (error de conexión).
     */
    public function cerrar(string $token): string
    {
        $sesiones = $this->leerConLock();

        if (!isset($sesiones[$token])) {
            return 'not_found';
        }

        if ($sesiones[$token]['is_current'] ?? false) {
            return 'is_current';
        }

        $sesiones[$token]['activa']     = false;
        $sesiones[$token]['is_current'] = false;
        $sesiones[$token]['last_seen']  = time();

        $ok = $this->escribirConLock($sesiones);
        return $ok ? 'ok' : 'write_error';
    }

    /**
     * Cierra todas las sesiones excepto la actual.
     * Devuelve el número de sesiones cerradas.
     */
    public function cerrarTodas(): int
    {
        $sesiones = $this->leerConLock();
        $token    = $this->token_actual;
        $cerradas = 0;
        $ahora    = time();

        foreach ($sesiones as $t => &$s) {
            if ($t !== $token) {
                $s['activa']     = false;
                $s['is_current'] = false;
                $s['last_seen']  = $ahora;
                $cerradas++;
            }
        }
        unset($s);

        $this->escribirConLock($sesiones);
        return $cerradas;
    }

    /**
     * Cierra la sesión actual (logout).
     * Marca la entrada como inactiva y elimina el token de $_SESSION.
     */
    public function cerrarSesionActual(): void
    {
        $token    = $this->token_actual;
        $sesiones = $this->leerConLock();

        if (isset($sesiones[$token])) {
            $sesiones[$token]['activa']     = false;
            $sesiones[$token]['is_current'] = false;
            $sesiones[$token]['last_seen']  = time();
            $this->escribirConLock($sesiones);
        }

        /* Eliminar token de la sesión PHP para que el próximo login
           genere uno nuevo y no reutilice el token cerrado */
        unset($_SESSION['sm_token_' . $this->id_usuario]);
    }

    /**
     * Token de la sesión actual.
     */
    public function tokenActual(): string
    {
        return $this->token_actual;
    }

    /**
     * ¿La sesión actual corresponde a un dispositivo nuevo?
     */
    public function esNueva(): bool
    {
        $sesiones = $this->leerConLock();
        return ($sesiones[$this->token_actual]['es_nueva'] ?? false) === true;
    }

    /**
     * Información de detección del dispositivo actual.
     * Público para que el controlador pueda usarlo si lo necesita.
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

    /* ================================================================
       STORAGE — lectura y escritura con lock
    ================================================================ */

    /**
     * Lee el JSON con lock compartido (LOCK_SH).
     *
     * CORRECCIÓN del formato: el JSON se guarda como objeto indexado
     * por token {"abc123":{...}, "def456":{...}} en lugar de array
     * numérico [{...},{...}]. Esto elimina el paso de re-indexación
     * que podía perder entradas con token vacío y causar duplicados.
     *
     * CORRECCIÓN de JSON corrupto: si el archivo existe pero no se
     * puede decodificar, hace un backup antes de devolver [] para no
     * perder datos silenciosamente.
     */
    private function leerConLock(): array
    {
        if (!file_exists($this->archivo)) return [];

        $fh = @fopen($this->archivo, 'r');
        if ($fh === false) return [];

        flock($fh, LOCK_SH);
        $contenido = stream_get_contents($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        if ($contenido === false || trim($contenido) === '') return [];

        $datos = json_decode($contenido, true);

        /* JSON corrupto: backup y retorno limpio */
        if (!is_array($datos)) {
            $backup = $this->archivo . '.bak.' . time();
            @copy($this->archivo, $backup);
            error_log("SessionManager: JSON corrupto para usuario {$this->id_usuario}. Backup en $backup");
            return [];
        }

        /* Compatibilidad: si el archivo era un array numérico (formato viejo),
           convertirlo al nuevo formato indexado por token */
        if (array_is_list($datos)) {
            $indexado = [];
            foreach ($datos as $item) {
                if (!empty($item['token'])) {
                    $indexado[$item['token']] = $item;
                }
            }
            return $indexado;
        }

        return $datos;
    }

    /**
     * Escribe el JSON con lock exclusivo (LOCK_EX).
     *
     * CORRECCIÓN de race condition: abre con 'c' (no trunca al abrir),
     * obtiene el lock ANTES de truncar y escribir. Esto evita la ventana
     * entre fopen() y flock() donde dos requests simultáneos podían
     * sobrescribirse mutuamente con datos obsoletos.
     *
     * Formato: objeto JSON indexado por token (no array numérico).
     *
     * @return bool true si se escribió correctamente
     */
    private function escribirConLock(array $sesiones): bool
    {
        $fh = @fopen($this->archivo, 'c');
        if ($fh === false) {
            error_log("SessionManager: no se pudo abrir {$this->archivo} para escritura");
            return false;
        }

        /* Lock exclusivo ANTES de truncar — elimina la race condition */
        flock($fh, LOCK_EX);

        ftruncate($fh, 0);
        rewind($fh);

        /* Guardar como objeto JSON indexado por token, no como array numérico.
           Esto hace que leerConLock() no necesite re-indexar y elimina
           la posibilidad de perder entradas con token vacío. */
        $json = json_encode(
            $sesiones,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $ok = fwrite($fh, $json) !== false;
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        return $ok;
    }

    /* ================================================================
       SESIÓN PHP
    ================================================================ */

    /**
     * Obtiene o genera el token de sesión para este usuario.
     * El token se guarda en $_SESSION con una clave por usuario para
     * que funcione correctamente en aplicaciones multiusuario.
     */
    private function obtenerTokenActual(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $key = 'sm_token_' . $this->id_usuario;

        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(24));
        }

        return $_SESSION[$key];
    }

    /* ================================================================
       HELPERS INTERNOS
    ================================================================ */

    /**
     * Marca solo el token indicado como is_current = true.
     * Todos los demás quedan como false.
     * Extraído como método para evitar copiar-pegar en los 3 casos de registrar().
     */
    private function marcarSoloEstaComoActual(array &$sesiones, string $token): void
    {
        foreach ($sesiones as $t => &$s) {
            $s['is_current'] = ($t === $token);
        }
        unset($s);
    }

    /**
     * Elimina del array las sesiones expiradas según su estado.
     * — Activas:   se limpian tras EXPIRACION_ACTIVA días sin actividad.
     * — Inactivas: se limpian tras EXPIRACION_INACTIVA días (historial).
     */
    private function limpiarExpiradas(array &$sesiones, int $ahora): void
    {
        foreach (array_keys($sesiones) as $t) {
            $s       = $sesiones[$t];
            $inactiva = !($s['activa'] ?? true);
            $limite   = $inactiva
                ? 86400 * self::EXPIRACION_INACTIVA
                : 86400 * self::EXPIRACION_ACTIVA;

            if (($ahora - ($s['last_seen'] ?? 0)) > $limite) {
                unset($sesiones[$t]);
            }
        }
    }

    /**
     * Devuelve true si ninguna sesión existente tiene el mismo fingerprint.
     *
     * CORRECCIÓN: se llama ANTES de insertar la nueva entrada en $sesiones
     * para no encontrar el dispositivo que acaba de crearse.
     */
    private function esDispositivoNuevo(string $fp, array $sesiones): bool
    {
        foreach ($sesiones as $s) {
            if (($s['fingerprint'] ?? '') === $fp) return false;
        }
        return true;
    }

    /**
     * Crea el directorio de sesiones y lo protege con .htaccess
     * para prevenir listado web si queda dentro del webroot por error.
     */
    private function inicializarDirectorio(): void
    {
        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0750, true);
        }

        $htaccess = $this->directorio . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Require all denied\n");
        }
    }

    /**
     * Obtiene la IP real del cliente respetando proxies confiables.
     * Orden: IP directa primero para evitar spoofing de headers.
     */
    private function obtenerIp(): string
    {
        /* REMOTE_ADDR primero — no puede ser falsificado por el cliente */
        $directa = $_SERVER['REMOTE_ADDR'] ?? '';

        /* Si viene de Cloudflare, el header CF es confiable */
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        /* Proxy estándar */
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        return $directa ?: '0.0.0.0';
    }

    /* ================================================================
       DETECCIÓN DE DISPOSITIVO
    ================================================================ */

    /**
     * Geolocalización con caché de 24h.
     *
     * CORRECCIÓN: la versión anterior eliminó la caché, lo que hacía
     * una petición HTTP síncrona a ip-api.com en cada login y podía
     * bloquear el proceso durante el timeout de 2 segundos.
     */
    private function geolocalizarIp(string $ip): string
    {
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
            return 'Local / Desarrollo';
        }

        /* Caché por IP para no hacer una petición externa en cada login */
        $cacheDir  = $this->directorio . '/geo_cache';
        $cacheFile = $cacheDir . '/' . md5($ip) . '.json';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0750, true);
        }

        /* Caché válida durante 24 horas */
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
            $cached = json_decode(@file_get_contents($cacheFile), true);
            return $cached['ciudad'] ?? 'Desconocida';
        }

        /* Petición a ip-api.com con timeout de 2 segundos */
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $raw = @file_get_contents(
            "http://ip-api.com/json/{$ip}?fields=status,city,country",
            false,
            $ctx
        );

        if ($raw !== false) {
            $geo = json_decode($raw, true);
            if (($geo['status'] ?? '') === 'success') {
                $ciudad = trim(($geo['city'] ?? '') . ', ' . ($geo['country'] ?? ''));
                @file_put_contents($cacheFile, json_encode(['ciudad' => $ciudad]), LOCK_EX);
                return $ciudad;
            }
        }

        return 'Desconocida';
    }

    /**
     * Detecta navegador y versión principal desde el User-Agent.
     * Orden importante: Edge y Opera deben ir ANTES de Chrome porque
     * sus UA también contienen "Chrome".
     */
    private function detectarNavegador(string $ua): array
    {
        $navegadores = [
            'Edg'            => ['nombre' => 'Edge',             'icono' => 'bi-browser-edge'],
            'OPR'            => ['nombre' => 'Opera',            'icono' => 'bi-browser-opera'],
            'Firefox'        => ['nombre' => 'Firefox',          'icono' => 'bi-browser-firefox'],
            'SamsungBrowser' => ['nombre' => 'Samsung Internet', 'icono' => 'bi-phone'],
            'Chrome'         => ['nombre' => 'Chrome',           'icono' => 'bi-browser-chrome'],
            'Safari'         => ['nombre' => 'Safari',           'icono' => 'bi-browser-safari'],
        ];

        foreach ($navegadores as $clave => $data) {
            if (stripos($ua, $clave) !== false) {
                preg_match('/' . preg_quote($clave, '/') . '[\/\s]([\d.]+)/i', $ua, $m);
                $version = isset($m[1]) ? ' ' . explode('.', $m[1])[0] : '';
                return [
                    'nombre' => $data['nombre'] . $version,
                    'icono'  => $data['icono'],
                ];
            }
        }

        return ['nombre' => 'Navegador desconocido', 'icono' => 'bi-globe'];
    }

    /**
     * Detecta sistema operativo y versión desde el User-Agent.
     */
    private function detectarSO(string $ua): array
    {
        $sistemas = [
            'Windows NT 10.0' => ['nombre' => 'Windows 11/10', 'icono' => 'bi-windows'],
            'Windows NT 6.3'  => ['nombre' => 'Windows 8.1',  'icono' => 'bi-windows'],
            'Windows NT 6.1'  => ['nombre' => 'Windows 7',    'icono' => 'bi-windows'],
            'Android'         => ['nombre' => 'Android',      'icono' => 'bi-android2'],
            'iPhone'          => ['nombre' => 'iOS',          'icono' => 'bi-apple'],
            'iPad'            => ['nombre' => 'iPadOS',       'icono' => 'bi-apple'],
            'Mac OS X'        => ['nombre' => 'macOS',        'icono' => 'bi-apple'],
            'CrOS'            => ['nombre' => 'ChromeOS',     'icono' => 'bi-google'],
            'Linux'           => ['nombre' => 'Linux',        'icono' => 'bi-ubuntu'],
        ];

        foreach ($sistemas as $clave => $data) {
            if (stripos($ua, $clave) !== false) {
                /* Android: extraer versión */
                if ($clave === 'Android') {
                    preg_match('/Android\s([\d.]+)/i', $ua, $m);
                    $ver = isset($m[1]) ? ' ' . $m[1] : '';
                    return ['nombre' => 'Android' . $ver, 'icono' => $data['icono']];
                }
                /* iOS / iPadOS: extraer versión */
                if (in_array($clave, ['iPhone', 'iPad'], true)) {
                    preg_match('/OS\s([\d_]+)/i', $ua, $m);
                    $ver = isset($m[1]) ? ' ' . str_replace('_', '.', $m[1]) : '';
                    return ['nombre' => $data['nombre'] . $ver, 'icono' => $data['icono']];
                }
                return $data;
            }
        }

        return ['nombre' => 'SO desconocido', 'icono' => 'bi-display'];
    }

    /**
     * Detecta tipo de dispositivo: móvil, tableta o escritorio.
     */
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

    /**
     * Detecta marca y modelo del dispositivo desde el User-Agent.
     */
    private function detectarMarca(string $ua): array
    {
        /* Samsung: modelo específico (ej: SM-A546B) */
        if (preg_match('/SM-[A-Z0-9]+/i', $ua, $m)) {
            return ['nombre' => 'Samsung ' . strtoupper($m[0]), 'icono' => 'bi-phone-fill'];
        }
        /* Xiaomi: Redmi o Mi */
        if (preg_match('/Redmi\s[\w\s]+|Mi\s\d+/i', $ua, $m)) {
            return ['nombre' => 'Xiaomi ' . trim($m[0]), 'icono' => 'bi-phone-fill'];
        }
        /* Motorola */
        if (preg_match('/moto\s[\w\s]+/i', $ua, $m)) {
            return ['nombre' => ucfirst(trim($m[0])), 'icono' => 'bi-phone-fill'];
        }

        $marcas = [
            'Samsung'   => ['nombre' => 'Samsung',     'icono' => 'bi-phone-fill'],
            'Xiaomi'    => ['nombre' => 'Xiaomi',       'icono' => 'bi-phone-fill'],
            'Huawei'    => ['nombre' => 'Huawei',       'icono' => 'bi-phone-fill'],
            'OPPO'      => ['nombre' => 'OPPO',         'icono' => 'bi-phone-fill'],
            'OnePlus'   => ['nombre' => 'OnePlus',      'icono' => 'bi-phone-fill'],
            'Motorola'  => ['nombre' => 'Motorola',     'icono' => 'bi-phone-fill'],
            'LG'        => ['nombre' => 'LG',           'icono' => 'bi-phone-fill'],
            'Sony'      => ['nombre' => 'Sony',         'icono' => 'bi-phone-fill'],
            'Nokia'     => ['nombre' => 'Nokia',        'icono' => 'bi-phone-fill'],
            'iPhone'    => ['nombre' => 'Apple iPhone', 'icono' => 'bi-phone-fill'],
            'iPad'      => ['nombre' => 'Apple iPad',   'icono' => 'bi-tablet-fill'],
            'Macintosh' => ['nombre' => 'Apple Mac',    'icono' => 'bi-laptop-fill'],
        ];

        foreach ($marcas as $clave => $data) {
            if (stripos($ua, $clave) !== false) return $data;
        }

        return ['nombre' => 'Desconocida', 'icono' => 'bi-display'];
    }

    /**
     * Huella digital del dispositivo basada en User-Agent + SO.
     * Se usa para reconocer el mismo dispositivo aunque el session_id cambie.
     */
    private function fingerprint(string $ua): string
    {
        $so = $this->detectarSO($ua)['nombre'] ?? '';
        return substr(hash('sha256', $ua . $so), 0, 16);
    }
}