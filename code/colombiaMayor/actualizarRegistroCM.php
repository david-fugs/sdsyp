<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_registro = intval($_POST['id_registro']);
    $cedula_persona_cm = $mysqli->real_escape_string($_POST['cedula_persona_cm']);
    $id_condicion = intval($_POST['id_condicion']);
    $id_meta = intval($_POST['id_meta']);
    $id_actividad = intval($_POST['id_actividad']);
    $id_accion = intval($_POST['id_accion']);
    $id_politica_publica = !empty($_POST['id_politica_publica']) ? intval($_POST['id_politica_publica']) : NULL;
    $fecha_registro_actividad = $_POST['fecha_registro_actividad'];
    $observaciones = $mysqli->real_escape_string($_POST['observaciones'] ?? '');
    
    $usuario_id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    // Validar permisos si es contratista (tipo 9)
    if ($tipo_usuario == 9) {
        $sql_check = "SELECT usuario_registro FROM registros_individuales_cm WHERE id_registro_individual_cm = ?";
        $stmt_check = $mysqli->prepare($sql_check);
        $stmt_check->bind_param("i", $id_registro);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $registro = $result_check->fetch_assoc();
            if ($registro['usuario_registro'] != $usuario_id) {
                echo json_encode(['success' => false, 'message' => 'No tiene permisos para editar este registro']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            exit();
        }
        $stmt_check->close();
    }
    
    // Actualizar registro
    $sql = "UPDATE registros_individuales_cm SET 
            cedula_persona_cm = ?,
            id_condicion = ?,
            id_meta = ?,
            id_actividad = ?,
            id_accion = ?,
            id_politica_publica = ?,
            fecha_registro_actividad = ?,
            observaciones = ?
            WHERE id_registro_individual_cm = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("siiiiissi", 
        $cedula_persona_cm,
        $id_condicion,
        $id_meta,
        $id_actividad,
        $id_accion,
        $id_politica_publica,
        $fecha_registro_actividad,
        $observaciones,
        $id_registro
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $mysqli->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$mysqli->close();
?>
