<?php
/**
 * PreferenciasManager.php
 * Servicio para gestionar preferencias de usuario (tema, idioma, zona horaria, formato de fecha, notificaciones).
 * Guarda preferencias en un archivo JSON por usuario (puedes migrar a BD si lo prefieres).
 */
class PreferenciasManager {
    private $id_usuario;
    private $archivo;
    private $defaults = [
        'tema' => 'claro',
        'idioma' => 'es',
        'zona_horaria' => 'America/Bogota',
        'formato_fecha' => 'dd/mm/yyyy',
        'notificaciones' => 'email',
    ];

    public function __construct($id_usuario) {
        $this->id_usuario = $id_usuario;
        $dir = __DIR__ . '/../../storage/preferencias/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $this->archivo = $dir . 'prefs_' . $id_usuario . '.json';
    }

    public function obtener() {
        if (!file_exists($this->archivo)) return $this->defaults;
        $data = json_decode(file_get_contents($this->archivo), true);
        return array_merge($this->defaults, $data ?: []);
    }

    public function guardar($cambios) {
        $prefs = $this->obtener();
        foreach ($cambios as $k => $v) {
            if (array_key_exists($k, $this->defaults)) {
                $prefs[$k] = $v;
            }
        }
        file_put_contents($this->archivo, json_encode($prefs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $prefs;
    }

    public function restablecer() {
        file_put_contents($this->archivo, json_encode($this->defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $this->defaults;
    }

    // Para soporte de idiomas
    public function obtenerStringsIdioma($idioma) {
        $file = __DIR__ . '/../../public/assets/lang/' . $idioma . '.json';
        if (!file_exists($file)) return ['strings' => []];
        $strings = json_decode(file_get_contents($file), true);
        return ['strings' => $strings ?: []];
    }
}
