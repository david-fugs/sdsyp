<?php
include("../../conexion.php");

echo "<h2>Creando tabla de Políticas Públicas...</h2>";

// Crear la tabla
$create_table_sql = "CREATE TABLE IF NOT EXISTS politicas_publicas (
    id_politica INT AUTO_INCREMENT PRIMARY KEY,
    descripcion_politica VARCHAR(500) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($mysqli->query($create_table_sql)) {
    echo "<p>✓ Tabla 'politicas_publicas' creada exitosamente</p>";
} else {
    echo "<p>✗ Error creando tabla: " . $mysqli->error . "</p>";
}

// Verificar si ya existen datos
$check_data = "SELECT COUNT(*) as total FROM politicas_publicas";
$result = $mysqli->query($check_data);
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    echo "<h3>Insertando datos de ejemplo...</h3>";
    
    $sample_data = [
        'Política Nacional de Envejecimiento y Vejez',
        'Política Pública Nacional de Discapacidad e Inclusión Social',
        'Política de Seguridad Alimentaria y Nutricional',
        'Política de Primera Infancia',
        'Política Nacional de Equidad de Género'
    ];
    
    foreach ($sample_data as $politica) {
        $insert_sql = "INSERT INTO politicas_publicas (descripcion_politica) VALUES (?)";
        $stmt = $mysqli->prepare($insert_sql);
        $stmt->bind_param("s", $politica);
        
        if ($stmt->execute()) {
            echo "<p>✓ Insertada: " . $politica . "</p>";
        } else {
            echo "<p>✗ Error insertando: " . $politica . " - " . $mysqli->error . "</p>";
        }
        $stmt->close();
    }
} else {
    echo "<p>⚠ Ya existen " . $row['total'] . " registros en la tabla</p>";
}

// Mostrar datos actuales
echo "<h3>Datos actuales en la tabla:</h3>";
$show_data = "SELECT * FROM politicas_publicas ORDER BY id_politica";
$result = $mysqli->query($show_data);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Descripción</th><th>Fecha Creación</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_politica'] . "</td>";
        echo "<td>" . $row['descripcion_politica'] . "</td>";
        echo "<td>" . $row['fecha_creacion'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay datos en la tabla</p>";
}

echo "<h3>Proceso completado!</h3>";
echo "<p><a href='seePublicPolicies.php'>Ir a gestionar Políticas Públicas</a></p>";

$mysqli->close();
?>
