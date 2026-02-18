<?php
include("../conexion.php"); 
session_start();
require_once('filtros_grupos.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cedula = trim($_POST['cedula'] ?? '');

    if ($cedula === '') {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Cédula vacía']);
        exit;
    }

    // Obtener tipo de usuario de la sesión
    $tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
    $grupos_permitidos = getGruposPermitidos($mysqli, $tipo_usuario);
    
    // Si el usuario tiene restricciones de grupo, verificar que la persona pertenezca a un grupo permitido
    $where_grupo = "";
    if (!empty($grupos_permitidos) && in_array($tipo_usuario, [4, 5])) {
        $ids_grupos = implode(',', $grupos_permitidos);
        $where_grupo = " AND p.id_grupo IN ($ids_grupos)";
    }

    // Preparar consulta para evitar inyecciones SQL - incluir los nuevos campos
    // Excluir grupos con estado: Trasladado, Fallecido, Evadido
    $stmt = $mysqli->prepare("
        SELECT p.nombres_persona, p.apellidos_persona, p.id_meta, p.id_actividad, p.id_accion, p.id_politica_publica,
               m.descripcion_meta, a.descripcion_actividad, acc.descripcion_accion, pol.descripcion_politica
        FROM personas p
        LEFT JOIN metas m ON p.id_meta = m.id_meta
        LEFT JOIN actividades a ON p.id_actividad = a.id_actividad
        LEFT JOIN acciones acc ON p.id_accion = acc.id_accion
        LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        WHERE p.cedula_persona = ? $where_grupo
        AND (g.descripcion_grupo NOT LIKE '%Trasladado%' OR g.descripcion_grupo IS NULL)
        AND (g.descripcion_grupo NOT LIKE '%Fallecido%' OR g.descripcion_grupo IS NULL)
        AND (g.descripcion_grupo NOT LIKE '%Evadido%' OR g.descripcion_grupo IS NULL)
    ");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($row = $resultado->fetch_assoc()) {
        // Consultar si existe algún movimiento con id_condicion = 8 (fallecimiento)
        $fallecido = false;
        $stmt_fall = $mysqli->prepare("SELECT 1 FROM movimiento_persona WHERE cedula_persona = ? AND id_condicion = 8 LIMIT 1");
        $stmt_fall->bind_param("s", $cedula);
        $stmt_fall->execute();
        $res_fall = $stmt_fall->get_result();
        if ($res_fall->fetch_assoc()) {
            $fallecido = true;
        }
        $stmt_fall->close();
            // Obtener la última condición y departamento_de_procedencia desde movimiento_persona (si existe)
            $lastCond = null;
            $lastDept = null;
            $stmt_last = $mysqli->prepare("SELECT id_condicion, departamento_procedencia FROM movimiento_persona WHERE cedula_persona = ? ORDER BY fecha_movimiento DESC LIMIT 1");
            $stmt_last->bind_param("s", $cedula);
            $stmt_last->execute();
            $res_last = $stmt_last->get_result();
            if ($r = $res_last->fetch_assoc()) {
                $lastCond = $r['id_condicion'];
                $lastDept = $r['departamento_procedencia'];
            }
            $stmt_last->close();

            echo json_encode([
                'encontrado' => true,
                'nombres' => $row['nombres_persona'],
                'apellidos' => $row['apellidos_persona'],
                'id_meta' => $row['id_meta'],
                'id_actividad' => $row['id_actividad'],
                'id_accion' => $row['id_accion'],
                'id_politica_publica' => $row['id_politica_publica'],
                'descripcion_meta' => $row['descripcion_meta'],
                'descripcion_actividad' => $row['descripcion_actividad'],
                'descripcion_accion' => $row['descripcion_accion'],
                'descripcion_politica' => $row['descripcion_politica'],
                'fallecido' => $fallecido,
                'id_condicion' => $lastCond,
                'departamento_procedencia' => $lastDept
            ]);
    } else {
        echo json_encode(['encontrado' => false, 'mensaje' => 'Persona no encontrada']);
    }

    $stmt->close();
} else {
    echo json_encode(['encontrado' => false, 'mensaje' => 'Método no permitido']);
}
?>
