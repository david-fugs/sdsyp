<?php
include("conexion.php");

echo "<h2>Actualizando estructura de base de datos...</h2>";

// 1. Agregar la nueva columna id_actividad a la tabla acciones
$sql1 = "ALTER TABLE acciones ADD COLUMN id_actividad INT";
if ($mysqli->query($sql1)) {
    echo "<p>✓ Columna id_actividad agregada exitosamente a la tabla acciones</p>";
} else {
    if (strpos($mysqli->error, "Duplicate column name") !== false) {
        echo "<p>⚠ La columna id_actividad ya existe en la tabla acciones</p>";
    } else {
        echo "<p>✗ Error agregando columna id_actividad: " . $mysqli->error . "</p>";
    }
}

// 2. Opcional: Crear clave foránea (descomenta si quieres usar claves foráneas)
/*
$sql2 = "ALTER TABLE acciones ADD CONSTRAINT fk_acciones_actividades 
         FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad) ON DELETE CASCADE";
if ($mysqli->query($sql2)) {
    echo "<p>✓ Clave foránea creada exitosamente</p>";
} else {
    echo "<p>✗ Error creando clave foránea: " . $mysqli->error . "</p>";
}
*/

// 3. Migrar datos existentes (asignar actividades a acciones basándose en las metas)
echo "<h3>Migrando datos existentes...</h3>";

// Verificar si hay datos para migrar
$check_sql = "SELECT COUNT(*) as total FROM acciones WHERE id_meta IS NOT NULL AND id_actividad IS NULL";
$result = $mysqli->query($check_sql);
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    // Migrar: asignar la primera actividad de cada meta a las acciones de esa meta
    $migrate_sql = "UPDATE acciones a
                    JOIN (
                        SELECT id_meta, MIN(id_actividad) as id_actividad 
                        FROM actividades 
                        GROUP BY id_meta
                    ) act ON a.id_meta = act.id_meta
                    SET a.id_actividad = act.id_actividad
                    WHERE a.id_actividad IS NULL";
    
    if ($mysqli->query($migrate_sql)) {
        echo "<p>✓ Datos migrados exitosamente. Se asignaron actividades a " . $mysqli->affected_rows . " acciones</p>";
    } else {
        echo "<p>✗ Error migrando datos: " . $mysqli->error . "</p>";
    }
} else {
    echo "<p>⚠ No hay datos para migrar o ya fueron migrados</p>";
}

// 4. Opcional: Eliminar la columna id_meta (descomenta cuando estés seguro de que todo funciona)
/*
echo "<h3>Eliminando columna id_meta...</h3>";
$sql3 = "ALTER TABLE acciones DROP COLUMN id_meta";
if ($mysqli->query($sql3)) {
    echo "<p>✓ Columna id_meta eliminada exitosamente</p>";
} else {
    echo "<p>✗ Error eliminando columna id_meta: " . $mysqli->error . "</p>";
}
*/

echo "<h3>Proceso completado!</h3>";
echo "<p><a href='code/action/seeActions.php'>Ir a ver las acciones</a></p>";

$mysqli->close();
?>
