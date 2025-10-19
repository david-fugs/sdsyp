<?php
require 'conexion.php';

$cedula = '34057604';

$query = "SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.id_grupo, g.descripcion_grupo 
          FROM personas p 
          LEFT JOIN grupos g ON p.id_grupo = g.id_grupo 
          WHERE p.cedula_persona = '$cedula'";

$result = $mysqli->query($query);

if ($row = $result->fetch_assoc()) {
    echo "Cédula: " . $row['cedula_persona'] . "\n";
    echo "Nombre: " . $row['nombres_persona'] . " " . $row['apellidos_persona'] . "\n";
    echo "ID Grupo: " . $row['id_grupo'] . "\n";
    echo "Grupo: " . $row['descripcion_grupo'] . "\n";
} else {
    echo "No encontrado\n";
}

$mysqli->close();
?>
