<?php
session_start();
require_once('../../conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$cedulas_raw = $_POST['cedulas'] ?? '[]';
$fecha       = trim($_POST['fecha_asistencia'] ?? '');
$id_usuario  = intval($_SESSION['id']);

// Validar fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'message' => 'Fecha inválida']);
    exit;
}

$cedulas = json_decode($cedulas_raw, true);
if (!is_array($cedulas) || count($cedulas) === 0) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron cédulas']);
    exit;
}

// Limpiar registros previos del mismo usuario y fecha (reemplazar)
$stmtDel = $mysqli->prepare("DELETE FROM control_asistencia WHERE fecha_asistencia = ? AND id_usuario = ?");
if (!$stmtDel) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . $mysqli->error]);
    exit;
}
$stmtDel->bind_param('si', $fecha, $id_usuario);
$stmtDel->execute();
$stmtDel->close();

// Insertar nuevos registros
$stmtIns = $mysqli->prepare("INSERT INTO control_asistencia (cedula_persona, fecha_asistencia, id_usuario) VALUES (?, ?, ?)");
if (!$stmtIns) {
    echo json_encode(['success' => false, 'message' => 'Error preparando inserción: ' . $mysqli->error]);
    exit;
}

$errors   = 0;
$inserted = 0;
foreach ($cedulas as $cedula) {
    $cedula = strval($cedula);
    if (empty($cedula)) continue;
    $stmtIns->bind_param('ssi', $cedula, $fecha, $id_usuario);
    if ($stmtIns->execute()) {
        $inserted++;
    } else {
        $errors++;
    }
}
$stmtIns->close();

echo json_encode([
    'success' => $errors === 0,
    'message' => $errors === 0 ? 'Asistencia guardada correctamente' : "Guardado con $errors error(es)",
    'count'   => $inserted
]);
