-- Revierte database/soporte_cuentas.sql (se pierde la configuración del correo de recuperación
-- y las marcas de clave temporal pendientes).
DROP TABLE IF EXISTS correo_recuperacion_config;
ALTER TABLE usuario DROP COLUMN debe_cambiar_clave;
