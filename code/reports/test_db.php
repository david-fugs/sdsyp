<?php
// Archivo de prueba para verificar datos de informes
include("../../conexion.php");

echo "<h2>Prueba de conexión y consultas para informes</h2>";

// Verificar conexión
if ($mysqli->connect_error) {
    echo "<p style='color: red;'>Error de conexión: " . $mysqli->connect_error . "</p>";
    exit;
}
echo "<p style='color: green;'>Conexión exitosa</p>";

// Probar consulta básica de personas
$query_test = "SELECT COUNT(*) as total FROM personas";
$result = $mysqli->query($query_test);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Total personas en la base de datos: " . $row['total'] . "</p>";
} else {
    echo "<p style='color: red;'>Error en consulta de personas: " . $mysqli->error . "</p>";
}

// Probar consulta de movimientos
$query_mov = "SELECT COUNT(*) as total FROM movimiento_persona";
$result_mov = $mysqli->query($query_mov);
if ($result_mov) {
    $row_mov = $result_mov->fetch_assoc();
    echo "<p>Total movimientos en la base de datos: " . $row_mov['total'] . "</p>";
} else {
    echo "<p style='color: red;'>Error en consulta de movimientos: " . $mysqli->error . "</p>";
}

// Probar consulta de personas por año
$year = 2024;
$query_year = "SELECT COUNT(*) as total FROM personas WHERE YEAR(fecha_registro) = $year";
$result_year = $mysqli->query($query_year);
if ($result_year) {
    $row_year = $result_year->fetch_assoc();
    echo "<p>Personas registradas en $year: " . $row_year['total'] . "</p>";
} else {
    echo "<p style='color: red;'>Error en consulta por año: " . $mysqli->error . "</p>";
}

// Mostrar estructura de tabla personas
echo "<h3>Estructura de tabla 'personas':</h3>";
$desc_query = "DESCRIBE personas";
$desc_result = $mysqli->query($desc_query);
if ($desc_result) {
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $desc_result->fetch_assoc()) {
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
}

// Mostrar estructura de tabla movimiento_persona
echo "<h3>Estructura de tabla 'movimiento_persona':</h3>";
$desc_query2 = "DESCRIBE movimiento_persona";
$desc_result2 = $mysqli->query($desc_query2);
if ($desc_result2) {
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $desc_result2->fetch_assoc()) {
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
}

$mysqli->close();
?>
