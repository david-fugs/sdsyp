<?php
session_start();
include("../../conexion.php");
require_once('../filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;
$id_usuario_session = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

// Inicializar variable $where
$where = '';

// Aplicar filtro de grupos según tipo de usuario (para la tabla grupos)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

// Filtro para usuarios tipo 2: solo ver sus propios registros
if ($tipo_usuario == 2 && $id_usuario_session) {
    $where .= " AND ra.id_usuario = " . $id_usuario_session;
}

// Filtro para usuarios tipo 3 (CONTRATISTA): solo ver sus propios registros
if ($tipo_usuario == 3 && $id_usuario_session) {
    $where .= " AND ra.id_usuario = " . $id_usuario_session;
}

// Consulta SQL para obtener los datos
$filtro_anio = isset($_GET['filtro_anio']) ? intval($_GET['filtro_anio']) : '';
$filtro_mes = isset($_GET['filtro_mes']) ? intval($_GET['filtro_mes']) : '';
$filtro_funcionario = isset($_GET['filtro_funcionario']) ? intval($_GET['filtro_funcionario']) : '';
if ($filtro_anio) {
    $where .= " AND YEAR(ra.fecha_atencion) = $filtro_anio ";
}
if ($filtro_mes) {
    $where .= " AND MONTH(ra.fecha_atencion) = $filtro_mes ";
}
if ($filtro_funcionario) {
    $where .= " AND ra.id_usuario = $filtro_funcionario ";
}

$query = "SELECT ra.id_registro, ra.id_meta, ra.id_actividad, ra.id_entregas, ra.id_accion, ra.politica_publica, ra.id_centro_vida,
       ra.fecha_atencion, ra.nombre_lider, ra.telefono_contacto, ra.id_comuna, ra.medio_verificacion,
       ra.cantidad_masculino, ra.cantidad_femenino, ra.tipo_actividad, ra.observacion_actividad,
       ra.funcionario_responsable, ra.otro_lugar, ra.id_usuario,
       m.descripcion_meta, a.descripcion_actividad, ac.descripcion_accion, pp.descripcion_politica,
       actc.descripcion_actividad AS descripcion_entrega,
       g.descripcion_grupo AS centro_vida, c.nombre_com AS nombre_comuna, u.nombre AS nombre_funcionario
FROM registro_actividades AS ra
LEFT JOIN metas m ON ra.id_meta = m.id_meta
LEFT JOIN actividades a ON ra.id_actividad = a.id_actividad
LEFT JOIN acciones ac ON ra.id_accion = ac.id_accion
LEFT JOIN politicas_publicas pp ON ra.politica_publica = pp.id_politica
LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
LEFT JOIN comunas c ON ra.id_comuna = c.id_com
LEFT JOIN usuarios u ON CAST(ra.funcionario_responsable AS UNSIGNED) = u.id AND ra.funcionario_responsable REGEXP '^[0-9]+$'
LEFT JOIN actividad_contratista actc ON ra.id_entregas = actc.id_actividad_contratista
WHERE 1=1 $where 
" . ($where_grupos_filtro ? " AND ra.id_centro_vida IS NOT NULL AND EXISTS (SELECT 1 FROM grupos g2 WHERE g2.id_grupo = ra.id_centro_vida $where_grupos_filtro)" : "") . "
ORDER BY ra.fecha_atencion DESC
";

$result = $mysqli->query($query);

// Verificar si la consulta falló
if (!$result) {
    echo "<tr><td colspan='17' class='text-center text-danger'>
            <i class='bi bi-exclamation-triangle'></i><br>
            Error en la consulta: " . htmlspecialchars($mysqli->error) . "
          </td></tr>";
    $mysqli->close();
    exit;
}

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='fade-in'>";
        echo "<td class='col-id'>" . $row['id_registro'] . "</td>";
        echo "<td class='col-meta' title='" . htmlspecialchars($row['descripcion_meta'] ?? 'N/A') . "'>" . ($row['descripcion_meta'] ?? 'N/A') . "</td>";
        echo "<td class='col-actividad' title='" . htmlspecialchars($row['descripcion_actividad'] ?? 'N/A') . "'>" . ($row['descripcion_actividad'] ?? 'N/A') . "</td>";
        echo "<td class='col-accion' title='" . htmlspecialchars($row['descripcion_accion'] ?? 'N/A') . "'>" . ($row['descripcion_accion'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['descripcion_politica'] ?? 'N/A') . "'>" . ($row['descripcion_politica'] ?? 'N/A') . "</td>";
        
        // Mostrar lugar del evento o "otro lugar"
        $lugarEvento = $row['otro_lugar'] ? htmlspecialchars($row['otro_lugar']) : ($row['centro_vida'] ?? 'N/A');
        echo "<td title='" . $lugarEvento . "'>" . $lugarEvento . "</td>";
        
        echo "<td>" . ($row['fecha_atencion'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['nombre_lider'] ?? 'N/A') . "'>" . ($row['nombre_lider'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['telefono_contacto'] ?? 'N/A') . "'>" . ($row['telefono_contacto'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['nombre_comuna'] ?? 'N/A') . "'>" . ($row['nombre_comuna'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['medio_verificacion'] ?? 'N/A') . "'>" . ($row['medio_verificacion'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['cantidad_masculino'] ?? '0') . "</td>";
        echo "<td>" . ($row['cantidad_femenino'] ?? '0') . "</td>";
        echo "<td title='" . htmlspecialchars($row['tipo_actividad'] ?? 'N/A') . "'>" . ($row['tipo_actividad'] ?? 'N/A') . "</td>";
        echo "<td title='" . htmlspecialchars($row['observacion_actividad'] ?? '') . "'>" . ($row['observacion_actividad'] ?? '') . "</td>";
        
        // Mostrar funcionario responsable (nombre de usuario si es ID numérico, o el texto directo)
        $funcionarioDisplay = $row['nombre_funcionario'] ? htmlspecialchars($row['nombre_funcionario']) : htmlspecialchars($row['funcionario_responsable'] ?? 'N/A');
        echo "<td title='" . $funcionarioDisplay . "'>" . $funcionarioDisplay . "</td>";
        // Botones de acción modernos
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar registro"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id_registro="' . $row['id_registro'] . '"
                        data-meta="' . ($row['id_meta'] ?? '') . '"
                        data-actividad="' . ($row['id_actividad'] ?? '') . '"
                        data-accion="' . ($row['id_accion'] ?? '') . '"
                        data-entregas_actividad="' . ($row['id_entregas'] ?? '') . '"
                        data-politica_publica="' . ($row['politica_publica'] ?? '') . '"
                        data-centro_vida="' . ($row['id_centro_vida'] ?? '') . '"
                        data-fecha_atencion="' . ($row['fecha_atencion'] ?? '') . '"
                        data-nombre_lider="' . ($row['nombre_lider'] ?? '') . '"
                        data-telefono_contacto="' . ($row['telefono_contacto'] ?? '') . '"
                        data-comuna="' . ($row['id_comuna'] ?? '') . '"
                        data-medio_verificacion="' . ($row['medio_verificacion'] ?? '') . '"
                        data-cantidad_masculino="' . ($row['cantidad_masculino'] ?? '0') . '"
                        data-cantidad_femenino="' . ($row['cantidad_femenino'] ?? '0') . '"
                        data-tipo_actividad="' . ($row['tipo_actividad'] ?? '') . '"
                        data-observacion_actividad="' . ($row['observacion_actividad'] ?? '') . '"
                        data-funcionario_responsable="' . htmlspecialchars($row['funcionario_responsable'] ?? '') . '"
                        data-otro_lugar="' . htmlspecialchars($row['otro_lugar'] ?? '') . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" 
                       class="btn-action btn-delete btn-delete-registro" 
                       title="Eliminar registro"
                       data-id="' . $row['id_registro'] . '">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='17' class='text-center text-muted'>
            <i class='bi bi-search'></i><br>
            No se encontraron registros que coincidan con los filtros aplicados.
          </td></tr>";
}


$mysqli->close();
