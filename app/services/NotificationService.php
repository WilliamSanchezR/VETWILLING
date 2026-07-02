<?php

require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/NotificationEvent.php';

/**
 * Dispatcher central de notificaciones — VetWilling.
 *
 * Todo evento que deba generar una notificación pasa por aquí.
 * Cada fase del roadmap añade un canal en afterCreate():
 *
 *   Fase 2 (actual) — in-app insertion
 *   Fase 3          — SSE broadcast
 *   Fase 6          — FCM push
 *
 * Los llamadores externos siguen usando notificar() / notificarBatch()
 * del helper; este servicio es la implementación interna.
 */
class NotificationService
{
    private Notificacion $model;

    public function __construct()
    {
        $this->model = new Notificacion();
    }

    // =========================================================================
    // API pública
    // =========================================================================

    /**
     * Despacha una notificación para un único usuario.
     *
     * @return int|false  id_notificacion insertado, o false en cualquier error.
     */
    public function dispatch(NotificationEvent $event): int|false
    {
        if ($event->id_usuario <= 0) {
            error_log('[NotificationService::dispatch] id_usuario inválido');
            return false;
        }

        try {
            $id = $this->crearInApp($event);
            if ($id === false) return false;

            $this->afterCreate($id, $event);
            return $id;

        } catch (\Throwable $e) {
            error_log('[NotificationService::dispatch] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Despacha el mismo evento para múltiples usuarios (fan-out on write).
     * Usa una única transacción de BD para todos los destinatarios.
     *
     * @param  NotificationEvent[] $events  Un evento por destinatario (mismos datos, distinto id_usuario).
     * @return int  Cantidad de notificaciones persistidas.
     */
    public function dispatchBatch(array $events): int
    {
        $events = array_values(array_filter(
            $events,
            static fn($e) => $e instanceof NotificationEvent && $e->id_usuario > 0
        ));

        if (empty($events)) return 0;

        try {
            $payload = array_map(
                static fn(NotificationEvent $e) => [
                    'id_usuario'  => $e->id_usuario,
                    'tipo'        => $e->tipo,
                    'titulo'      => $e->titulo,
                    'mensaje'     => $e->mensaje,
                    'url_accion'  => $e->url_accion,
                    'id_paciente' => $e->id_paciente,
                ],
                $events
            );

            $insertadas = $this->model->crearBatch($payload);

            // Fase 3+: por ahora afterCreate se omite en batch para no
            // hacer N llamadas; se añadirá como afterBatch() en Fase 3.

            return $insertadas;

        } catch (\Throwable $e) {
            error_log('[NotificationService::dispatchBatch] ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // Métodos privados
    // =========================================================================

    /**
     * Persiste la notificación en la tabla `notificacion`.
     *
     * @return int|false  id_notificacion insertado, o false si falla.
     */
    private function crearInApp(NotificationEvent $event): int|false
    {
        return $this->model->crear([
            'id_usuario'  => $event->id_usuario,
            'tipo'        => $event->tipo,
            'titulo'      => $event->titulo,
            'mensaje'     => $event->mensaje,
            'url_accion'  => $event->url_accion,
            'id_paciente' => $event->id_paciente,
        ]);
    }

    /**
     * Hook de extensión post-creación.
     * Cada fase futura añade su canal aquí sin tocar el flujo principal.
     *
     * @param int               $id_notificacion  ID recién insertado.
     * @param NotificationEvent $event            Datos originales del evento.
     */
    private function afterCreate(int $id_notificacion, NotificationEvent $event): void
    {
        // Fase 3 — SSE: notificar al canal abierto del usuario en tiempo real.
        // SseBroadcaster::send($event->id_usuario, $id_notificacion);

        // Fase 6 — Push: enviar FCM si el usuario tiene token registrado y permiso.
        // PushDispatcher::send($event->id_usuario, $event->titulo, $event->mensaje, $event->url_accion);
    }
}
