<?php
include("../../conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id_fecha'])) {
    echo json_encode(['success' => false, 'message' => 'ID de fecha no proporcionado']);
    exit;
}

$id_fecha = intval($_POST['id_fecha']);

$query = "DELETE FROM historial_fechas_contratacion WHERE id_fecha_contratacion = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_fecha);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Fecha eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la fecha']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la fecha: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>
