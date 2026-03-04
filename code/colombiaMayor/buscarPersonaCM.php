<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'encontrada' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    
    // Buscar persona en Colombia Mayor (excluir solo fallecidos y retiros definitivos)
    $query = "SELECT 
                cedula_persona_cm, 
                nombres_persona_cm, 
                apellidos_persona_cm, 
                genero_persona_cm,
                estado_cm 
              FROM personas_colombia_mayor 
              WHERE cedula_persona_cm = '$cedula'
              AND (condicion_componente IS NULL 
                   OR condicion_componente NOT LIKE '%Fallecido%' 
                   AND condicion_componente NOT LIKE '%Retiro Definitivo%')";
    
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        $persona = $result->fetch_assoc();
        $nombre_completo = $persona['nombres_persona_cm'] . ' ' . $persona['apellidos_persona_cm'];
        echo json_encode([
            'success' => true,
            'encontrada' => true,
            'nombres' => $persona['nombres_persona_cm'],
            'apellidos' => $persona['apellidos_persona_cm'],
            'nombre_completo' => $nombre_completo,
            'estado' => $persona['estado_cm'],
            'persona' => [
                'cedula' => $cedula,
                'nombre_completo' => $nombre_completo,
                'nombres' => $persona['nombres_persona_cm'],
                'apellidos' => $persona['apellidos_persona_cm'],
                'genero' => $persona['genero_persona_cm'] ?? 'N/A',
                'estado' => $persona['estado_cm']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'encontrada' => false,
            'message' => 'No se encontró una persona con esa cédula en Colombia Mayor'
        ]);
    }
}
?>
