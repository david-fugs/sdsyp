<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $cedula_original = $_POST['cedula_original'];
    $fecha_movimiento = $_POST['fecha_movimiento'];
    $observacion_movimiento = $_POST['observacion_movimiento'];
    $id_condicion = $_POST['id_condicion'];
    $id_centro_vida_traslado = isset($_POST['id_centro_vida_traslado']) ? $_POST['id_centro_vida_traslado'] : null;
    $id_movimiento_persona = $_POST['id_movimiento_persona'];

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

        // No contar la persona actual si ya estaba en el grupo (para evitar double counting en edición)
        $query_persona_actual = "SELECT id_grupo FROM personas WHERE cedula_persona = '$cedula_original'";
        $result_persona_actual = $mysqli->query($query_persona_actual);
        $grupo_actual = $result_persona_actual->fetch_assoc()['id_grupo'];
        
        // Si la persona ya estaba en el mismo grupo, no incrementar el contador
        if ($grupo_actual == $id_centro_vida_traslado) {
            $personas_actuales--;
        }

        if ($personas_actuales >= $limite_grupo) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite alcanzado',
                        text: 'El centro de vida ha alcanzado su límite máximo de " . $limite_grupo . " personas',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = 'seePersonMovement.php';
                    });
                });
              </script>";
            return;
        }
    }

    //actualizar movimiento_persona
    $sql_update_movimiento = "UPDATE movimiento_persona SET 
                             cedula_persona = '$cedula_persona', 
                             fecha_movimiento = '$fecha_movimiento', 
                             observacion_movimiento = '$observacion_movimiento', 
                             id_condicion = '$id_condicion',
                             id_centro_vida_traslado = " . ($id_centro_vida_traslado ? "'$id_centro_vida_traslado'" : "0") . "
                             WHERE id_movimiento_persona = '$id_movimiento_persona'";
    
    // Si se especificó un centro de vida para traslado, actualizar también el grupo de la persona
    if ($id_centro_vida_traslado) {
        $sql_update_persona = "UPDATE personas SET id_grupo = '$id_centro_vida_traslado' WHERE cedula_persona = '$cedula_persona'";
        $mysqli->query($sql_update_persona);
    }
    //ejecutar consulta
    if ($mysqli->query($sql_update_movimiento)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Movimiento actualizado correctamente',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seePersonMovement.php';
                });
            });
          </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar movimiento: " . addslashes($mysqli->error) . "',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seePersonMovement.php';
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
                window.location.href = 'seePersonMovement.php';
            });
        });
      </script>";
}
