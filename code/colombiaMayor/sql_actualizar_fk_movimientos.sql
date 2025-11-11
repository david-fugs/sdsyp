-- Script para actualizar la foreign key de movimientos_colombia_mayor
-- Para que apunte a condiciones_componente en lugar de condiciones_colombia_mayor

USE softepuc_sdsyp;

-- 1. Eliminar la foreign key antigua
ALTER TABLE movimientos_colombia_mayor 
DROP FOREIGN KEY movimientos_colombia_mayor_ibfk_2;

-- 2. Crear la nueva foreign key apuntando a condiciones_componente
ALTER TABLE movimientos_colombia_mayor 
ADD CONSTRAINT movimientos_colombia_mayor_ibfk_condicion 
FOREIGN KEY (id_condicion_cm) 
REFERENCES condiciones_componente(id_condicion);

-- Verificar los cambios
SHOW CREATE TABLE movimientos_colombia_mayor\G
