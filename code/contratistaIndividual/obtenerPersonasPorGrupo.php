<?php
include("../../conexion.php");
include("../../filtros_grupos.php");
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_grupo = isset($_POST['id_grupo']) ? intval($_POST['id_grupo']) : 0;
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    
    if ($id_grupo <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de grupo inválido']);
        exit;
    }

    // Obtener filtro de grupos permitidos según permisos del usuario
    $where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
    
    // Consultar personas activas del grupo seleccionado
    // Excluir personas que tengan movimientos con condiciones: Fallecido, Retirado Voluntario, Evadido
    $query = "SELECT 
                p.cedula_persona,
                p.nombres_persona,
                p.apellidos_persona,
                p.genero_persona,
                g.descripcion_grupo
              FROM personas p
              LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
              WHERE p.id_grupo = $id_grupo 
              AND p.estado_persona = 1
              AND NOT EXISTS (
                  SELECT 1 FROM movimiento_persona mp
                  INNER JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                  WHERE mp.cedula_persona = p.cedula_persona
                  AND (
                      UPPER(cc.descripcion_condicion) LIKE '%FALLECIDO%'
                      OR UPPER(cc.descripcion_condicion) LIKE '%RETIRADO VOLUNTARIO%'
                      OR UPPER(cc.descripcion_condicion) LIKE '%EVADIDO%'
                  )
              )
              $where_grupos
              ORDER BY p.nombres_persona, p.apellidos_persona ASC";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Error en consulta: ' . $mysqli->error]);
        exit;
    }
    
    $personas = [];
    while ($row = $result->fetch_assoc()) {
        $personas[] = [
            'cedula' => $row['cedula_persona'],
            'nombres' => $row['nombres_persona'],
            'apellidos' => $row['apellidos_persona'],
            'genero' => $row['genero_persona'],
            'grupo' => $row['descripcion_grupo']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'personas' => $personas,
        'total' => count($personas)
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
