<?php
session_start();
require_once 'code/filtros_grupos.php';
require_once 'conexion.php';

echo "=== PRUEBA FINAL DE FILTROS ===\n\n";

// Test para Técnico CPSAM (tipo 4)
$_SESSION['tipo_usuario'] = 4;
$tipo_usuario = 4;

echo "--- Usuario Tipo 4 (TÉCNICO CPSAM) ---\n";
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
echo "WHERE clause: $where_grupos\n\n";

// Dropdown de centros
$grupos_query = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC";
$result_grupos_query = mysqli_query($mysqli, $grupos_query);
$result_grupos = mysqli_fetch_all($result_grupos_query, MYSQLI_ASSOC);

echo "Grupos en dropdown (Total: " . count($result_grupos) . "):\n";
foreach ($result_grupos as $grupo) {
    echo "  - ID: " . $grupo['id_grupo'] . " | " . $grupo['descripcion_grupo'] . "\n";
}

// Test actividades
echo "\nActividades visibles (últimas 5):\n";
$where = '';
$query_act = "SELECT ra.id_registro, g.descripcion_grupo, ra.fecha_atencion, ra.nombre_lider
              FROM registro_actividades AS ra
              LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
              WHERE 1 $where $where_grupos
              ORDER BY ra.fecha_atencion DESC
              LIMIT 5";
$result_act = $mysqli->query($query_act);
if ($result_act && $result_act->num_rows > 0) {
    while ($row = $result_act->fetch_assoc()) {
        echo "  - ID: " . $row['id_registro'] . " | Centro: " . ($row['descripcion_grupo'] ?? 'N/A') . " | Fecha: " . $row['fecha_atencion'] . "\n";
    }
} else {
    echo "  (Sin registros)\n";
}

// Test para Técnico CV (tipo 5)
echo "\n\n--- Usuario Tipo 5 (TÉCNICO CENTRO VIDA) ---\n";
$_SESSION['tipo_usuario'] = 5;
$tipo_usuario = 5;

$where_grupos_cv = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');
echo "WHERE clause: $where_grupos_cv\n\n";

// Dropdown de centros
$grupos_query_cv = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos_cv ORDER BY g.descripcion_grupo ASC";
$result_grupos_query_cv = mysqli_query($mysqli, $grupos_query_cv);
$result_grupos_cv = mysqli_fetch_all($result_grupos_query_cv, MYSQLI_ASSOC);

echo "Grupos en dropdown (Total: " . count($result_grupos_cv) . "):\n";
foreach ($result_grupos_cv as $grupo) {
    echo "  - ID: " . $grupo['id_grupo'] . " | " . $grupo['descripcion_grupo'] . "\n";
}

// Test actividades
echo "\nActividades visibles (últimas 5):\n";
$query_act_cv = "SELECT ra.id_registro, g.descripcion_grupo, ra.fecha_atencion, ra.nombre_lider
                 FROM registro_actividades AS ra
                 LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
                 WHERE 1 $where_grupos_cv
                 ORDER BY ra.fecha_atencion DESC
                 LIMIT 5";
$result_act_cv = $mysqli->query($query_act_cv);
if ($result_act_cv && $result_act_cv->num_rows > 0) {
    while ($row = $result_act_cv->fetch_assoc()) {
        echo "  - ID: " . $row['id_registro'] . " | Centro: " . ($row['descripcion_grupo'] ?? 'N/A') . " | Fecha: " . $row['fecha_atencion'] . "\n";
    }
} else {
    echo "  (Sin registros)\n";
}

echo "\n\n=== PRUEBA COMPLETADA ===\n";

$mysqli->close();
?>
