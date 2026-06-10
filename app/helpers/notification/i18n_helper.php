<?php

if (!function_exists('notif_t')) {
    function notif_t(string $clave, ?int $n = null): string
    {
        $t = $GLOBALS['t'] ?? null;

        if (!is_callable($t)) {
            return $clave;
        }

        return $t($clave, $n);
    }
}

if (!function_exists('notif_strings')) {
    function notif_strings(): array
    {
        $idioma  = $GLOBALS['lang'] ?? 'es';
        $strings = I18n::obtenerStrings($idioma);

        return array_filter(
            $strings,
            fn($clave) => str_starts_with($clave, 'notif.'),
            ARRAY_FILTER_USE_KEY
        );
    }
}