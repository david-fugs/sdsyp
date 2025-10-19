<?php
session_start();
require_once 'code/filtros_grupos.php';
require_once 'conexion.php';

// Simular usuario tipo 4
$_SESSION['tipo_usuario'] = 4;
$tipo_usuario = 4;

echo "=== DEBUG: TÉCNICO CPSAM (Tipo 4) ===\n\n";

// Test con alias 'p' (tabla personas)
$where_grupos_p = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');
echo "WHERE con alias 'p': " . $where_grupos_p . "\n\n";

// Test con alias 'g' (tabla grupos)
$where_grupos_g = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
echo "WHERE con alias 'g': " . $where_grupos_g . "\n\n";

// Ver qué grupos están devolviendo
$grupos_permitidos = getGruposPermitidos($mysqli, $tipo_usuario);
echo "IDs de grupos permitidos: " . implode(', ', $grupos_permitidos) . "\n\n";

// Verificar qué grupos son
echo "Detalles de grupos permitidos:\n";
if (!empty($grupos_permitidos)) {
    $ids = implode(',', $grupos_permitidos);
    $query = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE id_grupo IN ($ids) ORDER BY descripcion_grupo";
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "  - ID: " . $row['id_grupo'] . " | " . $row['descripcion_grupo'] . "\n";
    }
}

// Probar consulta de personas con el filtro
echo "\n=== CONSULTA DE PERSONAS (Primeras 5) ===\n";
$where = "WHERE p.estado_persona = 1";
$where .= $where_grupos_p;

$query_personas = "
SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, g.descripcion_grupo, p.id_grupo
FROM personas p
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
$where
ORDER BY p.apellidos_persona ASC
LIMIT 5
";

echo "Query: " . $query_personas . "\n\n";

$result = $mysqli->query($query_personas);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['cedula_persona'] . " | " . $row['nombres_persona'] . " " . $row['apellidos_persona'] . " | Grupo: " . ($row['descripcion_grupo'] ?? 'N/A') . " (ID: " . $row['id_grupo'] . ")\n";
    }
} else {
    echo "  (Sin resultados o error: " . $mysqli->error . ")\n";
}

$mysqli->close();
?>
