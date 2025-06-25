<?php
session_start();
include("../../conexion.php");

// Consulta para obtener políticas públicas y sus acciones
$query = "
    SELECT 
        'politica' as tipo,
        pp.id_politica as id,
        pp.descripcion_politica as descripcion,
        pp.descripcion_politica as politica_nombre,
        NULL as actividad_nombre,
        NULL as id_politica_rel,
        NULL as id_actividad,
        NULL as id_accion
    FROM politicas_publicas pp
    
    UNION ALL
    
    SELECT 
        'accion' as tipo,
        a.id_accion as id,
        a.descripcion_accion as descripcion,
        pp.descripcion_politica as politica_nombre,
        act.descripcion_actividad as actividad_nombre,
        a.id_politica as id_politica_rel,
        a.id_actividad,
        a.id_accion
    FROM acciones a
    LEFT JOIN politicas_publicas pp ON a.id_politica = pp.id_politica
    JOIN actividades act ON a.id_actividad = act.id_actividad
    
    ORDER BY politica_nombre, tipo DESC, id
";

$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        
        // Tipo
        if ($row['tipo'] == 'politica') {
            echo "<td><span class='badge bg-success'>Política</span></td>";
        } else {
            echo "<td><span class='badge bg-primary'>Acción</span></td>";
        }
        
        // ID
        echo "<td>" . $row['id'] . "</td>";
        
        // Descripción
        echo "<td>" . $row['descripcion'] . "</td>";
        
        // Política Pública
        echo "<td>" . ($row['politica_nombre'] ?? 'N/A') . "</td>";
        
        // Actividad
        echo "<td>" . ($row['actividad_nombre'] ?? 'N/A') . "</td>";
        
        // Acciones (botones)
        echo '<td>';
        
        if ($row['tipo'] == 'politica') {
            // Botones para política
            echo '
                <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicion"
                    data-id_politica="' . $row['id'] . '"
                    data-descripcion_politica="' . $row['descripcion'] . '"
                    title="Editar Política">
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?delete=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" 
                   onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta política pública?\')"
                   title="Eliminar Política">
                    <i class="bi bi-trash"></i>
                </a>';
        } else {
            // Botones para acción
            echo '
                <button type="button" class="btn btn-sm btn-outline-warning me-1" 
                    data-bs-toggle="modal" data-bs-target="#modalEdicionAction"
                    data-id_accion="' . $row['id_accion'] . '"
                    data-descripcion_accion="' . $row['descripcion'] . '"
                    data-id_politica="' . $row['id_politica_rel'] . '"
                    data-id_actividad="' . $row['id_actividad'] . '"
                    title="Editar Acción">
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?deleteAction=' . $row['id_accion'] . '" class="btn btn-sm btn-outline-danger" 
                   onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta acción?\')"
                   title="Eliminar Acción">
                    <i class="bi bi-trash"></i>
                </a>';
        }
        
        echo '</td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No se encontraron registros.</td></tr>";
}

$mysqli->close();
?>
