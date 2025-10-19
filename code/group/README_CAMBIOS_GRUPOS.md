# DOCUMENTACIÓN DE CAMBIOS - HISTORIAL DE FECHAS DE CONTRATACIÓN DE GRUPOS

## Fecha de Implementación
**18 de Octubre de 2025**

## Resumen de Cambios

Se implementó un sistema completo para gestionar un historial de fechas de contratación de cada grupo, permitiendo:

1. Agregar múltiples fechas de contratación por grupo
2. Ver el historial completo en el modal de edición
3. Editar y eliminar fechas del historial
4. Mostrar la fecha más reciente en la tabla principal
5. Exportar la fecha de último contrato y días activos desde contrato en el Excel de informes

---

## 1. CAMBIOS EN BASE DE DATOS

### Nueva Tabla: `historial_fechas_contratacion`

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

**Ubicación del script SQL:** `code/group/sql_cambios_grupos.sql`

**Instrucciones de instalación:**
1. Abrir phpMyAdmin o MySQL Workbench
2. Seleccionar la base de datos SDSYP
3. Ejecutar el script `sql_cambios_grupos.sql`

---

## 2. ARCHIVOS MODIFICADOS

### 2.1. `code/group/seeGroup.php`

**Cambios realizados:**

1. **Tabla de grupos:** Se agregó una nueva columna "Fecha Contratación"
   - Muestra la fecha de contratación más reciente del grupo
   - Formato: dd/mm/yyyy
   - Si no hay fecha registrada muestra: "Sin registro"

2. **Modal Agregar Grupo:** Se agregó campo de fecha
   ```html
   <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" required>
   ```

3. **Modal Editar Grupo:** Se agregó sección completa de historial
   - Muestra todas las fechas registradas ordenadas de más reciente a más antigua
   - Cada fecha muestra:
     - Fecha en formato legible (ejemplo: 15 de enero de 2025)
     - Fecha y hora de registro
     - Botones para editar y eliminar
   - Formulario para agregar nueva fecha con botón "Agregar"

4. **JavaScript añadido:**
   - `cargarHistorialFechas(idGrupo)`: Carga el historial vía AJAX
   - `mostrarHistorialFechas(fechas, idGrupo)`: Renderiza el HTML del historial
   - Handler para agregar nueva fecha
   - Handler para editar fecha (con SweetAlert2)
   - Handler para eliminar fecha (con confirmación)

5. **DataTables:** Actualizado para 5 columnas (antes 4)

### 2.2. `code/group/getGroup.php`

**Cambios realizados:**

- **Consulta SQL modificada:** Ahora incluye subconsulta para obtener la fecha más reciente
  ```sql
  SELECT g.*, 
    (SELECT hfc.fecha_contratacion 
     FROM historial_fechas_contratacion hfc 
     WHERE hfc.id_grupo = g.id_grupo 
     ORDER BY hfc.fecha_contratacion DESC 
     LIMIT 1) AS fecha_contratacion_reciente
  FROM grupos g 
  ORDER BY g.id_grupo ASC
  ```

- **Renderizado:** Se agregó columna adicional con la fecha formateada
- **colspan:** Actualizado de 4 a 5 en mensaje "no se encontraron registros"

### 2.3. `code/group/addGroup.php`

**Cambios realizados:**

- Captura el campo `fecha_contratacion` del POST
- Obtiene el ID del grupo recién insertado con `$mysqli->insert_id`
- Inserta la fecha en la tabla `historial_fechas_contratacion`
- Manejo de errores actualizado para ambas operaciones

### 2.4. `code/group/editGroup.php`

**Sin cambios estructurales**

- El archivo sigue manejando solo descripción_grupo y limite_personas
- Las fechas de contratación se manejan por separado mediante los archivos AJAX

---

## 3. ARCHIVOS NUEVOS CREADOS

### 3.1. `code/group/getHistorialFechas.php`

**Propósito:** Obtener todas las fechas de contratación de un grupo

**Método:** GET

**Parámetros:**
- `id_grupo` (int): ID del grupo

**Respuesta JSON:**
```json
{
  "success": true,
  "data": [
    {
      "id_fecha_contratacion": 1,
      "id_grupo": 5,
      "fecha_contratacion": "2025-01-15",
      "created_at": "15/01/2025 10:30"
    }
  ]
}
```

### 3.2. `code/group/addFechaContratacion.php`

**Propósito:** Agregar una nueva fecha de contratación

**Método:** POST

**Parámetros:**
- `id_grupo` (int): ID del grupo
- `fecha_contratacion` (string): Fecha en formato YYYY-MM-DD

**Validaciones:**
- Formato de fecha válido
- Campos obligatorios presentes

**Respuesta JSON:**
```json
{
  "success": true,
  "message": "Fecha agregada correctamente"
}
```

### 3.3. `code/group/editFechaContratacion.php`

**Propósito:** Editar una fecha de contratación existente

**Método:** POST

**Parámetros:**
- `id_fecha` (int): ID de la fecha a editar
- `fecha_contratacion` (string): Nueva fecha en formato YYYY-MM-DD

**Respuesta JSON:**
```json
{
  "success": true,
  "message": "Fecha actualizada correctamente"
}
```

### 3.4. `code/group/deleteFechaContratacion.php`

**Propósito:** Eliminar una fecha de contratación

**Método:** POST

**Parámetros:**
- `id_fecha` (int): ID de la fecha a eliminar

**Respuesta JSON:**
```json
{
  "success": true,
  "message": "Fecha eliminada correctamente"
}
```

---

## 4. CAMBIOS EN REPORTES (EXCEL)

### 4.1. `code/reports/generateExcel.php`

**Nuevas columnas agregadas al final del Excel:**

| Posición | Nombre de Columna | Descripción |
|----------|-------------------|-------------|
| 56 | FECHA ÚLTIMO CONTRATO GRUPO | Fecha más reciente de contratación del grupo al que pertenece la persona |
| 57 | DÍAS ACTIVO DESDE CONTRATO | Número de días calculados desde la fecha de último contrato hasta activo_hasta (o fecha actual si sigue activo) |

**Modificaciones en la consulta SQL:**

Se agregó subconsulta para obtener la fecha de contrato:

```sql
-- Fecha más reciente de contratación del grupo
(SELECT hfc.fecha_contratacion
 FROM historial_fechas_contratacion hfc
 WHERE hfc.id_grupo = p.id_grupo
 ORDER BY hfc.fecha_contratacion DESC
 LIMIT 1) AS fecha_ultimo_contrato_grupo,
```

**Lógica de cálculo de días:**

```php
// Calcular días activos desde el contrato
if (!empty($row['fecha_ultimo_contrato_grupo'])) {
    $fecha_contrato = new DateTime($row['fecha_ultimo_contrato_grupo']);
    
    // Determinar fecha final
    if ($estado_actual == 'FALLECIDO' || $estado_actual == 'EVADIDO' || $estado_actual == 'RETIRADO VOLUNTARIO') {
        if (!empty($row['fecha_ultimo_movimiento'])) {
            $fecha_final = new DateTime($row['fecha_ultimo_movimiento']);
        }
    }
    
    if ($fecha_final === null) {
        $fecha_final = new DateTime(); // hoy
    }
    
    $interval_contrato = $fecha_contrato->diff($fecha_final);
    $dias_activo_desde_contrato = $interval_contrato->days;
}
```

**Casos especiales:**
- Si no hay fecha de contrato registrada: columnas quedan vacías
- Si la persona está FALLECIDA/EVADIDA/RETIRADA: se calcula hasta la fecha del último movimiento
- Si la persona está ACTIVA: se calcula hasta hoy

---

## 5. FLUJO DE TRABAJO DEL USUARIO

### 5.1. Agregar un nuevo grupo

1. Ir a `Grupos` desde el menú principal
2. Clic en botón "Agregar Grupo"
3. Llenar los campos:
   - Descripción del Grupo
   - Límite de Personas
   - **Fecha de Contratación** (nuevo campo)
4. Clic en "Guardar"
5. El grupo se crea y la fecha se registra automáticamente en el historial

### 5.2. Editar un grupo existente

1. Clic en el botón de editar (ícono de lápiz) en la tabla
2. Se abre el modal con:
   - Campos editables: Descripción y Límite de Personas
   - **Sección de Historial de Fechas:** Muestra todas las fechas registradas
3. Opciones disponibles:
   - Ver todas las fechas anteriores
   - Editar una fecha existente (clic en ícono lápiz)
   - Eliminar una fecha (clic en ícono basura)
   - Agregar nueva fecha (formulario en la parte inferior)
4. Los cambios en fechas se guardan inmediatamente vía AJAX
5. Clic en "Guardar Cambios" para actualizar descripción y límite

### 5.3. Exportar informe con fechas de contrato

1. Ir a `Informes y Reportes`
2. Seleccionar el año deseado
3. Clic en "Exportar Excel"
4. El archivo descargado incluirá las columnas 56 y 57 con:
   - Fecha de último contrato del grupo de cada persona
   - Días activos calculados desde esa fecha

---

## 6. VALIDACIONES IMPLEMENTADAS

### Frontend (JavaScript)
- Campo fecha requerido al agregar grupo
- Fecha no puede estar vacía al agregar nueva fecha en el historial
- Confirmación antes de eliminar una fecha

### Backend (PHP)
- Validación de formato de fecha (YYYY-MM-DD)
- Validación de datos obligatorios en todos los endpoints
- Prepared statements para prevenir SQL injection
- Verificación de método HTTP (GET/POST)

---

## 7. COMPATIBILIDAD Y RETROCOMPATIBILIDAD

### Base de datos
- La tabla `historial_fechas_contratacion` es nueva y no afecta tablas existentes
- La restricción `ON DELETE CASCADE` asegura que al eliminar un grupo se eliminen sus fechas

### Grupos existentes
- Los grupos creados antes de esta actualización aparecerán con "Sin registro" en la columna de fecha
- Al editar un grupo antiguo, se puede agregar su primera fecha de contratación
- El Excel mostrará columnas vacías para grupos sin fecha de contrato

### Reportes
- El Excel mantiene todas las columnas anteriores
- Las dos nuevas columnas se agregaron al final (posiciones 56-57)
- No afecta la estructura de columnas existentes

---

## 8. TESTING Y VERIFICACIÓN

### Pasos para probar la implementación:

1. **Ejecutar SQL:**
   ```bash
   # En phpMyAdmin o línea de comandos MySQL
   source code/group/sql_cambios_grupos.sql
   ```

2. **Verificar tabla creada:**
   ```sql
   SHOW TABLES LIKE 'historial_fechas_contratacion';
   DESCRIBE historial_fechas_contratacion;
   ```

3. **Probar agregar grupo:**
   - Crear un nuevo grupo con fecha de contratación
   - Verificar que aparezca en la tabla
   - Verificar que la fecha se muestre correctamente

4. **Probar historial:**
   - Editar un grupo existente
   - Verificar que se cargue el historial
   - Agregar una nueva fecha
   - Editar una fecha existente
   - Eliminar una fecha

5. **Probar Excel:**
   - Ir a Informes y Reportes
   - Exportar Excel del año actual
   - Verificar que las columnas 56 y 57 tengan datos
   - Verificar cálculo de días

### Consultas SQL útiles para verificación:

```sql
-- Ver todas las fechas de contratación
SELECT g.descripcion_grupo, hfc.fecha_contratacion, hfc.created_at
FROM historial_fechas_contratacion hfc
JOIN grupos g ON hfc.id_grupo = g.id_grupo
ORDER BY hfc.fecha_contratacion DESC;

-- Ver fecha más reciente por grupo
SELECT g.id_grupo, g.descripcion_grupo,
       (SELECT fecha_contratacion 
        FROM historial_fechas_contratacion 
        WHERE id_grupo = g.id_grupo 
        ORDER BY fecha_contratacion DESC 
        LIMIT 1) AS fecha_mas_reciente
FROM grupos g;

-- Verificar personas con fecha de contrato
SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona,
       g.descripcion_grupo,
       (SELECT fecha_contratacion 
        FROM historial_fechas_contratacion 
        WHERE id_grupo = p.id_grupo 
        ORDER BY fecha_contratacion DESC 
        LIMIT 1) AS fecha_ultimo_contrato
FROM personas p
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
WHERE p.estado_persona = 1
LIMIT 10;
```

---

## 9. SOLUCIÓN DE PROBLEMAS

### Problema: No se carga el historial en el modal
**Solución:** Verificar en la consola del navegador (F12) si hay errores AJAX. Verificar que el archivo `getHistorialFechas.php` tenga permisos de lectura.

### Problema: Error al agregar fecha
**Solución:** Verificar que la tabla `historial_fechas_contratacion` existe y tiene la estructura correcta.

### Problema: El Excel no muestra las columnas nuevas
**Solución:** Verificar que se ejecutó el script SQL. Limpiar caché del navegador. Verificar que no haya errores en la consola de PHP.

### Problema: "Sin registro" en todos los grupos
**Solución:** Los grupos creados antes de esta actualización necesitan que se les agregue manualmente su primera fecha. Editar cada grupo y agregar la fecha correspondiente.

---

## 10. ARCHIVOS DEL PROYECTO

### Archivos modificados:
- `code/group/seeGroup.php` - Interfaz principal de grupos
- `code/group/getGroup.php` - Obtención de datos de grupos
- `code/group/addGroup.php` - Creación de grupos
- `code/reports/generateExcel.php` - Generación de Excel

### Archivos nuevos:
- `code/group/sql_cambios_grupos.sql` - Script SQL
- `code/group/getHistorialFechas.php` - API: obtener historial
- `code/group/addFechaContratacion.php` - API: agregar fecha
- `code/group/editFechaContratacion.php` - API: editar fecha
- `code/group/deleteFechaContratacion.php` - API: eliminar fecha
- `code/group/README_CAMBIOS_GRUPOS.md` - Esta documentación

---

## 11. NOTAS ADICIONALES

- La fecha de contratación es un campo obligatorio al crear un nuevo grupo
- Se pueden tener múltiples fechas de contratación para un mismo grupo
- El historial mantiene registro de cuándo se agregó cada fecha (created_at)
- Al eliminar un grupo, todas sus fechas de contratación se eliminan automáticamente (CASCADE)
- El cálculo de "Días Activo Desde Contrato" en el Excel considera el estado actual de la persona

---

## 12. CONTACTO Y SOPORTE

Para reportar problemas o solicitar mejoras en este módulo, contactar al equipo de desarrollo.

**Versión:** 1.0  
**Fecha:** 18 de Octubre de 2025  
**Desarrollado para:** Sistema SDSYP
