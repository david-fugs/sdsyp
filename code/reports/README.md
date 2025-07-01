# Módulo de Informes Anuales - SDSYP

## Descripción
El módulo de informes anuales permite generar reportes detallados de personas, movimientos y estadísticas del sistema por año.

## Funcionalidades

### 1. Visualización de Datos
- **Selector de Año**: Permite seleccionar cualquier año desde 2020 hasta el año siguiente al actual
- **Estadísticas Resumidas**: Muestra indicadores clave del año seleccionado:
  - Personas nuevas registradas
  - Personas activas al final del año
  - Total de movimientos en el año
  - Total de registros en el informe

### 2. Tabla de Informe Detallado
La tabla incluye la siguiente información por persona:
- **Datos Básicos**: Cédula, nombres, apellidos, género
- **Información Personal**: Fecha de nacimiento, edad actual, teléfono, referencia
- **Fechas Importantes**: Fecha de registro en el sistema
- **Asignaciones**: Centro de vida, programas asignados, política pública
- **Estado Actual**: Estado de la persona (Activo, Evadido, Fallecido, etc.)
- **Movimientos**: Total de movimientos en el año seleccionado
- **Traslados**: Número de traslados realizados en el año

### 3. Exportación de Datos

#### Exportar a Excel
- **Hoja 1 - Datos Detallados**: Información completa de todas las personas
- **Hoja 2 - Estadísticas**: Resumen de estadísticas del año incluyendo:
  - Estadísticas generales
  - Personas por estado
  - Personas por centro de vida
  - Movimientos por tipo

#### Exportar a PDF
- Informe ejecutivo con estadísticas principales
- Tablas de distribución por estado, centro de vida y tipo de movimiento
- Fecha de generación del informe

#### Imprimir
- Versión optimizada para impresión
- Formato landscape (apaisado) para mejor visualización de datos

## Archivos del Módulo

### Frontend
- `seeReports.php`: Interfaz principal del módulo

### Backend
- `getReportStats.php`: API para obtener estadísticas del año
- `getReportData.php`: API para obtener datos detallados de personas
- `generatePDF.php`: Generador de PDF para informes

### Auxiliares
- `test_db.php`: Archivo de prueba para verificar conectividad y estructura de datos

## Navegación

### Acceso desde Dashboard
- **Menú Lateral**: Menú "Informes" > "Informes Anuales"
- **Barra Superior**: Acceso directo con ícono de informes

### Breadcrumb
- Inicio > Informes Anuales

## Características Técnicas

### DataTables
- Configurado en español
- Paginación de 15 registros por página
- Ordenamiento por apellidos por defecto
- Búsqueda y filtrado integrado

### Responsive Design
- Adaptable a dispositivos móviles
- Tablas con scroll horizontal en pantallas pequeñas
- Botones de exportación optimizados para móviles

### Compatibilidad
- Compatible con Chrome, Firefox, Safari, Edge
- Funciona con versiones de PHP 7.0+
- Requiere MySQL/MariaDB

## Casos de Uso

### Informes de Gestión
- Reportes anuales para dirección
- Estadísticas para presentaciones
- Análisis de tendencias por año

### Auditorías
- Seguimiento de movimientos de personas
- Verificación de traslados
- Control de estados de personas

### Planificación
- Análisis de capacidad por centro de vida
- Evaluación de programas
- Identificación de necesidades

## Notas Importantes

1. **Rendimiento**: Las consultas están optimizadas para manejar grandes volúmenes de datos
2. **Seguridad**: Validación de parámetros y preparación de consultas SQL
3. **Compatibilidad**: Manejo de campos opcionales como fecha_registro
4. **Usabilidad**: Interfaz intuitiva con feedback visual para operaciones

## Mantenimiento

### Actualizar Años Disponibles
Modificar las variables `$startYear` y `$endYear` en `seeReports.php`

### Agregar Nuevos Campos
1. Actualizar las consultas en `getReportData.php`
2. Modificar la tabla en `seeReports.php`
3. Actualizar la exportación a Excel
4. Ajustar el generador de PDF si es necesario

### Personalizar Estilos
Los estilos están integrados en `seeReports.php` y siguen el patrón de diseño del sistema SDSYP.
