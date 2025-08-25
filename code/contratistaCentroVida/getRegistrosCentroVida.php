<?php
include("../../conexion.php");

// Construir la consulta base
$query = "
    SELECT DISTINCT
        rcv.id_registro_centro_vida,
        p.cedula_persona,
        p.nombres_persona,
        p.apellidos_persona,
    acv.descripcion_actividad as actividad_centro_vida,
        rcv.politica_publica,
        pp.descripcion_politica AS politica_publica_descripcion,
        rcv.departamento_procedencia,
        rcv.observacion,
        rcv.funcionario_registro,
        rcv.fecha_registro,
        GROUP_CONCAT(rcvf.fecha_atencion ORDER BY rcvf.fecha_atencion ASC SEPARATOR ', ') as fechas_programadas
    FROM registro_centro_vida rcv
    INNER JOIN personas p ON rcv.cedula_persona = p.cedula_persona
    INNER JOIN actividad_centro_vida acv ON rcv.id_actividad_centro_vida = acv.id_actividad_centro_vida
    LEFT JOIN registro_centro_vida_fechas rcvf ON rcv.id_registro_centro_vida = rcvf.id_registro_centro_vida
    LEFT JOIN politicas_publicas pp ON rcv.politica_publica = pp.id_politica
";

// Aplicar filtros si existen
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['cedula_persona']) && !empty($_GET['cedula_persona'])) {
    $where_conditions[] = "p.cedula_persona = ?";
    $params[] = $_GET['cedula_persona'];
    $types .= "i";
}

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $where_conditions[] = "(p.nombres_persona LIKE ? OR p.apellidos_persona LIKE ?)";
    $params[] = "%" . $_GET['nombre'] . "%";
    $params[] = "%" . $_GET['nombre'] . "%";
    $types .= "ss";
}

if (isset($_GET['actividad']) && !empty($_GET['actividad'])) {
    $where_conditions[] = "rcv.id_actividad_centro_vida = ?";
    $params[] = $_GET['actividad'];
    $types .= "i";
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY rcv.id_registro_centro_vida ORDER BY rcv.fecha_registro DESC";

// Preparar y ejecutar la consulta
if (!empty($params)) {
    $stmt = $mysqli->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($query);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td>" . htmlspecialchars($row['cedula_persona']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombres_persona']) . "</td>";
        echo "<td>" . htmlspecialchars($row['apellidos_persona']) . "</td>";
    echo "<td>" . htmlspecialchars($row['actividad_centro_vida']) . "</td>";
        
        // Formatear fechas programadas
        $fechas = explode(', ', $row['fechas_programadas']);
        $fechas_formateadas = array_map(function($fecha) {
            return date('d/m/Y', strtotime($fecha));
        }, $fechas);
        $fechas_display = implode(', ', $fechas_formateadas);
        
        echo "<td title='" . htmlspecialchars($fechas_display) . "'>" . 
             (strlen($fechas_display) > 40 ? 
              substr($fechas_display, 0, 40) . "..." : 
              $fechas_display) . "</td>";
        
        // Mostrar descripción de política pública si existe, en caso contrario mostrar el id o 'No asignada'
        $politica_desc = $row['politica_publica_descripcion'] ?? null;
        if ($politica_desc) {
            echo "<td>" . htmlspecialchars($politica_desc) . "</td>";
        } else {
            // Si no hay descripción, mostrar id si existe, o texto por defecto
            $politica_id = $row['politica_publica'] ?? '';
            echo "<td>" . ($politica_id !== '' ? htmlspecialchars($politica_id) : 'No asignada') . "</td>";
        }
        echo "<td>" . htmlspecialchars($row['departamento_procedencia']) . "</td>";
        echo "<td title='" . htmlspecialchars($row['observacion']) . "'>" . 
             (strlen($row['observacion']) > 25 ? 
              substr(htmlspecialchars($row['observacion']), 0, 25) . "..." : 
              htmlspecialchars($row['observacion'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['funcionario_registro']) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_registro'])) . "</td>";

        // Botones de acción
        $editarOnclick = 'editarRegistro(' . $row['id_registro_centro_vida'] . ')';
        $actividadEsc = htmlspecialchars($row['actividad_centro_vida'], ENT_QUOTES);
        $eliminarOnclick = "confirmarEliminacion(" . $row['id_registro_centro_vida'] . ", '" . $actividadEsc . "')";

        echo '<td class="col-actions">'
            . '<div class="action-buttons">'
            . '<button type="button" class="btn-action btn-edit" title="Editar registro" onclick="' . $editarOnclick . '">'
            . '<i class="bi bi-pencil-fill"></i></button>'
            . '<button type="button" class="btn-action btn-delete" title="Eliminar registro" onclick="' . $eliminarOnclick . '">'
            . '<i class="bi bi-trash-fill"></i></button>'
            . '</div></td>';
        echo "</tr>";
    }
} else {
    echo "<tr>";
    // Ajustar colspan tras remover columna 'actividad_realizada'
    echo "<td colspan='11' class='text-center text-muted py-4'>";
    echo "<i class='bi bi-inbox fs-1 d-block mb-2' style='color: #e5e7eb;'></i>";
    echo "No hay registros de centro vida";
    echo "</td>";
    echo "</tr>";
}

if (isset($stmt)) {
    $stmt->close();
}
$mysqli->close();
?>
