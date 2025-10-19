# CORRECCIÓN COMPLETA DE FILTROS AJAX
## Fecha: 18 de Octubre, 2025

## PROBLEMA IDENTIFICADO

Los usuarios tipo 4 (TÉCNICO CPSAM) y tipo 5 (TÉCNICO CENTRO VIDA) podían ver y buscar personas de grupos que no les correspondían a través de las búsquedas AJAX, incluso después de aplicar los filtros en las vistas principales.

### Ejemplo del problema:
- Usuario tipo 4 (TÉCNICO CPSAM) buscaba la cédula **34057604**
- Esta cédula pertenece al grupo **CV DESCENTRALIZADOS** (ID: 1)
- El sistema **SÍ mostraba** los resultados, cuando **NO debería**

## CAUSA RAÍZ

**Faltaba `session_start()` en múltiples archivos AJAX**, lo que impedía que `$_SESSION['tipo_usuario']` se leyera correctamente. Resultado: Los filtros de grupo nunca se aplicaban porque `$tipo_usuario` era siempre `null`.

Adicionalmente, algunos archivos tenían un **filtro legacy** que no excluía a los tipos 4 y 5, causando conflictos.

---

## ARCHIVOS CORREGIDOS

### 1. **code/persons/getPersons.php**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2
- ✅ Modificado filtro legacy en línea 30:
  ```php
  // ANTES:
  if ($tipo_usuario != 1 && $id_grupo_session && $tipo_usuario != 3)
  
  // DESPUÉS:
  if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5]))
  ```
- ✅ Filtro `$where_grupos_filtro` ya aplicado correctamente

**Propósito:** Endpoint AJAX principal para la tabla de personas

---

### 2. **code/persons/getPersonsAjax.php** ⚠️ **ARCHIVO CRÍTICO**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2
- ✅ Agregado `require_once('../filtros_grupos.php');` en línea 4
- ✅ Agregado obtención de `$tipo_usuario` y `$id_grupo_session`
- ✅ Agregado cálculo de `$where_grupos_filtro`
- ✅ Agregado filtro legacy con exclusión de tipos 4 y 5
- ✅ Aplicado `$where_grupos_filtro` a la consulta WHERE

**Propósito:** Endpoint AJAX para búsqueda en tiempo real de personas por cédula/nombre

**ANTES (sin filtros):**
```php
<?php
include("../../conexion.php");

// Construir la cláusula WHERE base
$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona LIKE '%$cedula%'";
}
```

**DESPUÉS (con filtros completos):**
```php
<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

// Construir la cláusula WHERE base
$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona LIKE '%$cedula%'";
}

// ... otros filtros ...

// Filtrar por id_grupo si el tipo_usuario en la sesión es diferente de 1, 3, 4 y 5
if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;
```

---

### 3. **code/buscar_persona.php** ⚠️ **ARCHIVO CRÍTICO**
**Cambios:**
- ✅ Agregado `require_once('filtros_grupos.php');` en línea 4
- ✅ Agregado obtención de `$tipo_usuario` de la sesión
- ✅ Agregado cálculo de `$grupos_permitidos`
- ✅ Agregado `$where_grupo` dinámico en la consulta preparada
- ✅ Filtro solo se aplica a tipos 4 y 5

**Propósito:** Endpoint JSON para búsqueda de persona por cédula (usado en formularios)

**ANTES:**
```php
$stmt = $mysqli->prepare("
    SELECT p.nombres_persona, p.apellidos_persona, ...
    FROM personas p
    ...
    WHERE p.cedula_persona = ?
");
```

**DESPUÉS:**
```php
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$grupos_permitidos = getGruposPermitidos($mysqli, $tipo_usuario);

$where_grupo = "";
if (!empty($grupos_permitidos) && in_array($tipo_usuario, [4, 5])) {
    $ids_grupos = implode(',', $grupos_permitidos);
    $where_grupo = " AND p.id_grupo IN ($ids_grupos)";
}

$stmt = $mysqli->prepare("
    SELECT p.nombres_persona, p.apellidos_persona, ...
    FROM personas p
    ...
    WHERE p.cedula_persona = ? $where_grupo
");
```

---

### 4. **code/personMovement/getPersonMovement.php**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2

**Propósito:** Endpoint AJAX para tabla de movimientos de personas

---

### 5. **code/contratista/getActivitiesForm.php**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2

**Propósito:** Endpoint AJAX para formulario de actividades de contratista

---

### 6. **code/contratistaIndividual/getRegistros.php**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2

**Propósito:** Endpoint AJAX para registros individuales

---

### 7. **code/contratistaCentroVida/getRegistrosCentroVida.php**
**Cambios:**
- ✅ Agregado `session_start()` en línea 2

**Propósito:** Endpoint AJAX para registros de Centro de Vida

---

## RESULTADO ESPERADO

### ✅ Usuario tipo 4 (TÉCNICO CPSAM):
- **PUEDE ver:** Personas de grupos CPSAM, CONTRATISTA y Otro
- **NO PUEDE ver:** Personas de grupos CV (Centro de Vida)
- **Búsqueda por cédula:** Si busca una cédula de CV, no devuelve resultados
- **Búsqueda por nombre:** Si busca un nombre de persona de CV, no aparece
- **Grupos permitidos:** IDs 5, 6, 7, 8, 10, 11, 12, 15

### ✅ Usuario tipo 5 (TÉCNICO CENTRO VIDA):
- **PUEDE ver:** Personas de grupos CV, CONTRATISTA y Otro
- **NO PUEDE ver:** Personas de grupos CPSAM
- **Búsqueda por cédula:** Si busca una cédula de CPSAM, no devuelve resultados
- **Búsqueda por nombre:** Si busca un nombre de persona de CPSAM, no aparece
- **Grupos permitidos:** IDs 1, 2, 3, 4, 12, 15

---

## PRUEBAS REALIZADAS

### Test 1: Búsqueda AJAX de persona CV con usuario CPSAM
```bash
Usuario: tipo_usuario = 4 (TÉCNICO CPSAM)
Cédula buscada: 34057604 (Pertenece a CV DESCENTRALIZADOS, ID grupo: 1)
Resultado: ✅ 0 resultados (correcto - filtrado correctamente)
```

### Test 2: Búsqueda JSON de persona CV con usuario CPSAM
```bash
Usuario: tipo_usuario = 4 (TÉCNICO CPSAM)
Cédula buscada: 34057604 (CV DESCENTRALIZADOS)
Resultado: ✅ 0 resultados (correcto - filtrado correctamente)
```

### Test 3: Búsqueda de persona CPSAM con usuario CPSAM
```bash
Usuario: tipo_usuario = 4 (TÉCNICO CPSAM)
Cédula buscada: 24760989 (Pertenece a CPSAM SAN JOSE, ID grupo: 10)
Resultado: ✅ 1 resultado encontrado (correcto - debe ver sus propios grupos)
```

---

## VALIDACIÓN

Todos los archivos modificados fueron validados con `php -l` y **NO presentan errores de sintaxis**.

```bash
✅ code/persons/getPersons.php - No syntax errors
✅ code/persons/getPersonsAjax.php - No syntax errors
✅ code/buscar_persona.php - No syntax errors
✅ code/personMovement/getPersonMovement.php - No syntax errors
✅ code/contratista/getActivitiesForm.php - No syntax errors
✅ code/contratistaIndividual/getRegistros.php - No syntax errors
✅ code/contratistaCentroVida/getRegistrosCentroVida.php - No syntax errors
```

---

## INSTRUCCIONES DE PRUEBA

1. **Cerrar sesión completamente** del sistema
2. **Iniciar sesión** con un usuario tipo 4 (TÉCNICO CPSAM)
3. **Ir a la sección Personas**
4. **Probar búsqueda por cédula:**
   - Buscar cédula **34057604** (CV) → Debería mostrar **0 resultados**
   - Buscar cédula **24760989** (CPSAM) → Debería mostrar **1 resultado**
5. **Probar búsqueda por nombre:**
   - Buscar nombres de personas de CV → No deberían aparecer
   - Buscar nombres de personas de CPSAM → Deberían aparecer
6. **Verificar tabla principal:**
   - Solo deben aparecer personas de grupos: CPSAM + CONTRATISTA + Otro
   - NO deben aparecer personas de grupos CV

---

## NOTAS IMPORTANTES

⚠️ **session_start() es crítico:** Sin esta función, `$_SESSION['tipo_usuario']` es `null` y los filtros no funcionan.

⚠️ **Filtro legacy:** La condición en línea 30 de archivos similares debe **excluir tipos 4 y 5** para evitar restricciones adicionales no deseadas.

⚠️ **Prepared statements:** En `buscar_persona.php`, el `$where_grupo` no puede ser un parámetro preparado porque es parte de la estructura SQL, por eso se construye antes del prepare().

---

## RESUMEN TÉCNICO

| Archivo | session_start() | filtros_grupos.php | Filtro aplicado | Filtro legacy corregido |
|---------|----------------|-------------------|----------------|------------------------|
| getPersons.php | ✅ Agregado | ✅ Ya incluido | ✅ Ya aplicado | ✅ Corregido |
| getPersonsAjax.php | ✅ Agregado | ✅ Agregado | ✅ Agregado | ✅ Agregado |
| buscar_persona.php | ✅ Ya existe | ✅ Agregado | ✅ Agregado | N/A |
| getPersonMovement.php | ✅ Agregado | ✅ Ya incluido | ✅ Ya aplicado | N/A |
| getActivitiesForm.php | ✅ Agregado | ✅ Ya incluido | ✅ Ya aplicado | N/A |
| getRegistros.php | ✅ Agregado | ✅ Ya incluido | ✅ Ya aplicado | N/A |
| getRegistrosCentroVida.php | ✅ Agregado | ✅ Ya incluido | ✅ Ya aplicado | N/A |

**Total de archivos modificados:** 7 archivos  
**Total de líneas agregadas:** ~50 líneas  
**Total de tests exitosos:** 3/3 ✅
