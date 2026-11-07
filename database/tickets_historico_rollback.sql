-- Revierte database/tickets_historico.sql (borra lo que guardaron las columnas nuevas).
ALTER TABLE historico_tikects
    DROP COLUMN IF EXISTS tipo,
    DROP COLUMN IF EXISTS valor_anterior,
    DROP COLUMN IF EXISTS valor_nuevo,
    DROP COLUMN IF EXISTS descripcion;
