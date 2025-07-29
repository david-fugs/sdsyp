<?php
include("../../conexion.php");

// Construir la cláusula WHERE base
$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona LIKE '%$cedula%'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (p.nombres_persona LIKE '%$nombre%' OR p.apellidos_persona LIKE '%$nombre%')";
}

// Filtro por programa
if (!empty($_GET['programa'])) {
    $programa = $mysqli->real_escape_string($_GET['programa']);
    $where .= " AND pp.id_programa = '$programa'";
}

// Preparar filtro por estado (se aplicará después de la consulta principal)
$filtro_estado = '';
if (!empty($_GET['estado'])) {
    $filtro_estado = $_GET['estado'];
}

// Consulta SQL para obtener los datos
$query = "
SELECT p.*, 
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas,
       GROUP_CONCAT(pr.id_programa ORDER BY pr.nombre_programa ASC) AS ids_programas,
       g.descripcion_grupo,
       pol.descripcion_politica,
       (SELECT cc.descripcion_condicion 
        FROM movimiento_persona mp 
        JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
        WHERE mp.cedula_persona = p.cedula_persona 
        AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
        ORDER BY mp.fecha_movimiento DESC 
        LIMIT 1) AS estado_movimiento
FROM personas p
LEFT JOIN persona_programa pp ON p.cedula_persona = pp.cedula_persona
LEFT JOIN programas pr ON pp.id_programa = pr.id_programa
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
LEFT JOIN politicas_publicas pol ON p.id_politica_publica = pol.id_politica
$where
GROUP BY p.cedula_persona
ORDER BY p.apellidos_persona ASC
";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determinar el estado de la persona
        $estado_persona = $row['estado_movimiento'] ? $row['estado_movimiento'] : 'CPSAM ACTIVO';
        
        // Aplicar filtro por estado si está seleccionado
        if (!empty($filtro_estado)) {
            $estado_filtro_map = [
                'ACTIVO' => 'CPSAM ACTIVO',
                'EVADIDO' => 'CPSAM EVADIDO',
                'FALLECIDO' => 'CPSAM FALLECIDO',
                'RETIRADO_VOLUNTARIO' => 'CPSAM RETIRADO VOLUNTARIO',
                'TRASLADADO' => 'CPSAM TRASLADADO'
            ];
            
            if (isset($estado_filtro_map[$filtro_estado]) && $estado_persona !== $estado_filtro_map[$filtro_estado]) {
                continue; // Saltar esta fila si no coincide con el filtro
            }
        }
        
        // Determinar clase del badge y icono del estado
        $badge_class = '';
        $estado_icon = '';
        
        switch ($estado_persona) {
            case 'CPSAM ACTIVO':
                $badge_class = 'status-badge status-active';
                $estado_icon = '<i class="bi bi-check-circle-fill"></i>';
                break;
            case 'CPSAM EVADIDO':
                $badge_class = 'status-badge status-warning';
                $estado_icon = '<i class="bi bi-exclamation-triangle-fill"></i>';
                break;
            case 'CPSAM FALLECIDO':
                $badge_class = 'status-badge status-secondary';
                $estado_icon = '<i class="bi bi-x-circle-fill"></i>';
                break;
            case 'CPSAM RETIRADO VOLUNTARIO':
                $badge_class = 'status-badge status-info';
                $estado_icon = '<i class="bi bi-arrow-left-circle-fill"></i>';
                break;
            case 'CPSAM TRASLADADO':
                $badge_class = 'status-badge status-info';
                $estado_icon = '<i class="bi bi-arrow-right-circle-fill"></i>';
                break;
        }
        
        echo "<tr class='fade-in'>";
        // 1. Cédula
        echo "<td class='col-id'>" . htmlspecialchars($row['cedula_persona']) . "</td>";
        // 2. Nombre Completo
        echo "<td>" . htmlspecialchars($row['nombres_persona'] . ' ' . $row['apellidos_persona']) . "</td>";
        // 3. Género
        echo "<td>" . htmlspecialchars($row['genero_persona']) . "</td>";
        // 4. Edad
        $fecha_nacimiento = $row['fecha_nacimiento'];
        if ($fecha_nacimiento && $fecha_nacimiento != '0000-00-00') {
            $hoy = new DateTime();
            $nacimiento = new DateTime($fecha_nacimiento);
            $edad = $hoy->diff($nacimiento)->y;
            echo "<td><span class='badge bg-primary'>" . $edad . " años</span></td>";
        } else {
            echo "<td class='text-muted'>N/A</td>";
        }
        // 5. Programas
        echo "<td>" . htmlspecialchars($row['programas'] ?: 'Sin programa') . "</td>";
        // 6. Centro Vida / CPSAM
        echo "<td>" . htmlspecialchars($row['descripcion_grupo'] ?: 'No asignado') . "</td>";
        // 7. Estado
        echo "<td class='col-status'><span class='$badge_class'>$estado_icon " . str_replace('CPSAM ', '', $estado_persona) . "</span></td>";
        // 8. Política Pública
        // 9. Acciones
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar persona"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-cedula="' . htmlspecialchars($row['cedula_persona']) . '"
                        data-nombre="' . htmlspecialchars($row['nombres_persona']) . '"
                        data-apellidos="' . htmlspecialchars($row['apellidos_persona']) . '"
                        data-telefono="' . htmlspecialchars($row['telefono_persona']) . '"
                        data-referencia="' . htmlspecialchars($row['referencia_persona']) . '"
                        data-fecha-nacimiento="' . $row['fecha_nacimiento'] . '"
                        data-programas="' . htmlspecialchars($row['programas'] ?: '') . '"
                        data-genero="' . htmlspecialchars($row['genero_persona']) . '"
                        data-ids-programas="' . htmlspecialchars($row['ids_programas'] ?: '') . '"
                        data-id-grupo="' . htmlspecialchars($row['id_grupo'] ?: '') . '"
                        data-id-politica-publica="' . htmlspecialchars($row['id_politica_publica'] ?: '') . '"
                        data-eps="' . htmlspecialchars($row['eps'] ?? '') . '"
                        data-peso="' . htmlspecialchars($row['peso'] ?? '') . '"
                        data-talla="' . htmlspecialchars($row['talla'] ?? '') . '"
                        data-patologias="' . htmlspecialchars($row['patologias'] ?? '') . '"
                        data-factores-riesgo="' . htmlspecialchars($row['factores_riesgo'] ?? '') . '"
                        data-factores-preventivos="' . htmlspecialchars($row['factores_preventivos'] ?? '') . '"
                        data-ingresos-economicos="' . htmlspecialchars($row['ingresos_economicos'] ?? '') . '"
                        data-convivencia-actual="' . htmlspecialchars($row['convivencia_actual'] ?? '') . '"
                        data-resultado-actividad="' . htmlspecialchars($row['resultado_actividad'] ?? '') . '"
                        data-remision="' . htmlspecialchars($row['remision'] ?? '') . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <a href="?delete=' . htmlspecialchars($row['cedula_persona']) . '" 
                       class="btn-action btn-delete" 
                       title="Eliminar persona"
                       onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta persona?\')">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center text-muted'>
            <i class='bi bi-search'></i><br>
            No se encontraron registros que coincidan con los filtros aplicados.
          </td></tr>";
}

$mysqli->close();
?>
