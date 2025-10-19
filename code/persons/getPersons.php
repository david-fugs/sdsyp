<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

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
SELECT p.*, 
       GROUP_CONCAT(pr.nombre_programa ORDER BY pr.nombre_programa ASC) AS programas,
       GROUP_CONCAT(pr.id_programa ORDER BY pr.nombre_programa ASC) AS ids_programas,
       g.descripcion_grupo,
       pol.descripcion_politica,
       m.descripcion_meta,
       a.descripcion_actividad,
       acc.descripcion_accion,
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
        // Permitir también el estado 'Usuario Interesado' (sin importar mayúsculas/minúsculas)
        if (isset($row['estado_movimiento']) && trim(mb_strtolower($row['estado_movimiento'])) === 'usuario interesado') {
            $estado_persona = 'USUARIO INTERESADO';
        }

        // Aplicar filtro por estado si está seleccionado
        if (!empty($filtro_estado)) {
        $estado_filtro_map = [
            'ACTIVO' => 'CPSAM ACTIVO',
            'EVADIDO' => 'CPSAM EVADIDO',
            'FALLECIDO' => 'CPSAM FALLECIDO',
            'RETIRADO_VOLUNTARIO' => 'CPSAM RETIRADO VOLUNTARIO',
            'TRASLADADO' => 'CPSAM TRASLADADO',
            'USUARIO_INTERESADO' => 'USUARIO INTERESADO'
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
            case 'USUARIO INTERESADO':
                $badge_class = 'status-badge status-interesado';
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
            case 'USUARIO INTERESADO':
                $estado_icon = '<i class="bi bi-person-lines-fill"></i>';
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
        $estado_sin_cpsam = str_ireplace('CPSAM ', '', $estado_persona);
        if (strtoupper($estado_sin_cpsam) === 'TRASLADADO') {
            $estado_mostrar = 'ACTIVO (TRASLADADO)';
        } elseif ($estado_persona == 'Usuario interesado') {
            $estado_mostrar = 'Usuario Interesado';
        } else {
            $estado_mostrar = $estado_sin_cpsam;
        }

        // Si la condicion_componente indica una visita psicosocial fallida, mostrar 'Visita fallida'
        if (isset($row['condicion_componente']) && mb_strtolower(trim($row['condicion_componente'])) === 'visita psicosocial fallida') {
            $estado_mostrar = 'Visita fallida';
            // ajustar badge y icono para este caso
            $badge_class = 'status-badge status-warning';
            $estado_icon = '<i class="bi bi-exclamation-circle-fill"></i>';
        }

        if (isset($row['condicion_componente']) && $row['condicion_componente'] == 'Usuario interesado') {
            $estado_mostrar = 'Usuario Interesado';
        }
        echo "<td class='col-status'><span class='$badge_class'>$estado_icon $estado_mostrar</span></td>";

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
                        data-telefono-referencia="' . (isset($row['telefono_referencia_persona']) ? htmlspecialchars($row['telefono_referencia_persona']) : '') . '"
                        data-correo="' . (isset($row['correo_persona']) ? htmlspecialchars($row['correo_persona']) : '') . '"
                        data-direccion="' . (isset($row['direccion_persona']) ? htmlspecialchars($row['direccion_persona']) : '') . '"
                        data-condicion-ocupacion="' . (isset($row['condicion_ocupacion']) ? htmlspecialchars($row['condicion_ocupacion']) : '') . '"
                        data-condicion-componente="' . (isset($row['condicion_componente']) ? htmlspecialchars($row['condicion_componente']) : '') . '"
                        data-activo-desde="' . (isset($row['activo_desde']) ? htmlspecialchars($row['activo_desde']) : '') . '"
                        data-eps="' . (isset($row['eps']) ? htmlspecialchars($row['eps']) : '') . '"
                        data-peso="' . (isset($row['peso']) ? htmlspecialchars($row['peso']) : '') . '"
                        data-talla="' . (isset($row['talla']) ? htmlspecialchars($row['talla']) : '') . '"
                        data-patologias="' . (isset($row['patologias']) ? htmlspecialchars($row['patologias']) : '') . '"
                        data-factores-riesgo="' . (isset($row['factores_riesgo']) ? htmlspecialchars($row['factores_riesgo']) : '') . '"
                        data-factores-preventivos="' . (isset($row['factores_preventivos']) ? htmlspecialchars($row['factores_preventivos']) : '') . '"
                        data-ingresos-economicos="' . (isset($row['ingresos_economicos']) ? htmlspecialchars($row['ingresos_economicos']) : '') . '"
                        data-convivencia-actual="' . (isset($row['convivencia_actual']) ? htmlspecialchars($row['convivencia_actual']) : '') . '"
                        data-resultado-actividad="' . (isset($row['resultado_actividad']) ? htmlspecialchars($row['resultado_actividad']) : '') . '"
                        data-remision="' . (isset($row['remision']) ? htmlspecialchars($row['remision']) : '') . '"
                        data-id-barrio-persona="' . (isset($row['id_barrio_persona']) ? htmlspecialchars($row['id_barrio_persona']) : '') . '"
                        data-id-comuna-persona="' . (isset($row['id_comuna_persona']) ? htmlspecialchars($row['id_comuna_persona']) : '') . '"
                        data-zona-persona="' . (isset($row['zona_persona']) ? htmlspecialchars($row['zona_persona']) : '') . '"
                        data-id-meta="' . (isset($row['id_meta']) ? htmlspecialchars($row['id_meta']) : '') . '"
                        data-id-actividad="' . (isset($row['id_actividad']) ? htmlspecialchars($row['id_actividad']) : '') . '"
                        data-id-accion="' . (isset($row['id_accion']) ? htmlspecialchars($row['id_accion']) : '') . '"
                        data-id-politica-publica-nueva="' . (isset($row['id_politica_publica']) ? htmlspecialchars($row['id_politica_publica']) : '') . '"
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
        // Fin del if para tipo_usuario != 3
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
    echo "<tr><td colspan='8'>No se encontraron registros.</td></tr>";
}


$mysqli->close();
