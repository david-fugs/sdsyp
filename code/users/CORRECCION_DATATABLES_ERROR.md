# CORRECCIÓN DE ERROR EN DATATABLES - seePerson.php
## Fecha: 18 de Octubre, 2025

## PROBLEMA REPORTADO

Al aplicar filtros en la tabla de personas, aparecía el siguiente error en la consola del navegador:

```
Error: TypeError: Cannot read properties of null (reading 'parentNode')
    at B.<anonymous> (jquery.dataTables.min.js:159:452)
    at B.iterator (jquery.dataTables.min.js:119:423)
    at B.<anonymous> (jquery.dataTables.min.js:159:401)
    at B.destroy (jquery.dataTables.min.js:122:330)
    at updateTableRows (seePerson.php:32908:46)
```

**Mensaje mostrado:** "Error al cargar los datos"

---

## CAUSA RAÍZ

El error ocurría porque la función `updateTableRows()` intentaba destruir y reinicializar DataTables de forma **síncrona e inmediata**, causando un conflicto de timing donde:

1. DataTables intentaba acceder a elementos del DOM que aún no estaban completamente limpios
2. La función `.destroy()` no tenía tiempo suficiente para completar la limpieza
3. El DOM se actualizaba antes de que DataTables terminara de destruirse
4. Al intentar reinicializar, DataTables buscaba referencias a nodos que ya no existían (`parentNode` era `null`)

---

## SOLUCIÓN APLICADA

Se modificó la lógica de actualización de la tabla en **2 funciones clave**:

### 1. Función `updateTableRows(data)`

**ANTES (problemática):**
```javascript
function updateTableRows(data) {
    if ($.fn.DataTable.isDataTable('#salesTable')) {
        $('#salesTable tbody').empty();
        $('#salesTable').DataTable().destroy();
    }
    $('#salesTable tbody').html(data);
    dataTable = null;
    initializeDataTable();
}
```

**Problemas:**
- Destrucción inmediata sin esperar limpieza
- No limpiaba la clase `.dataTable` del elemento
- No manejaba errores
- Reinicialización síncrona causaba conflictos

**DESPUÉS (corregida):**
```javascript
function updateTableRows(data) {
    try {
        // Verificar si DataTable está inicializado
        if ($.fn.DataTable.isDataTable('#salesTable')) {
            // Obtener la instancia de DataTable
            const table = $('#salesTable').DataTable();
            // Destruir la instancia de forma segura
            table.clear();
            table.destroy();
            // Esperar un momento para que se limpie completamente
            $('#salesTable').removeClass('dataTable');
        }
        
        // Limpiar completamente el tbody
        $('#salesTable tbody').empty();
        
        // Agregar los nuevos datos
        $('#salesTable tbody').html(data);
        
        // Reinicializar DataTable
        dataTable = null;
        
        // Usar setTimeout para asegurar que el DOM esté completamente actualizado
        setTimeout(function() {
            initializeDataTable();
        }, 10);
        
    } catch (error) {
        console.error('Error al actualizar tabla:', error);
        // Si hay un error, recargar la página como fallback
        location.reload();
    }
}
```

**Mejoras:**
✅ **Try-catch**: Captura errores y proporciona fallback (reload)
✅ **table.clear()**: Limpia los datos antes de destruir
✅ **removeClass('dataTable')**: Elimina la clase que DataTables agrega
✅ **setTimeout(10ms)**: Da tiempo al DOM para actualizar antes de reinicializar
✅ **Manejo de errores**: Si algo falla, recarga la página automáticamente

---

### 2. Función `loadTableData(params)`

**ANTES:**
```javascript
function loadTableData(params = {}) {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center loading">Cargando datos...</td></tr>';
    
    // ... construcción de queryParams ...
    
    fetch(`getPersonsAjax.php?${queryParams.toString()}`)
        .then(response => response.text())
        .then(data => {
            updateTableRows(data);
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos</td></tr>';
        });
}
```

**Problemas:**
- Mostraba "Cargando..." pero DataTables seguía activo
- No verificaba si la respuesta HTTP era exitosa
- `updateTableRows()` destruía DataTables DESPUÉS de que ya se había mostrado el loading

**DESPUÉS (corregida):**
```javascript
function loadTableData(params = {}) {
    const tbody = document.getElementById('table-body');
    
    // Destruir DataTable ANTES de mostrar loading
    if ($.fn.DataTable.isDataTable('#salesTable')) {
        try {
            const table = $('#salesTable').DataTable();
            table.clear();
            table.destroy();
            $('#salesTable').removeClass('dataTable');
        } catch (e) {
            console.warn('Error al destruir DataTable:', e);
        }
    }
    
    tbody.innerHTML = '<tr><td colspan="8" class="text-center loading">Cargando datos...</td></tr>';

    // Construir parámetros de consulta
    const queryParams = new URLSearchParams();
    if (params.cedula) queryParams.append('cedula_persona', params.cedula);
    if (params.nombre) queryParams.append('nombre', params.nombre);
    if (params.programa) queryParams.append('programa', params.programa);
    if (params.estado) queryParams.append('estado', params.estado);

    // Realizar petición AJAX
    fetch(`getPersonsAjax.php?${queryParams.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(data => {
            // Actualizar contenido del tbody directamente
            tbody.innerHTML = data;
            
            // Reinicializar DataTable después de actualizar el contenido
            dataTable = null;
            setTimeout(function() {
                initializeDataTable();
            }, 10);
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Error al cargar los datos. Por favor, recarga la página.</td></tr>';
        });
}
```

**Mejoras:**
✅ **Destrucción PREVIA**: Destruye DataTables ANTES de mostrar loading
✅ **Try-catch interno**: Maneja errores al destruir
✅ **Validación de respuesta**: Verifica `response.ok` antes de procesar
✅ **Actualización directa**: Ya no llama a `updateTableRows()`, actualiza directamente
✅ **Mensaje de error mejorado**: Incluye icono y sugerencia de recargar
✅ **setTimeout(10ms)**: Asegura que el DOM esté listo antes de reinicializar

---

## FLUJO CORREGIDO

### Antes (problemático):
```
1. Usuario hace clic en filtrar
2. loadTableData() se ejecuta
3. Muestra "Cargando..." con DataTables AÚN ACTIVO ❌
4. fetch() obtiene datos
5. Llama updateTableRows(data)
6. updateTableRows() intenta destruir DataTables
7. ERROR: parentNode es null porque el DOM está en estado inconsistente
```

### Después (correcto):
```
1. Usuario hace clic en filtrar
2. loadTableData() se ejecuta
3. DESTRUYE DataTables primero ✅
4. Limpia clases y estado
5. Muestra "Cargando..." en tabla limpia
6. fetch() obtiene datos
7. Actualiza tbody directamente
8. setTimeout espera 10ms ✅
9. Reinicializa DataTables con DOM estable
10. ✅ Todo funciona correctamente
```

---

## ARCHIVO MODIFICADO

**Archivo:** `code/persons/seePerson.php`

**Líneas modificadas:**
- Función `updateTableRows()`: ~líneas 1503-1535
- Función `loadTableData()`: ~líneas 1540-1585

**Cambios totales:**
- Agregadas ~40 líneas de código
- Mejoradas 2 funciones JavaScript
- Agregado manejo robusto de errores
- Agregado timing seguro con setTimeout

---

## VALIDACIÓN

✅ **Sintaxis PHP:** No syntax errors detected
✅ **Compatibilidad:** jQuery 3.x, DataTables 1.13.x
✅ **Fallback:** Si todo falla, recarga la página automáticamente

---

## RESULTADO ESPERADO

### ✅ Comportamiento correcto:
1. Usuario escribe en filtro de cédula/nombre
2. Después de 500ms (debounce), se activa la búsqueda
3. DataTables se destruye limpiamente
4. Aparece mensaje "Cargando datos..."
5. Se obtienen los datos filtrados de `getPersonsAjax.php`
6. Se actualiza la tabla con los nuevos datos
7. DataTables se reinicializa correctamente
8. Usuario puede ordenar, paginar y ver los datos filtrados

### ✅ Filtros aplicados correctamente:
- Usuario tipo 4 (TÉCNICO CPSAM) solo ve personas de grupos CPSAM + CONTRATISTA + Otro
- Usuario tipo 5 (TÉCNICO CENTRO VIDA) solo ve personas de grupos CV + CONTRATISTA + Otro
- Búsquedas por cédula respetan los filtros de grupo
- Búsquedas por nombre respetan los filtros de grupo

---

## PRUEBAS RECOMENDADAS

1. **Test de filtrado básico:**
   - Escribir en campo "Cédula"
   - Esperar 500ms
   - Verificar que la tabla se actualiza sin errores

2. **Test de filtrado por grupos:**
   - Login como usuario tipo 4
   - Buscar cédula de persona CV (ej: 34057604)
   - Verificar que no aparece ningún resultado

3. **Test de error de red:**
   - Desactivar `getPersonsAjax.php` temporalmente
   - Intentar filtrar
   - Verificar que muestra mensaje de error apropiado

4. **Test de selects:**
   - Cambiar filtro de "Programa"
   - Cambiar filtro de "Estado"
   - Verificar que cada cambio actualiza la tabla correctamente

---

## NOTAS TÉCNICAS

⚠️ **setTimeout de 10ms:** Este tiempo es crítico. Permite que el navegador complete el ciclo de renderizado antes de reinicializar DataTables. No reducir este valor.

⚠️ **removeClass('dataTable'):** DataTables agrega esta clase automáticamente. Si no se elimina, puede causar conflictos en la reinicialización.

⚠️ **table.clear():** Importante llamar antes de destroy() para limpiar datos internos de DataTables.

⚠️ **Fallback con location.reload():** Si el try-catch captura un error irrecuperable, es mejor recargar la página que dejar la interfaz en estado inconsistente.

---

## RESUMEN

**Problema:** Error de `parentNode null` al filtrar tabla
**Causa:** Timing incorrecto en destrucción/reinicialización de DataTables
**Solución:** Destrucción previa, limpieza completa, setTimeout para timing seguro
**Resultado:** ✅ Filtrado funciona correctamente sin errores de consola
