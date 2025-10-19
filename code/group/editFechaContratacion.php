<?php
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id_fecha']) || !isset($_POST['fecha_contratacion'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_fecha = intval($_POST['id_fecha']);
$fecha_contratacion = $_POST['fecha_contratacion'];

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_contratacion)) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

$query = "UPDATE historial_fechas_contratacion SET fecha_contratacion = ? WHERE id_fecha_contratacion = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $fecha_contratacion, $id_fecha);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Fecha actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la fecha o no hubo cambios']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la fecha: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>
