<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['tipo_usuario'], [1, 8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id = $mysqli->real_escape_string($_GET['id']);
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Validar permisos
if($tipo_usuario == 9) {
    $sql = "SELECT r.*, 
            CONCAT(p.nombre, ' ', p.apellido) as persona_nombre,
            p.cedula as persona_cedula
            FROM registros_individuales_cm r
            INNER JOIN personas_colombia_mayor p ON r.id_persona = p.id
            WHERE r.id = '$id' AND r.usuario_registro = '$usuario_id'";
} else {
    $sql = "SELECT r.*, 
            CONCAT(p.nombre, ' ', p.apellido) as persona_nombre,
            p.cedula as persona_cedula
            FROM registros_individuales_cm r
            INNER JOIN personas_colombia_mayor p ON r.id_persona = p.id
            WHERE r.id = '$id'";
}

$result = $mysqli->query($sql);

if($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
}

$mysqli->close();
?>
