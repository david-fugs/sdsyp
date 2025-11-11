<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_registro = $_POST['fecha_registro'];
    $id_meta = intval($_POST['id_meta']);
    $id_actividad = intval($_POST['id_actividad']);
    $id_accion = intval($_POST['id_accion']);
    $id_politica_publica = !empty($_POST['id_politica_publica']) ? intval($_POST['id_politica_publica']) : NULL;
    $cantidad_masculino = intval($_POST['cantidad_masculino'] ?? 0);
    $cantidad_femenino = intval($_POST['cantidad_femenino'] ?? 0);
    $total_personas = intval($_POST['total_personas'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';
    $usuario_registro = $_SESSION['id'];
    
    // Validar que el total sea mayor a 0
    if ($total_personas == 0) {
        echo "<script>
            alert('Debe ingresar al menos una persona (masculino o femenino).');
            window.location.href = 'formRegistroMasivoCM.php';
        </script>";
        exit();
    }
    
    // Insertar registro masivo con campos de género
    $sql = "INSERT INTO registros_masivos_cm 
            (fecha_registro, id_meta, id_actividad, id_accion, id_politica_publica, cantidad_masculino, cantidad_femenino, total_personas, observaciones, usuario_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("siiiiiissi", $fecha_registro, $id_meta, $id_actividad, $id_accion, $id_politica_publica, $cantidad_masculino, $cantidad_femenino, $total_personas, $observaciones, $usuario_registro);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Registro masivo guardado correctamente. Total: $total_personas personas (M: $cantidad_masculino, F: $cantidad_femenino)');
            window.location.href = 'formRegistroMasivoCM.php';
        </script>";
    } else {
        echo "<script>
            alert('Error al guardar el registro masivo: " . $mysqli->error . "');
            window.location.href = 'formRegistroMasivoCM.php';
        </script>";
    }
    
    $stmt->close();
}

$mysqli->close();
?>
