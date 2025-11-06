<?php
session_start();
include("../../conexion.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_registro = isset($_POST['id_registro']) ? intval($_POST['id_registro']) : 0;

if ($id_registro <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de registro inválido']);
    exit;
}

$query = "DELETE FROM registro_actividades WHERE id_registro = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
    exit;
}

$stmt->bind_param("i", $id_registro);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el registro']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro']);
}

$stmt->close();
$mysqli->close();
?>
