# Estados de Personas en el Sistema CPSAM

## Fecha: 2024
## Descripción: Nueva columna de estado basada en movimientos de personas

### Estados Implementados:

#### 1. **CPSAM ACTIVO** 
- **Color:** Verde (text-success)
- **Condición:** La persona NO tiene movimientos de tipo: EVADIDO, FALLECIDO, RETIRADO VOLUNTARIO o TRASLADADO
- **Descripción:** Persona activa en el programa

#### 2. **CPSAM EVADIDO**
- **Color:** Amarillo/Naranja (text-warning)
- **Condición:** La persona tiene un movimiento de tipo "CPSAM EVADIDO"
- **Descripción:** Persona que se evadió del programa

#### 3. **CPSAM FALLECIDO**
- **Color:** Negro/Gris (text-dark)
- **Condición:** La persona tiene un movimiento de tipo "CPSAM FALLECIDO"
- **Descripción:** Persona fallecida

#### 4. **CPSAM RETIRADO VOLUNTARIO**
- **Color:** Azul claro (text-info)
- **Condición:** La persona tiene un movimiento de tipo "CPSAM RETIRADO VOLUNTARIO"
- **Descripción:** Persona que se retiró voluntariamente del programa

#### 5. **CPSAM TRASLADADO**
- **Color:** Azul (text-primary)
- **Condición:** La persona tiene un movimiento de tipo "CPSAM TRASLADADO"
- **Descripción:** Persona trasladada a otro centro/grupo

### Lógica de Determinación:

1. **Consulta SQL:** Se obtiene el último movimiento (más reciente) de cada persona que sea de los tipos específicos
2. **Estado por Defecto:** Si no tiene movimientos de estos tipos, se considera "CPSAM ACTIVO"
3. **Prioridad:** Se toma el movimiento más reciente para determinar el estado actual

### Archivos Modificados:

#### 1. `code/persons/seePerson.php`
- Agregada columna "Estado" en la tabla HTML
- Agregado filtro por estado en el formulario de búsqueda

#### 2. `code/persons/getPersons.php`
- Modificada consulta SQL para incluir el estado basado en movimientos
- Agregada lógica de colores para cada estado
- Implementado filtro por estado
- Actualizado colspan para incluir la nueva columna

### Consulta SQL Implementada:

```sql
SELECT p.*, 
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas,
       GROUP_CONCAT(pr.id_programa ORDER BY pr.nombre_programa ASC) AS ids_programas,
       g.descripcion_grupo,
       pol.descripcion_politica,
       (SELECT cc.descripcion_condicion 
        FROM movimiento_persona mp 
        JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
        WHERE mp.cedula_persona = p.cedula_persona 
        AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
        ORDER BY mp.fecha_movimiento DESC 
        LIMIT 1) AS estado_movimiento
FROM personas p...
```

### Filtros Disponibles:

- **Por Cédula**
- **Por Nombre**
- **Por Programa**
- **Por Estado** (Nuevo)
  - CPSAM ACTIVO
  - CPSAM EVADIDO
  - CPSAM FALLECIDO
  - CPSAM RETIRADO VOLUNTARIO
  - CPSAM TRASLADADO

### Impacto en el Conteo de Límites:

- Las personas con estados diferentes a "CPSAM ACTIVO" NO cuentan para los límites de grupo
- Esto libera cupos automáticamente cuando las personas cambian de estado
- La lógica de conteo ya implementada anteriormente considera estos estados

### Beneficios:

1. **Visibilidad:** Estado claro y visual de cada persona
2. **Filtrado:** Posibilidad de filtrar por estado específico
3. **Gestión:** Mejor control y seguimiento de las personas
4. **Reportes:** Base para generar reportes por estado
5. **Consistencia:** Estado basado en datos reales de movimientos
