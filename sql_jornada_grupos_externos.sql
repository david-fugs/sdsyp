-- ============================================================
-- Nuevos campos: jornada y grupo_externo
-- Ejecutar en orden sobre la base de datos del proyecto
-- ============================================================

-- 1. Campo jornada en la tabla personas
--    Valores posibles: 'Mañana' | 'Tarde' | NULL (no informado)
ALTER TABLE personas
  ADD COLUMN jornada ENUM('Mañana','Tarde') NULL DEFAULT NULL
    COMMENT 'Jornada de asistencia de la persona (Mañana o Tarde)';

-- ============================================================
-- 2. Catálogo de grupos externos
-- ============================================================
CREATE TABLE IF NOT EXISTS grupos_externos (
  id_grupo_externo    INT            NOT NULL AUTO_INCREMENT,
  nombre_grupo_externo VARCHAR(150)  NOT NULL,
  descripcion          VARCHAR(255)  NULL DEFAULT NULL,
  activo               TINYINT(1)   NOT NULL DEFAULT 1,
  created_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_grupo_externo),
  CONSTRAINT uq_nombre_grupo_externo UNIQUE (nombre_grupo_externo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de grupos externos disponibles para asignar a personas';

-- ============================================================
-- 3. Relación muchos a muchos: personas ↔ grupos_externos
-- ============================================================
CREATE TABLE IF NOT EXISTS persona_grupo_externo (
  id               INT         NOT NULL AUTO_INCREMENT,
  cedula_persona   VARCHAR(20) NOT NULL,
  id_grupo_externo INT         NOT NULL,
  created_at       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT uq_persona_grupo_externo
    UNIQUE (cedula_persona, id_grupo_externo),
  CONSTRAINT fk_pge_persona
    FOREIGN KEY (cedula_persona)
    REFERENCES personas(cedula_persona)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_pge_grupo_externo
    FOREIGN KEY (id_grupo_externo)
    REFERENCES grupos_externos(id_grupo_externo)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Relación muchos a muchos entre personas y grupos externos';

-- ============================================================
-- 4. Datos de ejemplo en grupos_externos
--    (ajustar o reemplazar según los grupos reales del proyecto)
-- ============================================================
INSERT IGNORE INTO grupos_externos (nombre_grupo_externo) VALUES
  ('Grupo Externo A'),
  ('Grupo Externo B'),
  ('Grupo Externo C');
