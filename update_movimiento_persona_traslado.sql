-- Actualización de la tabla movimiento_persona para agregar el campo id_centro_vida_traslado
-- Ejecutar este script si la tabla movimiento_persona no tiene el campo id_centro_vida_traslado

-- Verificar si el campo existe antes de agregarlo
ALTER TABLE movimiento_persona 
ADD COLUMN IF NOT EXISTS id_centro_vida_traslado INT NULL;

-- Si la sintaxis anterior no funciona en tu versión de MySQL, usar este comando:
-- ALTER TABLE movimiento_persona ADD id_centro_vida_traslado INT NULL;

-- Agregar foreign key constraint (opcional)
-- ALTER TABLE movimiento_persona 
-- ADD CONSTRAINT fk_centro_vida_traslado 
-- FOREIGN KEY (id_centro_vida_traslado) REFERENCES grupos(id_grupo);
