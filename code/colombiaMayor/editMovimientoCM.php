<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_SESSION['id'];
    
    $id_movimiento_cm = $_POST['id_movimiento_cm'];
    $cedula_persona_cm = $mysqli->real_escape_string($_POST['cedula_persona_cm']);
    $id_condicion_cm = $_POST['id_condicion_cm'];
    $fecha_movimiento_cm = $_POST['fecha_movimiento_cm'];
    $observaciones_cm = $mysqli->real_escape_string($_POST['observaciones_cm'] ?? '');

    // Actualizar movimiento
    $sql_update = "UPDATE movimientos_colombia_mayor SET
        id_condicion_cm = $id_condicion_cm,
        fecha_movimiento_cm = '$fecha_movimiento_cm',
        observaciones_cm = '$observaciones_cm',
        usuario_modificacion = '$id_usuario',
        fecha_modificacion = NOW()
    WHERE id_movimiento_cm = $id_movimiento_cm";

    if ($mysqli->query($sql_update)) {
        echo "<script>
            alert('Movimiento actualizado correctamente');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
    } else {
        echo "<script>
            alert('Error al actualizar movimiento: " . $mysqli->error . "');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
    }
} else {
    echo "<script>
            alert('Método no válido');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
}
?>
