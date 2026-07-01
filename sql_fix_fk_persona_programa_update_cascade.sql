-- Corrige el error:
-- "Cannot delete or update a parent row: a foreign key constraint fails
--  (`softepuc_sdsyp`.`persona_programa`, CONSTRAINT `persona_programa_ibfk_1` ...)"
--
-- La FK actual solo permite ON DELETE CASCADE pero no ON UPDATE CASCADE.
-- Al editar una persona y cambiar su cedula_persona, el UPDATE sobre la
-- tabla `personas` falla porque `persona_programa` sigue teniendo filas
-- con la cedula anterior. Con ON UPDATE CASCADE, MySQL actualiza
-- automáticamente `persona_programa.cedula_persona` cuando cambia
-- `personas.cedula_persona`.

ALTER TABLE persona_programa
  DROP FOREIGN KEY persona_programa_ibfk_1;

ALTER TABLE persona_programa
  ADD CONSTRAINT fk_persona_programa_cedula
    FOREIGN KEY (cedula_persona) REFERENCES personas (cedula_persona)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
