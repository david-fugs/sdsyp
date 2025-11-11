-- Verificación: Comprobar que las foreign keys están correctamente configuradas
-- para usar condiciones_componente en lugar de condiciones_colombia_mayor

USE softepuc_sdsyp;

-- 1. Verificar estructura de movimientos_colombia_mayor
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'softepuc_sdsyp' 
AND TABLE_NAME = 'movimientos_colombia_mayor'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 2. Verificar que existen datos en condiciones_componente
SELECT COUNT(*) as total_condiciones_cm 
FROM condiciones_componente 
WHERE descripcion_condicion LIKE 'C.M%';

-- 3. Listar todas las condiciones C.M disponibles
SELECT id_condicion, descripcion_condicion 
FROM condiciones_componente 
WHERE descripcion_condicion LIKE 'C.M%' 
ORDER BY descripcion_condicion;
