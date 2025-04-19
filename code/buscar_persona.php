<?php
include("../conexion.php"); 
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST['cedula'] ?? '');

    if ($cedula === '') {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Cédula vacía']);
        exit;
    }

    // Preparar consulta para evitar inyecciones SQL
    $stmt = $mysqli->prepare("SELECT nombres_persona, apellidos_persona FROM personas WHERE cedula_persona = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($row = $resultado->fetch_assoc()) {
        echo json_encode([
            'encontrado' => true,
            'nombres' => $row['nombres_persona'],
            'apellidos' => $row['apellidos_persona']
        ]);
    } else {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Persona no encontrada']);
    }

    $stmt->close();
} else {
    echo json_encode(['encontrado' => false, 'mensaje' => 'Método no permitido']);
}
?>
