-- Solución que el administrador escribe al gestionar un ticket (Ticket::actualizarInformacionTicket).
--
-- El código ya lee y guarda tickets.solucion, pero ninguna migración la creaba: en las bases que
-- no la tienen, abrir /admin/gestion-tickets falla con "Unknown column".
-- Si la base ya la tiene, repetirla falla con el error 1060 (columna duplicada) sin cambiar nada.
-- Reversión: database/tickets_solucion_rollback.sql

ALTER TABLE tickets
    ADD COLUMN solucion TEXT NULL AFTER descripcion;
