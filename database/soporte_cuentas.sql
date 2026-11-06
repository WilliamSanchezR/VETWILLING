-- Soporte de cuentas del superadministrador.
--
-- 1. correo_recuperacion_config: remitente, asunto, mensaje y vigencia del correo de recuperación
--    de contraseña (Medios de envío → Correo de recuperación). Una sola fila (id = 1).
--    Sin esta tabla el sistema sigue usando los valores por defecto.
-- 2. usuario.debe_cambiar_clave: 1 cuando el administrador asignó una clave temporal; el usuario
--    debe cambiarla al iniciar sesión. Sin la columna, el login funciona igual (se toma como 0).
--
-- Repetirla falla con el error 1050/1060 (tabla o columna duplicada) sin cambiar nada.
-- Reversión: database/soporte_cuentas_rollback.sql

CREATE TABLE correo_recuperacion_config (
    id                 TINYINT UNSIGNED NOT NULL DEFAULT 1,
    remitente          VARCHAR(255) NULL COMMENT 'Vacío = remitente del canal de correo',
    nombre_remitente   VARCHAR(255) NULL,
    asunto             VARCHAR(150) NULL,
    mensaje            TEXT NULL COMMENT 'Texto plano; admite {email} y {minutos}',
    minutos_vigencia   SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    fecha_modificacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modificado_por     INT NULL,
    PRIMARY KEY (id),
    CONSTRAINT chk_correo_recuperacion_unica CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO correo_recuperacion_config (id) VALUES (1);

ALTER TABLE usuario
    ADD COLUMN debe_cambiar_clave TINYINT(1) NOT NULL DEFAULT 0 AFTER credenciales_version;
