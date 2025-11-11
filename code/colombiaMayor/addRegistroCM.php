<?php
session_start();
include("../../conexion.php");

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    header("Location: ../../access.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_id = $_SESSION['id'];
    
    // Capturar datos del formulario
    $cedulas_json = $_POST['cedulas'] ?? '[]';
    $id_condicion = $_POST['id_condicion'];
    $id_meta = $_POST['id_meta'];
    $id_actividad = $_POST['id_actividad'];
    $id_accion = $_POST['id_accion'];
    $id_politica_publica = $_POST['id_politica_publica'] ?? null;
    $fecha_registro_actividad = $_POST['fecha_registro_actividad'];
    $observaciones = $mysqli->real_escape_string($_POST['observaciones'] ?? '');
    
    // Decodificar el JSON de cédulas
    $cedulas = json_decode($cedulas_json, true);
    
    if (empty($cedulas) || !is_array($cedulas)) {
        echo "<script>
            alert('Error: Debe agregar al menos una persona');
            window.location.href = 'formIndividualCM.php';
          </script>";
        exit();
    }
    
    // Iniciar transacción
    $mysqli->begin_transaction();
    
    try {
        $registros_exitosos = 0;
        $registros_fallidos = 0;
        $cedulas_fallidas = [];
        
        foreach ($cedulas as $cedula) {
            // Verificar que la persona existe
            $check_persona = "SELECT cedula_persona_cm FROM personas_colombia_mayor WHERE cedula_persona_cm = ?";
            $stmt_check = $mysqli->prepare($check_persona);
            $stmt_check->bind_param("s", $cedula);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows == 0) {
                $registros_fallidos++;
                $cedulas_fallidas[] = $cedula;
                $stmt_check->close();
                continue; // Saltar esta cédula
            }
            $stmt_check->close();
            
            // Insertar registro individual para esta persona
            $sql_insert = "INSERT INTO registros_individuales_cm (
                cedula_persona_cm,
                id_condicion,
                id_meta,
                id_actividad,
                id_accion,
                id_politica_publica,
                fecha_registro_actividad,
                observaciones,
                usuario_registro,
                fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $mysqli->prepare($sql_insert);
            $stmt->bind_param(
                "siiiiissi",
                $cedula,
                $id_condicion,
                $id_meta,
                $id_actividad,
                $id_accion,
                $id_politica_publica,
                $fecha_registro_actividad,
                $observaciones,
                $usuario_id
            );
            
            if ($stmt->execute()) {
                $registros_exitosos++;
            } else {
                $registros_fallidos++;
                $cedulas_fallidas[] = $cedula;
            }
            $stmt->close();
        }
        
        // Confirmar transacción
        $mysqli->commit();
        
        // Mensaje de resultado
        $mensaje = "Registros creados exitosamente: $registros_exitosos";
        if ($registros_fallidos > 0) {
            $mensaje .= "\\nRegistros fallidos: $registros_fallidos";
            if (!empty($cedulas_fallidas)) {
                $mensaje .= "\\nCédulas con error: " . implode(', ', $cedulas_fallidas);
            }
        }
        
        echo "<script>
            alert('$mensaje');
            window.location.href = 'formIndividualCM.php';
          </script>";
          
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $mysqli->rollback();
        
        echo "<script>
            alert('Error al crear los registros: " . $e->getMessage() . "');
            window.location.href = 'formIndividualCM.php';
          </script>";
    }
    
} else {
    header("Location: formIndividualCM.php");
    exit();
}

$mysqli->close();
?>
