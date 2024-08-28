<?php
session_start();
include("../../conexion.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

$num_doc_est = $_GET['num_doc_est'] ?? '';

if ($num_doc_est) {
    $query = "UPDATE prePostnatales SET estado_prePostnatales = 0 WHERE num_doc_est = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $num_doc_est);
    if ($stmt->execute()) {
        echo "El estado del estudiante con documento $num_doc_est ha sido actualizado.";
    } else {
        echo "Error al actualizar el estado del estudiante: " . $mysqli->error;
    }
    $stmt->close();
} else {
    echo "No se ha proporcionado un documento válido.";
}

$mysqli->close();
//puse la redireccion mejor en el js para que poder usar la funcion en los demas archivos
// header("Location: showprePostnatales.php");
?>
