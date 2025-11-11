-- Script para agregar la columna id_politica_publica a la tabla registros_individuales_cm
-- Ejecutar este script en la base de datos si la columna no existe

-- Verificar si la columna existe
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'registros_individuales_cm' 
AND COLUMN_NAME = 'id_politica_publica';

-- Si la columna no existe, ejecutar el siguiente ALTER TABLE:
ALTER TABLE registros_individuales_cm 
ADD COLUMN id_politica_publica INT NULL AFTER id_accion_cm,
ADD KEY idx_id_politica_publica (id_politica_publica),
ADD CONSTRAINT fk_registros_individuales_politica 
    FOREIGN KEY (id_politica_publica) 
    REFERENCES politicas_publicas(id_politica) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE;

-- Verificar la estructura de la tabla después del cambio
DESCRIBE registros_individuales_cm;
