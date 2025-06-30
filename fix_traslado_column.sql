-- Script para verificar y opcionalmente modificar la estructura de la tabla movimiento_persona
-- Ejecutar solo si deseas permitir NULL en lugar de usar 0

-- 1. Verificar la estructura actual de la columna
DESCRIBE movimiento_persona;

-- 2. Si quieres permitir NULL en lugar de usar 0, ejecuta esta consulta:
-- (OPCIONAL - solo si prefieres NULL en lugar de 0)
/*
ALTER TABLE movimiento_persona 
MODIFY COLUMN id_centro_vida_traslado INT NULL;
*/

-- 3. Si ya has insertado registros con 0 y quieres cambiarlos a NULL:
-- (OPCIONAL - solo si cambias la estructura y quieres limpiar datos existentes)
/*
UPDATE movimiento_persona 
SET id_centro_vida_traslado = NULL 
WHERE id_centro_vida_traslado = 0;
*/

-- 4. Verificar los datos actuales
SELECT 
    id_movimiento_persona,
    cedula_persona,
    id_condicion,
    id_centro_vida_traslado,
    fecha_movimiento
FROM movimiento_persona 
ORDER BY id_movimiento_persona DESC 
LIMIT 10;
