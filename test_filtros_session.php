<?php
session_start();
require_once 'code/filtros_grupos.php';
require_once 'conexion.php';

// Simular sesión de usuario tipo 4 (TÉCNICO CPSAM)
$_SESSION['tipo_usuario'] = 4;

echo "=== SIMULANDO USUARIO TIPO 4 (TÉCNICO CPSAM) ===\n\n";

$tipo_usuario = $_SESSION['tipo_usuario'];
$where_grupos = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

echo "Filtro WHERE generado:\n";
echo $where_grupos . "\n\n";

echo "=== GRUPOS VISIBLES PARA TÉCNICO CPSAM ===\n";
$query_grupos = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos ORDER BY g.descripcion_grupo ASC";
echo "Query: " . $query_grupos . "\n\n";

$result = $mysqli->query($query_grupos);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['id_grupo'] . " - " . $row['descripcion_grupo'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n=== REGISTROS DE ACTIVIDADES VISIBLES ===\n";
$query_actividades = "SELECT ra.id_registro, g.descripcion_grupo, ra.fecha_atencion, ra.nombre_lider
                      FROM registro_actividades ra
                      LEFT JOIN grupos g ON ra.id_centro_vida = g.id_grupo
                      WHERE 1 $where_grupos
                      ORDER BY ra.fecha_atencion DESC
                      LIMIT 10";
echo "Query: " . $query_actividades . "\n\n";

$result_act = $mysqli->query($query_actividades);
if ($result_act) {
    if ($result_act->num_rows > 0) {
        while ($row = $result_act->fetch_assoc()) {
            echo "ID: " . $row['id_registro'] . " | Centro: " . ($row['descripcion_grupo'] ?? 'N/A') . " | Fecha: " . $row['fecha_atencion'] . " | Líder: " . $row['nombre_lider'] . "\n";
        }
    } else {
        echo "No hay registros de actividades con los grupos permitidos\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n=== PERSONAS VISIBLES ===\n";
$query_personas = "SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, g.descripcion_grupo
                   FROM personas p
                   LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
                   WHERE p.estado_persona = 1 $where_grupos
                   ORDER BY p.apellidos_persona ASC
                   LIMIT 10";
echo "Query: " . $query_personas . "\n\n";

$result_per = $mysqli->query($query_personas);
if ($result_per) {
    if ($result_per->num_rows > 0) {
        while ($row = $result_per->fetch_assoc()) {
            echo $row['cedula_persona'] . " | " . $row['nombres_persona'] . " " . $row['apellidos_persona'] . " | Grupo: " . ($row['descripcion_grupo'] ?? 'N/A') . "\n";
        }
    } else {
        echo "No hay personas con los grupos permitidos\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

// Ahora simular tipo 5 (TÉCNICO CV)
echo "\n\n=== SIMULANDO USUARIO TIPO 5 (TÉCNICO CENTRO VIDA) ===\n\n";

$_SESSION['tipo_usuario'] = 5;
$tipo_usuario = $_SESSION['tipo_usuario'];
$where_grupos_cv = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'g');

echo "Filtro WHERE generado:\n";
echo $where_grupos_cv . "\n\n";

echo "=== GRUPOS VISIBLES PARA TÉCNICO CV ===\n";
$query_grupos_cv = "SELECT g.* FROM grupos g WHERE 1=1 $where_grupos_cv ORDER BY g.descripcion_grupo ASC";

$result_cv = $mysqli->query($query_grupos_cv);
if ($result_cv) {
    while ($row = $result_cv->fetch_assoc()) {
        echo $row['id_grupo'] . " - " . $row['descripcion_grupo'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

$mysqli->close();
?>
