<?php
// Archivo para verificar la estructura de las tablas
include("../../conexion.php");

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Verificación de Estructura de Tablas</h1>";

// Verificar tabla personas
echo "<h2>Estructura de tabla 'personas':</h2>";
$result = $mysqli->query("DESCRIBE personas");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
    echo "Error al obtener estructura de personas: " . $mysqli->error;
}

// Verificar otras tablas importantes
$tablas = ['movimiento_persona', 'grupos', 'politicas_publicas', 'barrios', 'comunas', 'condiciones_componente', 'metas', 'actividades', 'acciones'];

foreach ($tablas as $tabla) {
    echo "<h2>Estructura de tabla '$tabla':</h2>";
    $result = $mysqli->query("DESCRIBE $tabla");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table><br>";
    } else {
        echo "Tabla '$tabla' no existe o error: " . $mysqli->error . "<br><br>";
    }
}

// Verificar algunos registros de ejemplo
echo "<h2>Registros de ejemplo - Personas (primeros 3):</h2>";
$result = $mysqli->query("SELECT * FROM personas WHERE estado_persona = 1 LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre><hr>";
    }
} else {
    echo "Error al obtener registros de personas: " . $mysqli->error;
}

$mysqli->close();
?>
