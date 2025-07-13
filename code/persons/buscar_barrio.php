<?php
// buscar_barrio.php

header('Content-Type: application/json');
require_once '../../conexion.php';

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
if ($term === '') {
    echo json_encode([]);
    exit;
}

// Usar $mysqli del archivo de conexión
if (!isset($mysqli) || $mysqli->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

$sql = "SELECT b.id_bar, b.nombre_bar, b.zona_bar, c.id_com, c.nombre_com FROM barrios b LEFT JOIN comunas c ON b.id_com = c.id_com WHERE b.nombre_bar LIKE ? LIMIT 10";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error en la consulta: " . $mysqli->error]);
    exit;
}
$like = "%$term%";
$stmt->bind_param('s', $like);
$stmt->execute();
$result = $stmt->get_result();

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
