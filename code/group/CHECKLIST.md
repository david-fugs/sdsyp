# ✅ CHECKLIST DE INSTALACIÓN Y VERIFICACIÓN

## 📋 LISTA DE VERIFICACIÓN PASO A PASO

### FASE 1: PREPARACIÓN
- [ ] Hacer backup de la base de datos actual
- [ ] Tener acceso a phpMyAdmin o MySQL
- [ ] Tener el proyecto SDSYP funcionando correctamente

---

### FASE 2: INSTALACIÓN DE BASE DE DATOS

#### Paso 1: Ejecutar Script SQL
- [ ] Abrir phpMyAdmin
- [ ] Seleccionar base de datos `sdsyp`
- [ ] Ir a pestaña "SQL"
- [ ] Copiar contenido de `sql_cambios_grupos.sql`
- [ ] Pegar y ejecutar
- [ ] Verificar mensaje de éxito

#### Paso 2: Verificar Tabla Creada
- [ ] Ejecutar: `SHOW TABLES LIKE 'historial_fechas_contratacion';`
- [ ] Debe aparecer 1 tabla
- [ ] Ejecutar: `DESCRIBE historial_fechas_contratacion;`
- [ ] Debe mostrar 5 columnas

**Consulta de verificación:**
```sql
SELECT COUNT(*) as total_tablas 
FROM information_schema.tables 
WHERE table_schema = 'sdsyp' 
AND table_name = 'historial_fechas_contratacion';
-- Debe retornar: total_tablas = 1
```

---

### FASE 3: VERIFICACIÓN DE ARCHIVOS

#### Archivos PHP Modificados
- [ ] `code/group/seeGroup.php` existe
- [ ] `code/group/getGroup.php` existe
- [ ] `code/group/addGroup.php` existe
- [ ] `code/reports/generateExcel.php` existe

#### Archivos PHP Nuevos
- [ ] `code/group/getHistorialFechas.php` existe
- [ ] `code/group/addFechaContratacion.php` existe
- [ ] `code/group/editFechaContratacion.php` existe
- [ ] `code/group/deleteFechaContratacion.php` existe

#### Archivos de Documentación
- [ ] `code/group/sql_cambios_grupos.sql` existe
- [ ] `code/group/README_CAMBIOS_GRUPOS.md` existe
- [ ] `code/group/INSTALACION_RAPIDA.md` existe
- [ ] `code/group/EJEMPLOS_EXCEL.md` existe
- [ ] `code/group/RESUMEN_FINAL.md` existe
- [ ] `code/group/CHECKLIST.md` existe (este archivo)

---

### FASE 4: PRUEBAS FUNCIONALES

#### Test 1: Crear Nuevo Grupo
- [ ] Ir a: `http://localhost/sdsyp/code/group/seeGroup.php`
- [ ] Clic en "Agregar Grupo"
- [ ] Llenar campos:
  - [ ] Descripción: "Grupo Test"
  - [ ] Límite: 10
  - [ ] Fecha: (seleccionar hoy)
- [ ] Clic en "Guardar"
- [ ] Verificar mensaje de éxito
- [ ] Verificar que aparece en la tabla
- [ ] Verificar que muestra la fecha en formato dd/mm/yyyy

**Resultado esperado:** ✅ Grupo creado con fecha visible

---

#### Test 2: Ver Historial de Fechas
- [ ] Clic en botón "Editar" (ícono lápiz) del grupo creado
- [ ] Esperar a que cargue el historial
- [ ] Verificar que se muestra la sección "Historial de Fechas de Contratación"
- [ ] Verificar que aparece la fecha creada
- [ ] Verificar que tiene botones de editar y eliminar
- [ ] Verificar que hay formulario para agregar nueva fecha

**Resultado esperado:** ✅ Historial visible con la fecha

---

#### Test 3: Agregar Nueva Fecha al Historial
- [ ] En el modal de edición, ir a "Agregar Nueva Fecha"
- [ ] Seleccionar una fecha diferente
- [ ] Clic en "Agregar"
- [ ] Verificar mensaje de éxito
- [ ] Verificar que aparece la nueva fecha en el historial
- [ ] Verificar que están ordenadas de más reciente a más antigua

**Resultado esperado:** ✅ Nueva fecha agregada y visible

---

#### Test 4: Editar Fecha Existente
- [ ] Clic en botón "Editar" (ícono lápiz) de una fecha
- [ ] Aparece popup con input de fecha
- [ ] Cambiar la fecha
- [ ] Clic en "Guardar"
- [ ] Verificar mensaje de éxito
- [ ] Verificar que la fecha se actualizó en el historial

**Resultado esperado:** ✅ Fecha editada correctamente

---

#### Test 5: Eliminar Fecha
- [ ] Clic en botón "Eliminar" (ícono basura) de una fecha
- [ ] Aparece confirmación
- [ ] Clic en "Sí, eliminar"
- [ ] Verificar mensaje de éxito
- [ ] Verificar que la fecha desapareció del historial

**Resultado esperado:** ✅ Fecha eliminada correctamente

---

#### Test 6: Verificar Fecha en Tabla Principal
- [ ] Cerrar el modal de edición
- [ ] Ver la tabla de grupos
- [ ] Verificar columna "Fecha Contratación"
- [ ] Verificar que muestra la fecha más reciente del historial
- [ ] Si todas las fechas fueron eliminadas, debe mostrar "Sin registro"

**Resultado esperado:** ✅ Fecha más reciente visible en tabla

---

#### Test 7: Exportar Excel con Nuevas Columnas
- [ ] Ir a: `http://localhost/sdsyp/code/reports/seeReports.php`
- [ ] Seleccionar año actual
- [ ] Clic en "Exportar Excel"
- [ ] Abrir archivo descargado
- [ ] Desplazarse a las últimas columnas
- [ ] Verificar columna 56: "FECHA ÚLTIMO CONTRATO GRUPO"
- [ ] Verificar columna 57: "DÍAS ACTIVO DESDE CONTRATO"
- [ ] Verificar que tienen datos para personas con grupos que tienen fecha

**Resultado esperado:** ✅ Excel con 2 columnas nuevas funcionando

---

### FASE 5: PRUEBAS DE CASOS ESPECIALES

#### Test 8: Grupo Sin Fecha de Contrato
- [ ] Crear un grupo nuevo SIN poner fecha
  - **Nota:** Esto requiere modificar temporalmente el campo a no requerido, o editar un grupo existente antiguo
- [ ] Verificar que en la tabla muestra "Sin registro"
- [ ] Exportar Excel
- [ ] Verificar que las columnas 56 y 57 están vacías para personas de ese grupo

**Resultado esperado:** ✅ Manejo correcto de grupos sin fecha

---

#### Test 9: Múltiples Fechas en un Grupo
- [ ] Editar un grupo
- [ ] Agregar 3 fechas diferentes (ej: 01/01/2024, 01/06/2024, 01/01/2025)
- [ ] Cerrar modal
- [ ] Verificar que en la tabla muestra la más reciente (01/01/2025)
- [ ] Exportar Excel
- [ ] Verificar que en Excel también aparece la más reciente

**Resultado esperado:** ✅ Siempre muestra la fecha más reciente

---

#### Test 10: Eliminar Grupo con Fechas
- [ ] Clic en botón "Eliminar" de un grupo que tiene fechas
- [ ] Confirmar eliminación
- [ ] Verificar mensaje de éxito
- [ ] En phpMyAdmin, ejecutar:
  ```sql
  SELECT COUNT(*) FROM historial_fechas_contratacion WHERE id_grupo = [ID_ELIMINADO];
  ```
- [ ] Verificar que retorna 0 (fechas eliminadas por CASCADE)

**Resultado esperado:** ✅ Fechas eliminadas automáticamente

---

### FASE 6: VALIDACIONES

#### Test 11: Validación de Fecha Vacía
- [ ] Editar un grupo
- [ ] Ir a "Agregar Nueva Fecha"
- [ ] Dejar el campo de fecha vacío
- [ ] Clic en "Agregar"
- [ ] Verificar que muestra mensaje de advertencia

**Resultado esperado:** ✅ Validación funciona

---

#### Test 12: Verificar Prepared Statements
- [ ] Revisar archivos PHP creados
- [ ] Verificar que TODOS usan `$mysqli->prepare()`
- [ ] Verificar que TODOS usan `bind_param()`
- [ ] No debe haber concatenación directa en queries

**Resultado esperado:** ✅ Seguridad SQL injection implementada

---

### FASE 7: VERIFICACIÓN DE DATOS

#### Test 13: Consulta SQL de Verificación Completa
```sql
-- Ejecutar esta consulta para ver estado completo
SELECT 
    g.id_grupo,
    g.descripcion_grupo,
    COUNT(hfc.id_fecha_contratacion) AS total_fechas,
    MAX(hfc.fecha_contratacion) AS fecha_mas_reciente,
    MIN(hfc.fecha_contratacion) AS fecha_mas_antigua
FROM grupos g
LEFT JOIN historial_fechas_contratacion hfc ON g.id_grupo = hfc.id_grupo
GROUP BY g.id_grupo, g.descripcion_grupo
ORDER BY g.id_grupo;
```

- [ ] Ejecutar consulta
- [ ] Verificar que muestra todos los grupos
- [ ] Verificar que `total_fechas` es correcto
- [ ] Verificar que `fecha_mas_reciente` coincide con la tabla web

**Resultado esperado:** ✅ Datos consistentes

---

### FASE 8: DOCUMENTACIÓN

#### Test 14: Revisar Documentación
- [ ] Leer `INSTALACION_RAPIDA.md`
- [ ] Leer sección 1-5 de `README_CAMBIOS_GRUPOS.md`
- [ ] Ver ejemplos en `EJEMPLOS_EXCEL.md`
- [ ] Entender el resumen en `RESUMEN_FINAL.md`

**Resultado esperado:** ✅ Documentación clara y completa

---

### FASE 9: RENDIMIENTO

#### Test 15: Probar con Múltiples Grupos
- [ ] Crear 10 grupos con fechas
- [ ] Verificar que la tabla carga rápido
- [ ] Abrir modal de edición de varios grupos
- [ ] Verificar que el historial carga rápido (< 1 segundo)
- [ ] Exportar Excel con datos de varios grupos
- [ ] Verificar que genera el archivo correctamente

**Resultado esperado:** ✅ Buen rendimiento

---

### FASE 10: COMPATIBILIDAD

#### Test 16: Grupos Existentes
- [ ] Identificar grupos creados antes de esta actualización
- [ ] Verificar que aparecen con "Sin registro"
- [ ] Editar uno de esos grupos
- [ ] Agregar su primera fecha de contratación
- [ ] Verificar que ahora muestra la fecha

**Resultado esperado:** ✅ Compatibilidad con datos anteriores

---

## 📊 RESUMEN DE RESULTADOS

### Resumen por Fase

| Fase | Tests | Pasados | Fallados | Estado |
|------|-------|---------|----------|--------|
| 1. Preparación | 3 | __ | __ | ⏳ |
| 2. Base de Datos | 2 | __ | __ | ⏳ |
| 3. Archivos | 14 | __ | __ | ⏳ |
| 4. Funcionales | 7 | __ | __ | ⏳ |
| 5. Casos Especiales | 3 | __ | __ | ⏳ |
| 6. Validaciones | 2 | __ | __ | ⏳ |
| 7. Datos | 1 | __ | __ | ⏳ |
| 8. Documentación | 1 | __ | __ | ⏳ |
| 9. Rendimiento | 1 | __ | __ | ⏳ |
| 10. Compatibilidad | 1 | __ | __ | ⏳ |

**TOTAL:** ___ / 16 tests pasados

---

## 🎯 CRITERIOS DE ACEPTACIÓN

Para considerar la instalación exitosa, se deben cumplir:

- ✅ Todas las pruebas de Fase 1-4 (básicas)
- ✅ Al menos 80% de pruebas de Fase 5-6 (especiales)
- ✅ Todas las pruebas de Fase 2 (base de datos)
- ✅ Sin errores PHP en ningún archivo

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### Si un test falla:

1. **Leer el error completo** en el navegador o consola
2. **Verificar la base de datos** con las consultas SQL
3. **Revisar el archivo PHP** correspondiente
4. **Consultar la documentación** en README_CAMBIOS_GRUPOS.md
5. **Verificar permisos** de archivos y carpetas

### Errores comunes:

| Error | Solución |
|-------|----------|
| Tabla no existe | Ejecutar sql_cambios_grupos.sql |
| 404 en archivo PHP | Verificar que existen todos los archivos nuevos |
| Historial no carga | Ver consola del navegador (F12) |
| Excel sin columnas | Limpiar caché, verificar tabla existe |
| Fecha no se guarda | Verificar tipo de dato DATE en tabla |

---

## ✅ CHECKLIST FINAL

### Antes de Pasar a Producción:

- [ ] ✅ Script SQL ejecutado sin errores
- [ ] ✅ Tabla historial_fechas_contratacion creada
- [ ] ✅ Todos los archivos nuevos están presentes
- [ ] ✅ Pruebas funcionales básicas (Test 1-7) pasadas
- [ ] ✅ Excel genera correctamente con 2 columnas nuevas
- [ ] ✅ Sin errores en consola del navegador
- [ ] ✅ Sin errores PHP
- [ ] ✅ Documentación revisada
- [ ] ✅ Backup de base de datos realizado
- [ ] ✅ Usuarios capacitados en el uso del sistema

---

## 📝 NOTAS

- Este checklist debe ser completado por el usuario final
- Marcar cada item con ✅ cuando se complete
- Si un test falla, marcar con ❌ y anotar el problema
- Guardar este archivo completado para referencia futura

---

**Fecha de ejecución:** _______________  
**Ejecutado por:** _______________  
**Resultado final:** ⏳ PENDIENTE / ✅ EXITOSO / ❌ FALLIDO

---

**Versión del checklist:** 1.0  
**Última actualización:** 18/10/2025
