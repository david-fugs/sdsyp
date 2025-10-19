# EJEMPLOS Y ESTRUCTURA DEL EXCEL - FECHAS DE CONTRATACIÓN

## Estructura Completa del Excel

El archivo Excel exportado desde `Informes y Reportes` ahora contiene **57 columnas** (anteriormente 55).

### Columnas 1-55 (Sin cambios)
Todas las columnas anteriores se mantienen igual.

### NUEVAS COLUMNAS (56-57)

| # | Nombre de Columna | Tipo de Dato | Ejemplo | Descripción |
|---|-------------------|--------------|---------|-------------|
| 56 | FECHA ÚLTIMO CONTRATO GRUPO | Fecha (dd/mm/yyyy) | 15/01/2025 | Fecha más reciente de contratación del grupo al que pertenece la persona |
| 57 | DÍAS ACTIVO DESDE CONTRATO | Número entero | 287 | Días transcurridos desde la fecha de último contrato hasta activo_hasta (o hoy si sigue activo) |

---

## Ejemplos de Datos en el Excel

### Ejemplo 1: Persona Activa con Grupo que tiene Fecha de Contrato

| CÉDULA | NOMBRES | APELLIDOS | GRUPO | ESTADO ACTUAL | ACTIVO DESDE | ACTIVO HASTA | DÍAS ACTIVOS | **FECHA ÚLTIMO CONTRATO GRUPO** | **DÍAS ACTIVO DESDE CONTRATO** |
|--------|---------|-----------|-------|---------------|--------------|--------------|--------------|--------------------------------|-------------------------------|
| 1234567890 | Juan | Pérez | Centro Vida Norte | ACTIVO | 01/02/2025 | 18/10/2025 | 260 | **15/01/2025** | **277** |

**Explicación:**
- El grupo "Centro Vida Norte" fue contratado el 15/01/2025
- Juan ingresó el 01/02/2025 (17 días después del contrato)
- Está activo hasta hoy (18/10/2025)
- Días desde contrato: del 15/01/2025 al 18/10/2025 = 277 días

---

### Ejemplo 2: Persona Fallecida

| CÉDULA | NOMBRES | APELLIDOS | GRUPO | ESTADO ACTUAL | ACTIVO DESDE | ACTIVO HASTA | DÍAS ACTIVOS | **FECHA ÚLTIMO CONTRATO GRUPO** | **DÍAS ACTIVO DESDE CONTRATO** |
|--------|---------|-----------|-------|---------------|--------------|--------------|--------------|--------------------------------|-------------------------------|
| 9876543210 | María | González | Centro Vida Sur | FALLECIDO | 10/03/2025 | 15/09/2025 | 189 | **01/03/2025** | **198** |

**Explicación:**
- El grupo fue contratado el 01/03/2025
- María ingresó el 10/03/2025
- Falleció el 15/09/2025
- Días desde contrato: del 01/03/2025 al 15/09/2025 = 198 días
- El cálculo usa la fecha de fallecimiento, NO la fecha actual

---

### Ejemplo 3: Grupo Sin Fecha de Contrato Registrada

| CÉDULA | NOMBRES | APELLIDOS | GRUPO | ESTADO ACTUAL | ACTIVO DESDE | ACTIVO HASTA | DÍAS ACTIVOS | **FECHA ÚLTIMO CONTRATO GRUPO** | **DÍAS ACTIVO DESDE CONTRATO** |
|--------|---------|-----------|-------|---------------|--------------|--------------|--------------|--------------------------------|-------------------------------|
| 1122334455 | Carlos | Ramírez | Centro Vida Este | ACTIVO | 05/04/2025 | 18/10/2025 | 196 | **(vacío)** | **(vacío)** |

**Explicación:**
- El grupo "Centro Vida Este" aún no tiene fecha de contratación registrada
- Las columnas 56 y 57 quedan vacías
- Se debe agregar la fecha manualmente editando el grupo

---

### Ejemplo 4: Grupo con Múltiples Fechas de Contrato (Renovaciones)

| CÉDULA | NOMBRES | APELLIDOS | GRUPO | ESTADO ACTUAL | ACTIVO DESDE | ACTIVO HASTA | DÍAS ACTIVOS | **FECHA ÚLTIMO CONTRATO GRUPO** | **DÍAS ACTIVO DESDE CONTRATO** |
|--------|---------|-----------|-------|---------------|--------------|--------------|--------------|--------------------------------|-------------------------------|
| 5566778899 | Ana | Torres | Centro Vida Oeste | ACTIVO | 15/02/2024 | 18/10/2025 | 611 | **10/01/2025** | **282** |

**Explicación:**
- El grupo tiene varias fechas en su historial:
  - Primera contratación: 01/02/2024
  - Renovación: 10/01/2025 ← **Esta es la que se muestra (más reciente)**
- Ana lleva 611 días activos en total (desde 15/02/2024)
- Pero desde el último contrato (renovación) solo 282 días

---

## Lógica de Cálculo de "DÍAS ACTIVO DESDE CONTRATO"

```
SI (fecha_ultimo_contrato_grupo EXISTE) ENTONCES:
    
    fecha_inicio = fecha_ultimo_contrato_grupo
    
    // NUEVO: Si activo_desde es anterior, usar activo_desde como inicio
    SI (activo_desde EXISTE Y activo_desde < fecha_ultimo_contrato_grupo) ENTONCES:
        fecha_inicio = activo_desde
    FIN SI
    
    SI (estado_actual = "FALLECIDO" O "EVADIDO" O "RETIRADO VOLUNTARIO") ENTONCES:
        fecha_fin = fecha_ultimo_movimiento
    SI NO:
        fecha_fin = HOY
    FIN SI
    
    dias_activo_desde_contrato = (fecha_fin - fecha_inicio) + 1  // Cálculo inclusivo
    
SI NO:
    dias_activo_desde_contrato = (vacío)
FIN SI
```

---

## Casos Especiales

### Caso A: Persona ingresó ANTES del último contrato del grupo

| Concepto | Valor |
|----------|-------|
| Ingreso de la persona (activo_desde) | 01/12/2024 |
| Último contrato del grupo | 15/01/2025 |
| Fecha actual | 18/10/2025 |
| **Fecha de inicio usada** | **01/12/2024** (activo_desde porque es anterior) |
| **Días desde contrato** | **322 días** (01/12/2024 a 18/10/2025) |

**Nota:** ✅ **NUEVO COMPORTAMIENTO:** Si la persona ingresó ANTES del último contrato, se usa `activo_desde` como fecha de inicio del cálculo.

---

### Caso B: Grupo con fecha de contrato pero sin personas

No aplica, porque solo se exportan personas en el informe.

---

### Caso C: Persona EVADIDA (fecha de evasión conocida)

| Concepto | Valor |
|----------|-------|
| Último contrato grupo | 10/02/2025 |
| Evasión | 20/08/2025 |
| **Días desde contrato** | **192 días** (10/02/2025 a 20/08/2025) |

Se usa la fecha de evasión, NO la fecha actual.

---

## Diferencia entre "DÍAS ACTIVOS" y "DÍAS ACTIVO DESDE CONTRATO"

| Columna | Punto de Inicio | Punto Final | Propósito |
|---------|----------------|-------------|-----------|
| **DÍAS ACTIVOS** (col 55) | activo_desde (de la persona) | activo_hasta o hoy | Medir permanencia de la persona en el sistema |
| **DÍAS ACTIVO DESDE CONTRATO** (col 57) | fecha_ultimo_contrato_grupo | activo_hasta o hoy | Medir antigüedad desde el último contrato del grupo |

### Ejemplo Comparativo:

```
Persona: Pedro Gómez
- Ingresó al sistema: 01/06/2024 (activo_desde)
- Grupo contratado por última vez: 01/01/2025
- Fecha actual: 18/10/2025
- Estado: ACTIVO

DÍAS ACTIVOS = 505 días (01/06/2024 a 18/10/2025) + 1 = 506 días
DÍAS ACTIVO DESDE CONTRATO = 506 días (usa 01/06/2024 porque es anterior al contrato)
```

**Nota:** Ambos cálculos ahora son iguales cuando `activo_desde` < `fecha_ultimo_contrato`.

---

## Formato de Fecha en el Excel

- **Formato mostrado:** dd/mm/yyyy (ejemplo: 15/01/2025)
- **Tipo de celda:** Texto (no formato fecha de Excel)
- **Razón:** Evitar problemas de interpretación regional

---

## Consulta SQL para Verificar Datos

```sql
SELECT 
    p.cedula_persona,
    p.nombres_persona,
    p.apellidos_persona,
    g.descripcion_grupo,
    p.activo_desde,
    (SELECT hfc.fecha_contratacion
     FROM historial_fechas_contratacion hfc
     WHERE hfc.id_grupo = p.id_grupo
     ORDER BY hfc.fecha_contratacion DESC
     LIMIT 1) AS fecha_ultimo_contrato_grupo,
    CASE 
        WHEN (SELECT hfc.fecha_contratacion
              FROM historial_fechas_contratacion hfc
              WHERE hfc.id_grupo = p.id_grupo
              ORDER BY hfc.fecha_contratacion DESC
              LIMIT 1) IS NOT NULL 
        THEN DATEDIFF(CURDATE(), 
                     (SELECT hfc.fecha_contratacion
                      FROM historial_fechas_contratacion hfc
                      WHERE hfc.id_grupo = p.id_grupo
                      ORDER BY hfc.fecha_contratacion DESC
                      LIMIT 1))
        ELSE NULL
    END AS dias_desde_contrato
FROM personas p
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
WHERE p.estado_persona = 1
ORDER BY p.apellidos_persona
LIMIT 10;
```

---

## Preguntas Frecuentes

### ¿Por qué un grupo tiene múltiples fechas de contratación?

Porque se permiten renovaciones o extensiones de contrato. El historial mantiene todas las fechas para trazabilidad, pero en el Excel solo se muestra la más reciente.

### ¿Qué pasa si elimino todas las fechas de un grupo?

Las columnas 56 y 57 en el Excel quedarán vacías para las personas de ese grupo.

### ¿Puedo editar las fechas después de crearlas?

Sí, desde el modal de edición del grupo puedes:
- Ver todo el historial
- Editar fechas existentes
- Eliminar fechas
- Agregar nuevas fechas

### ¿Los días incluyen la fecha de inicio?

No. El cálculo es: `fecha_fin - fecha_inicio` (días entre fechas, excluyendo el día inicial).

---

## Vista Previa del Excel

```
[Columnas 1-54 aquí]  |  Columna 55        |  Columna 56                      |  Columna 57
                      |  DÍAS ACTIVOS      |  FECHA ÚLTIMO CONTRATO GRUPO     |  DÍAS ACTIVO DESDE CONTRATO
----------------------|--------------------|----------------------------------|---------------------------
[datos anteriores]    |  260               |  15/01/2025                      |  277
[datos anteriores]    |  189               |  01/03/2025                      |  198
[datos anteriores]    |  196               |                                  |
[datos anteriores]    |  611               |  10/01/2025                      |  282
```

---

**Última actualización:** 18/10/2025  
**Versión del documento:** 1.0
