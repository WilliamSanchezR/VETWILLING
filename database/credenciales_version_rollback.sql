-- Reversión de database/credenciales_version.sql.
-- Ejecutar SOLO después de revertir el código que usa la columna (si no, el login responderá 503).

ALTER TABLE usuario
    DROP COLUMN credenciales_version;
