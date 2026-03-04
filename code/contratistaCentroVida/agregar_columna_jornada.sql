-- Script para agregar la columna jornada a la tabla masiva_centro_vida
-- Ejecutar este script en phpMyAdmin o MySQL si la columna no existe

ALTER TABLE `masiva_centro_vida` 
ADD COLUMN `jornada` VARCHAR(100) NULL DEFAULT NULL AFTER `tipo_registro`;

-- Verificar que la columna se haya agregado correctamente
DESCRIBE masiva_centro_vida;
