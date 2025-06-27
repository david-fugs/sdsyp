<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descripcion_politica = $_POST['descripcion_politica'];
    $id_accion = $_POST['id_accion'] ?? '';
    
    // Validar que la descripción no esté vacía
    if (empty($descripcion_politica)) {
        echo "<script>
            alert('Error: La descripción de la política pública es requerida');
            window.location.href = 'seePublicPolicies.php';
          </script>";
        exit;
    }
    
    // Insertar en politicas_publicas usando prepared statements para seguridad
    $sql_insert_politica = "INSERT INTO politicas_publicas (descripcion_politica, id_accion) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql_insert_politica);
    $stmt->bind_param("si", $descripcion_politica, $id_accion);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Política pública insertada correctamente');
            window.location.href = 'seePublicPolicies.php';
          </script>";
    } else {
        echo "<script>
            alert('Error: " . $mysqli->error . "');
            window.location.href = 'seePublicPolicies.php';
          </script>";
    }
    
    $stmt->close();
}

$mysqli->close();
?>
