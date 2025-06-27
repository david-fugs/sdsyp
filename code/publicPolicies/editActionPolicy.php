<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descripcion_accion = $_POST['descripcion_accion'];
    $id_politica = $_POST['id_politica'];
    $id_actividad = $_POST['id_actividad'];
    $id_accion = $_POST['id_accion'];
    
    // Validar que todos los campos estén llenos
    if (empty($descripcion_accion) || empty($id_politica) || empty($id_actividad) || empty($id_accion)) {
        echo "<script>
            alert('Error: Todos los campos son requeridos');
            window.location.href = 'seePublicPolicies.php';
          </script>";
        exit;
    }
    
    // Actualizar acción usando prepared statements
    $sql_update_accion = "UPDATE acciones SET descripcion_accion=?, id_actividad=?, id_politica=? WHERE id_accion=?";
    $stmt = $mysqli->prepare($sql_update_accion);
    $stmt->bind_param("siii", $descripcion_accion, $id_actividad, $id_politica, $id_accion);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Acción actualizada correctamente');
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
