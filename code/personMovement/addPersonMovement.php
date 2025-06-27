<?php
include("../../conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturar datos del formulario
    $id_usuario = $_SESSION['id'];
    $cedula_persona = $_POST['cedula_persona'];
    $id_condicion = $_POST['id_condicion'];
    $id_grupo = $_POST['id_grupo'];
    $fecha_movimiento = $_POST['fecha_movimiento'];
    $observacion_movimiento = $_POST['observacion_movimiento'];

    // Verificar el límite del grupo
    $query_limite = "SELECT limite_personas FROM grupos WHERE id_grupo = '$id_grupo'";
    $result_limite = $mysqli->query($query_limite);
    $limite_grupo = $result_limite->fetch_assoc()['limite_personas'];

    // Contar personas actuales en el grupo
    $query_count = "SELECT COUNT(*) as total FROM personas WHERE id_grupo = '$id_grupo' AND estado_persona = 1";
    $result_count = $mysqli->query($query_count);
    $personas_actuales = $result_count->fetch_assoc()['total'];

    if ($personas_actuales >= $limite_grupo) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: 'El grupo ha alcanzado su límite máximo de " . $limite_grupo . " personas',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'seePersonMovement.php';
                });
            });
          </script>";
        exit;
    }

    // Actualizar el grupo de la persona
    $sql_update_persona = "UPDATE personas SET id_grupo = '$id_grupo' WHERE cedula_persona = '$cedula_persona'";
    $mysqli->query($sql_update_persona);

    $sql_insert_movimiento = "INSERT INTO movimiento_persona (cedula_persona, id_condicion, fecha_movimiento, observacion_movimiento)
    VALUES ('$cedula_persona', '$id_condicion', '$fecha_movimiento', '$observacion_movimiento')";

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
                    text: 'Error al agregar movimiento: " . addslashes($mysqli->error) . "',
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
