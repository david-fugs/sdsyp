<?php
include("../../conexion.php");
header('Content-Type: application/json');

if (!isset($_GET['id_grupo'])) {
    echo json_encode(['success' => false, 'message' => 'ID de grupo no proporcionado']);
    exit;
}

$id_grupo = intval($_GET['id_grupo']);

$query = "SELECT id_fecha_contratacion, id_grupo, fecha_contratacion, 
          DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at
          FROM historial_fechas_contratacion 
          WHERE id_grupo = ? 
          ORDER BY fecha_contratacion DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_grupo);
$stmt->execute();
$result = $stmt->get_result();

$fechas = [];
while ($row = $result->fetch_assoc()) {
    $fechas[] = $row;
}

echo json_encode(['success' => true, 'data' => $fechas]);

$stmt->close();
$mysqli->close();
?>
