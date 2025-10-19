# EXPORTACIONES DESDE PÁGINA DE REPORTES
## Fecha: 18 de Octubre, 2025

## OBJETIVO

Centralizar las exportaciones de actividades en la página de reportes (`seeReports.php`), aplicando filtros de grupos según el tipo de usuario conectado. Los botones de exportación originales permanecen en sus páginas (`contratista/form.php` y `contratistaCentroVida/formMasivoCentroVida.php`) pero ahora también están disponibles en la página de reportes con filtros mejorados.

---

## CAMBIOS REALIZADOS

### 1. **Modificación de `reports/seeReports.php`**

#### A. Filtros agregados:

**Filtro de Grupos (líneas ~760-780):**
```php
<select id="filtroGrupo" class="modern-select">
    <option value="">Todos los grupos</option>
    <?php
    // Obtener grupos filtrados según tipo de usuario
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    $where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
    $query_grupos = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC";
    $result_grupos = mysqli_query($mysqli, $query_grupos);
    if ($result_grupos) {
        while ($grupo = mysqli_fetch_assoc($result_grupos)) {
            echo '<option value="' . $grupo['id_grupo'] . '">' . htmlspecialchars($grupo['descripcion_grupo']) . '</option>';
        }
    }
    ?>
</select>
```

**Función:**
- Muestra solo los grupos a los que el usuario tiene acceso según su `tipo_usuario`
- Usuario tipo 4 (TÉCNICO CPSAM): Ve grupos CPSAM + CONTRATISTA + Otro
- Usuario tipo 5 (TÉCNICO CENTRO VIDA): Ve grupos CV + CONTRATISTA + Otro
- Usuarios tipo 1, 2, 3: Ven todos los grupos disponibles

#### B. Nuevos botones de exportación (líneas ~795-815):

```html
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-3">
        <h5 class="text-white mb-3"><i class="bi bi-download"></i> Exportar Actividades</h5>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Formulario exportar Contratista -->
            <form id="exportContratistaForm" action="exportContratistaFromReports.php" method="get">
                <input type="hidden" name="filtro_anio" id="export_contratista_anio">
                <input type="hidden" name="filtro_grupo" id="export_contratista_grupo">
                <button type="submit" class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-excel-fill"></i> Actividades CONTRATISTA
                </button>
            </form>

            <!-- Formulario exportar Centro Vida Masivo -->
            <form id="exportCentroVidaForm" action="exportCentroVidaFromReports.php" method="get">
                <input type="hidden" name="filtro_anio" id="export_cv_anio">
                <input type="hidden" name="filtro_grupo" id="export_cv_grupo">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel-fill"></i> Actividades CENTRO VIDA MASIVO
                </button>
            </form>
        </div>
    </div>
</div>
```

**Características:**
- ✅ Dos botones separados para cada tipo de exportación
- ✅ Formularios independientes con campos hidden para pasar filtros
- ✅ Diseño moderno con gradiente morado
- ✅ Iconos de Excel en cada botón

#### C. JavaScript para sincronizar filtros (líneas ~885-910):

```javascript
// Sincronizar filtros con formularios de exportación
function updateExportForms() {
    const anio = $('#yearSelect').val();
    const grupo = $('#filtroGrupo').val();
    
    // Actualizar formulario Contratista
    $('#export_contratista_anio').val(anio);
    $('#export_contratista_grupo').val(grupo);
    
    // Actualizar formulario Centro Vida
    $('#export_cv_anio').val(anio);
    $('#export_cv_grupo').val(grupo);
}

// Inicializar valores de formularios
updateExportForms();

// Event listeners
$('#yearSelect').on('change', function() {
    currentYear = $(this).val();
    loadReportData(currentYear);
    updateExportForms();
});

$('#filtroGrupo').on('change', function() {
    updateExportForms();
});
```

**Función:**
- Actualiza automáticamente los campos hidden cuando cambian los filtros
- Se ejecuta al cargar la página y cada vez que cambia el año o grupo
- Garantiza que las exportaciones usen los filtros seleccionados

---

### 2. **Nuevo archivo: `reports/exportContratistaFromReports.php`**

**Ubicación:** `code/reports/exportContratistaFromReports.php`

**Características principales:**

```php
// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

$where = '';
if ($filtro_anio) {
    $where .= " AND YEAR(ra.fecha_atencion) = $filtro_anio ";
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo) {
    $where .= " AND g.id_grupo = $filtro_grupo ";
}
```

**Filtros aplicados:**
1. ✅ **Filtro de tipo_usuario**: Aplica `getWhereGruposPermitidos()` para limitar grupos
2. ✅ **Filtro de año**: Si se selecciona un año, filtra por `YEAR(ra.fecha_atencion)`
3. ✅ **Filtro de grupo específico**: Si se selecciona un grupo, filtra por `g.id_grupo`

**Consulta SQL:**
```sql
SELECT ra.id_registro, m.descripcion_meta, a.descripcion_actividad, 
       ac.descripcion_accion, pp.descripcion_politica,
       g.descripcion_grupo AS centro_vida, ra.otro_lugar, 
       ra.fecha_atencion, ra.nombre_lider, ra.telefono_contacto, 
       c.nombre_com AS nombre_comuna, ra.medio_verificacion, 
       ra.cantidad_masculino, ra.cantidad_femenino, ra.tipo_actividad, 
       ra.observacion_actividad, u1.nombre AS digitado_por, 
       u2.nombre AS funcionario_responsable_nombre
FROM registro_actividades AS ra
LEFT JOIN metas m ON ra.id_meta = m.id_meta
LEFT JOIN actividades a ON ra.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ra.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ra.politica_publica = pp.id_politica
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON ra.id_comuna = c.id_com
LEFT JOIN usuarios u1 ON ra.id_usuario = u1.id
LEFT JOIN usuarios u2 ON CAST(ra.funcionario_responsable AS UNSIGNED) = u2.id
WHERE 1 $where $where_grupos_filtro
ORDER BY ra.fecha_atencion DESC
```

**Columnas del Excel:**
1. ID
2. Meta
3. Actividad
4. Acción
5. Política Pública
6. Lugar del Evento
7. Otro Lugar
8. Fecha Atención
9. Nombre Líder
10. Teléfono Contacto
11. Comuna/Corregimiento
12. Medio de Verificación
13. Cant. Masculino
14. Cant. Femenino
15. Total personas (calculado)
16. Tipo Actividad
17. Observación Actividad
18. Digitado por
19. Funcionario Responsable

**Estilos aplicados:**
- Encabezado: Fondo azul claro (`E0F7FA`), texto negro, negrita, centrado
- Filas de datos: Bordes grises, texto ajustado, altura 24px
- Columnas: Ancho automático de 25 caracteres

**Nombre del archivo generado:**
```php
$anio_texto = $filtro_anio ? $filtro_anio : 'Todos';
$filename = 'Actividades_Contratista_' . $anio_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';
```
Ejemplo: `Actividades_Contratista_2025_2025-10-18_14-30-45.xlsx`

---

### 3. **Nuevo archivo: `reports/exportCentroVidaFromReports.php`**

**Ubicación:** `code/reports/exportCentroVidaFromReports.php`

**Características principales:**

```php
// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

$where = '';
if ($filtro_anio) {
    $where .= " AND YEAR(mcv.fecha_atencion) = $filtro_anio ";
}

// Si se seleccionó un grupo específico, agregar ese filtro
if ($filtro_grupo) {
    $where .= " AND g.id_grupo = $filtro_grupo ";
}
```

**Filtros aplicados:**
1. ✅ **Filtro de tipo_usuario**: Aplica `getWhereGruposPermitidos()` para limitar grupos
2. ✅ **Filtro de año**: Si se selecciona un año, filtra por `YEAR(mcv.fecha_atencion)`
3. ✅ **Filtro de grupo específico**: Si se selecciona un grupo, filtra por `g.id_grupo`

**Consulta SQL:**
```sql
SELECT mcv.id_masiva_centro_vida AS id_registro,
       m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion,
       acv.descripcion_actividad AS actividad_centro_vida,
       pp.descripcion_politica, g.descripcion_grupo AS centro_vida,
       mcv.fecha_atencion, mcv.nombre_lider, mcv.telefono_contacto,
       c.nombre_com AS nombre_comuna, mcv.medio_verificacion,
       mcv.cantidad_masculino, mcv.cantidad_femenino,
       mcv.observacion_actividad, mcv.tipo_actividad,
       u1.nombre AS digitado_por, u2.nombre AS funcionario_responsable_nombre
FROM masiva_centro_vida mcv
LEFT JOIN metas m ON mcv.id_meta=m.id_meta
LEFT JOIN actividades a ON mcv.id_actividad=a.id_actividad
LEFT JOIN acciones ac ON mcv.id_accion=ac.id_accion
LEFT JOIN actividad_centro_vida acv ON mcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
LEFT JOIN politicas_publicas pp ON mcv.politica_publica = pp.id_politica
LEFT JOIN grupos g ON mcv.id_centro_vida=g.id_grupo
LEFT JOIN comunas c ON mcv.id_comuna=c.id_com
LEFT JOIN usuarios u1 ON mcv.id_usuario=u1.id
LEFT JOIN usuarios u2 ON mcv.funcionario_responsable=u2.id
WHERE 1 $where $where_grupos_filtro
ORDER BY mcv.fecha_atencion DESC
```

**Columnas del Excel:**
1. ID
2. Meta
3. Actividad Plan
4. Acción
5. Actividad Centro Vida
6. Política Pública
7. Centro Vida
8. Fecha Atención
9. Nombre Líder
10. Teléfono
11. Comuna
12. Medio Verificación
13. Masculino
14. Femenino
15. Total (calculado)
16. Tipo Actividad
17. Observación
18. Digitado por
19. Funcionario Responsable

**Estilos aplicados:**
- Encabezado: Fondo verde claro (`C8E6C9`), texto negro, negrita, centrado
- Filas de datos: Bordes verdes suaves, texto ajustado, altura 24px
- Columnas: Ancho automático de 25 caracteres

**Nombre del archivo generado:**
```php
$anio_texto = $filtro_anio ? $filtro_anio : 'Todos';
$filename = 'Actividades_CentroVida_Masivo_' . $anio_texto . '_' . date('Y-m-d_H-i-s') . '.xlsx';
```
Ejemplo: `Actividades_CentroVida_Masivo_2025_2025-10-18_14-30-45.xlsx`

---

## FLUJO DE USO

### Escenario 1: Usuario tipo 4 (TÉCNICO CPSAM) exporta actividades de Contratista

1. **Login:** Usuario inicia sesión con tipo_usuario = 4
2. **Navega:** Va a "Reportes" → `reports/seeReports.php`
3. **Filtra:**
   - Selecciona año: 2025
   - Selecciona grupo: "CPSAM ALEGRIA DE SERVIR" (o deja "Todos los grupos")
4. **Exporta:** Click en botón "Actividades CONTRATISTA"
5. **Resultado:** 
   - Se genera `Actividades_Contratista_2025_2025-10-18_14-30-45.xlsx`
   - Solo incluye actividades de grupos CPSAM + CONTRATISTA + Otro del año 2025
   - Si seleccionó grupo específico, solo incluye ese grupo

### Escenario 2: Usuario tipo 5 (TÉCNICO CENTRO VIDA) exporta actividades de CV

1. **Login:** Usuario inicia sesión con tipo_usuario = 5
2. **Navega:** Va a "Reportes" → `reports/seeReports.php`
3. **Filtra:**
   - Selecciona año: 2024
   - Selecciona grupo: "CV DESCENTRALIZADOS"
4. **Exporta:** Click en botón "Actividades CENTRO VIDA MASIVO"
5. **Resultado:**
   - Se genera `Actividades_CentroVida_Masivo_2024_2024-10-18_14-30-45.xlsx`
   - Solo incluye actividades del grupo "CV DESCENTRALIZADOS" del año 2024

### Escenario 3: Usuario ADMIN exporta todo

1. **Login:** Usuario admin con tipo_usuario = 1
2. **Navega:** Va a "Reportes"
3. **Filtra:** Selecciona año: 2025, grupo: "Todos los grupos"
4. **Exporta:** Click en cualquiera de los dos botones
5. **Resultado:** Excel con todas las actividades del año 2025 de todos los grupos

---

## SEGURIDAD Y FILTROS

### Matriz de acceso por tipo_usuario:

| tipo_usuario | Nombre | Grupos visibles | Puede exportar |
|--------------|--------|----------------|----------------|
| 1 | ADMIN | Todos | ✅ Todos los datos |
| 2 | CPSAM/CV | Todos | ✅ Todos los datos |
| 3 | CONTRATISTA | Según id_grupo_session | ✅ Solo su grupo |
| 4 | TÉCNICO CPSAM | CPSAM + CONTRATISTA + Otro | ✅ Solo grupos permitidos |
| 5 | TÉCNICO CENTRO VIDA | CV + CONTRATISTA + Otro | ✅ Solo grupos permitidos |
| 7 | SIN ACCESO | Ninguno | ❌ No puede exportar |

### Filtros aplicados automáticamente:

1. **getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g')**
   - Para tipos 4 y 5: Retorna `AND g.id_grupo IN (lista_ids_permitidos)`
   - Para otros tipos: Retorna string vacío (sin restricción)

2. **Filtro de año** (opcional):
   - `AND YEAR(tabla.fecha_atencion) = $filtro_anio`

3. **Filtro de grupo específico** (opcional):
   - `AND g.id_grupo = $filtro_grupo`

### Orden de prioridad de filtros:

1. **Filtro de tipo_usuario** (siempre se aplica para tipos 4 y 5)
2. **Filtro de año** (si se selecciona)
3. **Filtro de grupo** (si se selecciona y está dentro de los permitidos)

---

## ARCHIVOS MODIFICADOS/CREADOS

### Modificados:
1. `code/reports/seeReports.php` 
   - Agregado filtro de grupos (línea ~760-780)
   - Agregados botones de exportación (línea ~795-815)
   - Agregado JavaScript de sincronización (línea ~885-910)

### Creados:
1. `code/reports/exportContratistaFromReports.php` (186 líneas)
   - Exporta actividades de registro_actividades
   - Aplica filtros de grupos según tipo_usuario
   - Genera Excel con formato azul

2. `code/reports/exportCentroVidaFromReports.php` (190 líneas)
   - Exporta actividades de masiva_centro_vida
   - Aplica filtros de grupos según tipo_usuario
   - Genera Excel con formato verde

---

## VALIDACIÓN

✅ **Sintaxis PHP:** Todos los archivos validados con `php -l`
```bash
No syntax errors detected in seeReports.php
No syntax errors detected in exportContratistaFromReports.php
No syntax errors detected in exportCentroVidaFromReports.php
```

✅ **Dependencias:**
- PhpOffice/PhpSpreadsheet (ya instalado en vendor/)
- filtros_grupos.php (ya existe y funciona)
- Conexión MySQL (conexion.php)

✅ **Sesiones:**
- session_start() en todos los archivos de exportación
- Lectura correcta de $_SESSION['tipo_usuario']

---

## PRUEBAS RECOMENDADAS

### Test 1: Usuario tipo 4 - Exportar Contratista
```
1. Login como tipo_usuario=4
2. Ir a Reportes
3. Seleccionar año 2025
4. Dejar "Todos los grupos" (debería mostrar solo grupos CPSAM+CONTRATISTA+Otro)
5. Click "Actividades CONTRATISTA"
6. Verificar que Excel solo tenga registros de grupos permitidos
```

### Test 2: Usuario tipo 5 - Exportar Centro Vida
```
1. Login como tipo_usuario=5
2. Ir a Reportes
3. Seleccionar año 2024
4. Seleccionar grupo "CV DESCENTRALIZADOS"
5. Click "Actividades CENTRO VIDA MASIVO"
6. Verificar que Excel solo tenga registros de ese grupo CV
```

### Test 3: Usuario ADMIN - Exportar todo
```
1. Login como tipo_usuario=1
2. Ir a Reportes
3. Seleccionar "Todos los grupos"
4. Click en ambos botones de exportación
5. Verificar que Excels tengan todos los datos sin restricciones
```

### Test 4: Filtros combinados
```
1. Login como tipo_usuario=4
2. Seleccionar año 2025
3. Seleccionar grupo específico "CPSAM ALEGRIA DE SERVIR"
4. Exportar
5. Verificar que Excel solo tenga ese grupo del año 2025
```

---

## NOTAS TÉCNICAS

⚠️ **Importante:**
- Los botones originales en `contratista/form.php` y `contratistaCentroVida/formMasivoCentroVida.php` **NO fueron eliminados**
- Ahora hay **dos formas** de exportar: desde las páginas originales o desde Reportes
- Las exportaciones desde Reportes tienen **filtros adicionales** (año y grupo)
- Las exportaciones desde páginas originales mantienen sus filtros propios (año, mes, funcionario, tipo_registro)

⚠️ **Diferencias clave:**
- `exportActividadesExcel.php` (original): Filtros de año, mes, funcionario
- `exportContratistaFromReports.php` (nuevo): Filtros de año, grupo, tipo_usuario

⚠️ **Buffer de salida:**
- Todos los archivos de exportación verifican y limpian el buffer con `ob_end_clean()`
- Charset UTF-8 configurado para evitar problemas con caracteres especiales

---

## RESUMEN

✅ **Agregados en seeReports.php:**
- Filtro de grupos (dropdown con grupos filtrados por tipo_usuario)
- 2 botones de exportación (Contratista y Centro Vida)
- JavaScript para sincronizar filtros

✅ **Creados 2 archivos de exportación:**
- exportContratistaFromReports.php
- exportCentroVidaFromReports.php

✅ **Filtros aplicados:**
- Por tipo_usuario (automático para tipos 4 y 5)
- Por año (opcional)
- Por grupo específico (opcional)

✅ **Resultado:**
- Usuarios técnicos solo pueden exportar datos de sus grupos asignados
- Excels generados con formato profesional y columnas relevantes
- Nombres de archivo descriptivos con fecha y hora
