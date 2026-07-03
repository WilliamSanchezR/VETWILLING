<?php

/**
 * Fachada de notificaciones — VetWilling
 *
 * Thin wrappers sobre NotificationService para mantener compatibilidad
 * con todos los controllers que ya usan notificar() / notificarBatch().
 * La lógica real vive en app/services/NotificationService.php.
 *
 * Uso básico:
 *   require_once BASE_PATH . '/app/helpers/notification/notificacion_helper.php';
 *   notificar('cita', 'Tu cita está próxima', 'Tienes una cita mañana a las 10:00.', $id_usuario);
 */

if (!function_exists('notificar')) {

    require_once __DIR__ . '/../../services/NotificationEvent.php';
    require_once __DIR__ . '/../../services/NotificationService.php';

    // -------------------------------------------------------------------------
    // Catálogo de tipos — icono Remixicon, clase CSS, título por defecto
    // -------------------------------------------------------------------------
    function _catalogoNotificacion(): array
    {
        return [
            'cita'                       => ['ri-calendar-check-line',   'info',    'Recordatorio de cita'],
            'vacuna'                     => ['ri-syringe-line',           'warning', 'Vacuna próxima'],
            'tratamiento'                => ['ri-medicine-bottle-line',   'info',    'Seguimiento de tratamiento'],
            'inventario'                 => ['ri-inbox-archive-line',     'warning', 'Alerta de inventario'],
            'acceso_historial'           => ['ri-file-lock-line',         'info',    'Solicitud de acceso al historial'],
            'acceso_historial_respuesta' => ['ri-file-check-line',        'success', 'Respuesta a solicitud de historial'],
            'general'                    => ['ri-notification-3-line',    'info',    'Notificación'],
        ];
    }

    /**
     * Devuelve [icono, clase_color, titulo_defecto] para un tipo.
     * Si el tipo no existe en el catálogo retorna los valores de 'general'.
     */
    function metadataNotificacion(string $tipo): array
    {
        $catalogo = _catalogoNotificacion();
        return $catalogo[$tipo] ?? $catalogo['general'];
    }

    // -------------------------------------------------------------------------
    // Funciones de disparo — delegan a NotificationService
    // -------------------------------------------------------------------------

    /**
     * Crea una notificación in-app para un único usuario.
     *
     * @param string      $tipo        Clave del catálogo (cita, vacuna, tratamiento…)
     * @param string      $titulo      Texto corto visible en la tarjeta
     * @param string      $mensaje     Cuerpo del mensaje
     * @param int         $id_usuario  id_usuario del destinatario
     * @param int|null    $id_paciente Mascota relacionada (opcional)
     * @param string|null $url_accion  Ruta relativa al pulsar la notificación
     * @return int|false  id_notificacion insertado, o false si falló
     */
    function notificar(
        string  $tipo,
        string  $titulo,
        string  $mensaje,
        int     $id_usuario,
        ?int    $id_paciente = null,
        ?string $url_accion  = null
    ): int|false {
        return (new NotificationService())->dispatch(
            new NotificationEvent($tipo, $titulo, $mensaje, $id_usuario, $id_paciente, $url_accion)
        );
    }

    /**
     * Crea la misma notificación para múltiples usuarios (fan-out on write).
     * Cada destinatario recibe su propia fila en la tabla `notificacion`.
     *
     * @param string      $tipo
     * @param string      $titulo
     * @param string      $mensaje
     * @param int[]       $destinatarios  Array de id_usuario
     * @param int|null    $id_paciente    Mascota relacionada (opcional, misma para todos)
     * @param string|null $url_accion
     * @return int  Número de notificaciones insertadas correctamente
     */
    function notificarBatch(
        string  $tipo,
        string  $titulo,
        string  $mensaje,
        array   $destinatarios,
        ?int    $id_paciente = null,
        ?string $url_accion  = null
    ): int {
        $destinatarios = array_values(array_filter(
            array_map('intval', $destinatarios),
            static fn(int $id) => $id > 0
        ));

        if (empty($destinatarios)) return 0;

        $events = array_map(
            static fn(int $id) => new NotificationEvent(
                $tipo, $titulo, $mensaje, $id, $id_paciente, $url_accion
            ),
            $destinatarios
        );

        return (new NotificationService())->dispatchBatch($events);
    }
}
