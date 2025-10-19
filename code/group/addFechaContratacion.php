<?php
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id_grupo']) || !isset($_POST['fecha_contratacion'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_grupo = intval($_POST['id_grupo']);
$fecha_contratacion = $_POST['fecha_contratacion'];

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_contratacion)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

$query = "INSERT INTO historial_fechas_contratacion (id_grupo, fecha_contratacion) VALUES (?, ?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $id_grupo, $fecha_contratacion);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Fecha agregada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al agregar la fecha: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>
