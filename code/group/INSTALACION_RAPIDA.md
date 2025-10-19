# GUÍA RÁPIDA DE INSTALACIÓN - HISTORIAL DE FECHAS DE CONTRATACIÓN

## Paso 1: Ejecutar Script SQL

### Opción A - Usando phpMyAdmin:
1. Abrir phpMyAdmin en el navegador
2. Seleccionar la base de datos `sdsyp` (o el nombre de tu base de datos)
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido del archivo `sql_cambios_grupos.sql`
5. Hacer clic en "Continuar" o "Ejecutar"

### Opción B - Usando línea de comandos MySQL:
```bash
cd c:\xampp\htdocs\sdsyp\code\group
mysql -u root -p sdsyp < sql_cambios_grupos.sql
```

### Opción C - Copiar y pegar directamente:
```sql
CREATE TABLE IF NOT EXISTS `historial_fechas_contratacion` (
  `id_fecha_contratacion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` INT(11) NOT NULL,
  `fecha_contratacion` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_fecha_contratacion`),
  KEY `idx_id_grupo` (`id_grupo`),
  KEY `idx_fecha_contratacion` (`fecha_contratacion`),
  CONSTRAINT `fk_historial_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Paso 2: Verificar que la tabla se creó correctamente

Ejecutar esta consulta en phpMyAdmin o MySQL:
```sql
DESCRIBE historial_fechas_contratacion;
```

Deberías ver 5 columnas:
- id_fecha_contratacion
- id_grupo
- fecha_contratacion
- created_at
- updated_at

## Paso 3: Probar la funcionalidad

### 3.1. Probar agregar un grupo nuevo:
1. Ir a: `http://localhost/sdsyp/code/group/seeGroup.php`
2. Clic en "Agregar Grupo"
3. Llenar:
   - Descripción: "Grupo de Prueba"
   - Límite: 10
   - Fecha de Contratación: (seleccionar fecha actual)
4. Guardar
5. Verificar que aparece en la tabla con la fecha

### 3.2. Probar editar y gestionar historial:
1. Clic en el ícono de lápiz de cualquier grupo
2. Verificar que se muestra la sección "Historial de Fechas de Contratación"
3. Probar agregar una nueva fecha
4. Probar editar una fecha existente
5. Probar eliminar una fecha

### 3.3. Probar Excel:
1. Ir a: `http://localhost/sdsyp/code/reports/seeReports.php`
2. Seleccionar un año
3. Clic en "Exportar Excel"
4. Abrir el archivo descargado
5. Verificar que existen las columnas:
   - Columna 56: "FECHA ÚLTIMO CONTRATO GRUPO"
   - Columna 57: "DÍAS ACTIVO DESDE CONTRATO"

## Paso 4: Migrar datos existentes (OPCIONAL)

Si tienes grupos existentes y conoces sus fechas de contratación, puedes agregarlas manualmente:

### Opción A - Desde la interfaz web:
1. Editar cada grupo
2. Agregar la fecha de contratación correspondiente

### Opción B - Desde SQL (si tienes las fechas):
```sql
-- Ejemplo: Agregar fecha para el grupo con id_grupo = 1
INSERT INTO historial_fechas_contratacion (id_grupo, fecha_contratacion) 
VALUES (1, '2024-01-15');

-- Agregar múltiples fechas para diferentes grupos
INSERT INTO historial_fechas_contratacion (id_grupo, fecha_contratacion) VALUES
(2, '2024-02-20'),
(3, '2024-03-10'),
(4, '2024-04-05');
```

## Solución de Problemas Comunes

### Error: "Table 'sdsyp.historial_fechas_contratacion' doesn't exist"
**Solución:** Ejecutar el script SQL del Paso 1.

### Error: "Cannot add or update a child row: a foreign key constraint fails"
**Solución:** Verificar que el `id_grupo` existe en la tabla `grupos`.

### Error al cargar el historial en el modal (spinner infinito)
**Solución:** 
1. Abrir la consola del navegador (F12)
2. Ver si hay errores JavaScript o AJAX
3. Verificar que los archivos PHP tienen permisos de lectura
4. Verificar la ruta en el error de red

### La fecha no se muestra en la tabla principal
**Solución:**
1. Verificar que el grupo tiene al menos una fecha registrada en `historial_fechas_contratacion`
2. Refrescar la página (Ctrl + F5)

## Archivos Involucrados

### Archivos PHP modificados:
- ✅ `code/group/seeGroup.php`
- ✅ `code/group/getGroup.php`
- ✅ `code/group/addGroup.php`
- ✅ `code/reports/generateExcel.php`

### Archivos PHP nuevos:
- ✅ `code/group/getHistorialFechas.php`
- ✅ `code/group/addFechaContratacion.php`
- ✅ `code/group/editFechaContratacion.php`
- ✅ `code/group/deleteFechaContratacion.php`

### Documentación:
- ✅ `code/group/sql_cambios_grupos.sql`
- ✅ `code/group/README_CAMBIOS_GRUPOS.md`
- ✅ `code/group/INSTALACION_RAPIDA.md` (este archivo)

## Verificación Final

Ejecutar estas consultas para verificar que todo está funcionando:

```sql
-- 1. Verificar tabla existe
SHOW TABLES LIKE 'historial_fechas_contratacion';

-- 2. Ver estructura
DESCRIBE historial_fechas_contratacion;

-- 3. Ver grupos con sus fechas más recientes
SELECT g.id_grupo, g.descripcion_grupo,
       (SELECT fecha_contratacion 
        FROM historial_fechas_contratacion 
        WHERE id_grupo = g.id_grupo 
        ORDER BY fecha_contratacion DESC 
        LIMIT 1) AS fecha_mas_reciente
FROM grupos g;

-- 4. Ver todas las fechas registradas
SELECT g.descripcion_grupo, hfc.fecha_contratacion, hfc.created_at
FROM historial_fechas_contratacion hfc
JOIN grupos g ON hfc.id_grupo = g.id_grupo
ORDER BY hfc.fecha_contratacion DESC;
```

## ¡Listo!

Si todos los pasos se completaron correctamente, el sistema de historial de fechas de contratación está funcionando.

Para más detalles técnicos, consultar el archivo `README_CAMBIOS_GRUPOS.md`.

---

**Versión:** 1.0  
**Fecha:** 18/10/2025  
**Sistema:** SDSYP
