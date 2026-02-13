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
    
    // Procesar fotografías
    $fotos_guardadas = [];
    if (isset($_FILES['fotografias']) && !empty($_FILES['fotografias']['name'][0])) {
        $upload_dir = 'uploads/fotos_registros/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $maxFiles = 3;
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        
        $totalFiles = count($_FILES['fotografias']['name']);
        
        if ($totalFiles > $maxFiles) {
            echo "<script>
                alert('Error: Solo se permiten máximo 3 fotografías');
                window.location.href = 'formIndividualCM.php';
              </script>";
            exit();
        }
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['fotografias']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['fotografias']['tmp_name'][$i];
                $file_name = $_FILES['fotografias']['name'][$i];
                $file_size = $_FILES['fotografias']['size'][$i];
                $file_type = $_FILES['fotografias']['type'][$i];
                
                // Validar tipo
                if (!in_array($file_type, $allowedTypes)) {
                    echo "<script>
                        alert('Error: Solo se permiten archivos JPG, JPEG y PNG');
                        window.location.href = 'formIndividualCM.php';
                      </script>";
                    exit();
                }
                
                // Validar tamaño
                if ($file_size > $maxSize) {
                    echo "<script>
                        alert('Error: El archivo $file_name supera los 2MB');
                        window.location.href = 'formIndividualCM.php';
                      </script>";
                    exit();
                }
                
                // Generar nombre único
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $nuevo_nombre = 'foto_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $upload_dir . $nuevo_nombre;
                
                // Mover archivo
                if (move_uploaded_file($file_tmp, $ruta_destino)) {
                    $fotos_guardadas[] = $nuevo_nombre;
                }
            }
        }
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
                $id_registro = $stmt->insert_id;
                
                // Guardar fotografías asociadas al registro
                if (!empty($fotos_guardadas)) {
                    foreach ($fotos_guardadas as $foto) {
                        $sql_foto = "INSERT INTO fotos_registros_cm (id_registro_individual, ruta_foto, tipo_registro, fecha_subida) 
                                    VALUES (?, ?, 'individual', NOW())";
                        $stmt_foto = $mysqli->prepare($sql_foto);
                        $stmt_foto->bind_param("is", $id_registro, $foto);
                        $stmt_foto->execute();
                        $stmt_foto->close();
                    }
                }
                
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
        if (!empty($fotos_guardadas)) {
            $mensaje .= "\\nFotografías subidas: " . count($fotos_guardadas);
        }
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
        
        // Eliminar fotos si hubo error
        if (!empty($fotos_guardadas)) {
            foreach ($fotos_guardadas as $foto) {
                $ruta = 'uploads/fotos_registros/' . $foto;
                if (file_exists($ruta)) {
                    unlink($ruta);
                }
            }
        }
        
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
