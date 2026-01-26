<?php
session_start();
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una edición o inserción
    $id_actividad = isset($_POST['id_actividad_personalizada']) && !empty($_POST['id_actividad_personalizada']) 
                    ? intval($_POST['id_actividad_personalizada']) 
                    : null;
    
    // Recoger datos del formulario
    $hora_inicio = $_POST['hora_inicio'];
    $hora_finalizacion = $_POST['hora_finalizacion'];
    $fecha_actividad = $_POST['fecha_actividad'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $genero = $_POST['genero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = $_POST['numero_documento'];
    
    // Condiciones (checkboxes)
    $desplazado = isset($_POST['desplazado']) ? 1 : 0;
    $cabeza_hogar_mujer = isset($_POST['cabeza_hogar_mujer']) ? 1 : 0;
    $cabeza_hogar_hombre = isset($_POST['cabeza_hogar_hombre']) ? 1 : 0;
    $habitante_calle = isset($_POST['habitante_calle']) ? 1 : 0;
    
    $orientacion_sexual = $_POST['orientacion_sexual'] ?? null;
    $tipo_discapacidad = $_POST['tipo_discapacidad'] ?? null;
    $migrante = $_POST['migrante'] ?? null;
    
    // Etnia
    $mestizo = $_POST['mestizo'] ?? null;
    $afrodescendiente = $_POST['afrodescendiente'] ?? null;
    $indigena = $_POST['indigena'] ?? null;
    
    // Información adicional
    $tipo_seguridad_salud = $_POST['tipo_seguridad_salud'] ?? null;
    $condicion_ocupacional = $_POST['condicion_ocupacional'] ?? null;
    $nivel_estudio = $_POST['nivel_estudio'] ?? null;
    $telefono_celular = $_POST['telefono_celular'] ?? null;
    
    // Actividad
    $nombre_actividad = $_POST['nombre_actividad'];
    $evento_tema = $_POST['evento_tema'] ?? null;
    $numero_actividades = $_POST['numero_actividades'] ?? 1;
    
    // Beneficiados
    $total_masculino = $_POST['total_masculino'] ?? 0;
    $total_femenino = $_POST['total_femenino'] ?? 0;
    
    // Firma (solo actualizar si viene en el POST)
    $firma_data = $_POST['firma_data'] ?? null;
    
    // Usuario que registra
    $usuario_registro = $_SESSION['id'] ?? null;
    
    if ($id_actividad) {
        // ACTUALIZAR registro existente
        $query = "UPDATE actividad_personalizada SET
            hora_inicio = ?, hora_finalizacion = ?, fecha_actividad = ?, nombres_apellidos = ?, genero = ?, 
            fecha_nacimiento = ?, tipo_documento = ?, numero_documento = ?, desplazado = ?, cabeza_hogar_mujer = ?, 
            cabeza_hogar_hombre = ?, habitante_calle = ?, orientacion_sexual = ?, tipo_discapacidad = ?, migrante = ?,
            mestizo = ?, afrodescendiente = ?, indigena = ?, tipo_seguridad_salud = ?, condicion_ocupacional = ?,
            nivel_estudio = ?, telefono_celular = ?, nombre_actividad = ?, evento_tema = ?, numero_actividades = ?,
            total_masculino = ?, total_femenino = ?";
        
        // Solo actualizar firma si se proporciona una nueva
        if (!empty($firma_data)) {
            $query .= ", firma_data = ?";
        }
        
        $query .= " WHERE id_actividad_personalizada = ?";
        
        $stmt = $mysqli->prepare($query);
        
        if ($stmt) {
            if (!empty($firma_data)) {
                $stmt->bind_param(
                    "ssssssssiiiissssssssssssiisi",
                    $hora_inicio, $hora_finalizacion, $fecha_actividad, $nombres_apellidos, $genero,
                    $fecha_nacimiento, $tipo_documento, $numero_documento, $desplazado, $cabeza_hogar_mujer,
                    $cabeza_hogar_hombre, $habitante_calle, $orientacion_sexual, $tipo_discapacidad, $migrante,
                    $mestizo, $afrodescendiente, $indigena, $tipo_seguridad_salud, $condicion_ocupacional,
                    $nivel_estudio, $telefono_celular, $nombre_actividad, $evento_tema, $numero_actividades,
                    $total_masculino, $total_femenino, $firma_data, $id_actividad
                );
            } else {
                $stmt->bind_param(
                    "ssssssssiiiiisssssssssssiiii",
                    $hora_inicio, $hora_finalizacion, $fecha_actividad, $nombres_apellidos, $genero,
                    $fecha_nacimiento, $tipo_documento, $numero_documento, $desplazado, $cabeza_hogar_mujer,
                    $cabeza_hogar_hombre, $habitante_calle, $orientacion_sexual, $tipo_discapacidad, $migrante,
                    $mestizo, $afrodescendiente, $indigena, $tipo_seguridad_salud, $condicion_ocupacional,
                    $nivel_estudio, $telefono_celular, $nombre_actividad, $evento_tema, $numero_actividades,
                    $total_masculino, $total_femenino, $id_actividad
                );
            }
            
            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'El registro ha sido actualizado correctamente.',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            window.location.href = 'formActividadPersonalizada.php';
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al actualizar el registro: " . addslashes($stmt->error) . "',
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            window.location.href = 'formActividadPersonalizada.php';
                        });
                    });
                </script>";
            }
            $stmt->close();
        }
    } else {
        // INSERTAR nuevo registro
        $query = "INSERT INTO actividad_personalizada (
            hora_inicio, hora_finalizacion, fecha_actividad, nombres_apellidos, genero, 
            fecha_nacimiento, tipo_documento, numero_documento, desplazado, cabeza_hogar_mujer, 
            cabeza_hogar_hombre, habitante_calle, orientacion_sexual, tipo_discapacidad, migrante,
            mestizo, afrodescendiente, indigena, tipo_seguridad_salud, condicion_ocupacional,
            nivel_estudio, telefono_celular, nombre_actividad, evento_tema, numero_actividades,
            total_masculino, total_femenino, firma_data, usuario_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $mysqli->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param(
                "ssssssssiiiissssssssssssiiisi",
                $hora_inicio, $hora_finalizacion, $fecha_actividad, $nombres_apellidos, $genero,
                $fecha_nacimiento, $tipo_documento, $numero_documento, $desplazado, $cabeza_hogar_mujer,
                $cabeza_hogar_hombre, $habitante_calle, $orientacion_sexual, $tipo_discapacidad, $migrante,
                $mestizo, $afrodescendiente, $indigena, $tipo_seguridad_salud, $condicion_ocupacional,
                $nivel_estudio, $telefono_celular, $nombre_actividad, $evento_tema, $numero_actividades,
                $total_masculino, $total_femenino, $firma_data, $usuario_registro
            );
            
            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'El registro ha sido guardado correctamente.',
                            confirmButtonColor: '#667eea'
                        }).then(() => {
                            window.location.href = 'formActividadPersonalizada.php';
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al guardar el registro: " . addslashes($stmt->error) . "',
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            window.location.href = 'formActividadPersonalizada.php';
                        });
                    });
                </script>";
            }
            
            $stmt->close();
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la preparación de la consulta: " . addslashes($mysqli->error) . "',
                        confirmButtonColor: '#d33'
                    }).then(() => {
                        window.location.href = 'formActividadPersonalizada.php';
                    });
                });
            </script>";
        }
    }
    
    $mysqli->close();
} else {
    header("Location: formActividadPersonalizada.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body></body>
</html>
