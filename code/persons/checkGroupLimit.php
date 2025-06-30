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
    
    // Contar personas actuales en el grupo (excluyendo las que tienen movimientos que liberan cupo)
    $query_count = "SELECT COUNT(*) as total 
                   FROM personas p
                   WHERE p.id_grupo = ? 
                   AND p.estado_persona = 1
                   AND p.cedula_persona NOT IN (
                       SELECT DISTINCT mp.cedula_persona 
                       FROM movimiento_persona mp
                       JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                       WHERE cc.descripcion_condicion IN (
                           'CPSAM EVADIDO', 
                           'CPSAM FALLECIDO', 
                           'CPSAM RETIRADO VOLUNTARIO', 
                           'CPSAM TRASLADADO'
                       )
                   )";
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
