-- Tabla para Control de Asistencia Centro Vida
-- Ejecutar este script para crear la tabla necesaria

CREATE TABLE IF NOT EXISTS control_asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula_persona VARCHAR(20) NOT NULL,
    fecha_asistencia DATE NOT NULL,
    id_usuario INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha_usuario (fecha_asistencia, id_usuario),
    INDEX idx_cedula (cedula_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
