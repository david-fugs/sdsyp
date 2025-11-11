<?php
// Archivo de prueba para verificar cascadas
include("../../conexion.php");

echo "<h2>Prueba de Cascadas</h2>";

// Probar metas
echo "<h3>Metas:</h3>";
$query = "SELECT id_meta, descripcion_meta FROM metas ORDER BY id_meta";
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
    echo "Meta {$row['id_meta']}: {$row['descripcion_meta']}<br>";
}

// Probar actividades para meta 1
echo "<h3>Actividades para Meta 1:</h3>";
$id_meta = 1;
$query = "SELECT id_actividad, descripcion_actividad FROM actividades WHERE id_meta = $id_meta ORDER BY descripcion_actividad ASC";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Actividad {$row['id_actividad']}: {$row['descripcion_actividad']}<br>";
    }
} else {
    echo "No hay actividades para esta meta<br>";
}

// Probar acciones para actividad 1
echo "<h3>Acciones para Actividad 1:</h3>";
$id_actividad = 1;
$query = "SELECT id_accion, descripcion_accion FROM acciones WHERE id_actividad = $id_actividad ORDER BY descripcion_accion ASC";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Acción {$row['id_accion']}: {$row['descripcion_accion']}<br>";
    }
} else {
    echo "No hay acciones para esta actividad<br>";
}

$mysqli->close();
?>
