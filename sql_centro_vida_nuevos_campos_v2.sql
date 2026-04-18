-- ============================================================
-- SQL: Nuevos campos Centro Vida - Actualización completa
-- Fecha: 2026-04-18
-- ============================================================

-- 1. Agregar columna condicion_otra a registro_centro_vida
--    (texto libre cuando id_condicion es NULL / "Otra")
ALTER TABLE registro_centro_vida
    ADD COLUMN condicion_otra VARCHAR(255) NULL DEFAULT NULL
    AFTER id_condicion;

-- 2. Agregar columna profesion
ALTER TABLE registro_centro_vida
    ADD COLUMN profesion ENUM('Trabajo social','Psicología','Psicosocial') NULL DEFAULT NULL
    AFTER observacion;

-- 3. Agregar columna jornada
ALTER TABLE registro_centro_vida
    ADD COLUMN jornada ENUM('Mañana','Tarde') NULL DEFAULT NULL
    AFTER profesion;

-- ============================================================
-- 4. Tabla intermedia para Grupos Externos por Registro CV
-- ============================================================
CREATE TABLE IF NOT EXISTS registro_centro_vida_grupo_externo (
    id                          INT AUTO_INCREMENT PRIMARY KEY,
    id_registro_centro_vida     INT NOT NULL,
    id_grupo_externo            INT NOT NULL,
    UNIQUE KEY uq_reg_ge (id_registro_centro_vida, id_grupo_externo),
    CONSTRAINT fk_rcvge_registro  FOREIGN KEY (id_registro_centro_vida)
        REFERENCES registro_centro_vida(id_registro_centro_vida) ON DELETE CASCADE,
    CONSTRAINT fk_rcvge_ge        FOREIGN KEY (id_grupo_externo)
        REFERENCES grupos_externos(id_grupo_externo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTA: Las tablas grupos_externos y persona_grupo_externo
-- ya fueron creadas en sql_jornada_grupos_externos.sql.
-- Este script solo agrega los campos de registro_centro_vida
-- y la tabla de relación registro↔grupos_externos.
-- ============================================================
