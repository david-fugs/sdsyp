-- Script para actualizar la tabla acciones para vincularla con actividades en lugar de metas

-- 1. Agregar la nueva columna id_actividad a la tabla acciones
ALTER TABLE acciones ADD COLUMN id_actividad INT;

-- 2. Opcional: Si quieres mantener la relación, puedes crear una clave foránea
-- ALTER TABLE acciones ADD CONSTRAINT fk_acciones_actividades 
-- FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad) ON DELETE CASCADE;

-- 3. Eliminar la columna id_meta de la tabla acciones (opcional, después de migrar los datos)
-- ALTER TABLE acciones DROP COLUMN id_meta;

-- Nota: Si tienes datos existentes, puedes hacer una migración manual para asignar 
-- las acciones a actividades basándote en algún criterio específico
