<?php
session_start();
include("../../conexion.php");

header('Content-Type: application/json');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "SELECT * FROM actividad_personalizada WHERE id_actividad_personalizada = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $registro = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'registro' => $registro
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registro no encontrado'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID no válido'
    ]);
}

$mysqli->close();
?>
