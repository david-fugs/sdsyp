<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

include("../../conexion.php");

$id = $_GET['id'];
$campo = $_GET['campo'];
if($campo == "prepost"){
    $tabla = 'prepostnatales';
    $id_campo = 'id_prePostnatales';
    $ruta = 'viewSurveys.php?num_doc_est=' . $_GET['num_doc_est'];
}
else if($campo == "familia"){
    $tabla = 'familiasalud';
    $id_campo = 'id_salud_familiaSalud';
    $ruta = 'viewHealthFamilySurvey.php?num_doc_est=' . $_GET['num_doc_est'];
    
}
else if($campo == "desempeno"){
    $tabla = 'desempeno';
    $id_campo = 'id_desempeno';
    $ruta = 'viewPerformanceSurvey.php?num_doc_est=' . $_GET['num_doc_est'];
}
else if($campo == "educacion"){
    $tabla = 'educacion';
    $id_campo = 'id_educacion';
    $ruta = 'viewEducationSurvey.php?num_doc_est=' . $_GET['num_doc_est'];
}


if ($stmt = $mysqli->prepare("DELETE FROM $tabla WHERE $id_campo = ?")) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Encuesta eliminada correctamente.'); window.location.href = ' 
    $ruta';</script>";

} else {
    echo "<script>alert('Error al eliminar la encuesta.'); window.location.href = '
    $ruta';</script>";
}


$mysqli->close();
?>
