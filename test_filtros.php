<?php
require_once 'conexion.php';

echo "=== GRUPOS EN LA BASE DE DATOS ===\n\n";

$result = $mysqli->query("SELECT id_grupo, descripcion_grupo FROM grupos ORDER BY descripcion_grupo");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['id_grupo'] . " - " . $row['descripcion_grupo'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}

echo "\n=== PRUEBA DE FILTRO TIPO 4 (TÉCNICO CPSAM) ===\n\n";

$query4 = "SELECT id_grupo, descripcion_grupo FROM grupos 
          WHERE descripcion_grupo LIKE 'CPSAM %' 
             OR descripcion_grupo = 'CPSAM'
             OR descripcion_grupo LIKE 'Otro %'
             OR descripcion_grupo = 'Otro'
             OR descripcion_grupo LIKE 'Contratista %'
             OR descripcion_grupo = 'Contratista'
          ORDER BY descripcion_grupo ASC";

$result4 = $mysqli->query($query4);
if ($result4) {
    while ($row = $result4->fetch_assoc()) {
        echo $row['id_grupo'] . " - " . $row['descripcion_grupo'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}

echo "\n=== PRUEBA DE FILTRO TIPO 5 (TÉCNICO CV) ===\n\n";

$query5 = "SELECT id_grupo, descripcion_grupo FROM grupos 
          WHERE descripcion_grupo LIKE 'CV %' 
             OR descripcion_grupo = 'CV'
             OR descripcion_grupo LIKE 'Otro %'
             OR descripcion_grupo = 'Otro'
             OR descripcion_grupo LIKE 'Contratista %'
             OR descripcion_grupo = 'Contratista'
          ORDER BY descripcion_grupo ASC";

$result5 = $mysqli->query($query5);
if ($result5) {
    while ($row = $result5->fetch_assoc()) {
        echo $row['id_grupo'] . " - " . $row['descripcion_grupo'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}

$mysqli->close();
?>
