<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

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

// Filtro por creado por
if (!empty($_GET['creado_por'])) {
    $creado_por = $mysqli->real_escape_string($_GET['creado_por']);
    $where .= " AND u.nombre LIKE '%$creado_por%'";
}

// Filtrar por id_grupo si el tipo_usuario en la sesión es diferente de 1, 3, 4 y 5
// (No aplicar este filtro a ADMIN, CONTRATISTA ni a los nuevos TÉCNICOS)
if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;

// Preparar filtro por estado (se aplicará después de la consulta principal)
$filtro_estado = '';
if (!empty($_GET['estado'])) {
    $filtro_estado = $_GET['estado'];
}

// Consulta SQL para obtener los datos
$query = "
SELECT p.*, p.condicion_componente as condicion_componente,
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas,
       GROUP_CONCAT(pr.id_programa ORDER BY pr.nombre_programa ASC) AS ids_programas,
       g.descripcion_grupo,
       pol.descripcion_politica,
       m.descripcion_meta,
       a.descripcion_actividad,
       acc.descripcion_accion,
       u.nombre AS creado_por,
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
LEFT JOIN metas m ON p.id_meta = m.id_meta
LEFT JOIN actividades a ON p.id_actividad = a.id_actividad
LEFT JOIN acciones acc ON p.id_accion = acc.id_accion
LEFT JOIN usuarios u ON p.id_usuario = u.id
$where
GROUP BY p.cedula_persona
ORDER BY p.apellidos_persona ASC
";

$result = $mysqli->query($query);

// Si no hay resultados y se buscó por cédula, verificar si existe pero está fuera del alcance
if ((!$result || $result->num_rows == 0) && !empty($_GET['cedula_persona'])) {
    $cedula_buscar = $mysqli->real_escape_string($_GET['cedula_persona']);
    
    // Buscar sin restricciones de grupo
    $query_existe = "SELECT p.*, g.descripcion_grupo,
        (SELECT cc.descripcion_condicion 
         FROM movimiento_persona mp 
         JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
         WHERE mp.cedula_persona = p.cedula_persona 
         AND cc.descripcion_condicion IN ('CPSAM EVADIDO', 'CPSAM FALLECIDO', 'CPSAM RETIRADO VOLUNTARIO', 'CPSAM TRASLADADO')
         ORDER BY mp.fecha_movimiento DESC 
         LIMIT 1) AS estado_movimiento
        FROM personas p
        LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
        WHERE p.cedula_persona = '$cedula_buscar' AND p.estado_persona = 1";
    
    $result_existe = $mysqli->query($query_existe);
    
    if ($result_existe && $result_existe->num_rows > 0) {
        $row_persona = $result_existe->fetch_assoc();
        $nombre_completo = $row_persona['nombres_persona'] . ' ' . $row_persona['apellidos_persona'];
        $grupo = $row_persona['descripcion_grupo'] ? $row_persona['descripcion_grupo'] : 'No asignado';
        
        // Determinar el estado
        $estado_persona = $row_persona['estado_movimiento'] ? $row_persona['estado_movimiento'] : 'CPSAM ACTIVO';
        
        if (isset($row_persona['condicion_componente']) && 
            mb_strtolower(trim($row_persona['condicion_componente'])) === 'usuario interesado') {
            $estado_persona = 'USUARIO INTERESADO';
        }
        
        if (isset($row_persona['condicion_componente']) && 
            mb_strtolower(trim($row_persona['condicion_componente'])) === 'visita psicosocial fallida') {
            $estado_persona = 'VISITA PSICOSOCIAL FALLIDA';
        }
        
        $estado_mostrar = str_ireplace('CPSAM ', '', $estado_persona);
        
        // Devolver JSON indicando que existe pero no tiene acceso
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'tipo' => 'sin_acceso',
            'cedula' => $cedula_buscar,
            'nombre' => $nombre_completo,
            'grupo' => $grupo,
            'estado' => $estado_mostrar
        ]);
        $mysqli->close();
        exit;
    }
}

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
            case 'USUARIO INTERESADO':
                $badge_class = 'status-badge status-interesado';
                $estado_icon = '<i class="bi bi-person-lines-fill"></i>';
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
        // 7. Creado por
        echo "<td>" . htmlspecialchars($row['creado_por'] ?: 'N/A') . "</td>";
        // 8. Estado
        // Mostrar 'ACTIVO (TRASLADADO)' si el estado es 'CPSAM TRASLADADO', o 'Usuario Interesado' si corresponde (sin importar mayúsculas/minúsculas)
        if ($estado_persona === 'CPSAM TRASLADADO') {
            $estado_mostrar = 'ACTIVO (TRASLADADO)';
        } elseif ($estado_persona == 'Usuario interesado') {
            $estado_mostrar = 'Usuario Interesado';
        } else {
            $estado_mostrar = str_replace('CPSAM ', '', $estado_persona);
        }
        if ($row['condicion_componente'] == 'Usuario interesado') {
            $estado_mostrar = 'Usuario Interesado';
        }
        echo "<td class='col-status'><span class='$badge_class'>$estado_icon $estado_mostrar</span></td>";
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
                        data-grupo-sisben="' . (isset($row['grupo_sisben']) ? htmlspecialchars($row['grupo_sisben']) : '') . '"
                        data-persona-discapacidad="' . (isset($row['persona_discapacidad']) ? htmlspecialchars($row['persona_discapacidad']) : '') . '"
                        data-cual-discapacidad="' . (isset($row['cual_discapacidad']) ? htmlspecialchars($row['cual_discapacidad']) : '') . '"
                        data-tipo-identificacion="' . (isset($row['tipo_identificacion']) ? htmlspecialchars($row['tipo_identificacion']) : '') . '"
                        data-cabeza-hogar="' . (isset($row['cabeza_hogar']) ? htmlspecialchars($row['cabeza_hogar']) : '') . '"
                        data-lider-comunidad="' . (isset($row['lider_comunidad']) ? htmlspecialchars($row['lider_comunidad']) : '') . '"
                        data-se-reconoce-como="' . (isset($row['se_reconoce_como']) ? htmlspecialchars($row['se_reconoce_como']) : '') . '"
                        data-orientacion-sexual="' . (isset($row['orientacion_sexual']) ? htmlspecialchars($row['orientacion_sexual']) : '') . '"
                        data-experiencia-migratoria="' . (isset($row['experiencia_migratoria']) ? htmlspecialchars($row['experiencia_migratoria']) : '') . '"
                        data-grupo-etnico="' . (isset($row['grupo_etnico']) ? htmlspecialchars($row['grupo_etnico']) : '') . '"
                        data-tipo-salud="' . (isset($row['tipo_salud']) ? htmlspecialchars($row['tipo_salud']) : '') . '"
                        data-nivel-educativo="' . (isset($row['nivel_educativo']) ? htmlspecialchars($row['nivel_educativo']) : '') . '"
                        data-telefono-referencia="' . (isset($row['telefono_referencia_persona']) ? htmlspecialchars($row['telefono_referencia_persona']) : '') . '"
                        data-correo="' . (isset($row['correo_persona']) ? htmlspecialchars($row['correo_persona']) : '') . '"
                        data-direccion="' . (isset($row['direccion_persona']) ? htmlspecialchars($row['direccion_persona']) : '') . '"
                        data-condicion-ocupacion="' . (isset($row['condicion_ocupacion']) ? htmlspecialchars($row['condicion_ocupacion']) : '') . '"
                        data-condicion-componente="' . (isset($row['condicion_componente']) ? htmlspecialchars($row['condicion_componente']) : '') . '"
                        data-activo-desde="' . (isset($row['activo_desde']) ? htmlspecialchars($row['activo_desde']) : '') . '"
                        data-eps="' . htmlspecialchars($row['eps'] ?? '') . '"
                        data-peso="' . htmlspecialchars($row['peso'] ?? '') . '"
                        data-talla="' . htmlspecialchars($row['talla'] ?? '') . '"
                        data-patologias="' . htmlspecialchars($row['patologias'] ?? '') . '"
                        data-factores-riesgo="' . htmlspecialchars($row['factores_riesgo'] ?? '') . '"
                        data-factores-preventivos="' . htmlspecialchars($row['factores_preventivos'] ?? '') . '"
                        data-ingresos-economicos="' . htmlspecialchars($row['ingresos_economicos'] ?? '') . '"
                        data-convivencia-actual="' . htmlspecialchars($row['convivencia_actual'] ?? '') . '"
                        data-resultado-actividad="' . htmlspecialchars($row['resultado_actividad'] ?? '') . '"
                        data-remision="' . htmlspecialchars($row['remision'] ?? '') . '"
                        data-id-barrio-persona="' . (isset($row['id_barrio_persona']) ? htmlspecialchars($row['id_barrio_persona']) : '') . '"
                        data-id-comuna-persona="' . (isset($row['id_comuna_persona']) ? htmlspecialchars($row['id_comuna_persona']) : '') . '"
                        data-zona-persona="' . (isset($row['zona_persona']) ? htmlspecialchars($row['zona_persona']) : '') . '"
                        data-id-meta="' . (isset($row['id_meta']) ? htmlspecialchars($row['id_meta']) : '') . '"
                        data-id-actividad="' . (isset($row['id_actividad']) ? htmlspecialchars($row['id_actividad']) : '') . '"
                        data-id-accion="' . (isset($row['id_accion']) ? htmlspecialchars($row['id_accion']) : '') . '"
                        data-id-politica-publica-nueva="' . (isset($row['id_politica_publica']) ? htmlspecialchars($row['id_politica_publica']) : '') . '">
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
