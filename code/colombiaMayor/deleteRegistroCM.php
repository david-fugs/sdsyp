<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id = $mysqli->real_escape_string($_POST['id']);
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Validar permisos
if($tipo_usuario == 9) {
    $sql_check = "SELECT usuario_registro FROM registros_individuales_cm WHERE id = '$id'";
    $result_check = $mysqli->query($sql_check);
    $registro = $result_check->fetch_assoc();
    
    if($registro['usuario_registro'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar este registro']);
        exit();
    }
}

// Eliminar registro
$sql = "DELETE FROM registros_individuales_cm WHERE id = '$id'";

if($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Registro eliminado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $mysqli->error]);
}

$mysqli->close();
?>
