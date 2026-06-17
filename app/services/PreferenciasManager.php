<?php
/**
 * PreferenciasManager.php – VetWilling
 *
 * Servicio para gestionar preferencias de usuario por usuario.
 * Las preferencias se almacenan en archivos JSON individuales en storage/preferencias/.
 *
 * Preferencias disponibles:
 *  - tema           : 'claro' | 'oscuro'
 *  - idioma         : 'es' | 'en' | 'pt'
 *  - zona_horaria   : ej. 'America/Bogota'
 *  - formato_fecha  : 'dd/mm/yyyy' | 'mm/dd/yyyy' | 'yyyy-mm-dd'
 *  - notificaciones : 'email' | 'ninguno'
 *
 * Mejoras v2:
 *  - Cache estático por id_usuario para evitar múltiples lecturas de disco.
 *  - obtenerStringsIdioma() usa I18n::obtenerStrings() como fuente primaria
 *    (funciona sin archivos JSON externos para EN y PT).
 *  - sincronizarSesion() actualiza $_SESSION['lang'] tras guardar.
 *  - Validación de valores permitidos al guardar.
 */
class PreferenciasManager
{
    // ── Cache estático compartido entre instancias ─────────────────────────────
    /** @var array<int, array<string, string>> */
    private static array $cache = [];

    // ── Instancia ────────────────────────────────────────────────────────────
    private int    $id_usuario;
    private string $archivo;

    /** @var array<string, string> */
    private array $defaults = [
        'tema'          => 'claro',
        'idioma'        => 'es',
        'zona_horaria'  => 'America/Bogota',
        'formato_fecha' => 'dd/mm/yyyy',
        'notificaciones'=> 'email',
    ];

    /** Valores permitidos por campo (null = cualquier string no vacío) */
    private array $valoresPermitidos = [
        'tema'           => ['claro', 'oscuro'],
        'idioma'         => ['es', 'en', 'pt'],
        'notificaciones' => ['email', 'ninguno'],
        'zona_horaria'   => null, // libre
        'formato_fecha'  => ['dd/mm/yyyy', 'mm/dd/yyyy', 'yyyy-mm-dd'],
    ];

    public function __construct(int $id_usuario)
    {
        $this->id_usuario = $id_usuario;

        $dir = defined('BASE_PATH')
            ? BASE_PATH . '/storage/preferencias/'
            : __DIR__ . '/../../storage/preferencias/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->archivo = $dir . 'prefs_' . $id_usuario . '.json';
    }

    // ── Leer preferencias ─────────────────────────────────────────────────────

    /**
     * Obtiene las preferencias del usuario.
     * Usa cache estático para evitar múltiples lecturas del mismo archivo
     * durante una sola petición HTTP.
     *
     * @return array<string, string>
     */
    public function obtener(): array
    {
        if (isset(self::$cache[$this->id_usuario])) {
            return self::$cache[$this->id_usuario];
        }

        if (!file_exists($this->archivo)) {
            self::$cache[$this->id_usuario] = $this->defaults;
            return $this->defaults;
        }

        $contenido = file_get_contents($this->archivo);
        $data      = $contenido !== false
            ? json_decode($contenido, true)
            : null;

        $prefs = array_merge($this->defaults, is_array($data) ? $data : []);

        self::$cache[$this->id_usuario] = $prefs;
        return $prefs;
    }

    // ── Guardar preferencias ──────────────────────────────────────────────────

    /**
     * Guarda uno o varios cambios de preferencias.
     * Solo acepta claves definidas en $defaults; ignora el resto.
     * Valida los valores contra $valoresPermitidos.
     * Sincroniza $_SESSION['lang'] si se cambia el idioma.
     *
     * @param  array<string, string> $cambios
     * @return array<string, string>
     */
    public function guardar(array $cambios): array
    {
        $prefs = $this->obtener();

        foreach ($cambios as $clave => $valor) {
            if (!array_key_exists($clave, $this->defaults)) {
                continue; // ignorar claves desconocidas
            }

            $permitidos = $this->valoresPermitidos[$clave] ?? null;

            if ($permitidos !== null && !in_array($valor, $permitidos, true)) {
                continue; // ignorar valores no permitidos
            }

            $prefs[$clave] = (string) $valor;
        }

        // Persistir en disco
        file_put_contents(
            $this->archivo,
            json_encode($prefs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Actualizar cache en memoria
        self::$cache[$this->id_usuario] = $prefs;

        // Sincronizar sesión con el idioma guardado
        if (isset($cambios['idioma'])) {
            $this->sincronizarSesion($prefs['idioma']);
        }

        return $prefs;
    }

    // ── Restablecer ───────────────────────────────────────────────────────────

    /**
     * Restablece todas las preferencias a los valores por defecto.
     *
     * @return array<string, string>
     */
    public function restablecer(): array
    {
        file_put_contents(
            $this->archivo,
            json_encode($this->defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        self::$cache[$this->id_usuario] = $this->defaults;

        $this->sincronizarSesion($this->defaults['idioma']);

        return $this->defaults;
    }

    // ── Strings de idioma para JavaScript ─────────────────────────────────────

    /**
     * Devuelve todos los strings del idioma indicado para su uso en JS.
     *
     * Fuente primaria : I18n::obtenerStrings() — funciona siempre.
     * Fuente secundaria: archivo JSON en public/assets/lang/ — si existe.
     *
     * Esto resuelve el problema de que en.json y pt.json no existen:
     * la fuente primaria (I18n) siempre tiene los tres idiomas completos.
     *
     * @param  string $idioma
     * @return array{strings: array<string, string>}
     */
    public function obtenerStringsIdioma(string $idioma): array
    {
        // Fuente primaria: clase I18n (siempre disponible para es/en/pt)
        if (class_exists('I18n')) {
            return ['strings' => I18n::obtenerStrings($idioma)];
        }

        // Fuente secundaria: archivo JSON (solo si existe)
        if (defined('BASE_PATH')) {
            $archivo = BASE_PATH . '/public/assets/lang/' . $idioma . '.json';
        } else {
            $archivo = __DIR__ . '/../../public/assets/lang/' . $idioma . '.json';
        }

        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            $strings   = $contenido !== false
                ? json_decode($contenido, true)
                : null;
            return ['strings' => is_array($strings) ? $strings : []];
        }

        // Fallback final: array vacío (el JS usará las claves como texto)
        return ['strings' => []];
    }

    // ── Sincronización de sesión ──────────────────────────────────────────────

    /**
     * Sincroniza $_SESSION['lang'] con el idioma proporcionado.
     * Evita desincronía entre PreferenciasManager y bootstrap.php.
     *
     * @param  string $idioma
     * @return void
     */
    public function sincronizarSesion(string $idioma): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['lang'] = $idioma;
        }
    }

    // ── Cache ─────────────────────────────────────────────────────────────────

    /**
     * Limpia el cache estático para el usuario actual.
     * Útil cuando se sabe que el archivo cambió externamente.
     */
    public function invalidarCache(): void
    {
        unset(self::$cache[$this->id_usuario]);
    }

    /**
     * Limpia todo el cache estático (útil en tests).
     */
    public static function limpiarCacheGlobal(): void
    {
        self::$cache = [];
    }
}
