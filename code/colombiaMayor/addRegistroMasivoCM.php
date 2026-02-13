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
                window.location.href = 'formRegistroMasivoCM.php';
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
                        window.location.href = 'formRegistroMasivoCM.php';
                      </script>";
                    exit();
                }
                
                // Validar tamaño
                if ($file_size > $maxSize) {
                    echo "<script>
                        alert('Error: El archivo $file_name supera los 2MB');
                        window.location.href = 'formRegistroMasivoCM.php';
                      </script>";
                    exit();
                }
                
                // Generar nombre único
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $nuevo_nombre = 'foto_masivo_' . time() . '_' . uniqid() . '.' . $extension;
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
        // Insertar registro masivo con campos de género
        $sql = "INSERT INTO registros_masivos_cm 
                (fecha_registro, id_meta, id_actividad, id_accion, id_politica_publica, cantidad_masculino, cantidad_femenino, total_personas, observaciones, usuario_registro) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("siiiiiissi", $fecha_registro, $id_meta, $id_actividad, $id_accion, $id_politica_publica, $cantidad_masculino, $cantidad_femenino, $total_personas, $observaciones, $usuario_registro);
        
        if ($stmt->execute()) {
            $id_registro = $stmt->insert_id;
            
            // Guardar fotografías asociadas al registro
            if (!empty($fotos_guardadas)) {
                foreach ($fotos_guardadas as $foto) {
                    $sql_foto = "INSERT INTO fotos_registros_cm (id_registro_masivo, ruta_foto, tipo_registro, fecha_subida) 
                                VALUES (?, ?, 'masivo', NOW())";
                    $stmt_foto = $mysqli->prepare($sql_foto);
                    $stmt_foto->bind_param("is", $id_registro, $foto);
                    $stmt_foto->execute();
                    $stmt_foto->close();
                }
            }
            
            // Confirmar transacción
            $mysqli->commit();
            
            $mensaje = "Registro masivo guardado correctamente. Total: $total_personas personas (M: $cantidad_masculino, F: $cantidad_femenino)";
            if (!empty($fotos_guardadas)) {
                $mensaje .= "\\nFotografías subidas: " . count($fotos_guardadas);
            }
            
            echo "<script>
                alert('$mensaje');
                window.location.href = 'formRegistroMasivoCM.php';
            </script>";
        } else {
            throw new Exception($mysqli->error);
        }
        
        $stmt->close();
        
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
            alert('Error al guardar el registro masivo: " . $e->getMessage() . "');
            window.location.href = 'formRegistroMasivoCM.php';
        </script>";
    }
}

$mysqli->close();
?>
