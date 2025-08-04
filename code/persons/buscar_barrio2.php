<?php
// buscar_barrio.php

header('Content-Type: application/json');
require_once '../../conexion.php';

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
if ($term === '') {
    echo json_encode([]);
    exit;
}

if (!isset($mysqli) || $mysqli->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

$term_esc = $mysqli->real_escape_string($term);
$sql = "SELECT b.id_bar, b.nombre_bar, b.zona_bar, c.id_com, c.nombre_com FROM barrios b LEFT JOIN comunas c ON b.id_com = c.id_com WHERE b.id_bar LIKE '%$term_esc%' LIMIT 10";
$result = $mysqli->query($sql);
if (!$result) {
    echo json_encode(["error" => "Error en la consulta: " . $mysqli->error]);
    exit;
}

$barrios = [];
while ($row = $result->fetch_assoc()) {
    $barrios[] = [
        'id_bar' => $row['id_bar'],
        'nombre_bar' => $row['nombre_bar'],
        'zona_bar' => $row['zona_bar'],
        'id_com' => $row['id_com'],
        'nombre_com' => $row['nombre_com']
    ];
}
echo json_encode($barrios);
