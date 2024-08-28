<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

include("../../conexion.php");

$id_prePostnatales = $_GET['id_prePostnatales'];

if ($stmt = $mysqli->prepare("DELETE FROM prePostnatales WHERE id_prePostnatales = ?")) {
    $stmt->bind_param("i", $id_prePostnatales);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Encuesta eliminada correctamente.'); window.location.href = 'viewSurveys.php?num_doc_est=" . $_GET['num_doc_est'] . "';</script>";
} else {
    echo "<script>alert('Error al eliminar la encuesta.'); window.location.href = 'viewSurveys.php?num_doc_est=" . $_GET['num_doc_est'] . "';</script>";
}

$mysqli->close();
?>
