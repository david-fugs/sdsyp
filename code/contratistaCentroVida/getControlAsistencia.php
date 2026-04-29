<?php
session_start();
require_once('../../conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'cedulas' => []]);
    exit;
}

$fecha      = trim($_GET['fecha'] ?? '');
$id_usuario = intval($_SESSION['id']);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'cedulas' => [], 'message' => 'Fecha inválida']);
    exit;
}

$stmt = $mysqli->prepare("SELECT cedula_persona FROM control_asistencia WHERE fecha_asistencia = ? AND id_usuario = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'cedulas' => [], 'message' => $mysqli->error]);
    exit;
}
$stmt->bind_param('si', $fecha, $id_usuario);
$stmt->execute();
$result  = $stmt->get_result();
$cedulas = [];
while ($row = $result->fetch_assoc()) {
    $cedulas[] = $row['cedula_persona'];
}
$stmt->close();

echo json_encode([
    'success' => true,
    'cedulas' => $cedulas,
    'count'   => count($cedulas)
]);
