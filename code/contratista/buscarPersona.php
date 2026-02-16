<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $mysqli->real_escape_string($_POST['cedula']);
    
    // Obtener tipo de usuario y aplicar filtro de grupos
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    $where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
    
    // Buscar persona habilitada en grupos CPSAM/Contratista
    $query = "SELECT 
        p.cedula_persona, 
        p.nombres_persona, 
        p.apellidos_persona,
        p.genero_persona,
        g.descripcion_grupo,
        (SELECT cc.descripcion_condicion 
         FROM movimiento_persona mp 
         JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
         WHERE mp.cedula_persona = p.cedula_persona 
         AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
         ORDER BY mp.fecha_movimiento DESC 
         LIMIT 1) AS estado_inactivo
    FROM personas p
    LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
    WHERE p.cedula_persona = '$cedula'
    AND p.estado_persona = 1
    $where_grupos_filtro";
    
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        $persona = $result->fetch_assoc();
        
        // Verificar si está inactiva por algún movimiento
        if ($persona['estado_inactivo']) {
            echo json_encode([
                'encontrada' => false,
                'mensaje' => 'Persona encontrada pero no está activa (Estado: ' . $persona['estado_inactivo'] . ')'
            ]);
        } else {
            // Persona activa y válida
            $nombre_completo = trim($persona['nombres_persona'] . ' ' . $persona['apellidos_persona']);
            echo json_encode([
                'encontrada' => true,
                'cedula' => $persona['cedula_persona'],
                'nombres' => $persona['nombres_persona'],
                'apellidos' => $persona['apellidos_persona'],
                'nombre_completo' => $nombre_completo,
                'genero' => $persona['genero_persona'],
                'grupo' => $persona['descripcion_grupo']
            ]);
        }
    } else {
        echo json_encode([
            'encontrada' => false,
            'mensaje' => 'Cédula no encontrada en grupos CPSAM/Contratista o persona no habilitada'
        ]);
    }
}

$mysqli->close();
?>
