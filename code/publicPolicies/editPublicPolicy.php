<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descripcion_politica = $_POST['descripcion_politica'];
    $id_politica = $_POST['id_politica'];
    $id_accion = $_POST['id_accion'] ?? '';
    
    // Validar que los campos no estén vacíos
    if (empty($descripcion_politica) || empty($id_politica)) {
        echo "<script>
            alert('Error: Todos los campos son requeridos');
            window.location.href = 'seePublicPolicies.php';
          </script>";
        exit;
    }
    
    // Actualizar política pública usando prepared statements
    $sql_update_politica = "UPDATE politicas_publicas SET descripcion_politica=?, id_accion=? WHERE id_politica=?";
    $stmt = $mysqli->prepare($sql_update_politica);
    $stmt->bind_param("sii", $descripcion_politica, $id_accion, $id_politica);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Política pública actualizada correctamente');
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
