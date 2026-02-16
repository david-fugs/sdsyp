<?php
session_start();
include("../../conexion.php");

// Recoger datos del formulario
$id_meta = isset($_POST['id_meta']) ? intval($_POST['id_meta']) : 0;
$id_actividad = isset($_POST['id_actividad']) ? intval($_POST['id_actividad']) : 0;
$id_accion = isset($_POST['id_accion']) ? intval($_POST['id_accion']) : 0;
$politica_publica = isset($_POST['politica_publica']) ? mysqli_real_escape_string($mysqli, $_POST['politica_publica']) : '';
// Si id_centro_vida es "OTRO", convertirlo a NULL (0)
$id_centro_vida_raw = isset($_POST['id_centro_vida']) ? $_POST['id_centro_vida'] : '';
$id_centro_vida = ($id_centro_vida_raw === 'OTRO' || $id_centro_vida_raw === '') ? 0 : intval($id_centro_vida_raw);
$fecha_atencion = isset($_POST['fecha_atencion']) ? mysqli_real_escape_string($mysqli, $_POST['fecha_atencion']) : '';
$nombre_lider = isset($_POST['nombre_lider']) ? mysqli_real_escape_string($mysqli, $_POST['nombre_lider']) : '';
$telefono_contacto = isset($_POST['telefono_contacto']) ? mysqli_real_escape_string($mysqli, $_POST['telefono_contacto']) : '';
$id_comuna = isset($_POST['id_comuna']) ? intval($_POST['id_comuna']) : 0;
$medio_verificacion = isset($_POST['medio_verificacion']) ? mysqli_real_escape_string($mysqli, $_POST['medio_verificacion']) : '';
$cantidad_masculino = isset($_POST['cantidad_masculino']) ? intval($_POST['cantidad_masculino']) : 0;
$cantidad_femenino = isset($_POST['cantidad_femenino']) ? intval($_POST['cantidad_femenino']) : 0;
$tipo_actividad = isset($_POST['tipo_actividad']) ? mysqli_real_escape_string($mysqli, $_POST['tipo_actividad']) : '';
$observacion_actividad = isset($_POST['observacion_actividad']) ? mysqli_real_escape_string($mysqli, $_POST['observacion_actividad']) : '';
$id_usuario = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
$funcionario_responsable = isset($_POST['funcionario_responsable']) ? mysqli_real_escape_string($mysqli, $_POST['funcionario_responsable']) : '';
$id_entregas = isset($_POST['id_entregas']) ? intval($_POST['id_entregas']) : 0;
$otro_lugar = isset($_POST['otro_lugar']) ? mysqli_real_escape_string($mysqli, $_POST['otro_lugar']) : '';
$cedulas_json = isset($_POST['cedulas_json']) ? $_POST['cedulas_json'] : '';

// Consulta plana para insertar el registro de actividad masiva
$query = "INSERT INTO registro_actividades (
    id_meta,
    id_actividad,
    id_accion,
    politica_publica,
    id_centro_vida,
    fecha_atencion,
    nombre_lider,
    telefono_contacto,
    id_comuna,
    medio_verificacion,
    cantidad_masculino,
    cantidad_femenino,
    tipo_actividad,
    observacion_actividad,
    id_usuario,
    funcionario_responsable,
    id_entregas,
    otro_lugar
) VALUES (
    $id_meta,
    $id_actividad,
    $id_accion,
    '$politica_publica',
    $id_centro_vida,
    '$fecha_atencion',
    '$nombre_lider',
    '$telefono_contacto',
    $id_comuna,
    '$medio_verificacion',
    $cantidad_masculino,
    $cantidad_femenino,
    '$tipo_actividad',
    '$observacion_actividad',
    $id_usuario,
    '$funcionario_responsable',
    $id_entregas,
    '$otro_lugar'
)";

$result = mysqli_query($mysqli, $query);

if ($result) {
    // Si es "Registro de Actividad" y hay cédulas, crear registros individuales
    if ($tipo_actividad === 'Registro de Actividad' && !empty($cedulas_json)) {
        $cedulas = json_decode($cedulas_json, true);
        
        if (is_array($cedulas) && count($cedulas) > 0) {
            // Obtener el ID de condición por defecto "CPSAM Activo"
            $query_condicion = "SELECT id_condicion FROM condiciones_componente WHERE descripcion_condicion = 'CPSAM Activo' LIMIT 1";
            $result_condicion = $mysqli->query($query_condicion);
            $id_condicion_defecto = 1; // Valor por defecto si no encuentra
            if ($result_condicion && $result_condicion->num_rows > 0) {
                $row_cond = $result_condicion->fetch_assoc();
                $id_condicion_defecto = $row_cond['id_condicion'];
            }
            
            $registros_creados = 0;
            $registros_fallidos = 0;
            
            foreach ($cedulas as $cedula) {
                $cedula_escape = $mysqli->real_escape_string($cedula);
                
                // Insertar registro individual para esta persona
                $sql_individual = "INSERT INTO registro_individual (
                    cedula_persona, 
                    id_condicion, 
                    id_centro_vida_traslado, 
                    fecha_registro, 
                    observacion_registro, 
                    id_meta, 
                    id_actividad, 
                    id_accion, 
                    departamento_procedencia, 
                    id_politica_publica, 
                    id_usuario
                ) VALUES (
                    '$cedula_escape',
                    $id_condicion_defecto,
                    " . ($id_centro_vida ? $id_centro_vida : "0") . ",
                    '$fecha_atencion',
                    '$observacion_actividad',
                    $id_meta,
                    $id_actividad,
                    $id_accion,
                    '',
                    " . ($politica_publica ? intval($politica_publica) : "NULL") . ",
                    $id_usuario
                )";
                
                if ($mysqli->query($sql_individual)) {
                    $registros_creados++;
                } else {
                    $registros_fallidos++;
                }
            }
            
            // Preparar mensaje detallado con SweetAlert2
            $mensaje_html = "<div style='text-align: left;'>";
            $mensaje_html .= "<p><strong>✓ Registro de actividad guardado correctamente</strong></p>";
            $mensaje_html .= "<hr style='margin: 10px 0;'>";
            $mensaje_html .= "<p><i class='bi bi-gender-male'></i> <strong>Cantidad Masculino:</strong> $cantidad_masculino</p>";
            $mensaje_html .= "<p><i class='bi bi-gender-female'></i> <strong>Cantidad Femenino:</strong> $cantidad_femenino</p>";
            $mensaje_html .= "<p><i class='bi bi-people-fill'></i> <strong>Total Personas:</strong> " . ($cantidad_masculino + $cantidad_femenino) . "</p>";
            $mensaje_html .= "<hr style='margin: 10px 0;'>";
            $mensaje_html .= "<p><i class='bi bi-check-circle-fill text-success'></i> <strong>Registros individuales creados:</strong> $registros_creados de " . count($cedulas) . "</p>";
            if ($registros_fallidos > 0) {
                $mensaje_html .= "<p><i class='bi bi-exclamation-circle-fill text-warning'></i> <strong>Registros fallidos:</strong> $registros_fallidos</p>";
            }
            $mensaje_html .= "</div>";
            
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actividad Registrada',
                        html: `" . addslashes($mensaje_html) . "`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then(function() {
                        window.location.href = 'form.php';
                    });
                });
              </script>";
        } else {
            // Sin cédulas pero es Registro de Actividad
            $mensaje_html = "<div style='text-align: left;'>";
            $mensaje_html .= "<p><strong>✓ Registro de actividad guardado correctamente</strong></p>";
            $mensaje_html .= "<hr style='margin: 10px 0;'>";
            $mensaje_html .= "<p><i class='bi bi-gender-male'></i> <strong>Cantidad Masculino:</strong> $cantidad_masculino</p>";
            $mensaje_html .= "<p><i class='bi bi-gender-female'></i> <strong>Cantidad Femenino:</strong> $cantidad_femenino</p>";
            $mensaje_html .= "<p><i class='bi bi-people-fill'></i> <strong>Total Personas:</strong> " . ($cantidad_masculino + $cantidad_femenino) . "</p>";
            $mensaje_html .= "</div>";
            
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actividad Registrada',
                        html: `" . addslashes($mensaje_html) . "`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then(function() {
                        window.location.href = 'form.php';
                    });
                });
              </script>";
        }
    } else {
        // No es "Registro de Actividad"
        $mensaje_html = "<div style='text-align: left;'>";
        $mensaje_html .= "<p><strong>✓ Registro de actividad guardado correctamente</strong></p>";
        $mensaje_html .= "<hr style='margin: 10px 0;'>";
        $mensaje_html .= "<p><i class='bi bi-gender-male'></i> <strong>Cantidad Masculino:</strong> $cantidad_masculino</p>";
        $mensaje_html .= "<p><i class='bi bi-gender-female'></i> <strong>Cantidad Femenino:</strong> $cantidad_femenino</p>";
        $mensaje_html .= "<p><i class='bi bi-people-fill'></i> <strong>Total Personas:</strong> " . ($cantidad_masculino + $cantidad_femenino) . "</p>";
        $mensaje_html .= "</div>";
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Actividad Registrada',
                    html: `" . addslashes($mensaje_html) . "`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745'
                }).then(function() {
                    window.location.href = 'form.php';
                });
            });
          </script>";
    }
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar el registro: " . addslashes(mysqli_error($mysqli)) . "',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = 'form.php';
            });
        });
      </script>";
}

mysqli_close($mysqli);
?>
