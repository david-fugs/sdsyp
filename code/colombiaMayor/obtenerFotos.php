<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

if (isset($_GET['id_registro']) && isset($_GET['tipo'])) {
    $id_registro = intval($_GET['id_registro']);
    $tipo = $_GET['tipo']; // 'individual' o 'masivo'
    
    $sql = "";
    if ($tipo === 'individual') {
        $sql = "SELECT id_foto, ruta_foto, fecha_subida 
                FROM fotos_registros_cm 
                WHERE id_registro_individual = ? 
                ORDER BY fecha_subida ASC";
    } else if ($tipo === 'masivo') {
        $sql = "SELECT id_foto, ruta_foto, fecha_subida 
                FROM fotos_registros_cm 
                WHERE id_registro_masivo = ? 
                ORDER BY fecha_subida ASC";
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de registro inválido']);
        exit();
    }
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_registro);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $fotos = [];
    while ($row = $result->fetch_assoc()) {
        $fotos[] = [
            'id_foto' => $row['id_foto'],
            'ruta' => 'code/colombiaMayor/uploads/fotos_registros/' . $row['ruta_foto'],
            'fecha_subida' => $row['fecha_subida']
        ];
    }
    
    echo json_encode(['success' => true, 'fotos' => $fotos]);
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
}

$mysqli->close();
?>
