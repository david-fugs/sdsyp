-- Tabla para almacenar fotografías de los registros de Colombia Mayor
-- Ejecutar este script en la base de datos

CREATE TABLE IF NOT EXISTS `fotos_registros_cm` (
  `id_foto` INT(11) NOT NULL AUTO_INCREMENT,
  `id_registro_individual` INT(11) NULL DEFAULT NULL,
  `id_registro_masivo` INT(11) NULL DEFAULT NULL,
  `ruta_foto` VARCHAR(255) NOT NULL,
  `tipo_registro` ENUM('individual', 'masivo') NOT NULL,
  `fecha_subida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_foto`),
  INDEX `idx_registro_individual` (`id_registro_individual`),
  INDEX `idx_registro_masivo` (`id_registro_masivo`),
  INDEX `idx_tipo` (`tipo_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: En producción, considera agregar claves foráneas si las tablas relacionadas lo soportan
-- FOREIGN KEY (`id_registro_individual`) REFERENCES `registros_individuales_cm`(`id_registro_individual_cm`) ON DELETE CASCADE,
-- FOREIGN KEY (`id_registro_masivo`) REFERENCES `registros_masivos_cm`(`id_registro_masivo_cm`) ON DELETE CASCADE
