<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    
    // Verificar si se envió un array de cédulas (múltiples personas)
    $cedulas_json = isset($_POST['cedulas_json']) ? $_POST['cedulas_json'] : null;
    $cedulas_array = $cedulas_json ? json_decode($cedulas_json, true) : null;
    
    // Si no hay cédulas múltiples, usar cédula individual (modo antiguo)
    if (!$cedulas_array || empty($cedulas_array)) {
        $cedulas_array = isset($_POST['cedula_persona']) ? [$_POST['cedula_persona']] : [];
    }
    
    $id_condicion = $_POST['id_condicion'];
    $id_centro_vida_traslado = isset($_POST['id_centro_vida_traslado']) ? $_POST['id_centro_vida_traslado'] : null;
    $fecha_movimiento = $_POST['fecha_movimiento'];
    $observacion_movimiento = $_POST['observacion_movimiento'];
    $politica_publica = isset($_POST['politica_publica']) ? $_POST['politica_publica'] : null;
    $id_usuario = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    
    // Nuevos campos
    $id_meta = $_POST['id_meta'];
    $id_actividad = $_POST['id_actividad'];
    $id_accion = $_POST['id_accion'];
    $departamento_procedencia = $_POST['departamento_procedencia'];

    // Validar que haya al menos una cédula
    if (empty($cedulas_array)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin personas',
                    text: 'Debe agregar al menos una persona para crear el registro',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'form.php';
                });
            });
          </script>";
        exit;
    }

    // Contador de registros creados
    $registros_creados = 0;
    $errores = [];

    // Procesar cada cédula
    foreach ($cedulas_array as $cedula_persona) {
        $cedula_persona = trim($cedula_persona);
        
        if (empty($cedula_persona)) {
            continue;
        }

        // Solo validar límite si se especificó un centro de vida para traslado
        if ($id_centro_vida_traslado) {
            // Verificar el límite del grupo
            $query_limite = "SELECT limite_personas FROM grupos WHERE id_grupo = '$id_centro_vida_traslado'";
            $result_limite = $mysqli->query($query_limite);
            $limite_grupo = $result_limite->fetch_assoc()['limite_personas'];

            // Contar personas actuales en el grupo (excluyendo las que tienen movimientos que liberan cupo)
            $query_count = "SELECT COUNT(*) as total 
                           FROM personas p
                           WHERE p.id_grupo = '$id_centro_vida_traslado' 
                           AND p.estado_persona = 1
                           AND p.cedula_persona NOT IN (
                               SELECT DISTINCT mp.cedula_persona 
                               FROM movimiento_persona mp
                               JOIN condiciones_componente cc ON mp.id_condicion = cc.id_condicion
                               WHERE cc.descripcion_condicion IN (
                                   'CPSAM EVADIDO', 
                                   'CPSAM FALLECIDO', 
                                   'CPSAM RETIRADO VOLUNTARIO', 
                                   'CPSAM TRASLADADO'
                               )
                           )";
            $result_count = $mysqli->query($query_count);
            $personas_actuales = $result_count->fetch_assoc()['total'];

            if ($personas_actuales >= $limite_grupo) {
                $errores[] = "Cédula $cedula_persona: Límite del centro alcanzado";
                continue;
            }

            // Actualizar el grupo de la persona si se especificó traslado
            $sql_update_persona = "UPDATE personas SET id_grupo = '$id_centro_vida_traslado' WHERE cedula_persona = '$cedula_persona'";
            $mysqli->query($sql_update_persona);
        }

        $sql_insert_movimiento = "INSERT INTO registro_individual (cedula_persona, id_condicion, id_centro_vida_traslado, fecha_registro, observacion_registro, id_meta, id_actividad, id_accion, departamento_procedencia, id_politica_publica, id_usuario)
        VALUES ('$cedula_persona', '$id_condicion', " . ($id_centro_vida_traslado ? "'$id_centro_vida_traslado'" : "0") . ", '$fecha_movimiento', '$observacion_movimiento', '$id_meta', '$id_actividad', '$id_accion', '$departamento_procedencia','$politica_publica','$id_usuario')";

        // Ejecutar consulta
        if ($mysqli->query($sql_insert_movimiento)) {
            $registros_creados++;
        } else {
            $errores[] = "Cédula $cedula_persona: " . $mysqli->error;
        }
    }

    // Mostrar resultado final
    if ($registros_creados > 0) {
        // Preparar mensaje detallado con SweetAlert2
        $mensaje_html = "<div style='text-align: left;'>";
        $mensaje_html .= "<p><strong>✓ Registro(s) individual(es) creado(s) correctamente</strong></p>";
        $mensaje_html .= "<hr style='margin: 10px 0;'>";
        $mensaje_html .= "<p><i class='bi bi-people-fill'></i> <strong>Total de registros creados:</strong> $registros_creados</p>";
        
        if (!empty($errores)) {
            $mensaje_html .= "<hr style='margin: 10px 0;'>";
            $mensaje_html .= "<p class='text-warning'><i class='bi bi-exclamation-triangle-fill'></i> <strong>Algunos registros presentaron errores:</strong></p>";
            $mensaje_html .= "<div style='font-size: 0.9em;'>" . implode("<br>", $errores) . "</div>";
        }
        $mensaje_html .= "</div>";

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Registros Creados',
                    html: `" . addslashes($mensaje_html) . "`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745'
                }).then(function() {
                    window.location.href = 'form.php';
                });
            });
          </script>";
    } else {
        $mensaje_html = "<div style='text-align: left;'>";
        $mensaje_html .= "<p><strong>No se pudo crear ningún registro</strong></p>";
        if (!empty($errores)) {
            $mensaje_html .= "<hr style='margin: 10px 0;'>";
            $mensaje_html .= "<p><i class='bi bi-x-circle-fill'></i> <strong>Errores encontrados:</strong></p>";
            $mensaje_html .= "<div style='font-size: 0.9em;'>" . implode("<br>", $errores) . "</div>";
        }
        $mensaje_html .= "</div>";
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: `" . addslashes($mensaje_html) . "`,
                    confirmButtonText: 'OK'
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
                text: 'Método no válido',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = 'form.php';
            });
        });
      </script>";
}
