<?php
// Archivo de prueba para verificar búsqueda de barrios
header('Content-Type: text/html; charset=utf-8');

require_once '../../conexion.php';

echo "<h1>Test de Búsqueda de Barrios</h1>";

// Verificar conexión
if (!isset($mysqli) || $mysqli->connect_error) {
    echo "<p style='color:red'>Error de conexión: " . ($mysqli->connect_error ?? 'mysqli no definido') . "</p>";
    exit;
}

echo "<p style='color:green'>✓ Conexión exitosa</p>";

// Contar barrios
$sql_count = "SELECT COUNT(*) as total FROM barrios";
$result = $mysqli->query($sql_count);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Total de barrios en BD: <strong>" . $row['total'] . "</strong></p>";
} else {
    echo "<p style='color:red'>Error al contar barrios: " . $mysqli->error . "</p>";
}

// Buscar barrios con 'san'
$term = 'san';
$sql = "SELECT b.id_bar, b.nombre_bar, b.zona_bar, c.id_com, c.nombre_com 
        FROM barrios b 
        LEFT JOIN comunas c ON b.id_com = c.id_com 
        WHERE b.nombre_bar LIKE ? 
        LIMIT 10";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo "<p style='color:red'>Error al preparar consulta: " . $mysqli->error . "</p>";
    exit;
}

$like = "%$term%";
$stmt->bind_param('s', $like);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Resultados de búsqueda para 'san':</h2>";
echo "<table border='1' style='border-collapse:collapse; padding:10px;'>";
echo "<tr><th>ID Barrio</th><th>Nombre Barrio</th><th>Zona</th><th>ID Comuna</th><th>Nombre Comuna</th></tr>";

$count = 0;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id_bar'] . "</td>";
    echo "<td>" . $row['nombre_bar'] . "</td>";
    echo "<td>" . ($row['zona_bar'] ?? 'N/A') . "</td>";
    echo "<td>" . ($row['id_com'] ?? 'N/A') . "</td>";
    echo "<td>" . ($row['nombre_com'] ?? 'N/A') . "</td>";
    echo "</tr>";
    $count++;
}

echo "</table>";
echo "<p>Total encontrados: <strong>$count</strong></p>";

// Test de buscar_barrio.php
echo "<h2>Test de buscar_barrio.php</h2>";
$url = '../persons/buscar_barrio.php?term=san';
echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";
?>
