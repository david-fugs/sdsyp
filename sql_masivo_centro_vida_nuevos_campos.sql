-- SQL: Nuevos campos para masiva_centro_vida
-- Ejecutar en orden para agregar profesion y cambiar jornada a ENUM

-- 1. Agregar columna profesion
ALTER TABLE masiva_centro_vida
    ADD COLUMN profesion ENUM('Trabajo social','Psicología','Psicosocial') NULL DEFAULT NULL
    AFTER jornada;

-- 2. (Opcional) Si jornada era VARCHAR con múltiples valores separados por coma,
--    y ahora es un único valor, se puede migrar a ENUM.
--    Si ya es VARCHAR, el radio button simplemente enviará un único valor ('Mañana' o 'Tarde').
--    Para forzar ENUM de un único valor:
-- ALTER TABLE masiva_centro_vida
--     MODIFY COLUMN jornada ENUM('Mañana','Tarde') NULL DEFAULT NULL;
-- NOTA: Solo ejecutar si no existen registros con múltiples jornadas ('Mañana, Noche').
--       Si hay datos históricos con 'Noche', primero actualizarlos:
-- UPDATE masiva_centro_vida SET jornada = 'Tarde' WHERE jornada LIKE '%Noche%';
