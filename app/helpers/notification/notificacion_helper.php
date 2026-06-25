<?php

/**
 * Helper de Notificaciones
 * Fachada de alto nivel para disparar notificaciones desde cualquier controlador.
 * Usa function_exists para ser seguro ante inclusiones múltiples.
 */

if (!function_exists('notificar')) {

    require_once __DIR__ . '/../../models/Notificacion.php';

    // ── Catálogo de tipos ─────────────────────────────────────────────────────
    // Cada tipo mapea a [icono_remixicon, clase_color_css, titulo_por_defecto]

    function _catalogoNotificacion()
    {
        return [
            'actividad_nueva'       => ['ri-book-2-line',         'info',    'Nueva actividad'],
            'calificacion_publicada' => ['ri-check-double-fill',   'success', 'Calificación publicada'],
            'entrega_recibida'       => ['ri-mail-send-line',      'info',    'Entrega recibida'],
            'evento_nuevo'           => ['ri-calendar-event-line', 'warning', 'Nuevo evento'],
            'asistencia_ausente'     => ['ri-alarm-warning-line',  'warning', 'Ausencia registrada'],
            'general'                => ['ri-notification-3-line', 'info',    'Notificación'],
        ];
    }

    /**
     * Retorna [icono, clase_color, titulo_defecto] para un tipo dado.
     * Si el tipo no existe en el catálogo retorna los valores de 'general'.
     */
    function metadataNotificacion($tipo)
    {
        $catalogo = _catalogoNotificacion();
        return $catalogo[$tipo] ?? $catalogo['general'];
    }

    // ── Funciones de disparo ──────────────────────────────────────────────────

    /**
     * Crear una notificación individual.
     *
     * @param string      $tipo            Clave del catálogo
     * @param string      $titulo          Título corto visible en la tarjeta
     * @param string      $mensaje         Cuerpo del mensaje
     * @param int         $id_destinatario id_usuario del destinatario
     * @param int         $id_institucion
     * @param string|null $url_accion      URL al pulsar la notificación
     * @param string|null $entidad_tipo    'actividad' | 'calificacion' | 'entrega'
     * @param int|null    $entidad_id      ID de la entidad relacionada
     * @return bool
     */
    function notificar($tipo, $titulo, $mensaje, $id_destinatario, $id_institucion, $url_accion = null, $entidad_tipo = null, $entidad_id = null)
    {
        if ((int)$id_destinatario <= 0 || (int)$id_institucion <= 0) {
            return false;
        }
        try {
            $model = new Notificacion();
            $result = $model->crear([
                'id_institucion'  => (int)$id_institucion,
                'id_destinatario' => (int)$id_destinatario,
                'tipo'            => $tipo,
                'titulo'          => $titulo,
                'mensaje'         => $mensaje,
                'url_accion'      => $url_accion,
                'entidad_tipo'    => $entidad_tipo,
                'entidad_id'      => $entidad_id,
            ]);
            return $result !== false;
        } catch (Throwable $e) {
            error_log('[notificar] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear notificaciones en batch (fanout on write).
     * Todos los destinatarios reciben el mismo contenido en filas independientes.
     *
     * @param string   $tipo
     * @param string   $titulo
     * @param string   $mensaje
     * @param int[]    $destinatarios    Array de id_usuario
     * @param int      $id_institucion
     * @param string|null $url_accion
     * @param string|null $entidad_tipo
     * @param int|null    $entidad_id
     * @return int Número de notificaciones insertadas
     */
    function notificarBatch($tipo, $titulo, $mensaje, array $destinatarios, $id_institucion, $url_accion = null, $entidad_tipo = null, $entidad_id = null)
    {
        $destinatarios = array_values(array_filter(
            array_map('intval', $destinatarios),
            function ($id) {
                return $id > 0;
            }
        ));

        if (empty($destinatarios) || (int)$id_institucion <= 0) {
            return 0;
        }

        $notificaciones = array_map(function ($id_dest) use ($tipo, $titulo, $mensaje, $id_institucion, $url_accion, $entidad_tipo, $entidad_id) {
            return [
                'id_institucion'  => (int)$id_institucion,
                'id_destinatario' => $id_dest,
                'tipo'            => $tipo,
                'titulo'          => $titulo,
                'mensaje'         => $mensaje,
                'url_accion'      => $url_accion,
                'entidad_tipo'    => $entidad_tipo,
                'entidad_id'      => $entidad_id,
            ];
        }, $destinatarios);

        try {
            $model = new Notificacion();
            return $model->crearBatch($notificaciones);
        } catch (Throwable $e) {
            error_log('[notificarBatch] ' . $e->getMessage());
            return 0;
        }
    }
}
