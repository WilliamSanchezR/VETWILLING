-- ============================================================
-- Medios de envío: hace que el monitor y los reintentos funcionen
-- Ejecutar una sola vez en cada base de datos (local y producción).
-- ============================================================

-- 1. El registro de envíos guarda qué se envió, para poder reintentarlo,
--    y acepta correos que no son de una cita (bienvenidas, reportes, etc.)
ALTER TABLE notificaciones_enviadas
    MODIFY id_agendamiento INT NULL,
    ADD COLUMN tipo_notificacion VARCHAR(50) NULL AFTER medio_notificacion,
    ADD COLUMN asunto VARCHAR(255) NULL AFTER destinatario,
    ADD COLUMN cuerpo_html MEDIUMTEXT NULL AFTER asunto,
    ADD INDEX idx_notifenv_reintento (medio_notificacion, estado_envio, ultimo_intento);

-- 2. Plantilla para el correo de cancelación de cita (antes no existía)
INSERT IGNORE INTO plantilla_mensaje_canal
    (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa)
SELECT id_canal,
       'cancelacion',
       'Cancelación de Cita - VetWilling',
       '<p>Hola <strong>{nombre_propietario}</strong>, lamentamos informarte que la cita de <strong>{nombre_mascota}</strong> del <strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong> fue cancelada.</p>',
       'Hola {nombre_propietario}, la cita de {nombre_mascota} del {fecha_cita} a las {hora_cita} fue cancelada.',
       '["nombre_propietario", "nombre_mascota", "fecha_cita", "hora_cita", "tipo_servicio", "motivo_cancelacion"]',
       1
FROM canal_envio_config
WHERE codigo_canal = 'email';

-- 3. Las variables de las plantillas de cita incluyen el servicio
UPDATE plantilla_mensaje_canal p
JOIN canal_envio_config c ON c.id_canal = p.id_canal AND c.codigo_canal = 'email'
SET p.variables_disponibles = '["nombre_propietario", "nombre_mascota", "fecha_cita", "hora_cita", "tipo_servicio"]'
WHERE p.tipo_notificacion = 'recordatorio';
