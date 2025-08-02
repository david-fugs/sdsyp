<?php
header('Content-Type: application/json');
include("../../conexion.php");

$id_accion = isset($_POST['id_accion']) ? intval($_POST['id_accion']) : 0;
$response = ["politicas" => []];

if ($id_accion > 0) {
    // Buscar todas las políticas públicas relacionadas con la acción
    $query = "SELECT pp.id_politica, pp.descripcion_politica FROM politicas_publicas pp WHERE pp.id_accion = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_accion);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response["politicas"][] = $row;
    }
    $stmt->close();
}

echo json_encode($response);
