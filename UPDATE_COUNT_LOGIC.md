# Actualización de Lógica de Conteo de Personas por Grupo

## Fecha: 2024
## Descripción: Actualización para excluir del conteo personas con movimientos que liberan cupo

### Problema Original:
El sistema contaba todas las personas activas en un grupo sin considerar que algunas ya no ocupan cupo debido a movimientos específicos (evadidos, fallecidos, retirados voluntarios, trasladados).

### Solución Implementada:
Se actualizó la lógica de conteo en múltiples archivos para excluir del conteo a las personas que tienen al menos un movimiento de tipo:
- CPSAM EVADIDO
- CPSAM FALLECIDO  
- CPSAM RETIRADO VOLUNTARIO
- CPSAM TRASLADADO

### Archivos Modificados:

#### 1. `code/persons/checkGroupLimit.php`
- **Cambio:** Actualizada la consulta de conteo para excluir personas con movimientos que liberan cupo
- **Antes:** `SELECT COUNT(*) as total FROM personas WHERE id_grupo = ? AND estado_persona = 1`
- **Después:** Consulta con NOT IN que excluye personas con movimientos específicos

#### 2. `code/personMovement/addPersonMovement.php`
- **Cambio:** Actualizada la validación de límite antes de agregar movimientos de traslado
- **Impacto:** Ahora considera correctamente la capacidad disponible al trasladar personas

#### 3. `code/personMovement/editPersonMovement.php`
- **Cambio:** Agregada validación de límite completa que no existía
- **Nuevo:** Validación que considera si la persona ya estaba en el grupo (para evitar double counting)
- **Impacto:** Previene ediciones que excedan límites de capacidad

### Nueva Consulta de Conteo:
```sql
SELECT COUNT(*) as total 
FROM personas p
WHERE p.id_grupo = ? 
AND p.estado_persona = 1
AND p.cedula_persona NOT IN (
    SELECT DISTINCT mp.cedula_persona 
    FROM movimiento_persona mp
    JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
    WHERE cc.descripcion_condicion IN (
        'CPSAM EVADIDO', 
        'CPSAM FALLECIDO', 
        'CPSAM RETIRADO VOLUNTARIO', 
        'CPSAM TRASLADADO'
    )
)
```

### Beneficios:
1. **Capacidad Real:** Los límites de grupo ahora reflejan la capacidad real disponible
2. **Cupos Liberados:** Las personas con movimientos específicos liberan automáticamente su cupo
3. **Validación Mejorada:** Previene rechazos incorrectos por límites aparentemente alcanzados
4. **Consistencia:** Lógica uniforme en todos los puntos de validación

### Pruebas:
- Se creó `test_count_logic.php` para comparar conteos antes y después
- Validar diferencias entre conteo anterior y nuevo
- Verificar personas excluidas del conteo

### Mantenimiento:
- Si se agregan nuevos tipos de movimientos que liberen cupos, deben añadirse a la lista de exclusiones
- Monitorear que los nombres de condiciones sean exactos (case-sensitive)

### Archivos de Prueba:
- `test_count_logic.php` - Script temporal para verificar la lógica (eliminar después de pruebas)
