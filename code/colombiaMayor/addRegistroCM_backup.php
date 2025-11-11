<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id_persona = $mysqli->real_escape_string($_POST['id_persona']);
$id_condicion = $mysqli->real_escape_string($_POST['id_condicion']);
$id_meta = $mysqli->real_escape_string($_POST['id_meta']);
$id_actividad = $mysqli->real_escape_string($_POST['id_actividad']);
$id_accion = $mysqli->real_escape_string($_POST['id_accion']);
$fecha_registro = $mysqli->real_escape_string($_POST['fecha_registro']);
$observaciones = $mysqli->real_escape_string($_POST['observaciones']);
$usuario_registro = $_SESSION['id'];

// Validar que la persona existe
$sql_persona = "SELECT id FROM personas_colombia_mayor WHERE id = '$id_persona'";
$result_persona = $mysqli->query($sql_persona);

if($result_persona->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Persona no encontrada']);
    exit();
}

// Insertar registro
$sql = "INSERT INTO registros_individuales_cm (
    id_persona, 
    id_condicion, 
    id_meta, 
    id_actividad, 
    id_accion, 
    fecha_registro, 
    observaciones, 
    usuario_registro
) VALUES (
    '$id_persona',
    '$id_condicion',
    '$id_meta',
    '$id_actividad',
    '$id_accion',
    '$fecha_registro',
    '$observaciones',
    '$usuario_registro'
)";

if($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Registro guardado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $mysqli->error]);
}

$mysqli->close();
?>
