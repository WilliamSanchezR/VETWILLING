-- =========================================
-- SCRIPT PARA AGREGAR CAMPO recordatorio_enviado
-- Ejecutar este script en la base de datos vetwilling
-- =========================================

USE vetwilling;

-- Agregar columna recordatorio_enviado si no existe
ALTER TABLE agendamiento 
ADD COLUMN IF NOT EXISTS recordatorio_enviado TINYINT(1) DEFAULT 0 
COMMENT 'Indica si se envio el recordatorio por email (0=No, 1=Si)';

-- Verificar la estructura actualizada
DESCRIBE agendamiento;
