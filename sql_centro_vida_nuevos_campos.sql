-- ============================================================
-- SQL: Nuevos campos para Registros Centro Vida
-- Ejecutar en orden sobre la base de datos del proyecto
-- ============================================================

-- ============================================================
-- 1. Tabla registro_centro_vida
--    Campos: condicion_otra, profesion, jornada
-- ============================================================

-- 1.1 Condición personalizada ("Otra")
--     Se guarda el texto libre cuando el usuario elige "Otra" en el select de condición
ALTER TABLE registro_centro_vida
  ADD COLUMN condicion_otra VARCHAR(255) NULL DEFAULT NULL
    COMMENT 'Texto libre cuando la condición seleccionada es "Otra"';

-- 1.2 Profesión del profesional que atiende el registro
ALTER TABLE registro_centro_vida
  ADD COLUMN profesion ENUM('Trabajo social','Psicología','Psicosocial') NULL DEFAULT NULL
    COMMENT 'Profesión del funcionario que registra la actividad';

-- 1.3 Jornada de la actividad registrada (individual)
ALTER TABLE registro_centro_vida
  ADD COLUMN jornada ENUM('Mañana','Tarde') NULL DEFAULT NULL
    COMMENT 'Jornada en que se realizó la actividad (Mañana o Tarde)';

-- ============================================================
-- 2. Tabla registro_centro_vida_fechas
--    (estructura actual: id_registro_centro_vida, fecha_atencion)
--    Para el punto 6 (1 registro por persona por fecha), la lógica
--    cambia en el backend PHP: ya no se agrupa en un registro +
--    varias fechas, sino que se inserta 1 registro_centro_vida
--    por cada fecha. La tabla registro_centro_vida_fechas sigue
--    usándose (1 fila por registro, 1 fecha por fila).
--    No se requiere cambio estructural, solo cambio en el PHP.
-- ============================================================

-- ============================================================
-- 3. Tabla de registros masivos del modal formCentroVida
--    (addRegistroCentroVidaMasivo.php → tabla registro_centro_vida)
--    Los mismos campos aplican porque el masivo también inserta
--    en registro_centro_vida.  Los ALTER de arriba ya los cubren.
-- ============================================================

-- ============================================================
-- 4. Campo condicion_otra en personas
--    (si se quiere guardar también en el perfil de la persona)
--    NOTA: esto es OPCIONAL, solo aplicar si se requiere persistir
--    la condición personalizada en el perfil de personas.
-- ============================================================
-- ALTER TABLE personas
--   ADD COLUMN condicion_otra VARCHAR(255) NULL DEFAULT NULL
--     COMMENT 'Condición personalizada cuando se elige "Otra" en condicion_componente';

-- ============================================================
-- 5. Campo profesion en personas
--    OPCIONAL: si se quiere guardar la profesión también en el
--    perfil de la persona (seePerson.php).
-- ============================================================
-- ALTER TABLE personas
--   ADD COLUMN profesion ENUM('Trabajo social','Psicología','Psicosocial') NULL DEFAULT NULL
--     COMMENT 'Profesión del/la profesional que atiende a la persona';
