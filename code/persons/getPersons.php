<?php
include("../../conexion.php");
$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona = '$cedula'";
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

$data = [];

if ($result->num_rows > 0) {
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
        
        // Determinar color del estado
        $badge_class = '';
        switch ($estado_persona) {
            case 'CPSAM ACTIVO':
                $badge_class = 'status-badge status-active';
                break;
            case 'CPSAM EVADIDO':
                $badge_class = 'status-badge status-warning';
                break;
            case 'CPSAM FALLECIDO':
                $badge_class = 'status-badge status-secondary';
                break;
            case 'CPSAM RETIRADO VOLUNTARIO':
                $badge_class = 'status-badge status-info';
                break;
            case 'CPSAM TRASLADADO':
                $badge_class = 'status-badge status-info';
                break;
        }
        
        // Determinar icono del estado
        $estado_icon = '';
        switch ($estado_persona) {
            case 'CPSAM ACTIVO':
                $estado_icon = '<i class="bi bi-check-circle-fill"></i>';
                break;
            case 'CPSAM EVADIDO':
                $estado_icon = '<i class="bi bi-exclamation-triangle-fill"></i>';
                break;
            case 'CPSAM FALLECIDO':
                $estado_icon = '<i class="bi bi-x-circle-fill"></i>';
                break;
            case 'CPSAM RETIRADO VOLUNTARIO':
                $estado_icon = '<i class="bi bi-arrow-left-circle-fill"></i>';
                break;
            case 'CPSAM TRASLADADO':
                $estado_icon = '<i class="bi bi-arrow-right-circle-fill"></i>';
                break;
        }
        // Agregar datos al array
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . htmlspecialchars($row['cedula_persona']) . "</td>";
        echo "<td><b>" . htmlspecialchars($row['nombres_persona'] . ' ' . $row['apellidos_persona']) . "</b></td>";
        echo "<td>" . htmlspecialchars($row['genero_persona']) . "</td>";
        $fecha_nacimiento = $row['fecha_nacimiento'];
        if ($fecha_nacimiento && $fecha_nacimiento != '0000-00-00') {
            $hoy = new DateTime();
            $nacimiento = new DateTime($fecha_nacimiento);
            $edad = $hoy->diff($nacimiento)->y;
            echo "<td><span class='badge bg-primary'>" . $edad . " años</span></td>";
        } else {
            echo "<td class='text-muted'>N/A</td>";
        }
        echo "<td>" . htmlspecialchars($row['programas']) . "</td>";
        echo "<td>" . ($row['descripcion_grupo'] ? htmlspecialchars($row['descripcion_grupo']) : 'No asignado') . "</td>";
        echo "<td class='col-status'><span class='$badge_class'>$estado_icon " . str_replace('CPSAM ', '', $estado_persona) . "</span></td>";
        
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar persona"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-cedula="' . $row['cedula_persona'] . '"
                        data-nombre="' . $row['nombres_persona'] . '"
                        data-apellidos="' . $row['apellidos_persona'] . '"
                        data-telefono="' . $row['telefono_persona'] . '"
                        data-referencia="' . htmlspecialchars($row['referencia_persona']) . '"
                        data-fecha-nacimiento="' . $row['fecha_nacimiento'] . '"
                        data-programas="' . htmlspecialchars($row['programas']) . '"
                        data-genero="' . $row['genero_persona'] . '"
                        data-ids-programas="' .  $row['ids_programas']  . '"
                        data-id-grupo="' . $row['id_grupo'] . '"
                        data-id-politica-publica="' . $row['id_politica_publica'] . '"
                        data-grupo-sisben="' . $row['grupo_sisben'] . '"
                        data-persona-discapacidad="' . $row['persona_discapacidad'] . '"
                        data-cual-discapacidad="' . $row['cual_discapacidad'] . '"
                        data-tipo-identificacion="' . $row['tipo_identificacion'] . '"
                        data-cabeza-hogar="' . $row['cabeza_hogar'] . '"
                        data-lider-comunidad="' . $row['lider_comunidad'] . '"
                        data-se-reconoce-como="' . $row['se_reconoce_como'] . '"
                        data-orientacion-sexual="' . $row['orientacion_sexual'] . '"
                        data-experiencia-migratoria="' . $row['experiencia_migratoria'] . '"
                        data-grupo-etnico="' . $row['grupo_etnico'] . '"
                        data-tipo-salud="' . $row['tipo_salud'] . '"
                        data-nivel-educativo="' . $row['nivel_educativo'] . '"
                        data-id-barrio-persona="' . (isset($row['id_barrio_persona']) ? htmlspecialchars($row['id_barrio_persona']) : '') . '"
                        data-id-comuna-persona="' . (isset($row['id_comuna_persona']) ? htmlspecialchars($row['id_comuna_persona']) : '') . '"
                        data-zona-persona="' . (isset($row['zona_persona']) ? htmlspecialchars($row['zona_persona']) : '') . '"
                    >
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <a href="?delete=' . $row['cedula_persona'] . '" 
                       class="btn-action btn-delete" 
                       title="Eliminar persona"
                       onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta persona?\')">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </div>
            </td>';
        // Agregar data attributes para barrio, comuna y zona
        // NOTA: Estos campos deben existir en la tabla personas, si no, se deben agregar en la consulta y en la base de datos
        // Si no existen, dejar string vacío
        // Ejemplo: $row['barrio_persona']
        // Si no existen, puedes ajustar aquí para pruebas
        // data-barrio-persona, data-comuna-persona, data-zona-persona
        //
        // Si existen en $row:
        // data-barrio-persona="' . (isset($row['barrio_persona']) ? htmlspecialchars($row['barrio_persona']) : '') . '"
        // data-comuna-persona="' . (isset($row['comuna_persona']) ? htmlspecialchars($row['comuna_persona']) : '') . '"
        // data-zona-persona="' . (isset($row['zona_persona']) ? htmlspecialchars($row['zona_persona']) : '') . '"
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
