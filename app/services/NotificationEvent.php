<?php

/**
 * Value object que encapsula los datos de una notificación antes de despacharse.
 * Inmutable por diseño (readonly). Lo construye el llamador; lo consume NotificationService.
 */
final class NotificationEvent
{
    /**
     * @param string      $tipo        Tipo canónico: cita, vacuna, tratamiento, inventario,
     *                                 acceso_historial, acceso_historial_respuesta, general.
     * @param string      $titulo      Texto corto visible en la tarjeta de notificación.
     * @param string      $mensaje     Cuerpo del mensaje.
     * @param int         $id_usuario  Destinatario (id_usuario de la tabla usuario).
     * @param int|null    $id_paciente Mascota relacionada (opcional).
     * @param string|null $url_accion  Ruta relativa al pulsar la notificación.
     */
    public function __construct(
        public readonly string  $tipo,
        public readonly string  $titulo,
        public readonly string  $mensaje,
        public readonly int     $id_usuario,
        public readonly ?int    $id_paciente = null,
        public readonly ?string $url_accion  = null,
    ) {}
}
