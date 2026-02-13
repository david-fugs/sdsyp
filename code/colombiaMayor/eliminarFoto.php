<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_foto'])) {
    $id_foto = intval($_POST['id_foto']);
    
    // Obtener información de la foto
    $sql = "SELECT ruta_foto FROM fotos_registros_cm WHERE id_foto = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_foto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $foto = $result->fetch_assoc();
        $ruta_foto = 'uploads/fotos_registros/' . $foto['ruta_foto'];
        
        // Eliminar de la base de datos
        $sql_delete = "DELETE FROM fotos_registros_cm WHERE id_foto = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_foto);
        
        if ($stmt_delete->execute()) {
            // Eliminar archivo físico si existe
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto);
            }
            
            echo json_encode(['success' => true, 'message' => 'Fotografía eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la fotografía de la base de datos']);
        }
        
        $stmt_delete->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Fotografía no encontrada']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}

$mysqli->close();
?>
