# CRUD de Políticas Públicas

## 📋 Descripción
CRUD básico para gestionar Políticas Públicas. Esta implementación se centra únicamente en la gestión de políticas públicas sin ninguna relación con acciones o actividades.

## 📁 Archivos del Módulo

### Carpeta: `code/publicPolicies/`

1. **`seePublicPolicies.php`** - Vista principal del CRUD
2. **`getPublicPolicies.php`** - Obtiene y muestra los datos en la tabla
3. **`addPublicPolicy.php`** - Procesa la inserción de nuevas políticas
4. **`editPublicPolicy.php`** - Procesa la edición de políticas existentes
5. **`install_public_policies.php`** - Script de instalación automática
6. **`create_politicas_publicas_table.sql`** - Script SQL manual
7. **`README.md`** - Esta documentación

## 🗄️ Estructura de Base de Datos

### Tabla: `politicas_publicas`
```sql
CREATE TABLE politicas_publicas (
    id_politica INT AUTO_INCREMENT PRIMARY KEY,
    descripcion_politica VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🚀 Instalación

### Paso 1: Crear la tabla
Ejecuta en tu navegador: `http://localhost/sdsyp/code/publicPolicies/install_public_policies.php`

Este script:
- Crea la tabla `politicas_publicas`
- Inserta datos de ejemplo
- Verifica la instalación

### Paso 2: Acceder al CRUD
Ve a: `http://localhost/sdsyp/code/publicPolicies/seePublicPolicies.php`

## ✨ Características

### Funcionalidades Incluidas:
- ✅ **Ver todas las políticas públicas** (con DataTables)
- ✅ **Agregar nueva política pública**
- ✅ **Editar política existente**
- ✅ **Eliminar política** (con confirmación)
- ✅ **Validaciones de formulario**
- ✅ **Prepared statements** (seguridad SQL)
- ✅ **Responsive design** (Bootstrap 5)
- ✅ **Iconos apropiados** (Bootstrap Icons)

### Navegación:
- 📍 **Menú principal**: Políticas Públicas (icono: `fa-scale-balanced`)
- 🔗 **Ubicación**: En la sección de gestión del menú

## 🎨 Interfaz

### Diseño:
- **Color principal**: Verde (Bootstrap success)
- **Modal de edición**: Fondo oscuro
- **Iconos**: Balanza de justicia (`fa-scale-balanced`)
- **Tabla**: DataTables con traducción al español
- **Solo gestión de políticas públicas**: Sin relación con acciones o actividades

### Datos de Ejemplo:
1. Política Nacional de Envejecimiento y Vejez
2. Política Pública Nacional de Discapacidad e Inclusión Social
3. Política de Seguridad Alimentaria y Nutricional
4. Política de Primera Infancia
5. Política Nacional de Equidad de Género

## 🔧 Mantenimiento

### Para agregar campos adicionales:
1. Modificar la tabla SQL en `create_politicas_publicas_table.sql`
2. Actualizar los formularios en `seePublicPolicies.php`
3. Actualizar `addPublicPolicy.php` y `editPublicPolicy.php`
4. Modificar `getPublicPolicies.php` para mostrar nuevos campos

### Archivos de limpieza:
Después de la instalación exitosa, puedes eliminar:
- `install_public_policies.php`
- `create_politicas_publicas_table.sql`

## 🔒 Seguridad
- Uso de prepared statements
- Validación de formularios
- Sanitización de datos de entrada
- Confirmación antes de eliminar

## 📝 Notas Importantes
- Este CRUD está completamente separado del sistema de acciones y actividades
- Solo gestiona políticas públicas de manera independiente
- No hay vinculación con otros módulos del sistema
- Implementación limpia y enfocada únicamente en políticas públicas
