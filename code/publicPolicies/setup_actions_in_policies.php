<?php
include("../../conexion.php");

echo "<h2>Configurando Acciones dentro de Políticas Públicas...</h2>";

// 1. Agregar la columna id_politica a la tabla acciones si no existe
$sql_check = "SHOW COLUMNS FROM acciones LIKE 'id_politica'";
$result_check = $mysqli->query($sql_check);

if ($result_check->num_rows == 0) {
    $sql1 = "ALTER TABLE acciones ADD COLUMN id_politica INT";
    if ($mysqli->query($sql1)) {
        echo "<p>✓ Columna id_politica agregada exitosamente a la tabla acciones</p>";
    } else {
        echo "<p>✗ Error agregando columna id_politica: " . $mysqli->error . "</p>";
    }
} else {
    echo "<p>⚠ La columna id_politica ya existe en la tabla acciones</p>";
}

// 2. Verificar estructura de las tablas
echo "<h3>Estructura actual de la tabla acciones:</h3>";
$structure = mysqli_query($mysqli, "DESCRIBE acciones");
if ($structure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error verificando estructura: " . mysqli_error($mysqli);
}

// 3. Mostrar políticas públicas disponibles
echo "<h3>Políticas Públicas disponibles:</h3>";
$politicas = mysqli_query($mysqli, "SELECT * FROM politicas_publicas ORDER BY id_politica");
if ($politicas && mysqli_num_rows($politicas) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Descripción</th></tr>";
    while ($row = mysqli_fetch_assoc($politicas)) {
        echo "<tr>";
        echo "<td>" . $row['id_politica'] . "</td>";
        echo "<td>" . $row['descripcion_politica'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠ No hay políticas públicas registradas. <a href='seePublicPolicies.php'>Crear políticas públicas primero</a></p>";
}

// 4. Mostrar acciones existentes
echo "<h3>Acciones existentes:</h3>";
$acciones = mysqli_query($mysqli, "SELECT a.*, act.descripcion_actividad, pp.descripcion_politica 
                                  FROM acciones a 
                                  LEFT JOIN actividades act ON a.id_actividad = act.id_actividad
                                  LEFT JOIN politicas_publicas pp ON a.id_politica = pp.id_politica
                                  ORDER BY a.id_accion");
if ($acciones && mysqli_num_rows($acciones) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Acción</th><th>Descripción</th><th>Actividad</th><th>Política Pública</th></tr>";
    while ($row = mysqli_fetch_assoc($acciones)) {
        echo "<tr>";
        echo "<td>" . $row['id_accion'] . "</td>";
        echo "<td>" . $row['descripcion_accion'] . "</td>";
        echo "<td>" . ($row['descripcion_actividad'] ?? 'Sin actividad') . "</td>";
        echo "<td>" . ($row['descripcion_politica'] ?? 'Sin política asignada') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠ No hay acciones registradas.</p>";
}

echo "<h3>Proceso completado!</h3>";
echo "<p><a href='seePublicPolicies.php'>Ir a gestionar Políticas Públicas y Acciones</a></p>";

$mysqli->close();
?>
