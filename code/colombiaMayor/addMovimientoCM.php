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
    
    // Capturar datos del formulario
    $cedula_persona_cm = $mysqli->real_escape_string($_POST['cedula_persona_cm']);
    $id_condicion_cm = $_POST['id_condicion_cm'];
    $fecha_movimiento_cm = $_POST['fecha_movimiento_cm'];
    $observaciones_cm = $mysqli->real_escape_string($_POST['observaciones_cm'] ?? '');
    $departamento_procedencia_cm = $mysqli->real_escape_string($_POST['departamento_procedencia_cm'] ?? '');
    $municipio_procedencia_cm = $mysqli->real_escape_string($_POST['municipio_procedencia_cm'] ?? '');

    // Verificar que la persona existe
    $check_persona = "SELECT cedula_persona_cm, estado_cm FROM personas_colombia_mayor WHERE cedula_persona_cm = '$cedula_persona_cm'";
    $result_persona = $mysqli->query($check_persona);
    
    if ($result_persona->num_rows == 0) {
        echo "<script>
            alert('Error: La persona no está registrada en Colombia Mayor');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
        exit();
    }

    $persona = $result_persona->fetch_assoc();
    $estado_anterior = $persona['estado_cm'];

    // Obtener la descripción de la condición para determinar el nuevo estado (actualizado a condiciones_componente)
    $query_condicion = "SELECT descripcion_condicion FROM condiciones_componente WHERE id_condicion = $id_condicion_cm";
    $result_condicion = $mysqli->query($query_condicion);
    
    // Verificar que se encontró la condición
    if (!$result_condicion || $result_condicion->num_rows == 0) {
        echo "<script>
            alert('Error: La condición seleccionada no es válida');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
        exit();
    }
    
    $condicion = $result_condicion->fetch_assoc();
    $descripcion_condicion = strtolower($condicion['descripcion_condicion']);

    // Determinar el nuevo estado basado en la condición
    $estado_nuevo = $estado_anterior;
    if (strpos($descripcion_condicion, 'suspendido') !== false) {
        $estado_nuevo = 'SUSPENDIDO';
    } elseif (strpos($descripcion_condicion, 'fallecido') !== false) {
        $estado_nuevo = 'FALLECIDO';
    } elseif (strpos($descripcion_condicion, 'retiro voluntario') !== false) {
        $estado_nuevo = 'RETIRO_VOLUNTARIO';
    } elseif (strpos($descripcion_condicion, 'reactivación') !== false || strpos($descripcion_condicion, 'ingreso') !== false) {
        $estado_nuevo = 'ACTIVO';
    }

    // Insertar movimiento
    $sql_insert = "INSERT INTO movimientos_colombia_mayor (
        cedula_persona_cm,
        id_condicion_cm,
        fecha_movimiento_cm,
        observaciones_cm,
        departamento_procedencia_cm,
        municipio_procedencia_cm,
        estado_anterior_cm,
        estado_nuevo_cm,
        usuario_registro,
        fecha_registro
    ) VALUES (
        '$cedula_persona_cm',
        $id_condicion_cm,
        '$fecha_movimiento_cm',
        '$observaciones_cm',
        '$departamento_procedencia_cm',
        '$municipio_procedencia_cm',
        '$estado_anterior',
        '$estado_nuevo',
        '$id_usuario',
        NOW()
    )";

    if ($mysqli->query($sql_insert)) {
        // Actualizar el estado de la persona si cambió
        if ($estado_nuevo != $estado_anterior) {
            $update_persona = "UPDATE personas_colombia_mayor SET estado_cm = '$estado_nuevo', usuario_modificacion = '$id_usuario', fecha_modificacion = NOW() WHERE cedula_persona_cm = '$cedula_persona_cm'";
            $mysqli->query($update_persona);
        }

        echo "<script>
            alert('Movimiento registrado correctamente');
            window.location.href = 'seeMovimientosCM.php';
          </script>";
    } else {
        echo "<script>
            alert('Error al registrar movimiento: " . $mysqli->error . "');
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
