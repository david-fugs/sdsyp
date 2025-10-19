<?php
session_start();
$_SESSION['tipo_usuario'] = 4; // Simular usuario TÉCNICO CPSAM
$_SESSION['id_grupo'] = null; // Sin grupo específico en sesión

// Simular búsqueda por cédula
$_GET['cedula_persona'] = '34057604';

include("conexion.php");
require_once('code/filtros_grupos.php');

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : null;
$id_grupo_session = isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;

// Aplicar filtro de grupos según tipo de usuario (tipos 4 y 5)
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

echo "=== TEST BÚSQUEDA POR CÉDULA 34057604 (CV) ===\n";
echo "Tipo Usuario: $tipo_usuario (TÉCNICO CPSAM)\n";
echo "Filtro grupos: $where_grupos_filtro\n\n";

$where = "WHERE p.estado_persona = 1";

// Filtro por cédula
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona = '$cedula'";
}

// Filtrar por id_grupo si el tipo_usuario en la sesión es diferente de 1, 3, 4 y 5
if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

// Aplicar filtro adicional para usuarios técnicos (tipos 4 y 5)
$where .= $where_grupos_filtro;

echo "WHERE completo:\n$where\n\n";

// Consulta SQL para obtener los datos
$query = "
SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.id_grupo, g.descripcion_grupo
FROM personas p
LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
$where
";

echo "Query:\n$query\n\n";

$result = $mysqli->query($query);

echo "Resultados encontrados: " . $result->num_rows . "\n\n";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "❌ ERROR: Se encontró persona de CV cuando usuario es TÉCNICO CPSAM\n";
        echo "  Cédula: " . $row['cedula_persona'] . "\n";
        echo "  Nombre: " . $row['nombres_persona'] . " " . $row['apellidos_persona'] . "\n";
        echo "  Grupo: " . $row['descripcion_grupo'] . " (ID: " . $row['id_grupo'] . ")\n";
    }
} else {
    echo "✅ CORRECTO: No se encontró la persona (está filtrada correctamente)\n";
}

$mysqli->close();
?>
