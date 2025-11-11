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
$id_persona = $mysqli->real_escape_string($_POST['id_persona']);
$id_condicion = $mysqli->real_escape_string($_POST['id_condicion']);
$id_meta = $mysqli->real_escape_string($_POST['id_meta']);
$id_actividad = $mysqli->real_escape_string($_POST['id_actividad']);
$id_accion = $mysqli->real_escape_string($_POST['id_accion']);
$fecha_registro = $mysqli->real_escape_string($_POST['fecha_registro']);
$observaciones = $mysqli->real_escape_string($_POST['observaciones']);

// Validar permisos
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

if($tipo_usuario == 9) {
    // Contratista solo puede editar sus propios registros
    $sql_check = "SELECT usuario_registro FROM registros_individuales_cm WHERE id = '$id'";
    $result_check = $mysqli->query($sql_check);
    $registro = $result_check->fetch_assoc();
    
    if($registro['usuario_registro'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para editar este registro']);
        exit();
    }
}

// Actualizar registro
$sql = "UPDATE registros_individuales_cm SET 
    id_persona = '$id_persona',
    id_condicion = '$id_condicion',
    id_meta = '$id_meta',
    id_actividad = '$id_actividad',
    id_accion = '$id_accion',
    fecha_registro = '$fecha_registro',
    observaciones = '$observaciones'
WHERE id = '$id'";

if($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Registro actualizado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $mysqli->error]);
}

$mysqli->close();
?>
