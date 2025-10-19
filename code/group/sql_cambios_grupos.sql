-- ===================================================
-- Script SQL: Cambios para Historial de Fechas de Contratación de Grupos
-- Fecha: 2025-10-18
-- Descripción: Agrega tabla para almacenar historial de fechas de contratación
-- ===================================================

-- 1. Crear tabla historial_fechas_contratacion
-- Esta tabla almacena todas las fechas de contratación de cada grupo
-- permitiendo mantener un historial completo
CREATE TABLE IF NOT EXISTS `historial_fechas_contratacion` (
  `id_fecha_contratacion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` INT(11) NOT NULL,
  `fecha_contratacion` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_fecha_contratacion`),
  KEY `idx_id_grupo` (`id_grupo`),
  KEY `idx_fecha_contratacion` (`fecha_contratacion`),
  CONSTRAINT `fk_historial_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- Notas de uso:
-- ===================================================
-- 1. Ejecutar este script en la base de datos SDSYP
-- 2. La tabla permite múltiples fechas de contratación por grupo
-- 3. El campo created_at registra cuándo se insertó cada fecha
-- 4. El ON DELETE CASCADE asegura que al eliminar un grupo se eliminen sus fechas
-- 5. En la interfaz, solo se mostrará la fecha más reciente en la tabla principal
-- 6. En el modal de edición se mostrará el historial completo con opciones de editar/eliminar

-- Ejemplo de uso:
-- Para obtener la fecha más reciente de un grupo:
-- SELECT fecha_contratacion 
-- FROM historial_fechas_contratacion 
-- WHERE id_grupo = X 
-- ORDER BY fecha_contratacion DESC 
-- LIMIT 1;
