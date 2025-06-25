-- Script para crear la tabla politicas_publicas

CREATE TABLE IF NOT EXISTS politicas_publicas (
    id_politica INT AUTO_INCREMENT PRIMARY KEY,
    descripcion_politica VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar algunos datos de ejemplo (opcional)
INSERT INTO politicas_publicas (descripcion_politica) VALUES 
('Política Nacional de Envejecimiento y Vejez'),
('Política Pública Nacional de Discapacidad e Inclusión Social'),
('Política de Seguridad Alimentaria y Nutricional'),
('Política de Primera Infancia'),
('Política Nacional de Equidad de Género');

-- Verificar que la tabla se creó correctamente
SELECT * FROM politicas_publicas;
