<?php
include("../../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_grupo = $_POST['id_grupo'];
    
    // Obtener información del grupo
    $query_grupo = "SELECT descripcion_grupo, limite_personas FROM grupos WHERE id_grupo = ?";
    $stmt_grupo = $mysqli->prepare($query_grupo);
    $stmt_grupo->bind_param("i", $id_grupo);
    $stmt_grupo->execute();
    $result_grupo = $stmt_grupo->get_result();
    $grupo = $result_grupo->fetch_assoc();
    
    // Contar personas actuales en el grupo
    $query_count = "SELECT COUNT(*) as total FROM personas WHERE id_grupo = ? AND estado_persona = 1";
    $stmt_count = $mysqli->prepare($query_count);
    $stmt_count->bind_param("i", $id_grupo);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $count = $result_count->fetch_assoc();
    
    $personas_actuales = $count['total'];
    $limite_personas = $grupo['limite_personas'];
    
    $response = array(
        'limitReached' => $personas_actuales >= $limite_personas,
        'personasActuales' => $personas_actuales,
        'limite' => $limite_personas,
        'grupoNombre' => $grupo['descripcion_grupo']
    );
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
    $stmt_grupo->close();
    $stmt_count->close();
    $mysqli->close();
}
?>
