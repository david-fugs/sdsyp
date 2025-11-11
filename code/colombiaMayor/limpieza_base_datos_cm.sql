-- Script para limpiar tablas y columnas no utilizadas en Colombia Mayor
-- Fecha: 2025-11-10
-- IMPORTANTE: Hacer backup antes de ejecutar este script

-- ====================================
-- TABLAS QUE NO SE ESTÁN USANDO
-- ====================================

-- 1. auditoria_colombia_mayor - No se encuentra ninguna referencia en el código PHP
-- Verificar si tiene datos antes de eliminar:
SELECT COUNT(*) as total_registros FROM auditoria_colombia_mayor;

-- Si está vacía o no es importante, se puede eliminar con:
-- DROP TABLE IF EXISTS auditoria_colombia_mayor;

-- ====================================
-- RESUMEN DE TABLAS ACTIVAS EN USO
-- ====================================

-- TABLAS DE COLOMBIA MAYOR EN USO:
-- 1. personas_colombia_mayor - Tabla principal de personas
-- 2. registros_individuales_cm - Registros individuales (ACTUALIZADA con id_politica_publica)
-- 3. registros_masivos_cm - Registros masivos
-- 4. movimientos_colombia_mayor - Movimientos de personas
-- 5. pagos_colombia_mayor - Pagos a personas
-- 6. detalle_pagos_cm - Detalle de pagos
-- 7. exclusiones_pago_cm - Exclusiones de pago
-- 8. condiciones_colombia_mayor - Condiciones (catálogo)
-- 9. metas_colombia_mayor - Metas (catálogo)
-- 10. actividades_colombia_mayor - Actividades (catálogo)
-- 11. acciones_colombia_mayor - Acciones (catálogo)

-- ====================================
-- CAMBIOS APLICADOS RECIENTEMENTE
-- ====================================

-- 1. Se agregó la columna id_politica_publica a registros_individuales_cm
ALTER TABLE registros_individuales_cm 
ADD COLUMN id_politica_publica INT NULL AFTER id_accion_cm,
ADD KEY idx_id_politica_publica (id_politica_publica),
ADD CONSTRAINT fk_registros_individuales_politica 
    FOREIGN KEY (id_politica_publica) 
    REFERENCES politicas_publicas(id_politica) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE;

-- 2. Se eliminó la columna descripcion_registro de registros_individuales_cm
-- (Ya ejecutado)
-- ALTER TABLE registros_individuales_cm DROP COLUMN descripcion_registro;

-- ====================================
-- VERIFICACIÓN DE INTEGRIDAD
-- ====================================

-- Verificar que todas las foreign keys estén correctas
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'softepuc_sdsyp'
AND TABLE_NAME LIKE '%colombia%'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Verificar estructura de registros_individuales_cm
DESCRIBE registros_individuales_cm;

-- Verificar que la columna id_politica_publica existe
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'softepuc_sdsyp'
AND TABLE_NAME = 'registros_individuales_cm'
AND COLUMN_NAME = 'id_politica_publica';
