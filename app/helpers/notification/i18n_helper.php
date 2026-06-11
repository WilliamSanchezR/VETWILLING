<?php
/**
 * i18n_helper.php – VetWilling
 *
 * Funciones globales de traducción disponibles en todo el sistema.
 * Requiere que bootstrap.php haya ejecutado antes (establece $GLOBALS['t'] y $GLOBALS['lang']).
 *
 * Funciones expuestas:
 *  - notif_t()        — Traducción para el módulo de notificaciones (original).
 *  - notif_strings()  — Array de strings del módulo notif.* (original).
 *  - vw_t()           — Traducción global, con auto-recovery ante errores.
 *  - vw_lang()        — Idioma actual del sistema.
 *  - vw_lang_attr()   — Valor correcto para el atributo HTML lang="".
 *  - vw_lang_strings()— Todos los strings del idioma actual (para inyección JS).
 */

// ── Función original: notif_t() ───────────────────────────────────────────────

if (!function_exists('notif_t')) {
    /**
     * Traduce una clave usando $GLOBALS['t'].
     * Si la función global no está disponible, devuelve la clave como fallback.
     *
     * @param  string   $clave  Clave de traducción (ej. 'notif.ok')
     * @param  int|null $n      Valor para interpolar {n}
     * @return string
     */
    function notif_t(string $clave, ?int $n = null): string
    {
        $t = $GLOBALS['t'] ?? null;

        if (!is_callable($t)) {
            // Auto-recovery: devuelve la clave si no hay traductor disponible
            return $clave;
        }

        return $t($clave, $n);
    }
}

// ── Función original: notif_strings() ────────────────────────────────────────

if (!function_exists('notif_strings')) {
    /**
     * Devuelve todos los strings del módulo notif.* para el idioma actual.
     * Útil para inyectar en JavaScript solo las claves de notificaciones.
     *
     * @return array<string, string>
     */
    function notif_strings(): array
    {
        $idioma  = $GLOBALS['lang'] ?? 'es';

        if (!class_exists('I18n')) {
            return [];
        }

        $strings = I18n::obtenerStrings($idioma);

        return array_filter(
            $strings,
            static fn(string $clave): bool => str_starts_with($clave, 'notif.'),
            ARRAY_FILTER_USE_KEY
        );
    }
}

// ── NUEVA: vw_t() — traducción global con auto-recovery ──────────────────────

if (!function_exists('vw_t')) {
    /**
     * Traducción global con auto-recovery.
     *
     * Cadena de fallback:
     *  1. $GLOBALS['t']($clave) — Closure cargado por bootstrap.php
     *  2. I18n::cargar('es')    — Instancia directa al español
     *  3. $clave                — La clave como texto plano (nunca lanza excepción)
     *
     * @param  string   $clave    Clave de traducción
     * @param  int|null $n        Valor para interpolar {n}
     * @param  string   $default  Texto de emergencia si todo falla
     * @return string
     */
    function vw_t(string $clave, ?int $n = null, string $default = ''): string
    {
        try {
            // Nivel 1: usar el Closure global
            $t = $GLOBALS['t'] ?? null;
            if (is_callable($t)) {
                return $t($clave, $n);
            }

            // Nivel 2: instanciar directamente en español
            if (class_exists('I18n')) {
                $tEs = I18n::cargar('es');
                return $tEs($clave, $n);
            }
        } catch (\Throwable $e) {
            // Nivel 3: registrar y continuar sin interrumpir
            error_log('[VetWilling vw_t] Error al traducir "' . $clave . '": ' . $e->getMessage());
        }

        // Último recurso: texto de emergencia o la propia clave
        return $default !== '' ? $default : $clave;
    }
}

// ── NUEVA: vw_lang() — idioma actual ─────────────────────────────────────────

if (!function_exists('vw_lang')) {
    /**
     * Devuelve el código del idioma actual del sistema.
     *
     * @return string 'es' | 'en' | 'pt'
     */
    function vw_lang(): string
    {
        $lang = $GLOBALS['lang'] ?? ($_SESSION['lang'] ?? 'es');

        if (class_exists('I18n') && !in_array($lang, I18n::idiomasDisponibles(), true)) {
            return 'es';
        }

        return $lang;
    }
}

// ── NUEVA: vw_lang_attr() — valor correcto para HTML lang="" ─────────────────

if (!function_exists('vw_lang_attr')) {
    /**
     * Devuelve el valor adecuado para el atributo HTML lang="".
     *
     * Mapeo de códigos internos a BCP-47:
     *  es → es
     *  en → en
     *  pt → pt-BR
     *
     * @return string
     */
    function vw_lang_attr(): string
    {
        $mapa = ['es' => 'es', 'en' => 'en', 'pt' => 'pt-BR'];
        return $mapa[vw_lang()] ?? 'es';
    }
}

// ── NUEVA: vw_lang_strings() — todos los strings para inyección JS ────────────

if (!function_exists('vw_lang_strings')) {
    /**
     * Devuelve el array completo de strings del idioma actual.
     * Pensado para inyectar en JavaScript via json_encode().
     *
     * Ejemplo de uso en una vista:
     *   <script>window.__lang = <?= json_encode(vw_lang_strings(), JSON_HEX_TAG) ?>;</script>
     *
     * @return array<string, string>
     */
    function vw_lang_strings(): array
    {
        if (!class_exists('I18n')) {
            return [];
        }

        return I18n::obtenerStrings(vw_lang());
    }
}
