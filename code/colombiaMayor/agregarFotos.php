<?php
session_start();

// Verificar que el usuario tenga acceso (tipo 8 o 9)
if (!isset($_SESSION['tipo_usuario']) || !in_array($_SESSION['tipo_usuario'], [8, 9])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_registro']) && isset($_POST['tipo'])) {
    $id_registro = intval($_POST['id_registro']);
    $tipo = $_POST['tipo']; // 'individual' o 'masivo'
    
    // Procesar fotografías
    $fotos_guardadas = [];
    if (isset($_FILES['fotografias']) && !empty($_FILES['fotografias']['name'][0])) {
        $upload_dir = 'uploads/fotos_registros/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Contar fotos existentes
        $sql_count = "";
        if ($tipo === 'individual') {
            $sql_count = "SELECT COUNT(*) as total FROM fotos_registros_cm WHERE id_registro_individual = ?";
        } else {
            $sql_count = "SELECT COUNT(*) as total FROM fotos_registros_cm WHERE id_registro_masivo = ?";
        }
        
        $stmt_count = $mysqli->prepare($sql_count);
        $stmt_count->bind_param("i", $id_registro);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $fotos_existentes = $row_count['total'];
        $stmt_count->close();
        
        $maxFiles = 3;
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        
        $totalFiles = count($_FILES['fotografias']['name']);
        
        // Validar que no se exceda el límite total
        if (($fotos_existentes + $totalFiles) > $maxFiles) {
            echo json_encode([
                'success' => false, 
                'message' => "Ya tienes $fotos_existentes foto(s). Solo puedes agregar " . ($maxFiles - $fotos_existentes) . " más."
            ]);
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
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Solo se permiten archivos JPG, JPEG y PNG'
                    ]);
                    exit();
                }
                
                // Validar tamaño
                if ($file_size > $maxSize) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "El archivo $file_name supera los 2MB"
                    ]);
                    exit();
                }
                
                // Generar nombre único
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $nuevo_nombre = 'foto_' . $tipo . '_' . time() . '_' . uniqid() . '.' . $extension;
                $ruta_destino = $upload_dir . $nuevo_nombre;
                
                // Mover archivo
                if (move_uploaded_file($file_tmp, $ruta_destino)) {
                    // Guardar en base de datos
                    if ($tipo === 'individual') {
                        $sql = "INSERT INTO fotos_registros_cm (id_registro_individual, ruta_foto, tipo_registro, fecha_subida) 
                                VALUES (?, ?, 'individual', NOW())";
                    } else {
                        $sql = "INSERT INTO fotos_registros_cm (id_registro_masivo, ruta_foto, tipo_registro, fecha_subida) 
                                VALUES (?, ?, 'masivo', NOW())";
                    }
                    
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("is", $id_registro, $nuevo_nombre);
                    
                    if ($stmt->execute()) {
                        $fotos_guardadas[] = $nuevo_nombre;
                    } else {
                        // Eliminar archivo si no se guardó en BD
                        if (file_exists($ruta_destino)) {
                            unlink($ruta_destino);
                        }
                    }
                    
                    $stmt->close();
                }
            }
        }
    }
    
    if (count($fotos_guardadas) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => count($fotos_guardadas) . ' fotografía(s) agregada(s) correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'No se pudo agregar ninguna fotografía'
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}

$mysqli->close();
?>
