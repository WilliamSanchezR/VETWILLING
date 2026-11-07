-- Histórico de tickets con el formato que usa el código (Ticket::registrarHistorico).
--
-- La tabla original solo tenía (id_ticket_resp, ticket_id, usuario_id, mensaje, fecha). El código
-- guarda además el tipo de cambio y los valores anterior y nuevo; sin estas columnas, crear,
-- asignar o actualizar un ticket falla.
-- "mensaje" pasa a admitir NULL y se sigue llenando con la misma descripción.
--
-- Para MariaDB (Hostinger y XAMPP). Es seguro repetirla: IF [NOT] EXISTS no cambia lo que ya está.
-- Reversión: database/tickets_historico_rollback.sql

ALTER TABLE historico_tikects
    ADD COLUMN IF NOT EXISTS tipo VARCHAR(30) NULL AFTER usuario_id,
    ADD COLUMN IF NOT EXISTS valor_anterior TEXT NULL AFTER tipo,
    ADD COLUMN IF NOT EXISTS valor_nuevo TEXT NULL AFTER valor_anterior,
    ADD COLUMN IF NOT EXISTS descripcion TEXT NULL AFTER valor_nuevo,
    ADD COLUMN IF NOT EXISTS mensaje TEXT NULL AFTER descripcion;

ALTER TABLE historico_tikects MODIFY COLUMN mensaje TEXT NULL;
