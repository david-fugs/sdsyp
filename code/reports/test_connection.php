<?php
include("../../conexion.php");

header('Content-Type: application/json');

try {
    // Test de conexión básica
    if ($mysqli->connect_error) {
        echo json_encode([
            'error' => 'Error de conexión: ' . $mysqli->connect_error,
            'status' => 'connection_failed'
        ]);
        exit;
    }
    
    // Test de consulta simple
    $query = "SELECT COUNT(*) as total FROM personas";
    $result = $mysqli->query($query);
    
    if (!$result) {
        echo json_encode([
            'error' => 'Error en consulta: ' . $mysqli->error,
            'status' => 'query_failed'
        ]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'status' => 'ok',
        'total_personas' => $row['total'],
        'mysql_version' => $mysqli->server_info
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Excepción: ' . $e->getMessage(),
        'status' => 'exception'
    ]);
}

$mysqli->close();
?>
