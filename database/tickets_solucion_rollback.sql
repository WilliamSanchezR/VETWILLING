-- Revierte database/tickets_solucion.sql (borra las soluciones guardadas).
ALTER TABLE tickets DROP COLUMN solucion;
