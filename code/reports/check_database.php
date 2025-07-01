<?php
include("../../conexion.php");

header('Content-Type: application/json');

try {
    $queries = [];
    
    // Verificar tablas
    $tables = ['personas', 'movimiento_persona', 'condiciones_componente', 'grupos', 'programas', 'persona_programa', 'politicas_publicas'];
    
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $result = $mysqli->query($query);
        $queries[$table] = $result->num_rows > 0 ? 'EXISTS' : 'NOT EXISTS';
    }
    
    // Verificar estructura de personas
    $query = "DESCRIBE personas";
    $result = $mysqli->query($query);
    $personas_fields = [];
    while ($row = $result->fetch_assoc()) {
        $personas_fields[] = $row['Field'];
    }
    
    // Verificar estructura de movimiento_persona
    $query = "DESCRIBE movimiento_persona";
    $result = $mysqli->query($query);
    $movimiento_fields = [];
    while ($row = $result->fetch_assoc()) {
        $movimiento_fields[] = $row['Field'];
    }
    
    // Contar registros
    $counts = [];
    foreach ($tables as $table) {
        if ($queries[$table] === 'EXISTS') {
            $query = "SELECT COUNT(*) as total FROM $table";
            $result = $mysqli->query($query);
            $row = $result->fetch_assoc();
            $counts[$table] = $row['total'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'tables' => $queries,
        'personas_fields' => $personas_fields,
        'movimiento_fields' => $movimiento_fields,
        'record_counts' => $counts
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

$mysqli->close();
?>
