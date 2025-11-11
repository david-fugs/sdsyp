<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 8 && $_SESSION['tipo_usuario'] != 9)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

$id_detalle = $mysqli->real_escape_string($_POST['id_detalle']);
$estado = $mysqli->real_escape_string($_POST['estado']);
$motivo = isset($_POST['motivo']) ? $mysqli->real_escape_string($_POST['motivo']) : '';

if($estado == 'COBRADO') {
    $sql = "UPDATE detalle_pagos_cm SET 
            estado_cobro = '$estado',
            fecha_cobro = NOW()
            WHERE id = '$id_detalle'";
} else {
    $sql = "UPDATE detalle_pagos_cm SET 
            estado_cobro = '$estado',
            observaciones = '$motivo'
            WHERE id = '$id_detalle'";
}

if($mysqli->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $mysqli->error]);
}

$mysqli->close();
?>
