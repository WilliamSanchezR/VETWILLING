-- =========================================
-- SCRIPT PARA AGREGAR CAMPOS DE CANCELACION
-- RFS 36: Cancelación de Citas
-- Ejecutar este script en la base de datos vetwilling
-- =========================================

USE vetwilling;

-- Agregar columna motivo_cancelacion
ALTER TABLE agendamiento 
ADD COLUMN IF NOT EXISTS motivo_cancelacion VARCHAR(500) DEFAULT NULL 
COMMENT 'Motivo por el cual fue cancelada la cita';

-- Agregar columna fecha_cancelacion
ALTER TABLE agendamiento 
ADD COLUMN IF NOT EXISTS fecha_cancelacion TIMESTAMP NULL 
COMMENT 'Fecha y hora en que fue cancelada la cita';

-- Agregar columna usuario_cancelo
ALTER TABLE agendamiento 
ADD COLUMN IF NOT EXISTS usuario_cancelo INT DEFAULT NULL 
COMMENT 'ID del usuario que canceló la cita (referencia a tabla usuario)';

-- Verificar la estructura actualizada
DESCRIBE agendamiento;
