<?php
session_start();

if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id = intval($_POST['id']);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id   = $_SESSION['id'];

// Tipo 9 solo puede eliminar sus propios registros
if ($tipo_usuario == 9) {
    $stmt_check = $mysqli->prepare("SELECT usuario_registro FROM registros_masivos_cm WHERE id_registro_masivo_cm = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $registro = $result_check->fetch_assoc();
        if ($registro['usuario_registro'] != $usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar este registro']);
            exit();
        }
    }
    $stmt_check->close();
}

$mysqli->begin_transaction();

try {
     // Eliminar el registro masivo
    $stmt_delete = $mysqli->prepare("DELETE FROM registros_masivos_cm WHERE id_registro_masivo_cm = ?");
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows === 0) {
        throw new Exception("El registro no existe o ya fue eliminado");
    }
    $stmt_delete->close();

    $mysqli->commit();
    echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
