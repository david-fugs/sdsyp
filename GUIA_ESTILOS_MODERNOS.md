# Guía para Actualizar Estilos Modernos en Todos los Menús - SDSYP

## Archivos Creados

### 1. CSS Principal
- **Archivo**: `css/modern-table-styles.css`
- **Descripción**: Contiene todos los estilos modernos para tablas, botones, filtros y componentes.

### 2. Ejemplo Implementado
- **Archivo**: `code/persons/seePerson.php` (ACTUALIZADO)
- **Archivo**: `code/persons/getPersonsAjax.php` (NUEVO)

## Cómo Aplicar a Otros Menús

### Paso 1: Incluir el CSS
En cada archivo `see*.php`, agregar la siguiente línea en el `<head>`:

```html
<link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
```

### Paso 2: Actualizar la Estructura HTML

#### 2.1 Reemplazar el contenedor de tabla:
**ANTES:**
```html
<div class="container mt-5">
    <div class="position-relative mb-3">
        <h2 class="text-center">Título</h2>
        <button type="button" class="btn btn-success position-absolute top-0 end-0" data-bs-toggle="modal">
            Agregar Item
        </button>
    </div>
    <table class="table table-striped" id="salesTable">
```

**DESPUÉS:**
```html
<div class="container mt-5">
    <div class="modern-container">
        <!-- Header moderno -->
        <div class="modern-header">
            <h2><i class="bi bi-icon-name"></i> Título</h2>
            <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNew">
                <i class="bi bi-plus-circle-fill"></i>
                Agregar Item
            </button>
        </div>

        <!-- Filtros modernos (opcional) -->
        <div class="modern-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="filter-campo">Campo</label>
                    <input type="text" id="filter-campo" class="modern-input" placeholder="Buscar...">
                </div>
                <!-- Más filtros según sea necesario -->
            </div>
        </div>

        <!-- Tabla moderna -->
        <div class="modern-table-wrapper">
            <table class="modern-table" id="salesTable">
```

#### 2.2 Actualizar encabezados de tabla:
```html
<thead>
    <tr>
        <th class="col-id">ID</th>
        <th>Campo 1</th>
        <th>Campo 2</th>
        <th class="col-status">Estado</th>
        <th class="col-actions">Acciones</th>
    </tr>
</thead>
```

### Paso 3: Actualizar Botones de Acción

#### 3.1 En archivos get*.php, reemplazar botones:
**ANTES:**
```php
echo '<td>
    <button type="button" class="btn-edit" style="background-color:transparent; border:none;">
        <img src="../../img/editar.png" width="28" height="28">
    </button>
</td>';
echo '<td>
    <a href="?delete=' . $id . '" class="btn btn-sm btn-danger">
        <img src="../../img/delete1.png" width="20" height="20">
    </a>
</td>';
```

**DESPUÉS:**
```php
echo '<td class="col-actions">
    <div class="action-buttons">
        <button type="button" class="btn-action btn-edit" 
            title="Editar"
            data-bs-toggle="modal" data-bs-target="#modalEdicion"
            data-id="' . $row['id'] . '"
            data-campo1="' . htmlspecialchars($row['campo1']) . '">
            <i class="bi bi-pencil-fill"></i>
        </button>
        <a href="?delete=' . $id . '" 
           class="btn-action btn-delete" 
           title="Eliminar"
           onclick="return confirm(\'¿Confirmar eliminación?\')">
            <i class="bi bi-trash-fill"></i>
        </a>
    </div>
</td>';
```

### Paso 4: Actualizar Estados/Badges

**ANTES:**
```php
echo "<td><span class='text-success fw-bold'>$estado</span></td>";
```

**DESPUÉS:**
```php
$badge_class = '';
$estado_icon = '';
switch ($estado) {
    case 'ACTIVO':
        $badge_class = 'status-badge status-active';
        $estado_icon = '<i class="bi bi-check-circle-fill"></i>';
        break;
    case 'INACTIVO':
        $badge_class = 'status-badge status-inactive';
        $estado_icon = '<i class="bi bi-x-circle-fill"></i>';
        break;
}
echo "<td class='col-status'><span class='$badge_class'>$estado_icon $estado</span></td>";
```

### Paso 5: Actualizar Botones de Modal

**ANTES:**
```html
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-primary">Guardar</button>
```

**DESPUÉS:**
```html
<button type="button" class="btn-modern btn-outline btn-secondary" data-bs-dismiss="modal">
    <i class="bi bi-x-lg"></i>
    Cancelar
</button>
<button type="submit" class="btn-modern btn-primary">
    <i class="bi bi-check-lg"></i>
    Guardar
</button>
```

## Iconos Recomendados por Módulo

### Bootstrap Icons a usar:
- **Personas**: `bi-people-fill`, `bi-person-plus-fill`
- **Grupos**: `bi-collection-fill`, `bi-plus-circle-fill`
- **Centros**: `bi-building-fill`, `bi-geo-alt-fill`
- **Actividades**: `bi-calendar-event-fill`, `bi-activity`
- **Movimientos**: `bi-arrow-left-right`, `bi-shuffle`
- **Políticas**: `bi-file-text-fill`, `bi-clipboard-check`
- **Acciones**: `bi-lightning-fill`, `bi-gear-fill`
- **Condiciones**: `bi-check2-square`, `bi-list-check`

### Acciones comunes:
- **Editar**: `bi-pencil-fill`
- **Eliminar**: `bi-trash-fill`
- **Ver**: `bi-eye-fill`
- **Buscar**: `bi-search`
- **Filtrar**: `bi-funnel-fill`

## Clases CSS Principales

### Contenedores:
- `.modern-container`: Contenedor principal con bordes redondeados y sombra
- `.modern-header`: Header con gradiente azul
- `.modern-filters`: Área de filtros con fondo gris claro
- `.modern-table-wrapper`: Contenedor de tabla con scroll horizontal

### Botones:
- `.btn-modern`: Clase base para botones modernos
- `.btn-primary`, `.btn-success`, `.btn-danger`, etc.: Colores
- `.btn-action`: Botones circulares para acciones de tabla
- `.btn-edit`, `.btn-delete`, `.btn-view`: Estilos específicos de acción

### Estados:
- `.status-badge`: Badge base para estados
- `.status-active`, `.status-inactive`, `.status-warning`, etc.: Colores de estado

### Filtros:
- `.filter-row`: Contenedor grid para filtros
- `.filter-group`: Grupo individual de filtro
- `.modern-input`, `.modern-select`: Inputs y selects estilizados

## JavaScript para Filtros Dinámicos

Si se desea implementar filtros dinámicos, crear un archivo `get[Module]Ajax.php` similar al ejemplo y agregar el JavaScript correspondiente.

## Orden de Implementación Recomendado

1. **seePersons.php** ✅ (Ya implementado)
2. **seeGroup.php** - Grupos (similar estructura)
3. **seeCenter.php** - Centros de vida
4. **seeActivity.php** - Actividades
5. **seeActions.php** - Acciones
6. **seeCondition.php** - Condiciones
7. **seeMovement.php** - Movimientos
8. **seePersonMovement.php** - Movimientos de personas
9. **seePublicPolicies.php** - Políticas públicas
10. **seeGoals.php** - Metas

## Notas Importantes

1. **Siempre usar `htmlspecialchars()`** para escapar datos en HTML
2. **Mantener consistencia** en iconos y colores
3. **Responsive design** está incluido en el CSS
4. **Accesibilidad** mejorada con tooltips y ARIA labels
5. **Performance** optimizada con CSS variables y transiciones suaves

## Testing

Después de cada implementación:
1. Verificar responsive design en móviles
2. Probar funcionalidad de botones
3. Verificar filtros (si aplica)
4. Comprobar accesibilidad con teclado
5. Validar que los modales funcionen correctamente
