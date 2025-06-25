<?php
include("../../conexion.php");

echo "<h2>Debug - Verificar Actividades</h2>";

$actividades = "SELECT * FROM actividades JOIN metas ON actividades.id_meta = metas.id_meta ORDER BY actividades.descripcion_actividad";
$result_actividades = mysqli_query($mysqli, $actividades);

if (!$result_actividades) {
    die("Error en la consulta: " . mysqli_error($mysqli));
}

echo "<h3>Actividades disponibles:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID Actividad</th><th>Descripción Actividad</th><th>ID Meta</th><th>Descripción Meta</th></tr>";

while ($row = mysqli_fetch_assoc($result_actividades)) {
    echo "<tr>";
    echo "<td>" . $row['id_actividad'] . "</td>";
    echo "<td>" . $row['descripcion_actividad'] . "</td>";
    echo "<td>" . $row['id_meta'] . "</td>";
    echo "<td>" . $row['descripcion_meta'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Verificar si la tabla acciones existe y su estructura
echo "<h3>Estructura de tabla acciones:</h3>";
$structure = mysqli_query($mysqli, "DESCRIBE acciones");
if ($structure) {
    echo "<table border='1'>";
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

mysqli_close($mysqli);
?>
