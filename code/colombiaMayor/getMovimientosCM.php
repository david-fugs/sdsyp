<?php
// Este archivo es incluido por seeMovimientosCM.php

$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id'];

// Construir query base
$where = "WHERE 1=1";

// Si es contratista (tipo 9), solo ver sus propios registros
if ($tipo_usuario == 9) {
    $where .= " AND m.usuario_registro = '$id_usuario'";
}

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND m.cedula_persona_cm = '$cedula'";
}

// Filtro por nombre
if (!empty($_GET['nombre'])) {
    $nombre = $mysqli->real_escape_string($_GET['nombre']);
    $where .= " AND (p.nombres_persona_cm LIKE '%$nombre%' OR p.apellidos_persona_cm LIKE '%$nombre%')";
}

// Filtro por condición
if (!empty($_GET['condicion'])) {
    $condicion = $mysqli->real_escape_string($_GET['condicion']);
    $where .= " AND m.id_condicion_cm = '$condicion'";
}

// Consulta SQL (actualizada para usar condiciones_componente)
$query = "
    SELECT 
        m.*,
        p.nombres_persona_cm,
        p.apellidos_persona_cm,
        p.estado_cm,
        c.descripcion_condicion as descripcion_condicion_cm,
        u.nombre AS nombre_contratista
    FROM movimientos_colombia_mayor m
    INNER JOIN personas_colombia_mayor p ON m.cedula_persona_cm = p.cedula_persona_cm
    LEFT JOIN condiciones_componente c ON m.id_condicion_cm = c.id_condicion
    LEFT JOIN usuarios u ON m.usuario_registro = u.id
    $where
    ORDER BY m.fecha_movimiento_cm DESC, m.fecha_registro DESC
";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determinar si la condición indica inactividad
        $descripcion_condicion = strtolower($row['descripcion_condicion_cm']);
        $es_inactivo = (
            strpos($descripcion_condicion, 'suspendido') !== false ||
            strpos($descripcion_condicion, 'fallecido') !== false ||
            strpos($descripcion_condicion, 'retiro voluntario') !== false
        );

        $badge_condicion = '';
        if ($es_inactivo) {
            if (strpos($descripcion_condicion, 'fallecido') !== false) {
                $badge_condicion = 'badge-fallecido';
            } elseif (strpos($descripcion_condicion, 'suspendido') !== false) {
                $badge_condicion = 'badge-suspendido';
            } else {
                $badge_condicion = 'badge-retiro';
            }
        }

        echo "<tr class='fade-in'>";
        echo "<td><strong>" . htmlspecialchars($row['cedula_persona_cm']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['nombres_persona_cm']) . "</td>";
        echo "<td>" . htmlspecialchars($row['apellidos_persona_cm']) . "</td>";
        echo "<td><span class='badge $badge_condicion'>" . htmlspecialchars($row['descripcion_condicion_cm']) . "</span></td>";
        echo "<td>" . date('d/m/Y', strtotime($row['fecha_movimiento_cm'])) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['observaciones_cm'], 0, 100)) . (strlen($row['observaciones_cm']) > 100 ? '...' : '') . "</td>";

        // Botones de acción
        echo '<td class="col-actions">
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-edit" 
                        title="Editar movimiento"
                        data-bs-toggle="modal" data-bs-target="#modalEdicion"
                        data-id="' . $row['id_movimiento_cm'] . '"
                        data-cedula="' . htmlspecialchars($row['cedula_persona_cm']) . '"
                        data-condicion="' . $row['id_condicion_cm'] . '"
                        data-fecha="' . $row['fecha_movimiento_cm'] . '"
                        data-observacion="' . htmlspecialchars($row['observaciones_cm']) . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn-action btn-delete" 
                        title="Eliminar movimiento"
                        data-id="' . $row['id_movimiento_cm'] . '">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
              </td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No se encontraron registros</td></tr>";
}
?>
