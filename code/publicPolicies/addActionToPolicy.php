<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descripcion_accion = $_POST['descripcion_accion'];
    $id_politica = $_POST['id_politica'];
    $id_actividad = $_POST['id_actividad'];
    
    // Validar que todos los campos estén llenos
    if (empty($descripcion_accion) || empty($id_politica) || empty($id_actividad)) {
        echo "<script>
            alert('Error: Todos los campos son requeridos');
            window.location.href = 'seePublicPolicies.php';
          </script>";
        exit;
    }
    
    // Insertar en acciones usando prepared statements
    $sql_insert_accion = "INSERT INTO acciones (descripcion_accion, id_actividad, id_politica) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql_insert_accion);
    $stmt->bind_param("sii", $descripcion_accion, $id_actividad, $id_politica);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Acción agregada correctamente a la política pública');
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
