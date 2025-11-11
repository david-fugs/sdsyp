<?php
// Este archivo genera las filas de la tabla de registros masivos
// Puede ser incluido o llamado por AJAX

// Incluir conexión solo si no existe
if (!isset($mysqli)) {
    include("../../conexion.php");
}

$where = "1=1";

// Filtros opcionales (si se implementan)
if (isset($_GET['fecha_desde']) && $_GET['fecha_desde'] != '') {
    $fecha_desde = $mysqli->real_escape_string($_GET['fecha_desde']);
    $where .= " AND r.fecha_registro >= '$fecha_desde'";
}

if (isset($_GET['fecha_hasta']) && $_GET['fecha_hasta'] != '') {
    $fecha_hasta = $mysqli->real_escape_string($_GET['fecha_hasta']);
    $where .= " AND r.fecha_registro <= '$fecha_hasta'";
}

// Consulta
$sql = "SELECT r.id_registro_masivo_cm, r.fecha_registro, r.total_personas, r.cantidad_masculino, r.cantidad_femenino, r.observaciones,
        m.descripcion_meta,
        act.descripcion_actividad,
        acc.descripcion_accion,
        pp.descripcion_politica,
        u.nombre as usuario_nombre
        FROM registros_masivos_cm r
        INNER JOIN metas m ON r.id_meta = m.id_meta
        INNER JOIN actividades act ON r.id_actividad = act.id_actividad
        INNER JOIN acciones acc ON r.id_accion = acc.id_accion
        LEFT JOIN politicas_publicas pp ON r.id_politica_publica = pp.id_politica
        LEFT JOIN usuarios u ON r.usuario_registro = u.id
        WHERE $where
        ORDER BY r.fecha_registro DESC, r.id_registro_masivo_cm DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fecha_formato = date('d/m/Y', strtotime($row['fecha_registro']));
        $cantidad_masculino = $row['cantidad_masculino'] ?? 0;
        $cantidad_femenino = $row['cantidad_femenino'] ?? 0;
        
        echo "<tr>";
        echo "<td>{$fecha_formato}</td>";
        echo "<td class='col-meta'>" . htmlspecialchars($row['descripcion_meta']) . "</td>";
        echo "<td class='col-actividad'>" . htmlspecialchars($row['descripcion_actividad']) . "</td>";
        echo "<td class='col-accion'>" . htmlspecialchars($row['descripcion_accion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion_politica'] ?? 'No asignada') . "</td>";
        echo "<td class='text-center'><span class='badge bg-info'>{$cantidad_masculino}</span></td>";
        echo "<td class='text-center'><span class='badge bg-warning'>{$cantidad_femenino}</span></td>";
        echo "<td class='text-center'><span class='badge bg-primary'>{$row['total_personas']}</span></td>";
        echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
        echo "<td class='col-actions'>";
        echo "<button class='btn btn-sm btn-danger btn-action' onclick='eliminarRegistro({$row['id_registro_masivo_cm']})'>";
        echo "<i class='bi bi-trash'></i>";
        echo "</button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center'>No hay registros masivos</td></tr>";
}
?>
