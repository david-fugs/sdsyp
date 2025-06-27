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
    $id_movimiento_persona = $_POST['id_movimiento_persona'];

    //actualizar movimiento_persona
    $sql_update_movimiento = "UPDATE movimiento_persona SET cedula_persona = '$cedula_persona', fecha_movimiento = '$fecha_movimiento', observacion_movimiento = '$observacion_movimiento' , id_condicion = '$id_condicion'  WHERE id_movimiento_persona = '$id_movimiento_persona'";
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
