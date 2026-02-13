<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $usuario_id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    // Consultar registro
    $sql = "SELECT r.*, 
            CONCAT(p.nombres_persona_cm, ' ', p.apellidos_persona_cm) as nombre_completo
            FROM registros_individuales_cm r
            INNER JOIN personas_colombia_mayor p ON r.cedula_persona_cm = p.cedula_persona_cm
            WHERE r.id_registro_individual_cm = ?";
    
    // Si es contratista (tipo 9), solo puede ver sus propios registros
    if ($tipo_usuario == 9) {
        $sql .= " AND r.usuario_registro = $usuario_id";
    }
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado o sin permisos']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}

$mysqli->close();
?>
