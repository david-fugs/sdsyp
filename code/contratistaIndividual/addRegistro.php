<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
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
            exit;
        }

        // Actualizar el grupo de la persona si se especificó traslado
        $sql_update_persona = "UPDATE personas SET id_grupo = '$id_centro_vida_traslado' WHERE cedula_persona = '$cedula_persona'";
        $mysqli->query($sql_update_persona);
    }

    $sql_insert_movimiento = "INSERT INTO registro_individual (cedula_persona, id_condicion, id_centro_vida_traslado, fecha_registro, observacion_registro, id_meta, id_actividad, id_accion, departamento_procedencia,id_politica_publica,id_usuario)
    VALUES ('$cedula_persona', '$id_condicion', " . ($id_centro_vida_traslado ? "'$id_centro_vida_traslado'" : "0") . ", '$fecha_movimiento', '$observacion_movimiento', '$id_meta', '$id_actividad', '$id_accion', '$departamento_procedencia','$politica_publica','$id_usuario')";

    // Ejecutar consulta
    if ($mysqli->query($sql_insert_movimiento)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Movimiento agregado correctamente',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'form.php';
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
                    text: 'Error al agregar movimiento: " . addslashes($mysqli->error) . "',
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
