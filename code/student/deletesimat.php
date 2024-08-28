<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

include("../../conexion.php");

if (isset($_GET['num_doc_est'])) {
    $num_doc_est = $_GET['num_doc_est'];
    $query = "UPDATE estudiantes SET estado_est = 0 WHERE num_doc_est = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $num_doc_est);
    if ($stmt->execute()) {
        header("Location: showsimat.php");
        exit();
    } else {
        echo "Error al inactivar el estudiante: " . $stmt->error;
    }
} else {
    echo "Número de documento no proporcionado.";
}
?>
