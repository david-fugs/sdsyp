# Instrucciones de Implementación - Campo "Sin Convenio"

## Resumen de Cambios

Se ha agregado la funcionalidad para marcar personas como "Sin Convenio" en el sistema. Los cambios incluyen:

1. **Base de datos**: Nuevo campo `sin_convenio` en la tabla `personas`
2. **Modal Agregar Persona**: Checkbox "Sin Convenio"
3. **Modal Editar Persona**: Checkbox "Sin Convenio"
4. **Exportar a Excel**: Nueva columna "Con Convenio" que muestra "SÍ" o "NO"
5. **Dashboard (access.php)**: Nueva columna "SIN CONVENIO" en la tabla de distribución de personas por grupo

## Pasos para la Implementación

### 1. Ejecutar el SQL en la Base de Datos

Ejecuta el siguiente SQL en tu base de datos MySQL:

```sql
-- Archivo: sql_sin_convenio.sql
ALTER TABLE personas 
ADD COLUMN sin_convenio TINYINT(1) DEFAULT 0 COMMENT 'Indica si la persona está sin convenio (0=Con convenio, 1=Sin convenio)';
```

**Cómo ejecutar:**
- Abre phpMyAdmin (http://localhost/phpmyadmin)
- Selecciona tu base de datos
- Ve a la pestaña "SQL"
- Copia y pega el contenido del archivo `sql_sin_convenio.sql`
- Haz clic en "Continuar"

### 2. Verificar los Archivos Modificados

Los siguientes archivos han sido actualizados:

#### Archivos PHP Backend:
- `code/persons/addPerson.php` - Modificado para guardar el campo sin_convenio
- `code/persons/editPersona.php` - Modificado para actualizar el campo sin_convenio
- `code/persons/getPersons.php` - Agregado data-attribute para sin_convenio y badge en columna estado
- `code/persons/exportPersons.php` - Agregada columna "Con Convenio" en el Excel
- `code/reports/exportIndividualesFromReports.php` - Agregada columna "Con Convenio" en reportes
- `code/reports/exportMovimientos.php` - Agregada columna "Con Convenio" en reportes
- `code/reports/exportContratistaCombinadoFromReports.php` - Agregada columna "Con Convenio" en hoja Individuales

#### Archivos PHP Frontend:
- `code/persons/seePerson.php` - Agregado checkbox en modales de agregar y editar
- `access.php` - Agregada columna "SIN CONVENIO" en tabla de distribución

### 3. Funcionalidad Implementada

#### En el Modal de Agregar Persona:
- Se agregó un checkbox "Sin Convenio" al final del formulario
- Si el usuario marca el checkbox, la persona se guardará como "Sin Convenio"

#### En el Modal de Editar Persona:
- Se agregó el mismo checkbox "Sin Convenio"
- Al abrir el modal de edición, el checkbox se marca automáticamente si la persona está sin convenio
- Los cambios se guardan al actualizar la persona

#### En la Tabla de Personas (seePerson.php):
- En la columna "Estado", si una persona tiene activado "Sin Convenio", aparece un badge amarillo con el texto "SIN CONVENIO" junto al estado actual
- Esto permite identificar rápidamente qué personas están sin convenio

#### En Exportar a Excel (Personas):
- Nueva columna "Con Convenio" al final del archivo Excel
- Muestra "SÍ" si la persona tiene convenio (sin_convenio = 0)
- Muestra "NO" si la persona NO tiene convenio (sin_convenio = 1)

#### En Reportes - Exportar a Excel:
Los siguientes reportes ahora incluyen la columna "Con Convenio":
- **Actividades Individuales**: Hoja con datos de personas en actividades individuales
- **Movimientos de Personas**: Exportación de movimientos de personas
- **Reporte Combinado Contratista**: En la hoja de "Actividades Individuales"

#### En el Dashboard (access.php):
- Tabla "Distribución de Personas por Grupo" ahora tiene una nueva columna "SIN CONVENIO"
- Muestra la cantidad de personas sin convenio por cada grupo
- El número aparece en un badge amarillo para fácil identificación

## Notas Importantes

1. **Valor por defecto**: Por defecto, todas las personas tienen convenio (sin_convenio = 0)
2. **Retrocompatibilidad**: Las personas existentes automáticamente tendrán el valor 0 (con convenio)
3. **Validación**: No hay validación especial, es un simple checkbox opcional
4. **Permisos**: Todos los usuarios que pueden agregar/editar personas pueden modificar este campo

## Verificación de la Implementación

Para verificar que todo funciona correctamente:

1. ✅ Ejecutar el SQL y verificar que el campo se agregó: `DESCRIBE personas;`
2. ✅ Agregar una nueva persona y marcar "Sin Convenio"
3. ✅ Verificar en la base de datos que el campo `sin_convenio` = 1
4. ✅ Ver la tabla de personas y verificar que aparece el badge "SIN CONVENIO" en la columna Estado
5. ✅ Editar una persona existente y marcar/desmarcar "Sin Convenio"
6. ✅ Exportar a Excel desde Personas y verificar que la columna "Con Convenio" aparece correctamente
7. ✅ Exportar reportes (Actividades Individuales, Movimientos) y verificar la columna "Con Convenio"
8. ✅ Ir a access.php y verificar que la columna "SIN CONVENIO" muestra los conteos correctos

## Solución de Problemas

### El checkbox no aparece en el modal
- Limpiar caché del navegador (Ctrl + Shift + R)
- Verificar que los archivos se guardaron correctamente

### Error al guardar
- Verificar que el SQL se ejecutó correctamente
- Verificar permisos de escritura en los archivos PHP

### La columna no aparece en Excel
- Verificar que exportPersons.php se actualizó correctamente
- Probar exportar nuevamente limpiando filtros

### La columna no aparece en access.php
- Verificar que access.php se actualizó correctamente
- Refrescar la página (F5)

## Contacto

Si tienes algún problema con la implementación, verifica los archivos modificados y asegúrate de que el SQL se ejecutó correctamente.
