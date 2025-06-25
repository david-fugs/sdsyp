# Actualización del Sistema de Acciones

## Cambios Realizados

### 1. Base de Datos
- **ANTES**: Las acciones estaban vinculadas a metas a través de `id_meta`
- **DESPUÉS**: Las acciones ahora están vinculadas a actividades a través de `id_actividad`

### 2. Archivos Modificados

#### 2.1 seeActions.php
- Cambió la consulta de metas por actividades
- Actualizó la tabla HTML para mostrar "Descripción Actividad" en lugar de "Descripción Meta"
- Modificó los modales (agregar y editar) para usar actividades
- Corrigió el JavaScript para manejar `data-id_actividad` en lugar de `data-id_meta`

#### 2.2 getActions.php
- Modificó la consulta SQL para hacer JOIN con actividades en lugar de metas
- Actualizado para mostrar `descripcion_actividad` en lugar de `descripcion_meta`
- Cambió los atributos de datos del botón de editar

#### 2.3 addAction.php
- Cambió el campo de entrada de `id_meta` a `id_actividad`
- Actualizó la consulta INSERT para usar `id_actividad`

#### 2.4 editAction.php
- Cambió el campo de entrada de `id_meta` a `id_actividad`
- Actualizó la consulta UPDATE para usar `id_actividad`

### 3. Scripts de Base de Datos Creados

#### 3.1 update_acciones_db.sql
Script SQL manual con los comandos necesarios para actualizar la estructura.

#### 3.2 update_database.php
Script PHP automatizado que:
- Agrega la columna `id_actividad` a la tabla `acciones`
- Migra los datos existentes asignando actividades basándose en las metas
- Opcionalmente puede eliminar la columna `id_meta` cuando esté seguro

## Instrucciones de Implementación

### Paso 1: Ejecutar la actualización de base de datos
Visita en tu navegador: `http://localhost/sdsyp/update_database.php`

Este script:
1. Agregará la nueva columna `id_actividad`
2. Migrará los datos existentes
3. Te informará del estado de cada operación

### Paso 2: Verificar funcionamiento
1. Ve a: `http://localhost/sdsyp/code/action/seeActions.php`
2. Verifica que las acciones ahora muestren actividades en lugar de metas
3. Prueba agregar una nueva acción
4. Prueba editar una acción existente

### Paso 3: Limpieza (opcional)
Si todo funciona correctamente, puedes:
1. Descomentar las líneas en `update_database.php` para eliminar la columna `id_meta`
2. Ejecutar nuevamente el script
3. Eliminar los archivos de actualización: `update_database.php` y `update_acciones_db.sql`

## Estructura de la Nueva Relación

```
metas (1) → (N) actividades (1) → (N) acciones
```

Ahora las acciones están asociadas directamente con actividades, y las actividades siguen estando asociadas con metas, manteniendo la jerarquía lógica del sistema.

## Archivos de Respaldo
Se recomienda hacer una copia de seguridad de la base de datos antes de ejecutar los scripts de actualización.
