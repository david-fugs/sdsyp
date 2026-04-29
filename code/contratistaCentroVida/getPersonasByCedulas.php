<?php
session_start();
include("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'personas' => []]);
    exit;
}

$cedulas_raw = $_POST['cedulas'] ?? '[]';
$cedulas = json_decode($cedulas_raw, true);

if (!is_array($cedulas) || count($cedulas) === 0) {
    echo json_encode(['success' => false, 'personas' => [], 'message' => 'Sin cédulas']);
    exit;
}

// Sanitize each cedula
$cedulas_safe = array_map(function($c) use ($mysqli) {
    return "'" . $mysqli->real_escape_string(strval($c)) . "'";
}, $cedulas);

$in_clause = implode(',', $cedulas_safe);

$query = "SELECT cedula_persona, nombres_persona, apellidos_persona, genero_persona, jornada
          FROM personas
          WHERE cedula_persona IN ($in_clause)
          AND (estado_persona = 1 OR estado_persona IS NULL)
          ORDER BY nombres_persona ASC, apellidos_persona ASC";

$result = $mysqli->query($query);
$personas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $personas[] = $row;
    }
}

echo json_encode(['success' => true, 'personas' => $personas]);
