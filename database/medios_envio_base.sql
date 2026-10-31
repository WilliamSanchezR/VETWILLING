-- ============================================================
-- Medios de envío (base): tablas de canales y plantillas
-- Solo hace falta si la base NO tiene canal_envio_config / plantilla_mensaje_canal.
-- Ejecutar ANTES de database/medios_envio.sql. Es seguro repetirlo (IF NOT EXISTS / INSERT IGNORE).
-- No incluye credenciales: usuario, contraseña y remitente SMTP se configuran en la pantalla.
-- Generado a partir de la estructura de la base local (2026-10-28).
-- ============================================================

CREATE TABLE IF NOT EXISTS `canal_envio_config` (
  `id_canal` int unsigned NOT NULL AUTO_INCREMENT,
  `codigo_canal` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_canal` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `habilitado` tinyint(1) NOT NULL DEFAULT '1',
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` smallint unsigned DEFAULT NULL,
  `smtp_encriptacion` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'smtps | tls',
  `smtp_usuario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_remitente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_nombre_remitente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `push_api_key` text COLLATE utf8mb4_unicode_ci COMMENT 'FCM server key',
  `push_endpoint` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_reintentos` tinyint unsigned NOT NULL DEFAULT '3',
  `intervalo_reintento_seg` smallint unsigned NOT NULL DEFAULT '30',
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modificado_por` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id_canal`),
  UNIQUE KEY `codigo_canal` (`codigo_canal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plantilla_mensaje_canal` (
  `id_plantilla` int unsigned NOT NULL AUTO_INCREMENT,
  `id_canal` int unsigned NOT NULL,
  `tipo_notificacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'cita | vacuna | tratamiento | inventario | recordatorio | general',
  `asunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sólo para email',
  `cuerpo_html` text COLLATE utf8mb4_unicode_ci COMMENT 'HTML para email',
  `cuerpo_texto` text COLLATE utf8mb4_unicode_ci COMMENT 'Texto plano para push/in_app',
  `variables_disponibles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Documentación de {variables} usables',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_plantilla`),
  UNIQUE KEY `uk_canal_tipo` (`id_canal`,`tipo_notificacion`),
  CONSTRAINT `fk_plantilla_canal` FOREIGN KEY (`id_canal`) REFERENCES `canal_envio_config` (`id_canal`) ON DELETE CASCADE,
  CONSTRAINT `plantilla_mensaje_canal_chk_1` CHECK (json_valid(`variables_disponibles`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canales (sin credenciales)
INSERT IGNORE INTO canal_envio_config (id_canal, codigo_canal, nombre_canal, descripcion, habilitado, smtp_host, smtp_port, smtp_encriptacion, max_reintentos, intervalo_reintento_seg) VALUES ('1', 'email', 'Correo Electrónico', 'Envío de notificaciones y recordatorios por SMTP', '1', 'smtp.gmail.com', '465', 'smtps', '3', '30');
INSERT IGNORE INTO canal_envio_config (id_canal, codigo_canal, nombre_canal, descripcion, habilitado, smtp_host, smtp_port, smtp_encriptacion, max_reintentos, intervalo_reintento_seg) VALUES ('2', 'in_app', 'Notificaciones en la Aplicación', 'Notificaciones internas visibles en el panel del usuario', '1', NULL, NULL, NULL, '1', '0');
INSERT IGNORE INTO canal_envio_config (id_canal, codigo_canal, nombre_canal, descripcion, habilitado, smtp_host, smtp_port, smtp_encriptacion, max_reintentos, intervalo_reintento_seg) VALUES ('3', 'push', 'Notificaciones Móviles (Push)', 'Notificaciones push para la aplicación móvil via FCM', '0', NULL, NULL, NULL, '3', '30');

-- Plantillas iniciales (la de cancelación la agrega medios_envio.sql)
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('1', 'cita', 'Confirmación de Cita - VetWilling', '<p>Hola <strong>{nombre_propietario}</strong>, tu cita para <strong>{nombre_mascota}</strong> fue registrada el <strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong>.</p>', 'Hola {nombre_propietario}, tu cita para {nombre_mascota} fue registrada el {fecha_cita} a las {hora_cita}.', '[\"nombre_propietario\", \"nombre_mascota\", \"fecha_cita\", \"hora_cita\", \"tipo_servicio\", \"estado\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('1', 'recordatorio', 'Recordatorio de Cita - VetWilling', '<p>Hola <strong>{nombre_propietario}</strong>, te recordamos que tienes una cita mañana (<strong>{fecha_cita}</strong> a las <strong>{hora_cita}</strong>) para <strong>{nombre_mascota}</strong>.</p>', 'Recordatorio: cita de {nombre_mascota} el {fecha_cita} a las {hora_cita}.', '[\"nombre_propietario\", \"nombre_mascota\", \"fecha_cita\", \"hora_cita\", \"tipo_servicio\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('1', 'vacuna', 'Recordatorio de Vacuna - VetWilling', '<p>Hola <strong>{nombre_propietario}</strong>, la vacuna <strong>{nombre_vacuna}</strong> de <strong>{nombre_mascota}</strong> vence el <strong>{fecha_vencimiento}</strong>.</p>', 'La vacuna {nombre_vacuna} de {nombre_mascota} vence el {fecha_vencimiento}.', '[\"nombre_propietario\", \"nombre_mascota\", \"nombre_vacuna\", \"fecha_vencimiento\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('1', 'inventario', 'Alerta de Inventario - VetWilling', '<p>El producto <strong>{nombre_producto}</strong> tiene stock bajo: <strong>{stock_actual}</strong> unidades (mínimo: {stock_minimo}).</p>', 'Alerta: {nombre_producto} con stock bajo ({stock_actual} unidades).', '[\"nombre_producto\", \"stock_actual\", \"stock_minimo\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('1', 'general', 'Notificación - VetWilling', '<p>{mensaje}</p>', '{mensaje}', '[\"mensaje\", \"titulo\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('2', 'cita', NULL, NULL, 'Cita registrada para {nombre_mascota} el {fecha_cita}.', '[\"nombre_mascota\", \"fecha_cita\", \"hora_cita\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('2', 'recordatorio', NULL, NULL, 'Recordatorio: cita de {nombre_mascota} mañana a las {hora_cita}.', '[\"nombre_mascota\", \"hora_cita\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('2', 'vacuna', NULL, NULL, 'Vacuna {nombre_vacuna} de {nombre_mascota} vence el {fecha_vencimiento}.', '[\"nombre_mascota\", \"nombre_vacuna\", \"fecha_vencimiento\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('2', 'inventario', NULL, NULL, 'Stock bajo: {nombre_producto} ({stock_actual} unidades).', '[\"nombre_producto\", \"stock_actual\"]', '1');
INSERT IGNORE INTO plantilla_mensaje_canal (id_canal, tipo_notificacion, asunto, cuerpo_html, cuerpo_texto, variables_disponibles, activa) VALUES ('2', 'general', NULL, NULL, '{mensaje}', '[\"mensaje\"]', '1');
