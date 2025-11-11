<?php
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    
    $query = "SELECT cedula_persona_cm, nombres_persona_cm, apellidos_persona_cm, estado_cm 
              FROM personas_colombia_mayor 
              WHERE cedula_persona_cm = '$cedula'";
    
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        $persona = $result->fetch_assoc();
        $nombre_completo = $persona['nombres_persona_cm'] . ' ' . $persona['apellidos_persona_cm'];
        echo json_encode([
            'encontrada' => true,
            'nombres' => $persona['nombres_persona_cm'],
            'apellidos' => $persona['apellidos_persona_cm'],
            'nombre_completo' => $nombre_completo,
            'estado' => $persona['estado_cm']
        ]);
    } else {
        echo json_encode([
            'encontrada' => false
        ]);
    }
}
?>
