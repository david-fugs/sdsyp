-- Actualización de la tabla grupos para agregar el campo limite_personas
-- Ejecutar este script si la tabla grupos no tiene el campo limite_personas

-- Verificar si el campo existe antes de agregarlo
ALTER TABLE grupos 
ADD COLUMN IF NOT EXISTS limite_personas INT NOT NULL DEFAULT 10;

-- Si la sintaxis anterior no funciona en tu versión de MySQL, usar este comando:
-- ALTER TABLE grupos ADD limite_personas INT NOT NULL DEFAULT 10;

-- Actualizar grupos existentes con un límite por defecto si es necesario
UPDATE grupos SET limite_personas = 10 WHERE limite_personas IS NULL OR limite_personas = 0;
