-- Agregar campo sin_convenio a la tabla personas
-- Este campo indica si la persona está SIN CONVENIO
-- Valores: 0 = Con convenio (default), 1 = Sin convenio

ALTER TABLE personas 
ADD COLUMN sin_convenio TINYINT(1) DEFAULT 0 COMMENT 'Indica si la persona está sin convenio (0=Con convenio, 1=Sin convenio)';
