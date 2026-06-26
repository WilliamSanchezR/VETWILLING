-- ============================================================
-- Migración: Consolidar sistema de notificaciones
-- Fecha:     2026-06-25
-- Rama:      fix/consolidacion-notificaciones
-- ============================================================
-- PROPÓSITO:
--   1. Renombrar las tres tablas obsoletas/rotas a _bkp_*
--   2. Crear la tabla canónica `notificacion` con utf8mb4 e índices correctos
--   3. Migrar datos desde _bkp_notificacion (era la tabla correcta, datos válidos)
--   4. Migrar datos desde _bkp_notificaciones (plural, era write-only) generando
--      un titulo desde el tipo porque la tabla original no tenía esa columna
--
-- TABLAS QUE NO SE TOCAN:
--   - notificaciones_enviadas   (auditoría de correos, correcta)
--   - preferencias_notificacion (preferencias de usuario, correcta)
--
-- CÓMO EJECUTAR:
--   mysql -u root vetwilling_hat < 2026_06_25_consolidar_notificaciones.sql
--   (o pegarlo en phpMyAdmin con la BD seleccionada)
--
-- PARA BORRAR LOS BACKUPS (solo tras validar):
--   Descomenta el bloque DROP al final de este archivo y vuelve a correr.
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;   -- Evitar conflictos de FK durante rename/recreate

-- ============================================================
-- PASO 0 · Verificación previa (lectura, no modifica nada)
-- ============================================================
SELECT 'ANTES: conteo tablas originales' AS paso;

SELECT
    'notificacion'        AS tabla,
    COUNT(*)              AS filas
FROM notificacion
UNION ALL
SELECT
    'notificaciones',
    COUNT(*)
FROM notificaciones
UNION ALL
SELECT
    'notificaciones_leidas',
    COUNT(*)
FROM notificaciones_leidas;

-- ============================================================
-- PASO 1 · Renombrar tablas a _bkp_*  (idempotente via IF EXISTS)
-- ============================================================
SELECT 'PASO 1: Renombrando tablas a _bkp_*' AS paso;

-- 1a. Si los backups ya existen de una ejecución anterior, los eliminamos
--     para que el RENAME no falle. Sólo se ejecuta si el backup existe.
DROP TABLE IF EXISTS _bkp_notificacion;
DROP TABLE IF EXISTS _bkp_notificaciones;
DROP TABLE IF EXISTS _bkp_notificaciones_leidas;

-- 1b. Mover las tres tablas a sus nombres de backup.
--     Si alguna no existe, MySQL devolverá un error "Table doesn't exist"
--     que se puede ignorar — significa que ya fue renombrada antes.
RENAME TABLE
    notificacion          TO _bkp_notificacion,
    notificaciones        TO _bkp_notificaciones,
    notificaciones_leidas TO _bkp_notificaciones_leidas;

SELECT 'PASO 1 completado' AS resultado;

-- ============================================================
-- PASO 2 · Crear tabla canónica `notificacion`
-- ============================================================
SELECT 'PASO 2: Creando tabla notificacion (utf8mb4, InnoDB)' AS paso;

CREATE TABLE notificacion (
    id_notificacion  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_usuario       INT UNSIGNED    NOT NULL
                     COMMENT 'FK a la tabla de usuarios del sistema',
    tipo             VARCHAR(50)     NOT NULL DEFAULT 'general'
                     COMMENT 'cita | vacuna | tratamiento | inventario | acceso_historial | acceso_historial_respuesta | general',
    titulo           VARCHAR(255)    NOT NULL,
    mensaje          TEXT            DEFAULT NULL,
    url_accion       VARCHAR(500)    DEFAULT NULL
                     COMMENT 'Ruta relativa al hacer clic en la notificación',
    id_paciente      INT UNSIGNED    DEFAULT NULL
                     COMMENT 'FK opcional a pacientes (mascota relacionada)',
    leida            TINYINT(1)      NOT NULL DEFAULT 0,
    descartada       TINYINT(1)      NOT NULL DEFAULT 0,
    fecha_creacion   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_notificacion),

    -- Índice principal para queries del badge y listado por usuario
    INDEX idx_usuario_leida_desc (id_usuario, leida, descartada),

    -- Índice para filtrar por mascota
    INDEX idx_paciente (id_paciente)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabla canónica de notificaciones in-app — VetWilling';

SELECT 'PASO 2 completado' AS resultado;

-- ============================================================
-- PASO 3 · Migrar datos desde _bkp_notificacion
--          (tenía el esquema correcto — migración directa)
-- ============================================================
SELECT 'PASO 3: Migrando datos desde _bkp_notificacion' AS paso;

INSERT INTO notificacion
    (id_notificacion, id_usuario, tipo, titulo, mensaje,
     url_accion, id_paciente, leida, descartada, fecha_creacion)
SELECT
    id_notificacion,
    id_usuario,
    tipo,
    titulo,
    mensaje,
    url_accion,
    id_paciente,
    leida,
    descartada,
    fecha_creacion
FROM _bkp_notificacion;

SELECT CONCAT('PASO 3 completado — filas migradas: ', ROW_COUNT()) AS resultado;

-- ============================================================
-- PASO 4 · Migrar datos desde _bkp_notificaciones (plural)
--          Esquema origen: usuario_id, tipo, mensaje, leido,
--                          referencia_id, fecha
--          ▸ titulo      → generado desde tipo
--          ▸ id_paciente → NULL  (referencia_id NO es id_paciente)
--          ▸ url_accion  → NULL
-- ============================================================
SELECT 'PASO 4: Migrando datos desde _bkp_notificaciones (plural)' AS paso;

INSERT INTO notificacion
    (id_usuario, tipo, titulo, mensaje,
     url_accion, id_paciente, leida, descartada, fecha_creacion)
SELECT
    usuario_id                      AS id_usuario,
    tipo                            AS tipo,
    -- Generar titulo a partir del tipo (la tabla plural no tenía columna titulo)
    CASE tipo
        WHEN 'acceso_historial'
            THEN 'Solicitud de acceso al historial'
        WHEN 'acceso_historial_respuesta'
            THEN 'Respuesta a solicitud de historial'
        ELSE 'Notificación'
    END                             AS titulo,
    mensaje                         AS mensaje,
    NULL                            AS url_accion,
    NULL                            AS id_paciente,
    COALESCE(leido, 0)              AS leida,
    0                               AS descartada,
    COALESCE(fecha, NOW())          AS fecha_creacion
FROM _bkp_notificaciones;

SELECT CONCAT('PASO 4 completado — filas migradas: ', ROW_COUNT()) AS resultado;

-- ============================================================
-- PASO 5 · Verificación final
-- ============================================================
SELECT 'PASO 5: Verificación de conteos post-migración' AS paso;

SELECT
    'notificacion (nueva)'             AS tabla,
    COUNT(*)                           AS total,
    SUM(leida = 0 AND descartada = 0)  AS no_leidas,
    SUM(leida = 1)                     AS leidas,
    SUM(descartada = 1)                AS descartadas
FROM notificacion;

-- Conteo por tipo para validar la migración
SELECT tipo, COUNT(*) AS filas FROM notificacion GROUP BY tipo ORDER BY filas DESC;

-- Comparar con el origen (deben sumar igual)
SELECT 'Origen _bkp_notificacion' AS origen, COUNT(*) AS filas FROM _bkp_notificacion
UNION ALL
SELECT 'Origen _bkp_notificaciones',          COUNT(*)         FROM _bkp_notificaciones
UNION ALL
SELECT 'Total en nueva notificacion',          COUNT(*)         FROM notificacion;

SET foreign_key_checks = 1;

SELECT '=== MIGRACIÓN COMPLETA — valida los conteos antes de continuar ===' AS estado;

-- ============================================================
-- DROP DE BACKUPS — DESCOMENTA SÓLO TRAS VALIDAR MANUALMENTE
-- ============================================================
-- DROP TABLE IF EXISTS _bkp_notificacion;
-- DROP TABLE IF EXISTS _bkp_notificaciones;
-- DROP TABLE IF EXISTS _bkp_notificaciones_leidas;
