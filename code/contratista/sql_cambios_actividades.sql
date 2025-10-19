-- Script SQL para agregar los cambios solicitados
-- Autor: GitHub Copilot
-- Fecha: 2025-01-18

-- 1. Agregar la opción "Otro" en la tabla grupos (para lugar del evento)
-- Solo se necesitan descripcion_grupo y limite_personas (0 porque no tiene límite)
INSERT INTO grupos (descripcion_grupo, limite_personas) 
VALUES ('Otro', 0);

-- 2. Modificar la tabla registro_actividades para agregar el campo otro_lugar
-- Verificar si la columna ya existe antes de agregarla
ALTER TABLE registro_actividades 
ADD COLUMN IF NOT EXISTS otro_lugar VARCHAR(255) DEFAULT NULL COMMENT 'Lugar del evento cuando se selecciona "Otro"';

-- 3. Modificar la columna funcionario_responsable para aceptar texto en lugar de solo IDs
-- Cambiar de INT a VARCHAR para permitir nombres o IDs
ALTER TABLE registro_actividades 
MODIFY COLUMN funcionario_responsable VARCHAR(255) DEFAULT NULL COMMENT 'ID de usuario o nombre del funcionario responsable';

-- 4. Crear índice en funcionario_responsable para mejorar el rendimiento en consultas
CREATE INDEX idx_funcionario_responsable ON registro_actividades(funcionario_responsable);

-- Verificación de los cambios
SELECT 'Cambios aplicados correctamente' AS resultado;

-- Para verificar la estructura de la tabla actualizada:
-- DESCRIBE registro_actividades;

-- Para verificar que la opción "Otro" se agregó a grupos:
-- SELECT * FROM grupos WHERE descripcion_grupo = 'Otro';
