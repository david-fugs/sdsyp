<?php
echo "=== TEST FINAL: FILTROS DE BÚSQUEDA AJAX ===\n\n";

// Test 1: getPersonsAjax.php
echo "1. Test getPersonsAjax.php (Búsqueda por cédula de CV con usuario CPSAM)\n";
session_start();
$_SESSION['tipo_usuario'] = 4;
$_SESSION['id_grupo'] = null;
$_GET['cedula_persona'] = '34057604'; // Cédula de CV

include("conexion.php");
require_once('code/filtros_grupos.php');

$tipo_usuario = 4;
$id_grupo_session = null;
$where_grupos_filtro = getWhereGruposPermitidos($mysqli, $tipo_usuario, 'p');

$where = "WHERE p.estado_persona = 1";
if (!empty($_GET['cedula_persona'])) {
    $cedula = $mysqli->real_escape_string($_GET['cedula_persona']);
    $where .= " AND p.cedula_persona LIKE '%$cedula%'";
}

if ($tipo_usuario != 1 && $id_grupo_session && !in_array($tipo_usuario, [3, 4, 5])) {
    $where .= " AND p.id_grupo = '" . $mysqli->real_escape_string($id_grupo_session) . "'";
}

$where .= $where_grupos_filtro;

$query = "SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.id_grupo, g.descripcion_grupo
          FROM personas p
          LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
          $where";

$result = $mysqli->query($query);
echo "   WHERE: $where\n";
echo "   Resultados: " . $result->num_rows . "\n";
if ($result->num_rows > 0) {
    echo "   ❌ ERROR: Se encontró persona de CV\n";
} else {
    echo "   ✅ CORRECTO: No se encontró (filtrado correctamente)\n";
}

// Test 2: buscar_persona.php
echo "\n2. Test buscar_persona.php (Búsqueda de persona de CV con usuario CPSAM)\n";

$grupos_permitidos = getGruposPermitidos($mysqli, $tipo_usuario);
$ids_grupos = implode(',', $grupos_permitidos);
$where_grupo = " AND p.id_grupo IN ($ids_grupos)";

$stmt = $mysqli->prepare("
    SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.id_grupo, g.descripcion_grupo
    FROM personas p
    LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
    WHERE p.cedula_persona = ? $where_grupo
");
$cedula = '34057604';
$stmt->bind_param("s", $cedula);
$stmt->execute();
$resultado = $stmt->get_result();

echo "   Grupos permitidos: $ids_grupos\n";
echo "   WHERE grupo: $where_grupo\n";
echo "   Resultados: " . $resultado->num_rows . "\n";
if ($resultado->num_rows > 0) {
    echo "   ❌ ERROR: Se encontró persona de CV\n";
} else {
    echo "   ✅ CORRECTO: No se encontró (filtrado correctamente)\n";
}

// Test 3: Buscar persona de CPSAM (debería encontrarla)
echo "\n3. Test buscar_persona.php (Búsqueda de persona CPSAM con usuario CPSAM)\n";

$stmt2 = $mysqli->prepare("
    SELECT p.cedula_persona, p.nombres_persona, p.apellidos_persona, p.id_grupo, g.descripcion_grupo
    FROM personas p
    LEFT JOIN grupos g ON p.id_grupo = g.id_grupo
    WHERE p.id_grupo IN ($ids_grupos) AND p.estado_persona = 1
    LIMIT 1
");
$stmt2->execute();
$resultado2 = $stmt2->get_result();

if ($row = $resultado2->fetch_assoc()) {
    echo "   Persona encontrada: " . $row['nombres_persona'] . " " . $row['apellidos_persona'] . "\n";
    echo "   Grupo: " . $row['descripcion_grupo'] . " (ID: " . $row['id_grupo'] . ")\n";
    echo "   Cédula: " . $row['cedula_persona'] . "\n";
    
    // Ahora intentar buscar esta persona con el filtro
    $cedula_cpsam = $row['cedula_persona'];
    $stmt3 = $mysqli->prepare("
        SELECT p.cedula_persona, p.nombres_persona
        FROM personas p
        WHERE p.cedula_persona = ? $where_grupo
    ");
    $stmt3->bind_param("s", $cedula_cpsam);
    $stmt3->execute();
    $resultado3 = $stmt3->get_result();
    
    echo "   Resultados con filtro: " . $resultado3->num_rows . "\n";
    if ($resultado3->num_rows > 0) {
        echo "   ✅ CORRECTO: Se encontró persona de CPSAM\n";
    } else {
        echo "   ❌ ERROR: No se encontró persona de CPSAM (debería encontrarla)\n";
    }
}

echo "\n=== FIN DEL TEST ===\n";

$mysqli->close();
?>
