-- Versión de credenciales por usuario (seguridad, T1).
--
-- La aplicación guarda esta versión en la sesión al iniciar sesión y la compara con la base en
-- cada petición protegida. Subirla (cambio de contraseña o de correo, "cerrar las demás sesiones")
-- invalida todas las demás sesiones del usuario en su siguiente petición.
--
-- Orden de despliegue: aplicar esta migración ANTES de desplegar el código que la usa
-- (sin la columna, el login y las rutas protegidas responden 503).
-- Repetirla falla con el error 1060 (columna duplicada) sin cambiar nada.
-- Reversión: database/credenciales_version_rollback.sql (solo después de revertir el código).

ALTER TABLE usuario
    ADD COLUMN credenciales_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER password_hash;
