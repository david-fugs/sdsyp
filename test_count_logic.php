<?php
include("conexion.php");

// Script temporal para testear la nueva lógica de conteo
// Este archivo debe ser eliminado después de las pruebas

echo "<h2>Prueba de lógica de conteo actualizada</h2>";

// Verificar la estructura de la tabla movimiento_persona
echo "<h3>Estructura de la columna id_centro_vida_traslado:</h3>";
$query_structure = "SHOW COLUMNS FROM movimiento_persona LIKE 'id_centro_vida_traslado'";
$result_structure = $mysqli->query($query_structure);
if ($row_structure = $result_structure->fetch_assoc()) {
    echo "<p><strong>Tipo:</strong> " . $row_structure['Type'] . "</p>";
    echo "<p><strong>Null:</strong> " . $row_structure['Null'] . "</p>";
    echo "<p><strong>Default:</strong> " . $row_structure['Default'] . "</p>";
} else {
    echo "<p>Columna no encontrada</p>";
}

// Verificar valores en id_centro_vida_traslado
echo "<h3>Valores en id_centro_vida_traslado:</h3>";
$query_valores = "SELECT id_centro_vida_traslado, COUNT(*) as cantidad 
                  FROM movimiento_persona 
                  GROUP BY id_centro_vida_traslado 
                  ORDER BY id_centro_vida_traslado";
$result_valores = $mysqli->query($query_valores);

echo "<table border='1'>";
echo "<tr><th>Valor</th><th>Cantidad</th></tr>";
while ($row = $result_valores->fetch_assoc()) {
    $valor = $row['id_centro_vida_traslado'] === null ? 'NULL' : $row['id_centro_vida_traslado'];
    echo "<tr><td>" . $valor . "</td><td>" . $row['cantidad'] . "</td></tr>";
}
echo "</table>";

// Obtener todos los grupos
$query_grupos = "SELECT id_grupo, descripcion_grupo, limite_personas FROM grupos";
$result_grupos = $mysqli->query($query_grupos);

echo "<h3>Conteo por grupos:</h3>";
echo "<table border='1'>";
echo "<tr><th>Grupo</th><th>Límite</th><th>Conteo Anterior</th><th>Conteo Nuevo</th><th>Diferencia</th></tr>";

while ($grupo = $result_grupos->fetch_assoc()) {
    $id_grupo = $grupo['id_grupo'];
    
    // Conteo anterior (sin exclusiones)
    $query_old = "SELECT COUNT(*) as total FROM personas WHERE id_grupo = $id_grupo AND estado_persona = 1";
    $result_old = $mysqli->query($query_old);
    $count_old = $result_old->fetch_assoc()['total'];
    
    // Conteo nuevo (con exclusiones)
    $query_new = "SELECT COUNT(*) as total 
                  FROM personas p
                  WHERE p.id_grupo = $id_grupo 
                  AND p.estado_persona = 1
                  AND p.cedula_persona NOT IN (
                      SELECT DISTINCT mp.cedula_persona 
                      FROM movimiento_persona mp
                      JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                      WHERE cc.descripcion_condicion IN (
                          'CPSAM EVADIDO', 
                          'CPSAM FALLECIDO', 
                          'CPSAM RETIRADO VOLUNTARIO', 
                          'CPSAM TRASLADADO'
                      )
                  )";
    $result_new = $mysqli->query($query_new);
    $count_new = $result_new->fetch_assoc()['total'];
    
    $diferencia = $count_old - $count_new;
    
    echo "<tr>";
    echo "<td>" . $grupo['descripcion_grupo'] . "</td>";
    echo "<td>" . $grupo['limite_personas'] . "</td>";
    echo "<td>" . $count_old . "</td>";
    echo "<td>" . $count_new . "</td>";
    echo "<td>" . $diferencia . "</td>";
    echo "</tr>";
}

echo "</table>";

// Mostrar personas excluidas
echo "<h3>Personas excluidas del conteo por tener movimientos que liberan cupo:</h3>";
$query_excluidas = "SELECT DISTINCT p.cedula_persona, p.nombres_persona, p.apellidos_persona, 
                   cc.descripcion_condicion, g.descripcion_grupo
                   FROM personas p
                   JOIN movimiento_persona mp ON p.cedula_persona = mp.cedula_persona
                   JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                   JOIN grupos g ON p.id_grupo = g.id_grupo
                   WHERE cc.descripcion_condicion IN (
                       'CPSAM EVADIDO', 
                       'CPSAM FALLECIDO', 
                       'CPSAM RETIRADO VOLUNTARIO', 
                       'CPSAM TRASLADADO'
                   )
                   AND p.estado_persona = 1
                   ORDER BY g.descripcion_grupo, p.nombres_persona";

$result_excluidas = $mysqli->query($query_excluidas);

if ($result_excluidas->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Cédula</th><th>Nombre</th><th>Grupo</th><th>Movimiento</th></tr>";
    
    while ($persona = $result_excluidas->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $persona['cedula_persona'] . "</td>";
        echo "<td>" . $persona['nombres_persona'] . " " . $persona['apellidos_persona'] . "</td>";
        echo "<td>" . $persona['descripcion_grupo'] . "</td>";
        echo "<td>" . $persona['descripcion_condicion'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No hay personas excluidas del conteo.</p>";
}

$mysqli->close();
?>
