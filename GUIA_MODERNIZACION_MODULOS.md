# Guía de Modernización de Módulos - SDSYP

## Resumen
Esta guía documenta el patrón de modernización aplicado a los módulos de **Personas** (`seePerson.php`) y **Movimientos de Personas** (`seePersonMovement.php`). Puedes seguir estos pasos para modernizar otros módulos del sistema.

## Archivos Modernizados

### ✅ Completados
- `code/persons/seePerson.php` + `code/persons/getPersons.php`
- `code/personMovement/seePersonMovement.php` + `code/personMovement/getPersonMovement.php`

### 🔄 Pendientes (siguiendo el mismo patrón)
- `code/group/seeGroup.php`
- `code/center/seeCenter.php`
- `code/activities/seeActivity.php`
- `code/goals/getMetas.php`
- Y otros módulos...

## Patrón de Modernización

### 1. CSS y Dependencias
Agregar en el `<head>` del archivo principal:

```html
<link rel="stylesheet" type="text/css" href="../../css/modern-table-styles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
```

### 2. Estructura HTML Moderna

#### Header del Módulo
```html
<div class="container mt-5">
    <div class="modern-container">
        <!-- Header moderno -->
        <div class="modern-header">
            <h2><i class="bi bi-[icono-apropiado]"></i> Título del Módulo</h2>
            <button type="button" class="btn-modern btn-success" data-bs-toggle="modal" data-bs-target="#modalNew[Entidad]">
                <i class="bi bi-plus-circle-fill"></i>
                Agregar [Entidad]
            </button>
        </div>
```

#### Filtros Modernos
```html
<!-- Filtros modernos -->
<div class="modern-filters">
    <form action="see[Entidad].php" method="get" class="filter-row">
        <div class="filter-group">
            <label for="campo1">Campo 1</label>
            <input type="text" id="campo1" name="campo1" class="modern-input" 
                   placeholder="Buscar por..."
                   value="<?= isset($_GET['campo1']) ? htmlspecialchars($_GET['campo1']) : '' ?>">
        </div>
        <div class="filter-group">
            <label for="campo2">Campo 2</label>
            <select name="campo2" id="campo2" class="modern-select">
                <option value="">Todos</option>
                <!-- Opciones dinámicas -->
            </select>
        </div>
        <div class="filter-group">
            <button type="submit" class="btn-modern btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>
        </div>
    </form>
</div>
```

#### Tabla Moderna
```html
<!-- Tabla moderna -->
<div class="modern-table-wrapper">
    <table class="modern-table" id="dataTable">
        <thead>
            <tr>
                <th class="col-id">ID</th>
                <th>Campo 1</th>
                <th>Campo 2</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php include "get[Entidad].php"; ?>
        </tbody>
    </table>
</div>
```

### 3. Archivo de Datos (get[Entidad].php)

#### Estructura de Filas
```php
echo "<tr class='fade-in'>";
echo "<td class='col-id'>" . $row['id'] . "</td>";
echo "<td>" . $row['campo1'] . "</td>";
echo "<td>" . $row['campo2'] . "</td>";

// Botones de acción modernos
echo '<td class="col-actions">
        <div class="action-buttons">
            <button type="button" class="btn-action btn-edit" 
                title="Editar"
                data-bs-toggle="modal" data-bs-target="#modalEdicion"
                data-id="' . $row['id'] . '"
                data-campo1="' . $row['campo1'] . '"
                data-campo2="' . $row['campo2'] . '">
                <i class="bi bi-pencil-fill"></i>
            </button>
            <a href="?delete=' . $row['id'] . '" 
               class="btn-action btn-delete" 
               title="Eliminar"
               onclick="return confirm(\'¿Estás seguro?\')">
                <i class="bi bi-trash-fill"></i>
            </a>
        </div>
    </td>';
echo "</tr>";
```

### 4. Modales Modernos

#### Modal Agregar
```html
<div class="modal fade" id="modalNew[Entidad]" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="add[Entidad].php" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle-fill me-2"></i>Agregar [Entidad]
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Campos del formulario -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn-modern btn-outline btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-modern btn-success">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

#### Modal Editar
```html
<div class="modal fade" id="modalEdicion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="edit[Entidad].php" method="POST">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-fill me-2"></i>Editar [Entidad]
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Campos del formulario -->
                    <input type="hidden" name="id_original" id="id_original">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn-modern btn-outline btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-modern btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### 5. JavaScript para DataTables

```javascript
// Inicializar DataTables
let dataTable;

function initDataTable() {
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    
    dataTable = $('#dataTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        columnDefs: [
            { orderable: false, targets: [-1] }, // Última columna (acciones) no ordenable
            { className: "text-center", targets: [0, -1] } // Centrar ID y acciones
        ],
        order: [[0, 'desc']], // Orden por primera columna descendente
        dom: 'frtip',
        searching: false, // Usar filtros propios
        info: true,
        paging: true,
        responsive: true
    });
}

$(document).ready(function() {
    initDataTable();
    
    // Script para cargar datos en modal de edición
    $('#modalEdicion').on('shown.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('campo1').value = button.getAttribute('data-campo1');
        document.getElementById('campo2').value = button.getAttribute('data-campo2');
        document.getElementById('id_original').value = button.getAttribute('data-id');
    });
});
```

## Clases CSS Principales

### Botones
- `.btn-modern` - Clase base para botones
- `.btn-primary`, `.btn-success`, `.btn-danger` - Variantes de color
- `.btn-outline` - Estilo outline
- `.btn-action` - Botones de acción en tabla
- `.btn-edit`, `.btn-delete` - Variantes específicas

### Tabla
- `.modern-table` - Tabla principal
- `.modern-table-wrapper` - Contenedor de tabla
- `.col-id` - Columna de ID
- `.col-actions` - Columna de acciones
- `.fade-in` - Animación de filas

### Contenedores
- `.modern-container` - Contenedor principal
- `.modern-header` - Header del módulo
- `.modern-filters` - Sección de filtros
- `.filter-row` - Fila de filtros
- `.filter-group` - Grupo de filtro individual

### Formularios
- `.modern-input` - Input moderno
- `.modern-select` - Select moderno

## Iconos Bootstrap Recomendados

- `bi-people-fill` - Personas
- `bi-arrow-left-right` - Movimientos
- `bi-building` - Centros/Edificios
- `bi-diagram-3` - Grupos
- `bi-calendar-event` - Actividades
- `bi-target` - Metas/Objetivos
- `bi-plus-circle-fill` - Agregar
- `bi-pencil-fill` - Editar
- `bi-trash-fill` - Eliminar
- `bi-search` - Buscar
- `bi-save` - Guardar
- `bi-x-circle` - Cancelar

## Estado Actual

### ✅ Módulos Completados
1. **Personas** (`seePerson.php`)
   - ✅ CSS moderno aplicado
   - ✅ Tabla moderna con DataTables
   - ✅ Filtros unificados
   - ✅ Botones de acción modernos
   - ✅ Modales actualizados

2. **Movimientos de Personas** (`seePersonMovement.php`)
   - ✅ CSS moderno aplicado
   - ✅ Tabla moderna con DataTables
   - ✅ Filtros unificados
   - ✅ Botones de acción modernos
   - ✅ Modales actualizados
   - ✅ Validación de límites de grupos

## Próximos Pasos

Para continuar la modernización, aplicar este mismo patrón a:

1. **Grupos** (`code/group/seeGroup.php`)
2. **Centros** (`code/center/seeCenter.php`)
3. **Actividades** (`code/activities/seeActivity.php`)
4. **Metas** (`code/goals/getMetas.php`)
5. **Otros módulos** según prioridad

## Beneficios de la Modernización

- 🎨 **Diseño Uniforme**: Todos los módulos siguen el mismo patrón visual
- 📱 **Responsive**: Adaptable a dispositivos móviles
- 🚀 **Performance**: DataTables con paginación y búsqueda optimizada
- 🔍 **UX Mejorada**: Filtros intuitivos y botones de acción claros
- 🛠️ **Mantenimiento**: Código más limpio y estructurado
- ♿ **Accesibilidad**: Mejor contraste y navegación por teclado

---

*Archivo generado automáticamente - Última actualización: $(date)*
