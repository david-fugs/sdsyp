# Nuevos Tipos de Usuario - Sistema SDSYP

## Resumen
Se han agregado dos nuevos tipos de usuario al sistema para permitir acceso filtrado por grupos.

## Tipos de Usuario

### Tipos Existentes
- **1**: ADMIN - Acceso completo a todos los grupos y funcionalidades
- **2**: CPSAM/CV - Acceso a grupos específicos según asignación
- **3**: CONTRATISTA - Acceso según contratista asignado
- **7**: SIN ACCESO - Usuario sin permisos

### Tipos Nuevos (Octubre 2025)
- **4**: TÉCNICO CPSAM
- **5**: TÉCNICO CENTRO VIDA

## Filtrado de Grupos

### TÉCNICO CPSAM (tipo 4)
Puede ver únicamente grupos cuya descripción comience con:
- `CPSAM%`
- `Otro%`
- `Contratista%`

**Ejemplo de grupos visibles:**
- CPSAM Norte
- CPSAM Sur
- Otro Grupo Especial
- Contratista ABC

### TÉCNICO CENTRO VIDA (tipo 5)
Puede ver únicamente grupos cuya descripción comience con:
- `CV%`
- `Otro%`
- `Contratista%`

**Ejemplo de grupos visibles:**
- CV Norte
- CV Centro
- Otro Grupo Especial
- Contratista ABC

## Archivos Modificados

### 1. Gestión de Usuarios
- `code/users/showusers.php` - Agregados casos 4 y 5 al switch de tipos
- `code/users/editusers1.php` - Sin cambios (ya acepta cualquier tipo)

### 2. Librería de Filtros (NUEVO)
- **`code/filtros_grupos.php`** - Funciones reutilizables para filtrado:
  - `getGruposPermitidos($conexion, $tipo_usuario)` - Retorna array de IDs permitidos
  - `getWhereGruposPermitidos($conexion, $tipo_usuario, $alias_tabla)` - Retorna cláusula WHERE
  - `getGruposParaSelect($conexion, $tipo_usuario)` - Retorna grupos filtrados para dropdowns
  - `tieneAccesoGrupo($conexion, $tipo_usuario, $id_grupo)` - Verifica acceso a grupo específico
  - `getTipoUsuarioTexto($tipo_usuario)` - Retorna descripción del tipo

### 3. Módulo Contratista
- `code/contratista/form.php` - Filtro en dropdown de grupos
- `code/contratista/getActivitiesForm.php` - Filtro en consulta de actividades

### 4. Módulo Contratista Individual
- `code/contratistaIndividual/form.php` - Filtro en dropdown de grupos
- `code/contratistaIndividual/getRegistros.php` - Filtro en consulta de registros

### 5. Módulo Centro Vida
- `code/contratistaCentroVida/formCentroVida.php` - Incluye filtros
- `code/contratistaCentroVida/getRegistrosCentroVida.php` - Filtro en consulta
- `code/contratistaCentroVida/formMasivoCentroVida.php` - Filtro en dropdown y consulta

### 6. Módulo Movimientos
- `code/personMovement/seePersonMovement.php` - Filtro en dropdown de grupos
- `code/personMovement/getPersonMovement.php` - Filtro en consulta de movimientos

### 7. Módulo Personas
- `code/persons/seePerson.php` - Filtro en dropdown de grupos
- `code/persons/getPersons.php` - Filtro en consulta de personas

### 8. Módulo Reportes
- `code/reports/seeReports.php` - Incluye filtros
- `code/reports/getReportData.php` - Filtro en consulta de reportes
- `code/reports/generateExcel.php` - Filtro en exportación Excel

## Lógica de Filtrado

### Usuarios con Acceso Completo (tipos 1, 2, 3, 7)
```php
// No se aplica filtro, ven todos los grupos
SELECT * FROM grupos
```

### Usuario TÉCNICO CPSAM (tipo 4)
```php
SELECT g.* FROM grupos g 
WHERE g.descripcion_grupo LIKE 'CPSAM%' 
   OR g.descripcion_grupo LIKE 'Otro%'
   OR g.descripcion_grupo LIKE 'Contratista%'
```

### Usuario TÉCNICO CENTRO VIDA (tipo 5)
```php
SELECT g.* FROM grupos g 
WHERE g.descripcion_grupo LIKE 'CV%' 
   OR g.descripcion_grupo LIKE 'Otro%'
   OR g.descripcion_grupo LIKE 'Contratista%'
```

## Aplicación en Consultas

### Filtrado de Personas
```php
require_once('../filtros_grupos.php');
$tipo_usuario = $_SESSION['tipo_usuario'];
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

SELECT p.* FROM personas p
WHERE p.estado_persona = 1 $where_grupos
```

### Filtrado de Actividades
```php
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

SELECT ra.* FROM registro_actividades ra
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
WHERE 1 $where_grupos
```

## Cómo Crear Usuarios Técnicos

### Opción 1: Desde la Interfaz
1. Acceder a `Usuarios` → `Gestión de Usuarios`
2. Click en "Agregar Usuario"
3. Completar datos
4. En campo "Tipo Usuario" seleccionar:
   - "TÉCNICO CPSAM" (tipo 4), o
   - "TÉCNICO CENTRO VIDA" (tipo 5)
5. Guardar

### Opción 2: SQL Directo
```sql
-- Crear usuario Técnico CPSAM
INSERT INTO usuarios (usuario, nombre, password, tipo_usuario, estado) 
VALUES ('tecnico.cpsam', 'Juan Pérez', MD5('password123'), 4, 1);

-- Crear usuario Técnico Centro Vida
INSERT INTO usuarios (usuario, nombre, password, tipo_usuario, estado) 
VALUES ('tecnico.cv', 'María García', MD5('password123'), 5, 1);
```

## Pruebas Recomendadas

### Test 1: Verificar Filtrado de Grupos
1. Crear usuario tipo 4 (TÉCNICO CPSAM)
2. Iniciar sesión con ese usuario
3. Navegar a cualquier formulario que muestre grupos
4. Verificar que solo aparecen grupos CPSAM%, Otro%, Contratista%

### Test 2: Verificar Filtrado de Personas
1. Con usuario tipo 4, ir a módulo "Personas"
2. Verificar que solo aparecen personas de grupos CPSAM/Otro/Contratista
3. Con usuario tipo 5, repetir
4. Verificar que solo aparecen personas de grupos CV/Otro/Contratista

### Test 3: Verificar Exportación Excel
1. Con usuario tipo 4, ir a "Reportes"
2. Exportar Excel
3. Verificar que solo incluye personas de grupos permitidos

### Test 4: Verificar Separación de Datos
1. Crear persona en grupo "CPSAM Norte"
2. Crear persona en grupo "CV Sur"
3. Iniciar sesión como TÉCNICO CPSAM
4. Verificar que solo ve persona de CPSAM Norte
5. Iniciar sesión como TÉCNICO CENTRO VIDA
6. Verificar que solo ve persona de CV Sur

## Compatibilidad

- ✅ Compatible con usuarios existentes (tipos 1, 2, 3, 7)
- ✅ No afecta funcionalidad de administradores
- ✅ No requiere cambios en base de datos
- ✅ Filtros se aplican automáticamente a través de `filtros_grupos.php`

## Soporte

Para problemas o preguntas sobre los nuevos tipos de usuario:
1. Revisar logs del navegador (F12 → Console)
2. Verificar sesión activa: `$_SESSION['tipo_usuario']`
3. Confirmar que `filtros_grupos.php` está incluido en el archivo
4. Validar que grupos tienen nomenclatura correcta (CPSAM%, CV%, Otro%, Contratista%)

---

**Fecha de Implementación:** 18 de Octubre, 2025  
**Versión:** 1.0  
**Autor:** Sistema SDSYP
