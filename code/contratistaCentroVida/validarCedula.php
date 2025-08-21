<?php
include("../../conexion.php");

header('Content-Type: application/json');

if (isset($_POST['cedula_persona']) && !empty($_POST['cedula_persona'])) {
    $cedula_persona = $_POST['cedula_persona'];
    
    // Preparar la consulta para verificar si la cédula existe
    $query = "SELECT cedula_persona, nombres_persona, apellidos_persona FROM personas WHERE cedula_persona = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $cedula_persona);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // La cédula existe
        $persona = $result->fetch_assoc();
        echo json_encode([
            'existe' => true,
            'persona' => $persona,
            'mensaje' => 'Cédula encontrada: ' . $persona['nombres_persona'] . ' ' . $persona['apellidos_persona']
        ]);
    } else {
        // La cédula no existe
        echo json_encode([
            'existe' => false,
            'mensaje' => 'La cédula ' . $cedula_persona . ' no está registrada en el sistema.'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'existe' => false,
        'mensaje' => 'Cédula no proporcionada.'
    ]);
}

$mysqli->close();
?>
