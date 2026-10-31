-- ============================================================
-- REVERSIÓN de database/medios_envio.sql
-- Solo si hay que volver atrás. Hacer copia de seguridad antes (ver docs/auditoria/medios-envio.md).
--
-- Efectos:
--  - Se pierden el tipo, asunto y contenido guardados de cada envío (el monitor y "Reintentar"
--    del código nuevo dejan de funcionar).
--  - Se BORRAN del historial los envíos que no son de una cita (bienvenidas, recuperación,
--    fichas, pruebas), porque id_agendamiento vuelve a ser obligatorio.
--  - Si el código nuevo sigue desplegado, los correos se siguen enviando pero no se registran.
-- ============================================================

-- 1. Estados que el código anterior no conoce
UPDATE notificaciones_enviadas SET estado_envio = 'fallido' WHERE estado_envio IN ('reintentando', 'rechazado');

-- 2. Envíos sin cita: impiden volver a NOT NULL
DELETE FROM notificaciones_enviadas WHERE id_agendamiento IS NULL;

-- 3. Estructura anterior
ALTER TABLE notificaciones_enviadas
    DROP INDEX idx_notifenv_reintento,
    DROP COLUMN cuerpo_html,
    DROP COLUMN asunto,
    DROP COLUMN tipo_notificacion,
    MODIFY id_agendamiento INT NOT NULL;

-- 4. Plantilla de cancelación agregada por la migración
DELETE p FROM plantilla_mensaje_canal p
JOIN canal_envio_config c ON c.id_canal = p.id_canal AND c.codigo_canal = 'email'
WHERE p.tipo_notificacion = 'cancelacion';

-- 5. Variables originales de la plantilla de recordatorio
UPDATE plantilla_mensaje_canal p
JOIN canal_envio_config c ON c.id_canal = p.id_canal AND c.codigo_canal = 'email'
SET p.variables_disponibles = '["nombre_propietario", "nombre_mascota", "fecha_cita", "hora_cita"]'
WHERE p.tipo_notificacion = 'recordatorio';
