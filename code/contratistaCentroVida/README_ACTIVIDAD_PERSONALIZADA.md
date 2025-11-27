# Formulario de Actividad Personalizada

## 📋 Descripción
Nuevo formulario para registrar actividades personalizadas con información detallada de participantes y firma digital.

## 🗂️ Archivos Creados

1. **formActividadPersonalizada.php** - Página principal con el formulario y tabla de registros
2. **addActividadPersonalizada.php** - Procesa y guarda los registros en la base de datos
3. **exportActividadPersonalizada.php** - Exporta los registros a Excel
4. **actividad_personalizada.sql** - Script SQL para crear la tabla en la base de datos

## 🚀 Instalación

### Paso 1: Crear la tabla en la base de datos
Ejecuta el archivo SQL en tu base de datos:

```sql
-- Abre PhpMyAdmin o tu gestor de base de datos
-- Selecciona la base de datos del proyecto
-- Ejecuta el contenido de: actividad_personalizada.sql
```

O desde la línea de comandos:
```bash
mysql -u usuario -p nombre_base_datos < actividad_personalizada.sql
```

### Paso 2: Acceder al formulario
Navega a: `http://localhost/sdsyp/code/contratistaCentroVida/formActividadPersonalizada.php`

## 📝 Campos del Formulario

### 1️⃣ Información de Horario y Fecha
- **Hora de Inicio** - Hora de inicio de la actividad
- **Hora de Finalización** - Hora de finalización
- **Fecha de la Actividad** - Fecha cuando se realizó

### 2️⃣ Información Personal
- **Nombres y Apellidos** - Del usuario o líder
- **Género** - Hombre o Mujer
- **Fecha de Nacimiento** - Del participante
- **Tipo de Documento** - Tarjeta de Identidad, Cédula, Pasaporte, etc.
- **Número de Documento** - Identificación

### 3️⃣ Condición
Checkboxes:
- Desplazado
- Mujer/Hombre Cabeza de Hogar
- Habitante de Calle y/o Riesgo

Selects:
- **Orientación Sexual** - LGBTI, Hetero, Gay, Lesbiana, Bi, No binario, Otro
- **Tipo de Discapacidad** - Campo de texto libre
- **Migrante** - Sí/No

### 4️⃣ Etnia
- **Mestizo** - Sí/No
- **Afrodescendiente** - Sí/No
- **Indígena** - Sí/No

### 5️⃣ Información Adicional
- **Tipo de Seguridad en Salud** - Contributivo, Subsidiado, Vinculado
- **Condición Ocupacional** - Ama de casa, Empleado, Desempleado, Independiente, etc.
- **Nivel de Estudio** - De 0 a 12, Técnico, Tecnología, Profesional, Posgrado
- **Teléfono - Celular** - Contacto

### 6️⃣ Información de la Actividad
- **Nombre Actividad** - Título de la actividad
- **Evento/Tema o Asunto** - Descripción de lo que mueve la meta
- **Número de Actividades Realizadas** - Cantidad

### 7️⃣ Beneficiados
- **Total Masculino** - Cantidad de hombres beneficiados
- **Total Femenino** - Cantidad de mujeres beneficiadas
- **Total General** - Se calcula automáticamente (masculino + femenino)

### 8️⃣ Firma Digital
- Canvas para firmar con mouse o dedo táctil
- Botón para limpiar la firma
- La firma se guarda como imagen base64

## 📊 Exportar a Excel

El botón **"Exportar Excel"** genera un archivo con todas las columnas en el orden especificado:

**Orden de columnas:**
1. ID
2. Hora de Inicio
3. Hora de Finalización
4. Fecha de la Actividad
5. Nombres y Apellidos Usuario/Líder
6. Género
7. Fecha de Nacimiento
8. Tipo de Documento
9. Número de Documento
10. Desplazado
11. Mujer Cabeza de Hogar
12. Hombre Cabeza de Hogar
13. Orientación Sexual Población
14. Tipo de Discapacidad
15. Migrante
16. Habitante de Calle y/o Riesgo
17. Mestizo
18. Afrodescendiente
19. Indígena
20. Tipo de Seguridad en Salud
21. Condición Ocupacional
22. Nivel de Estudio
23. Teléfono - Celular
24. Nombre Actividad
25. Evento/Tema o Asunto
26. Número de Actividades Realizadas
27. Total Masculino
28. Total Femenino
29. Total General
30. Fecha de Registro
31. Tiene Firma

El archivo incluye:
- ✅ Encabezados con fondo morado (#667eea)
- ✅ Filas alternadas con fondo gris claro
- ✅ Fila de totales al final con fondo verde
- ✅ Formato de fechas dd/mm/yyyy
- ✅ Anchos de columna optimizados

## 🎨 Características

- ✨ Diseño moderno con gradiente morado
- 📱 Responsive (se adapta a móviles y tablets)
- 📋 DataTable para búsqueda y paginación
- 🖊️ Firma digital con Signature Pad
- ✔️ Validación de campos requeridos
- 🔔 Alertas con SweetAlert2
- ♿ Accesible y fácil de usar

## 🛠️ Tecnologías Utilizadas

- **Frontend:**
  - Bootstrap 5.3.5
  - Bootstrap Icons
  - jQuery 3.6.0
  - DataTables 1.11.5
  - SweetAlert2
  - Signature Pad 4.1.7

- **Backend:**
  - PHP 7.4+
  - MySQL/MariaDB

- **Exportación:**
  - PhpSpreadsheet (PhpOffice)

## 🔧 Funcionalidades

1. **Agregar Registro** - Modal con formulario completo
2. **Ver Detalle** - Visualiza información del registro
3. **Eliminar Registro** - Con confirmación SweetAlert
4. **Exportar a Excel** - Todos los registros con formato

## 📌 Notas Importantes

- La firma es **obligatoria** para guardar el registro
- El campo "Total General" se calcula automáticamente
- Los campos marcados con * son obligatorios
- Las firmas se guardan en formato base64 en la base de datos
- Para ver las firmas, se puede implementar una vista de detalle adicional

## 🐛 Solución de Problemas

**Problema:** No se muestra el canvas de firma
- **Solución:** Verifica que la librería Signature Pad esté cargando correctamente

**Problema:** Error al exportar a Excel
- **Solución:** Asegúrate de tener instalado PhpSpreadsheet vía Composer

**Problema:** No se guardan los datos
- **Solución:** Verifica que la tabla esté creada correctamente en la base de datos

## 📞 Soporte

Para más información o reportar errores, contacta al equipo de desarrollo.
