<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {    $descripcion_accion = $_POST['descripcion_accion'];
    $id_actividad = $_POST['id_actividad'];
      // Debug: Verificar qué valores estamos recibiendo
    // echo "Descripción: " . $descripcion_accion . "<br>";
    // echo "ID Actividad: " . $id_actividad . "<br>";
    // echo "POST data: "; print_r($_POST); echo "<br><br>";
    
    // Validar que id_actividad no esté vacío
    if (empty($id_actividad) || $id_actividad == "0" || $id_actividad == "") {
        echo "<script>
            alert('Error: Debe seleccionar una actividad válida');
            window.location.href = 'seeActions.php';
          </script>";
        exit;
    }
    
    //insertar en acciones usando prepared statements para seguridad
    $sql_insert_accion = "INSERT INTO acciones (descripcion_accion, id_actividad) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql_insert_accion);
    $stmt->bind_param("si", $descripcion_accion, $id_actividad);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Insertado correctamente');
            window.location.href = 'seeActions.php';
          </script>";
    } else {
        echo "<script>
            alert('Error: " . $mysqli->error . "');
            window.location.href = 'seeActions.php';
          </script>";
    }
    
    $stmt->close();


  
}