-- ============================================================
-- SQL: Nuevo campo area_psicosocial_alcaldia en registro_centro_vida
-- Descripción: Campo exclusivo para tipo_usuario 12 (alcaldía CV1)
-- ============================================================

ALTER TABLE registro_centro_vida
    ADD COLUMN area_psicosocial_alcaldia ENUM('Trabajo social','Psicologia','Trabajo social y psicologia') NULL DEFAULT NULL
    COMMENT 'Área psicosocial alcaldía CV1 (solo tipo_usuario 12)'
    AFTER jornada;
